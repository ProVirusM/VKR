<?php

namespace App\Controller;

use App\Entity\Customers;
use App\Repository\CustomersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/customers')]
class CustomersController extends AbstractController
{
    /**
     * Получить всех заказчиков (GET /api/customers)
     */
    #[Route('/', name: 'customers_index', methods: ['GET'])]
    public function index(CustomersRepository $customersRepository): JsonResponse
    {
        $customers = $customersRepository->findAll();

        $data = [];
        foreach ($customers as $customer) {
            $data[] = $this->serializeCustomer($customer);
        }

        return $this->json($data);
    }

    /**
     * Создать нового заказчика (POST /api/customers)
     */
    #[Route('/', name: 'customers_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательное поле user_id
        if (!isset($data['user_id'])) {
            return $this->json(['error' => 'Missing "user_id" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $customer = new Customers();

        // В реальном приложении нужно загрузить сущность User
        // $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        // $customer->setUsrId($user);

        // Временно используем сеттер с ID
        $customer->setUsrId($data['user_id']);

        $entityManager->persist($customer);
        $entityManager->flush();

        return $this->json(
            $this->serializeCustomer($customer),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Получить заказчика по ID (GET /api/customers/{cst_id})
     */
    #[Route('/{cst_id}', name: 'customers_show', methods: ['GET'])]
    #[ParamConverter('customer', options: ['mapping' => ['cst_id' => 'id']])]
    public function show(Customers $customer): JsonResponse
    {
        return $this->json($this->serializeCustomer($customer));
    }

    /**
     * Обновить заказчика (PUT /api/customers/{cst_id})
     */
    #[Route('/{cst_id}', name: 'customers_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('customer', options: ['mapping' => ['cst_id' => 'id']])]
    public function edit(Request $request, Customers $customer, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['user_id'])) {
            // Загрузить сущность User и установить
            $customer->setUsrId($data['user_id']);
        }

        $entityManager->flush();

        return $this->json($this->serializeCustomer($customer));
    }

    /**
     * Удалить заказчика (DELETE /api/customers/{cst_id})
     */
    #[Route('/{cst_id}', name: 'customers_delete', methods: ['DELETE'])]
    #[ParamConverter('customer', options: ['mapping' => ['cst_id' => 'id']])]
    public function delete(Customers $customer, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();

        return $this->json(['message' => 'Customer deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Сериализация заказчика в массив
     */
    private function serializeCustomer(Customers $customer): array
    {
        $chats = [];
        foreach ($customer->getChats() as $chat) {
            $chats[] = [
                'chat_id' => $chat->getId(),
                // добавьте другие поля чата по необходимости
            ];
        }

        $orders = [];
        foreach ($customer->getOrders() as $order) {
            $orders[] = [
                'order_id' => $order->getId(),
                // добавьте другие поля заказа по необходимости
            ];
        }

        $feedbacks = [];
        foreach ($customer->getFeedbacks() as $feedback) {
            $feedbacks[] = [
                'feedback_id' => $feedback->getId(),
                'estimation' => $feedback->getFdbEstimation()
            ];
        }

        return [
            'id' => $customer->getId(),
            'user_id' => $customer->getUsrId()?->getId(),
            'chats' => $chats,
            'orders' => $orders,
            'feedbacks' => $feedbacks,
            'messages_count' => $customer->getMessages()->count()
        ];
    }
}
