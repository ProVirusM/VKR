<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersStacks;
use App\Entity\OrdersContractors;
use App\Entity\Contractors;
use App\Repository\OrdersRepository;
use App\Repository\OrdersContractorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\CustomersRepository;
use App\Repository\StacksRepository;
use App\Repository\ContractorsRepository;
#[Route('/api/orders')]
class OrdersController extends AbstractController
{
    /**
     * Получить список всех заказов (GET /api/orders)
     */
    private $customerRepository;  // Делаем переменную доступной в контроллере
    private $stackRepository;
    private $contractorRepository;
    private $ordersContractorsRepository;
    // Внедряем репозиторий через конструктор
    public function __construct(
        CustomersRepository $customerRepository, 
        StacksRepository $stackRepository,
        ContractorsRepository $contractorRepository,
        OrdersContractorsRepository $ordersContractorsRepository
    )
    {
        $this->customerRepository = $customerRepository;
        $this->stackRepository = $stackRepository;
        $this->contractorRepository = $contractorRepository;
        $this->ordersContractorsRepository = $ordersContractorsRepository;
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
            $stacks = array_map('intval', explode(',', $stacks));  // Преобразуем строку в массив целых чисел
        }

        // Получаем заказы с фильтрами
        $ordersArray = $ordersRepository->findByFilters($direction, $language, $stacks);

        // Преобразуем в коллекцию и фильтруем
        $orders = (new ArrayCollection($ordersArray))
            ->filter(fn($order) => $order->getOrdStatus() == 'Новый');

        // Преобразуем заказы в формат JSON
        $data = [];
        foreach ($orders as $order) {
            $stacks = [];
            foreach ($order->getOrdersStacks() as $orderStack) {
                $stack = $orderStack->getStcId();
                $stacks[] = [
                    'id' => $stack->getId(),
                    'title' => $stack->getStcTitle(),
                ];
            }
            $data[] = [
                'id' => $order->getId(),
                'title' => $order->getOrdTitle(),
                'text' => $order->getOrdText(),
                'status' => $order->getOrdStatus(),
                'price' => $order->getOrdPrice(),
                'time' => $order->getOrdTime(),
                'stacks' => $stacks
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
    public function delete(int $ord_id, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $entityManager->getRepository(Orders::class)->find($ord_id);
        
        if (!$order) {
            return $this->json(['error' => 'Заказ не найден'], 404);
        }

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(['message' => 'Заказ успешно удален']);
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

    #[Route('/{orderId}/approve-contractor/{contractorId}', name: 'approve_contractor', methods: ['POST'])]
    public function approveContractor(
        int $orderId,
        int $contractorId,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            // Находим заказ
            $order = $entityManager->getRepository(Orders::class)->find($orderId);
            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            // Находим исполнителя
            $contractor = $entityManager->getRepository(Contractors::class)->find($contractorId);
            if (!$contractor) {
                return $this->json(['error' => 'Contractor not found'], 404);
            }

            // Находим связь заказа с исполнителем
            $orderContractor = $entityManager->getRepository(OrdersContractors::class)->findOneBy([
                'ord_id' => $order,
                'cnt_id' => $contractor
            ]);

            if (!$orderContractor) {
                return $this->json(['error' => 'Contractor is not associated with this order'], 404);
            }

            // Обновляем статус заказа
            $order->setOrdStatus('Завершен');

            // Обновляем статус связи с исполнителем
            $orderContractor->setOrdCntStatus('Назначен');

            // Сохраняем изменения
            $entityManager->flush();

            return $this->json([
                'message' => 'Contractor approved successfully',
                'order' => [
                    'id' => $order->getId(),
                    'status' => $order->getOrdStatus()
                ],
                'contractor' => [
                    'id' => $contractor->getId(),
                    'status' => $orderContractor->getOrdCntStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while approving the contractor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{orderId}/approved-contractor', name: 'get_approved_contractor', methods: ['GET'])]
    public function getApprovedContractor(
        int $orderId,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $order = $entityManager->getRepository(Orders::class)->find($orderId);
            if (!$order) {
                return $this->json(['error' => 'Order not found'], 404);
            }

            // Находим связь заказа с исполнителем, где статус "Назначен"
            $orderContractor = $entityManager->getRepository(OrdersContractors::class)->findOneBy([
                'ord_id' => $order,
                'ord_cnt_status' => 'Назначен'
            ]);

            if (!$orderContractor) {
                return $this->json(['error' => 'No approved contractor found for this order'], 404);
            }

            $contractor = $orderContractor->getCntId();
            $user = $contractor->getUsrId();

            return $this->json([
                'contractorId' => $contractor->getId(),
                'userName' => $user->getUsrName(),
                'userSurname' => $user->getUsrSurname(),
                'userPatronymic' => $user->getUsrPatronymic(),
                'status' => $orderContractor->getOrdCntStatus()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while getting the approved contractor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/contractors-with-rating', name: 'api_order_contractors_with_rating', methods: ['GET'])]
    public function getContractorsWithRating(int $id, EntityManagerInterface $em): JsonResponse
    {
        $order = $em->getRepository(Orders::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $result = [];
        $Tmax = new \DateTime(); // Текущая дата
        $lambda = 0.01; // Коэффициент затухания

        foreach ($order->getOrdersContractors() as $orderContractor) {
            $contractor = $orderContractor->getCntId();
            $user = $contractor->getUsrId();

            // Собираем отзывы
            $feedbacks = $contractor->getFeedbacks();
            $ratings = [];
            $weights = [];
            foreach ($feedbacks as $feedback) {
                $score = $feedback->getFdbEstimation();
                $Tq = $feedback->getFdbTimestamp();
                if ($score !== null && $Tq !== null) {
                    // Разница в днях между Tmax и Tq
                    $days = $Tmax->diff($Tq)->days;
                    // w(T_q) = exp(-lambda * (Tmax - Tq))
                    $w = exp(-$lambda * $days);
                    $ratings[] = $score * $w;
                    $weights[] = $w;
                }
            }
            $weightedRating = null;
            if (count($weights) > 0 && array_sum($weights) > 0) {
                $weightedRating = array_sum($ratings) / array_sum($weights);
            }

            $result[] = [
                'contractorId' => $contractor->getId(),
                'userName' => $user ? $user->getUsrName() : null,
                'userSurname' => $user ? $user->getUsrSurname() : null,
                'userPatronymic' => $user ? $user->getUsrPatronymic() : null,
                'rating' => $weightedRating !== null ? round($weightedRating, 2) : null
            ];
        }

        // Сортировка: только по рейтингу (desc)
        usort($result, function($a, $b) {
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        });

        return $this->json($result);
    }
}
