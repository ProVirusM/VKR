<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersStacks;
use App\Entity\Stacks;
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
                'stack_title' => $orderStack->getStcId()?->getStcTitle(),
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

        // Загружаем сущности
        $order = $entityManager->getRepository(Orders::class)->find($data['order_id']);
        $stack = $entityManager->getRepository(Stacks::class)->find($data['stack_id']);

        if (!$order || !$stack) {
            return $this->json(['error' => 'Order or Stack not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $orderStack = new OrdersStacks();
        $orderStack->setOrdId($order);
        $orderStack->setStcId($stack);

        $entityManager->persist($orderStack);
        $entityManager->flush();

        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
            'stack_title' => $orderStack->getStcId()?->getStcTitle(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить связь по ID (GET /api/orders-stacks/{id})
     */
    #[Route('/{id}', name: 'orders_stacks_show', methods: ['GET'])]
    public function show(OrdersStacks $orderStack): JsonResponse
    {
        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
            'stack_title' => $orderStack->getStcId()?->getStcTitle(),
        ]);
    }

    /**
     * Обновить связь (PUT /api/orders-stacks/{id})
     */
    #[Route('/{id}', name: 'orders_stacks_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, OrdersStacks $orderStack, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['order_id'])) {
            $order = $entityManager->getRepository(Orders::class)->find($data['order_id']);
            if ($order) {
                $orderStack->setOrdId($order);
            }
        }

        if (isset($data['stack_id'])) {
            $stack = $entityManager->getRepository(Stacks::class)->find($data['stack_id']);
            if ($stack) {
                $orderStack->setStcId($stack);
            }
        }

        $entityManager->flush();

        return $this->json([
            'id' => $orderStack->getId(),
            'order_id' => $orderStack->getOrdId()?->getId(),
            'stack_id' => $orderStack->getStcId()?->getId(),
            'stack_title' => $orderStack->getStcId()?->getStcTitle(),
        ]);
    }

    /**
     * Удалить связь (DELETE /api/orders-stacks/{id})
     */
    #[Route('/{id}', name: 'orders_stacks_delete', methods: ['DELETE'])]
    public function delete(OrdersStacks $orderStack, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($orderStack);
        $entityManager->flush();

        return $this->json(['message' => 'Order-Stack relation deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}