<?php

namespace App\Controller;

use App\Repository\OrdersRepository;
use App\Repository\ProjectsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reports')]
class ReportsController extends AbstractController
{
    #[Route('/dashboard', name: 'reports_dashboard', methods: ['GET'])]
    public function getDashboardStats(
        OrdersRepository $ordersRepository,
        ProjectsRepository $projectsRepository,
        UsersRepository $usersRepository
    ): JsonResponse {
        // Получаем статистику по заказам
        $totalOrders = $ordersRepository->count([]);
        $activeOrders = $ordersRepository->count(['status' => 'active']);
        $completedOrders = $ordersRepository->count(['status' => 'completed']);

        // Получаем статистику по проектам
        $totalProjects = $projectsRepository->count([]);

        // Получаем статистику по пользователям
        $totalUsers = $usersRepository->count([]);
        $totalContractors = $usersRepository->count(['roles' => ['ROLE_CONTRACTOR']]);
        $totalCustomers = $usersRepository->count(['roles' => ['ROLE_CUSTOMER']]);

        return $this->json([
            'orders' => [
                'total' => $totalOrders,
                'active' => $activeOrders,
                'completed' => $completedOrders,
            ],
            'projects' => [
                'total' => $totalProjects,
            ],
            'users' => [
                'total' => $totalUsers,
                'contractors' => $totalContractors,
                'customers' => $totalCustomers,
            ],
        ]);
    }

    #[Route('/orders-by-status', name: 'reports_orders_by_status', methods: ['GET'])]
    public function getOrdersByStatus(OrdersRepository $ordersRepository): JsonResponse
    {
        $orders = $ordersRepository->findAll();
        $statusCounts = [];

        foreach ($orders as $order) {
            $status = $order->getStatus();
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        return $this->json($statusCounts);
    }

    #[Route('/projects-by-direction', name: 'reports_projects_by_direction', methods: ['GET'])]
    public function getProjectsByDirection(ProjectsRepository $projectsRepository): JsonResponse
    {
        $projects = $projectsRepository->findAll();
        $directionCounts = [];

        foreach ($projects as $project) {
            $direction = $project->getDirection()->getDrcTitle();
            if (!isset($directionCounts[$direction])) {
                $directionCounts[$direction] = 0;
            }
            $directionCounts[$direction]++;
        }

        return $this->json($directionCounts);
    }
}
