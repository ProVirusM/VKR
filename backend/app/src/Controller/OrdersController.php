<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersStacks;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\CustomersRepository;
use App\Repository\StacksRepository;
#[Route('/api/orders')]
class OrdersController extends AbstractController
{
    /**
     * Получить список всех заказов (GET /api/orders)
     */
    private $customerRepository;  // Делаем переменную доступной в контроллере
    private $stackRepository;
    // Внедряем репозиторий через конструктор
    public function __construct(CustomersRepository $customerRepository, StacksRepository $stackRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->stackRepository = $stackRepository;
    }
    #[Route('/', name: 'orders_index', methods: ['GET'])]
//    public function index(OrdersRepository $ordersRepository): JsonResponse
//    {
//        $orders = $ordersRepository->findAll();
//        $data = [];
//
//        foreach ($orders as $order) {
//            $data[] = [
//                'ord_id' => $order->getId(),
//                'ord_title' => $order->getOrdTitle(),
//                'ord_text' => $order->getOrdText(),
//                'ord_status' => $order->getOrdStatus(),
//                'ord_price' => $order->getOrdPrice(),
//                'ord_time' => $order->getOrdTime(),
//                'customer_id' => $order->getCstId()?->getId(),
//            ];
//        }
//
//        return $this->json($data);
//    }
    #[Route('/', name: 'orders_index', methods: ['GET'])]
    public function index(Request $request, OrdersRepository $ordersRepository): JsonResponse
    {
        // Получаем параметры фильтра
        $direction = $request->query->get('direction');
        $language = $request->query->get('language');

        // Получаем параметр stacks как строку
        $stacks = $request->query->get('stacks', '');

        // Если параметр stacks передан как строка, преобразуем его в массив
        if (is_string($stacks) && !empty($stacks)) {
            $stacks = explode(',', $stacks);  // Преобразуем строку в массив
        }

        // Получаем заказы с фильтрами
        $orders = $ordersRepository->findByFilters($direction, $language, $stacks);

        // Преобразуем заказы в формат JSON
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'title' => $order->getOrdTitle(),
                'text' => $order->getOrdText(),
                'status' => $order->getOrdStatus(),
                'price' => $order->getOrdPrice(),
                'time' => $order->getOrdTime(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Получить один заказ по ID (GET /api/orders/{ord_id})
     */
    #[Route('/{ord_id}', name: 'orders_show', methods: ['GET'])]
    #[ParamConverter('order', options: ['mapping' => ['ord_id' => 'ord_id']])]
    public function show(Orders $order): JsonResponse
    {
        return $this->json([
            'ord_id' => $order->getId(),
            'ord_title' => $order->getOrdTitle(),
            'ord_text' => $order->getOrdText(),
            'ord_status' => $order->getOrdStatus(),
            'ord_price' => $order->getOrdPrice(),
            'ord_time' => $order->getOrdTime(),
            'customer_id' => $order->getCstId()?->getId(),
        ]);
    }

    /**
     * Создать новый заказ (POST /api/orders)
     */
    #[Route('/', name: 'orders_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['ord_title'], $data['ord_text'], $data['ord_status'], $data['ord_price'], $data['ord_time'], $data['cst_id'],$data['ord_stacks'])) {
            return $this->json(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $customer = $this->customerRepository->find($data['cst_id']);

        $order = new Orders();
        $order->setOrdTitle($data['ord_title']);
        $order->setCstId($customer);
        $order->setOrdText($data['ord_text']);
        $order->setOrdStatus($data['ord_status']);
        $order->setOrdPrice($data['ord_price']);
        $order->setOrdTime($data['ord_time']);

        foreach ($data['ord_stacks'] as $stackId) {
            $stack = $this->stackRepository->find($stackId);
            if ($stack) {
                // Создаем OrdersStacks объект и связываем его с заказом и стеком
                $ordersStack = new OrdersStacks();
                $ordersStack->setStcId($stack);  // Связываем стек с объектом OrdersStacks
                $ordersStack->setOrdId($order);  // Связываем заказ с объектом OrdersStacks
                $order->addOrdersStack($ordersStack);  // Добавляем в заказ
            }
        }
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'ord_id' => $order->getId(),
            'ord_title' => $order->getOrdTitle(),
            'ord_text' => $order->getOrdText(),
            'ord_status' => $order->getOrdStatus(),
            'ord_price' => $order->getOrdPrice(),
            'ord_time' => $order->getOrdTime(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Обновить заказ (PUT /api/orders/{ord_id})
     */
    #[Route('/{ord_id}', name: 'orders_update', methods: ['PUT'])]
    #[ParamConverter('order', options: ['mapping' => ['ord_id' => 'ord_id']])]
    public function update(Request $request, Orders $order, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['ord_title'])) {
            $order->setOrdTitle($data['ord_title']);
        }
        if (isset($data['ord_text'])) {
            $order->setOrdText($data['ord_text']);
        }
        if (isset($data['ord_status'])) {
            $order->setOrdStatus($data['ord_status']);
        }
        if (isset($data['ord_price'])) {
            $order->setOrdPrice($data['ord_price']);
        }
        if (isset($data['ord_time'])) {
            $order->setOrdTime($data['ord_time']);
        }

        $entityManager->flush();

        return $this->json([
            'ord_id' => $order->getId(),
            'ord_title' => $order->getOrdTitle(),
            'ord_text' => $order->getOrdText(),
            'ord_status' => $order->getOrdStatus(),
            'ord_price' => $order->getOrdPrice(),
            'ord_time' => $order->getOrdTime(),
        ]);
    }

    /**
     * Удалить заказ (DELETE /api/orders/{ord_id})
     */
    #[Route('/{ord_id}', name: 'orders_delete', methods: ['DELETE'])]
    #[ParamConverter('order', options: ['mapping' => ['ord_id' => 'ord_id']])]
    public function delete(Orders $order, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(['message' => 'Order deleted successfully']);
    }

    #[Route('/{id}/full', name: 'order_full', methods: ['GET'])]
    public function getOrderWithStacks(int $id, OrdersRepository $ordersRepo): JsonResponse
    {
        $order = $ordersRepo->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $stacks = [];
        foreach ($order->getOrdersStacks() as $orderStack) {
            $stack = $orderStack->getStcId();
            $stacks[] = [
                'id' => $stack->getId(),
                'title' => $stack->getStcTitle(),
            ];
        }

        return $this->json([
            'order' => [
                'id' => $order->getId(),
                'ord_title' => $order->getOrdTitle(),
                'ord_text' => $order->getOrdText(),
                'ord_price' => $order->getOrdPrice(),
                'ord_time' => $order->getOrdTime(),
                'ord_status' => $order->getOrdStatus(),
            ],
            'stacks' => $stacks
        ]);
    }
    #[Route('/{id}/contractors', name: 'api_order_contractors', methods: ['GET'])]
    public function getContractors(int $id, OrdersRepository $ordersRepository): JsonResponse
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $result = [];
        foreach ($order->getOrdersContractors() as $orderContractor) {
            $contractor = $orderContractor->getCntId();
            $user = $contractor->getUsrId();

            $result[] = [
                'contractorId' => $contractor->getId(),
                'userName' => $user ? $user->getUsrName() : null,
                'userSurname' => $user ? $user->getUsrSurname() : null,
                'userPatronymic' => $user ? $user->getUsrPatronymic() : null,
                // сюда можно добавить другие нужные поля исполнителя
            ];
        }

        return $this->json($result);
    }


}
