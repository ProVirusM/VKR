<?php
namespace App\Controller;

use App\Entity\OrdersContractors;
use App\Repository\OrdersContractorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/orders-contractors')]
class OrdersContractorsController extends AbstractController
{
    /**
     * Получить список всех связей заказов и подрядчиков (GET /api/orders-contractors)
     */
    #[Route('/', name: 'orders_contractors_index', methods: ['GET'])]
    public function index(OrdersContractorsRepository $ordersContractorsRepository): JsonResponse
    {
        $ordersContractors = $ordersContractorsRepository->findAll();

        $data = [];
        foreach ($ordersContractors as $orderContractor) {
            $data[] = [
                'id' => $orderContractor->getId(),
                'order_id' => $orderContractor->getOrdId()?->getId(),
                'contractor_id' => $orderContractor->getCntId()?->getId(),
                'status' => $orderContractor->getOrdCntStatus(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новую связь заказа и подрядчика (POST /api/orders-contractors)
     */
    #[Route('/', name: 'orders_contractors_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['order_id', 'contractor_id', 'status'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $orderContractor = new OrdersContractors();
        $orderContractor->setOrdCntStatus($data['status']);

        // Здесь предполагается, что вы передаете ID существующих Order и Contractor
        // В реальном приложении вам нужно будет загрузить эти сущности из базы данных
        // Например:
        // $order = $entityManager->getRepository(Orders::class)->find($data['order_id']);
        // $contractor = $entityManager->getRepository(Contractors::class)->find($data['contractor_id']);
        // $orderContractor->setOrdId($order);
        // $orderContractor->setCntId($contractor);

        // Временно используем сеттеры, которые принимают ID (если они у вас реализованы)
        $orderContractor->setOrdId($data['order_id']);
        $orderContractor->setCntId($data['contractor_id']);

        $entityManager->persist($orderContractor);
        $entityManager->flush();

        return $this->json([
            'id' => $orderContractor->getId(),
            'order_id' => $orderContractor->getOrdId()?->getId(),
            'contractor_id' => $orderContractor->getCntId()?->getId(),
            'status' => $orderContractor->getOrdCntStatus(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить связь по ID (GET /api/orders-contractors/{ord_cnt_id})
     */
    #[Route('/{ord_cnt_id}', name: 'orders_contractors_show', methods: ['GET'])]
    #[ParamConverter('orderContractor', options: ['mapping' => ['ord_cnt_id' => 'id']])]
    public function show(OrdersContractors $orderContractor): JsonResponse
    {
        return $this->json([
            'id' => $orderContractor->getId(),
            'order_id' => $orderContractor->getOrdId()?->getId(),
            'contractor_id' => $orderContractor->getCntId()?->getId(),
            'status' => $orderContractor->getOrdCntStatus(),
        ]);
    }

    /**
     * Обновить связь (PUT /api/orders-contractors/{ord_cnt_id})
     */
    #[Route('/{ord_cnt_id}', name: 'orders_contractors_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('orderContractor', options: ['mapping' => ['ord_cnt_id' => 'id']])]
    public function edit(Request $request, OrdersContractors $orderContractor, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $orderContractor->setOrdCntStatus($data['status']);
        }

        if (isset($data['order_id'])) {
            // Аналогично методу new, нужно загрузить сущность Order
            $orderContractor->setOrdId($data['order_id']);
        }

        if (isset($data['contractor_id'])) {
            // Аналогично методу new, нужно загрузить сущность Contractor
            $orderContractor->setCntId($data['contractor_id']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $orderContractor->getId(),
            'order_id' => $orderContractor->getOrdId()?->getId(),
            'contractor_id' => $orderContractor->getCntId()?->getId(),
            'status' => $orderContractor->getOrdCntStatus(),
        ]);
    }

    /**
     * Удалить связь (DELETE /api/orders-contractors/{ord_cnt_id})
     */
    #[Route('/{ord_cnt_id}', name: 'orders_contractors_delete', methods: ['DELETE'])]
    #[ParamConverter('orderContractor', options: ['mapping' => ['ord_cnt_id' => 'id']])]
    public function delete(OrdersContractors $orderContractor, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($orderContractor);
        $entityManager->flush();

        return $this->json(['message' => 'Order-Contractor relation deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}