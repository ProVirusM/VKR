<?php
// src/Controller/Api/ProfileController.php

// src/Controller/Api/ProfileController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends AbstractController
{
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
        $orders = $user->getContractors()?->getApprovedOrders(); // аналогично
        return $this->json($orders);
    }

    #[Route('/api/contractor/responded-orders', name: 'contractor_responded_orders', methods: ['GET'])]
    public function getRespondedOrders(UserInterface $user): JsonResponse
    {
        $orders = $user->getContractors()?->getRespondedOrders();
        return $this->json($orders);
    }

    #[Route('/api/contractor/reviews', name: 'contractor_reviews', methods: ['GET'])]
    public function getContractorReviews(UserInterface $user): JsonResponse
    {
        $reviews = $user->getContractors()?->getReviews();
        return $this->json($reviews);
    }
}
