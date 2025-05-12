<?php
namespace App\Controller;

use App\Entity\Feedbacks;
use App\Repository\FeedbacksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/feedbacks')]
class FeedbacksController extends AbstractController
{
    /**
     * Получить список всех отзывов (GET /api/feedbacks)
     */
    #[Route('/', name: 'feedbacks_index', methods: ['GET'])]
    public function index(FeedbacksRepository $feedbacksRepository): JsonResponse
    {
        $feedbacks = $feedbacksRepository->findAll();

        $data = [];
        foreach ($feedbacks as $feedback) {
            $data[] = [
                'id' => $feedback->getId(),
                'text' => $feedback->getFdbText(),
                'estimation' => $feedback->getFdbEstimation(),
                'timestamp' => $feedback->getFdbTimestamp()->format('Y-m-d H:i:s'),
                'contractor_id' => $feedback->getCntId(),
                'customer_id' => $feedback->getCstId(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новый отзыв (POST /api/feedbacks)
     */
    #[Route('/', name: 'feedbacks_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['text', 'estimation', 'contractor_id', 'customer_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $feedback = new Feedbacks();
        $feedback->setFdbText($data['text']);
        $feedback->setFdbEstimation($data['estimation']);
        $feedback->setFdbTimestamp(new \DateTime());
        $feedback->setCntId($data['contractor_id']);
        $feedback->setCstId($data['customer_id']);

        $entityManager->persist($feedback);
        $entityManager->flush();

        return $this->json([
            'id' => $feedback->getId(),
            'text' => $feedback->getFdbText(),
            'estimation' => $feedback->getFdbEstimation(),
            'timestamp' => $feedback->getFdbTimestamp()->format('Y-m-d H:i:s'),
            'contractor_id' => $feedback->getCntId(),
            'customer_id' => $feedback->getCstId(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить один отзыв по ID (GET /api/feedbacks/{fdb_id})
     */
    #[Route('/{fdb_id}', name: 'feedbacks_show', methods: ['GET'])]
    #[ParamConverter('feedback', options: ['mapping' => ['fdb_id' => 'id']])]
    public function show(Feedbacks $feedback): JsonResponse
    {
        return $this->json([
            'id' => $feedback->getId(),
            'text' => $feedback->getFdbText(),
            'estimation' => $feedback->getFdbEstimation(),
            'timestamp' => $feedback->getFdbTimestamp()->format('Y-m-d H:i:s'),
            'contractor_id' => $feedback->getCntId(),
            'customer_id' => $feedback->getCstId(),
        ]);
    }

    /**
     * Обновить отзыв (PUT /api/feedbacks/{fdb_id})
     */
    #[Route('/{fdb_id}', name: 'feedbacks_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('feedback', options: ['mapping' => ['fdb_id' => 'id']])]
    public function edit(Request $request, Feedbacks $feedback, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['text'])) {
            $feedback->setFdbText($data['text']);
        }

        if (isset($data['estimation'])) {
            $feedback->setFdbEstimation($data['estimation']);
        }

        if (isset($data['contractor_id'])) {
            $feedback->setCntId($data['contractor_id']);
        }

        if (isset($data['customer_id'])) {
            $feedback->setCstId($data['customer_id']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $feedback->getId(),
            'text' => $feedback->getFdbText(),
            'estimation' => $feedback->getFdbEstimation(),
            'timestamp' => $feedback->getFdbTimestamp()->format('Y-m-d H:i:s'),
            'contractor_id' => $feedback->getCntId(),
            'customer_id' => $feedback->getCstId(),
        ]);
    }

    /**
     * Удалить отзыв (DELETE /api/feedbacks/{fdb_id})
     */
    #[Route('/{fdb_id}', name: 'feedbacks_delete', methods: ['DELETE'])]
    #[ParamConverter('feedback', options: ['mapping' => ['fdb_id' => 'id']])]
    public function delete(Feedbacks $feedback, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($feedback);
        $entityManager->flush();

        return $this->json(['message' => 'Feedback deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}