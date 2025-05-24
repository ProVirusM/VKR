<?php

namespace App\Controller;

use App\Entity\ProjectsGitHub;
use App\Repository\OrdersRepository;
use App\Repository\ProjectsGitHubRepository;
use App\Repository\UserRepository;
use App\Repository\StacksRepository;
use App\Repository\CustomersRepository;
use App\Repository\ContractorsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/reports')]
class ReportsController extends AbstractController
{
    public function __construct(
        private OrdersRepository $ordersRepository,
        private ProjectsGitHubRepository $projectsRepository,
        private UserRepository $usersRepository,
        private StacksRepository $stacksRepository,
        private CustomersRepository $customersRepository,
        private ContractorsRepository $contractorsRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/dashboard', name: 'reports_dashboard', methods: ['GET'])]
    public function getDashboardStats(): JsonResponse
    {
        try {
            // Получаем статистику по заказам
            $totalOrders = $this->ordersRepository->count([]);
            $activeOrders = $this->ordersRepository->count(['ord_status' => 'Новый']);
            $completedOrders = $this->ordersRepository->count(['ord_status' => 'Завершен']);

            // Получаем статистику по проектам
            $totalProjects = $this->projectsRepository->count([]);

            // Получаем статистику по пользователям
            $totalUsers = $this->usersRepository->count([]);
            $totalContractors = $this->contractorsRepository->count([]);
            $totalCustomers = $this->customersRepository->count([]);

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
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch dashboard stats: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/orders-by-status', name: 'reports_orders_by_status', methods: ['GET'])]
    public function getOrdersByStatus(): JsonResponse
    {
        try {
            $orders = $this->ordersRepository->findAll();
            $statusCounts = [];

            foreach ($orders as $order) {
                $status = $order->getOrdStatus();
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
                $statusCounts[$status]++;
            }

            return $this->json($statusCounts);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch orders by status: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/projects-by-direction', name: 'reports_projects_by_direction', methods: ['GET'])]
    public function getProjectsByDirection(): JsonResponse
    {
        try {
            $stacks = $this->stacksRepository->findAll();
            $directionCounts = [];

            foreach ($stacks as $stack) {
                $direction = $stack->getDrcId();
                if ($direction) {
                    $directionTitle = $direction->getDrcTitle();
                    if (!isset($directionCounts[$directionTitle])) {
                        $directionCounts[$directionTitle] = 0;
                    }
                    $directionCounts[$directionTitle]++;
                }
            }

            return $this->json($directionCounts);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch projects by direction: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
