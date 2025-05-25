<?php

namespace App\Controller;

use App\Entity\OrdersContractors;
use App\Entity\Orders;
use App\Entity\Contractors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/orders')]
class OrderResponseController extends AbstractController
{
    #[Route('/{id}/respond', name: 'api_order_respond', methods: ['POST'])]
    //#[IsGranted('contractor')]
    public function respond(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $contractor = $user->getContractors();

        if (!$contractor) {
            return new JsonResponse(['message' => 'Исполнитель не найден'], Response::HTTP_NOT_FOUND);
        }

        $order = $entityManager->getRepository(Orders::class)->find($id);
        if (!$order) {
            return new JsonResponse(['message' => 'Заказ не найден'], Response::HTTP_NOT_FOUND);
        }

        // Проверяем, не откликнулся ли уже исполнитель
        $existingResponse = $entityManager->getRepository(OrdersContractors::class)
            ->findOneBy(['ord_id' => $order, 'cnt_id' => $contractor]);

        if ($existingResponse) {
            return new JsonResponse(['message' => 'Вы уже откликнулись на этот заказ'], Response::HTTP_BAD_REQUEST);
        }

        // Создаем новый отклик
        $orderContractor = new OrdersContractors();
        $orderContractor->setOrdId($order);
        $orderContractor->setCntId($contractor);
        $orderContractor->setOrdCntStatus('Ожидает');

        $entityManager->persist($orderContractor);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Отклик успешно отправлен'], Response::HTTP_CREATED);
    }

    #[Route('/{id}/cancel-response', name: 'api_order_cancel_response', methods: ['POST'])]
    //#[IsGranted('contractor')]
    public function cancelResponse(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $contractor = $user->getContractors();

        if (!$contractor) {
            return new JsonResponse(['message' => 'Исполнитель не найден'], Response::HTTP_NOT_FOUND);
        }

        $order = $entityManager->getRepository(Orders::class)->find($id);
        if (!$order) {
            return new JsonResponse(['message' => 'Заказ не найден'], Response::HTTP_NOT_FOUND);
        }

        // Находим отклик исполнителя
        $response = $entityManager->getRepository(OrdersContractors::class)
            ->findOneBy(['ord_id' => $order, 'cnt_id' => $contractor]);

        if (!$response) {
            return new JsonResponse(['message' => 'Отклик не найден'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($response);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Отклик успешно отменен'], Response::HTTP_OK);
    }

    #[Route('/{id}/check-response', name: 'api_order_check_response', methods: ['GET'])]
    //#[IsGranted('contractor')]
    public function checkResponse(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $contractor = $user->getContractors();

        if (!$contractor) {
            return new JsonResponse(['message' => 'Исполнитель не найден'], Response::HTTP_NOT_FOUND);
        }

        $order = $entityManager->getRepository(Orders::class)->find($id);
        if (!$order) {
            return new JsonResponse(['message' => 'Заказ не найден'], Response::HTTP_NOT_FOUND);
        }

        // Проверяем, откликнулся ли исполнитель
        $response = $entityManager->getRepository(OrdersContractors::class)
            ->findOneBy(['ord_id' => $order, 'cnt_id' => $contractor]);

        return new JsonResponse(['hasResponded' => $response !== null], Response::HTTP_OK);
    }
} 