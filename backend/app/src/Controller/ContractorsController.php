<?php

namespace App\Controller;

use App\Entity\Contractors;
use App\Repository\ContractorsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/contractors')]
class ContractorsController extends AbstractController
{
    /**
     * Получить всех подрядчиков (GET /api/contractors)
     */
    #[Route('/', name: 'contractors_index', methods: ['GET'])]
    public function index(ContractorsRepository $contractorsRepository): JsonResponse
    {
        $contractors = $contractorsRepository->findAll();

        $data = [];
        foreach ($contractors as $contractor) {
            $data[] = $this->serializeContractor($contractor);
        }

        return $this->json($data);
    }

    /**
     * Создать нового подрядчика (POST /api/contractors)
     */
    #[Route('/', name: 'contractors_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['text', 'user_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $contractor = new Contractors();
        $contractor->setCntText($data['text']);

        // В реальном приложении нужно загрузить сущность User
        // $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        // $contractor->setUsrId($user);

        // Временно используем сеттер с ID
        $contractor->setUsrId($data['user_id']);

        $entityManager->persist($contractor);
        $entityManager->flush();

        return $this->json(
            $this->serializeContractor($contractor),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Получить информацию о текущем исполнителе (GET /api/contractors/me)
     */
    #[Route('/me', name: 'contractors_me', methods: ['GET'])]
    //#[IsGranted('contractor')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        $contractor = $user->getContractors();

        if (!$contractor) {
            return $this->json(['error' => 'Contractor not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $contractor->getId(),
            'description' => $contractor->getCntText()
        ]);
    }

    /**
     * Обновить описание текущего исполнителя (PUT /api/contractors/me)
     */
    #[Route('/me', name: 'contractors_update_me', methods: ['PUT'])]
    //#[IsGranted('contractor')]
    public function updateMe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $contractor = $user->getContractors();

        if (!$contractor) {
            return $this->json(['error' => 'Contractor not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['text'])) {
            return $this->json(['error' => 'Missing "text" field'], Response::HTTP_BAD_REQUEST);
        }

        $contractor->setCntText($data['text']);
        $entityManager->flush();

        return $this->json([
            'id' => $contractor->getId(),
            'description' => $contractor->getCntText()
        ]);
    }

    /**
     * Получить подрядчика по ID (GET /api/contractors/{cnt_id})
     */
    #[Route('/{cnt_id}', name: 'contractors_show', methods: ['GET'])]
    public function show(int $cnt_id, ContractorsRepository $contractorsRepository): JsonResponse
    {
        $contractor = $contractorsRepository->find($cnt_id);
        
        if (!$contractor) {
            return $this->json(['error' => 'Contractor not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serializeContractor($contractor));
    }

    /**
     * Обновить подрядчика (PUT /api/contractors/{cnt_id})
     */
    #[Route('/{cnt_id}', name: 'contractors_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('contractor', options: ['mapping' => ['cnt_id' => 'id']])]
    public function edit(Request $request, Contractors $contractor, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['text'])) {
            $contractor->setCntText($data['text']);
        }

        if (isset($data['user_id'])) {
            // Загрузить сущность User и установить
            $contractor->setUsrId($data['user_id']);
        }

        $entityManager->flush();

        return $this->json($this->serializeContractor($contractor));
    }

    /**
     * Удалить подрядчика (DELETE /api/contractors/{cnt_id})
     */
    #[Route('/{cnt_id}', name: 'contractors_delete', methods: ['DELETE'])]
    #[ParamConverter('contractor', options: ['mapping' => ['cnt_id' => 'id']])]
    public function delete(Contractors $contractor, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($contractor);
        $entityManager->flush();

        return $this->json(['message' => 'Contractor deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Получить полную информацию о подрядчике (GET /api/contractors/{cnt_id}/full-profile)
     */
    #[Route('/{cnt_id}/full-profile', name: 'contractors_full_profile', methods: ['GET'])]
    //#[ParamConverter('contractor', options: ['mapping' => ['cnt_id' => 'id']])]
    public function fullProfile(int $cnt_id, ContractorsRepository $contractorsRepository): JsonResponse
    {
        $contractor = $contractorsRepository->find($cnt_id);
        if (!$contractor) {
            return $this->json(['error' => 'Contractor not found'], 404);
        }
        $user = $contractor->getUsrId();

        // Проекты
        $projects = [];
        foreach ($contractor->getProjectsGitHubs() as $project) {
            $photos = [];
            foreach ($project->getPhotosProjectsGitHubs() as $photo) {
                $photos[] = [
                    'id' => $photo->getId(),
                    'link' => $photo->getPpghLink(),
                ];
            }
            $projects[] = [
                'id' => $project->getId(),
                'name' => $project->getPghName(),
                'repository' => $project->getPghRepository(),
                'text' => $project->getPghText(),
                'photos' => $photos,
            ];
        }

        // Заказы
        $orders = [];
        foreach ($contractor->getOrdersContractors() as $orderContractor) {
            $order = $orderContractor->getOrdId();
            if ($order && $order->getOrdStatus()=='Завершен') {
                $orders[] = [
                    'id' => $order->getId(),
                    'title' => $order->getOrdTitle(),
                    'status' => $order->getOrdStatus(),
                    'price' => $order->getOrdPrice(),
                    'time' => $order->getOrdTime(),
                    'order_contractor_status' => $orderContractor->getOrdCntStatus(),
                ];
            }
        }

        // Отзывы
        $feedbacks = [];
        foreach ($contractor->getFeedbacks() as $feedback) {
            $feedbacks[] = [
                'id' => $feedback->getId(),
                'text' => $feedback->getFdbText(),
                'estimation' => $feedback->getFdbEstimation(),
                'timestamp' => $feedback->getFdbTimestamp() ? $feedback->getFdbTimestamp()->format('Y-m-d H:i:s') : null,
            ];
        }

        return $this->json([
            'id' => $contractor->getId(),
            'user' => [
                'id' => $user?->getId(),
                'name' => $user?->getUsrName(),
                'surname' => $user?->getUsrSurname(),
                'patronymic' => $user?->getUsrPatronymic(),
                'email' => $user?->getEmail(),
            ],
            'description' => $contractor->getCntText(),
            'projects' => $projects,
            'orders' => $orders,
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Сериализация подрядчика в массив
     */
    private function serializeContractor(Contractors $contractor): array
    {
        $orders = [];
        foreach ($contractor->getOrdersContractors() as $orderContractor) {
            $orders[] = [
                'order_id' => $orderContractor->getOrdId()?->getId(),
                'status' => $orderContractor->getOrdCntStatus()
            ];
        }

        $projects = [];
        foreach ($contractor->getProjectsGitHubs() as $project) {
            $projects[] = [
                'project_id' => $project->getId(),
                'name' => $project->getPghName()
            ];
        }

        $feedbacks = [];
        foreach ($contractor->getFeedbacks() as $feedback) {
            $feedbacks[] = [
                'feedback_id' => $feedback->getId(),
                'estimation' => $feedback->getFdbEstimation()
            ];
        }

        return [
            'id' => $contractor->getId(),
            'text' => $contractor->getCntText(),
            'user_id' => $contractor->getUsrId()?->getId(),
            'orders' => $orders,
            'projects' => $projects,
            'feedbacks' => $feedbacks
        ];
    }
}
