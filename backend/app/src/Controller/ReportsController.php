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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use TCPDF;

#[Route('/api/reports')]
class ReportsController extends AbstractController
{
    private $logoPath;

    public function __construct(
        private OrdersRepository $ordersRepository,
        private ProjectsGitHubRepository $projectsRepository,
        private UserRepository $usersRepository,
        private StacksRepository $stacksRepository,
        private CustomersRepository $customersRepository,
        private ContractorsRepository $contractorsRepository,
        private EntityManagerInterface $entityManager
    ) {
        $this->logoPath = __DIR__ . '/../../../../frontend/public/logo4.svg';
    }

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

    #[Route('/generate-pdf/{type}', name: 'reports_generate_pdf', methods: ['GET'])]
    public function generatePDF(string $type): Response
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Установка информации о документе
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('IT-заказы');
            $pdf->SetTitle('Отчет - ' . $this->getReportTitle($type));

            // Установка отступов
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Установка автоматического разрыва страницы
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Добавление страницы
            $pdf->AddPage();

            // Добавление логотипа
            if (file_exists($this->logoPath)) {
                $pdf->Image($this->logoPath, 15, 10, 50);
            }

            // Заголовок отчета
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->Cell(0, 20, $this->getReportTitle($type), 0, 1, 'C');
            $pdf->Ln(10);

            // Добавление данных в зависимости от типа отчета
            switch ($type) {
                case 'contractors':
                    $this->addContractorsReport($pdf);
                    break;
                case 'active-orders':
                    $this->addActiveOrdersReport($pdf);
                    break;
                case 'completed-orders':
                    $this->addCompletedOrdersReport($pdf);
                    break;
                case 'customers':
                    $this->addCustomersReport($pdf);
                    break;
            }

            // Добавление даты и времени генерации
            $pdf->Ln(20);
            $pdf->SetFont('dejavusans', 'I', 8);
            $pdf->Cell(0, 10, 'Отчет сгенерирован: ' . date('d.m.Y H:i:s'), 0, 1, 'R');

            // Генерация PDF
            return new Response(
                $pdf->Output('report.pdf', 'S'),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $type . '_report.pdf"'
                ]
            );
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getReportTitle(string $type): string
    {
        return match($type) {
            'contractors' => 'Отчет о количестве исполнителей',
            'active-orders' => 'Отчет о количестве требуемых выполнения заказов',
            'completed-orders' => 'Отчет о количестве выполненных заказов',
            'customers' => 'Отчет о количестве заказчиков',
            default => 'Отчет'
        };
    }

    private function addContractorsReport(TCPDF $pdf): void
    {
        $totalContractors = $this->contractorsRepository->count([]);
        $contractors = $this->contractorsRepository->findAll();
        
        // Подсчет статистики по количеству проектов
        $projectStats = [
            '0 проектов' => 0,
            '1-2 проекта' => 0,
            '3-5 проектов' => 0,
            'более 5 проектов' => 0
        ];

        foreach ($contractors as $contractor) {
            $projectCount = count($contractor->getProjectsGitHubs());
            if ($projectCount == 0) {
                $projectStats['0 проектов']++;
            } elseif ($projectCount <= 2) {
                $projectStats['1-2 проекта']++;
            } elseif ($projectCount <= 5) {
                $projectStats['3-5 проектов']++;
            } else {
                $projectStats['более 5 проектов']++;
            }
        }

        // Подсчет статистики по количеству заказов
        $orderStats = [
            '0 заказов' => 0,
            '1-2 заказа' => 0,
            '3-5 заказов' => 0,
            'более 5 заказов' => 0
        ];

        foreach ($contractors as $contractor) {
            $orderCount = count($contractor->getOrdersContractors());
            if ($orderCount == 0) {
                $orderStats['0 заказов']++;
            } elseif ($orderCount <= 2) {
                $orderStats['1-2 заказа']++;
            } elseif ($orderCount <= 5) {
                $orderStats['3-5 заказов']++;
            } else {
                $orderStats['более 5 заказов']++;
            }
        }

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 10, 'Общая статистика по исполнителям:', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(80, 7, 'Общее количество исполнителей:', 0, 0);
        $pdf->Cell(0, 7, $totalContractors, 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 7, 'Распределение по количеству проектов:', 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('dejavusans', '', 10);
        foreach ($projectStats as $range => $count) {
            $pdf->Cell(80, 7, $range . ':', 0, 0);
            $pdf->Cell(0, 7, $count, 0, 1);
        }
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 7, 'Распределение по количеству заказов:', 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('dejavusans', '', 10);
        foreach ($orderStats as $range => $count) {
            $pdf->Cell(80, 7, $range . ':', 0, 0);
            $pdf->Cell(0, 7, $count, 0, 1);
        }
    }

    private function addActiveOrdersReport(TCPDF $pdf): void
    {
        $activeOrders = $this->ordersRepository->count(['ord_status' => 'Новый']);
        $inProgressOrders = $this->ordersRepository->count(['ord_status' => 'В работе']);
        $orders = $this->ordersRepository->findBy(['ord_status' => ['Новый', 'В работе']]);

        // Подсчет статистики по бюджету
        $budgetStats = [
            'до 50 000' => 0,
            '50 000 - 100 000' => 0,
            '100 000 - 200 000' => 0,
            'более 200 000' => 0
        ];

        foreach ($orders as $order) {
            $price = $order->getOrdPrice();
            if ($price <= 50000) {
                $budgetStats['до 50 000']++;
            } elseif ($price <= 100000) {
                $budgetStats['50 000 - 100 000']++;
            } elseif ($price <= 200000) {
                $budgetStats['100 000 - 200 000']++;
            } else {
                $budgetStats['более 200 000']++;
            }
        }

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 10, 'Статистика по активным заказам:', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(80, 7, 'Всего активных заказов:', 0, 0);
        $pdf->Cell(0, 7, $activeOrders + $inProgressOrders, 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 7, 'Распределение по бюджету:', 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('dejavusans', '', 10);
        foreach ($budgetStats as $range => $count) {
            $pdf->Cell(80, 7, $range . ' руб.:', 0, 0);
            $pdf->Cell(0, 7, $count, 0, 1);
        }
    }

    private function addCompletedOrdersReport(TCPDF $pdf): void
    {
        $completedOrders = $this->ordersRepository->count(['ord_status' => 'Завершен']);
        $totalOrders = $this->ordersRepository->count([]);
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 10, 'Статистика по выполненным заказам:', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(80, 7, 'Выполненных заказов:', 0, 0);
        $pdf->Cell(0, 7, $completedOrders, 0, 1);
        $pdf->Cell(80, 7, 'Общее количество заказов:', 0, 0);
        $pdf->Cell(0, 7, $totalOrders, 0, 1);
        $pdf->Cell(80, 7, 'Процент выполнения:', 0, 0);
        $pdf->Cell(0, 7, $completionRate . '%', 0, 1);
    }

    private function addCustomersReport(TCPDF $pdf): void
    {
        $totalCustomers = $this->customersRepository->count([]);
        $customers = $this->customersRepository->findAll();
        
        // Подсчет статистики по количеству заказов
        $orderStats = [
            '1 заказ' => 0,
            '2-3 заказа' => 0,
            '4-5 заказов' => 0,
            'более 5 заказов' => 0
        ];

        foreach ($customers as $customer) {
            $orderCount = count($customer->getOrders());
            if ($orderCount == 1) {
                $orderStats['1 заказ']++;
            } elseif ($orderCount <= 3) {
                $orderStats['2-3 заказа']++;
            } elseif ($orderCount <= 5) {
                $orderStats['4-5 заказов']++;
            } else {
                $orderStats['более 5 заказов']++;
            }
        }

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 10, 'Статистика по заказчикам:', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(80, 7, 'Общее количество заказчиков:', 0, 0);
        $pdf->Cell(0, 7, $totalCustomers, 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 7, 'Распределение по количеству заказов:', 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('dejavusans', '', 10);
        foreach ($orderStats as $range => $count) {
            $pdf->Cell(80, 7, $range . ':', 0, 0);
            $pdf->Cell(0, 7, $count, 0, 1);
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
