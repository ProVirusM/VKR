<?php
// src/Controller/Api/ProfileController.php

// src/Controller/Api/ProfileController.php
namespace App\Controller;

use App\Entity\Contractors;
use App\Entity\Orders;
use App\Entity\OrdersContractors;
use App\Repository\ContractorsRepository;
use App\Repository\OrdersContractorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class ProfileController extends AbstractController
{
    private $contractorsRepository;
    private $ordersContractorsRepository;
    private $serializer;
    private $entityManager;

    public function __construct(
        ContractorsRepository $contractorsRepository,
        OrdersContractorsRepository $ordersContractorsRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    )
    {
        $this->contractorsRepository = $contractorsRepository;
        $this->ordersContractorsRepository = $ordersContractorsRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/profile", name="api_profile", methods={"GET"})
     */
    #[Route('/api/profile', name: 'profile', methods: ['GET'])]
    public function getProfile(Request $request, UserInterface $user): JsonResponse
    {
        // Выводим информацию о пользователе
        return $this->json([
            'name' => $user->getUsrName(), // Пример поля для имени
            'surname' => $user->getUsrSurname(),
            'patronymic' => $user->getUsrPatronymic(),
//            'contractors' => $user->getContractors(),
//            'customers' => $user->getCustomers(),
            'contractorId' => $user->getContractors()?->getId(),
            'customerId' => $user->getCustomers()?->getId(),
            'email' => $user->getEmail(),
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/customer/active-orders', name: 'customer_active_orders', methods: ['GET'])]
    public function getActiveOrders(UserInterface $user): JsonResponse
    {
        $orders = $user->getCustomers()?->getOrders()
            ->filter(fn($order) => $order->getOrdStatus() == 'Новый');



        return $this->json($orders, 200, [], ['groups' => 'order:read']);
    }


    #[Route('/api/customer/completed-orders', name: 'customer_completed_orders', methods: ['GET'])]
    public function getCompletedOrders(UserInterface $user): JsonResponse
    {
        $orders = $user->getCustomers()?->getOrders()
            ->filter(fn($order) => $order->getOrdStatus() == 'Завершен');

        return $this->json($orders, 200, [], ['groups' => 'order:read']);
    }

    #[Route('/api/contractor/approved-orders', name: 'contractor_approved_orders', methods: ['GET'])]
    public function getApprovedOrders(UserInterface $user): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $contractor = $this->contractorsRepository->findOneBy(['usr_id' => $user->getId()]);

        if (!$contractor) {
            // Если пользователь не подрядчик, возвращаем пустой массив
            return $this->json([]);
        }

        // Находим все связи OrdersContractors для этого подрядчика со статусом 'Назначен'
        $approvedOrdersContractors = $this->ordersContractorsRepository->findBy([
            'cnt_id' => $contractor->getId(),
            'ord_cnt_status' => 'Назначен',
        ]);

        $ordersData = [];
        foreach ($approvedOrdersContractors as $orderContractor) {
            $order = $orderContractor->getOrdId(); // Получаем объект Orders
            if ($order) {
                 $stacks = [];
                foreach ($order->getOrdersStacks() as $orderStack) {
                    $stack = $orderStack->getStcId();
                    if ($stack) {
                        $stacks[] = [
                            'id' => $stack->getId(),
                            'title' => $stack->getStcTitle(),
                        ];
                    }
                }

                $ordersData[] = [
                    'id' => $order->getId(),
                    'title' => $order->getOrdTitle(),
                    'text' => $order->getOrdText(),
                    'status' => $order->getOrdStatus(), // Статус самого заказа
                    'price' => $order->getOrdPrice(),
                    'time' => $order->getOrdTime(),
                    'contractor_status' => $orderContractor->getOrdCntStatus(), // Статус связи с подрядчиком
                     'stacks' => $stacks
                ];
            }
        }

        return $this->json($ordersData);
    }

    #[Route('/api/contractor/responded-orders', name: 'contractor_responded_orders', methods: ['GET'])]
    public function getRespondedOrders(UserInterface $user): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $contractor = $this->contractorsRepository->findOneBy(['usr_id' => $user->getId()]);

        if (!$contractor) {
            // Если пользователь не подрядчик, возвращаем пустой массив
            return $this->json([]);
        }

        // Находим все связи OrdersContractors для этого подрядчика со статусом 'Назначен'
        $approvedOrdersContractors = $this->ordersContractorsRepository->findBy([
            'cnt_id' => $contractor->getId(),
            'ord_cnt_status' => 'Ожидает',
        ]);

        $ordersData = [];
        foreach ($approvedOrdersContractors as $orderContractor) {
            $order = $orderContractor->getOrdId(); // Получаем объект Orders
            if ($order) {
                $stacks = [];
                foreach ($order->getOrdersStacks() as $orderStack) {
                    $stack = $orderStack->getStcId();
                    if ($stack) {
                        $stacks[] = [
                            'id' => $stack->getId(),
                            'title' => $stack->getStcTitle(),
                        ];
                    }
                }

                $ordersData[] = [
                    'id' => $order->getId(),
                    'title' => $order->getOrdTitle(),
                    'text' => $order->getOrdText(),
                    'status' => $order->getOrdStatus(), // Статус самого заказа
                    'price' => $order->getOrdPrice(),
                    'time' => $order->getOrdTime(),
                    'contractor_status' => $orderContractor->getOrdCntStatus(), // Статус связи с подрядчиком
                    'stacks' => $stacks
                ];
            }
        }

        return $this->json($ordersData);
    }

    #[Route('/api/contractor/reviews', name: 'contractor_reviews', methods: ['GET'])]
    public function getContractorReviews(UserInterface $user): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $contractor = $this->contractorsRepository->findOneBy(['usr_id' => $user->getId()]);

        if (!$contractor) {
            return $this->json([]);
        }

        // Получаем отзывы через EntityManager
        $reviews = $this->entityManager->getRepository(\App\Entity\Feedbacks::class)
            ->findBy(['cnt_id' => $contractor->getId()]);

        $reviewsData = [];
        foreach ($reviews as $review) {
            $customer = $review->getCstId();
            $reviewsData[] = [
                'id' => $review->getId(),
                'text' => $review->getFdbText(),
                'estimation' => $review->getFdbEstimation(),
                'timestamp' => $review->getFdbTimestamp() ? $review->getFdbTimestamp()->format('Y-m-d H:i:s') : null,
                'customer_name' => $customer?->getUsrId()?->getUsrName(),
                'customer_surname' => $customer?->getUsrId()?->getUsrSurname(),
            ];
        }

        return $this->json($reviewsData);
    }
}
