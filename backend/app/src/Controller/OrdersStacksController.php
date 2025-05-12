<?php

namespace App\Controller;

use App\Entity\OrdersStacks;
use App\Repository\OrdersStacksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/orders-stacks')]
class OrdersStacksController extends AbstractController
{
    /**
     * Получить все связи заказов и стеков (GET /api/orders-stacks)
     */
    #[Route('/', name: 'orders_stacks_index', methods: ['GET'])]
    public function index(OrdersStacksRepository $ordersStacksRepository): JsonResponse
    {
        $ordersStacks = $ordersStacksRepository->findAll();

        $data = [];
        foreach ($ordersStacks as $orderStack) {
            $data[] = [
                'id' => $orderStack->getId(),
                'order_id' => $orderStack->getOrdId()?->getId(),
                'stack_id' => $orderStack->getStcId()?->getId(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новую связь заказа и стека (POST /api/orders-stacks)
     */
    #[Route('/', name: 'orders_stacks_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['order_id', 'stack_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $orderStack = new OrdersStacks();

        // В реальном приложении нужно загрузить сущности Order и Stack
        // $order = $entityManager->getRepository(Orders::class)->find($data['order_id']);
        // $stack = $entityManager->getRepository(Stacks::class)->find($data['stack_id']);
        // $orderStack->setOrdId($order);
        // $orderStack->setStcId($stack);

        // Временно используем сеттеры с ID
        $orderStack->setOrdId($data['order_id']);
        $orderStack->setStcId($data['stack_id']);

        $entityManager->persist($orderStack);
        $entityManager->flush();

        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить связь по ID (GET /api/orders-stacks/{ord_stc_id})
     */
    #[Route('/{ord_stc_id}', name: 'orders_stacks_show', methods: ['GET'])]
    #[ParamConverter('orderStack', options: ['mapping' => ['ord_stc_id' => 'id']])]
    public function show(OrdersStacks $orderStack): JsonResponse
    {
        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
        ]);
    }

    /**
     * Обновить связь (PUT /api/orders-stacks/{ord_stc_id})
     */
    #[Route('/{ord_stc_id}', name: 'orders_stacks_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('orderStack', options: ['mapping' => ['ord_stc_id' => 'id']])]
    public function edit(Request $request, OrdersStacks $orderStack, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['order_id'])) {
            // Загрузить сущность Order и установить
            $orderStack->setOrdId($data['order_id']);
        }

        if (isset($data['stack_id'])) {
            // Загрузить сущность Stack и установить
            $orderStack->setStcId($data['stack_id']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
        ]);
    }

    /**
     * Удалить связь (DELETE /api/orders-stacks/{ord_stc_id})
     */
    #[Route('/{ord_stc_id}', name: 'orders_stacks_delete', methods: ['DELETE'])]
    #[ParamConverter('orderStack', options: ['mapping' => ['ord_stc_id' => 'id']])]
    public function delete(OrdersStacks $orderStack, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($orderStack);
        $entityManager->flush();

        return $this->json(['message' => 'Order-Stack relation deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}