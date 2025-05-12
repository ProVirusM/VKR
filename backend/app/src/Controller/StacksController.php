<?php

namespace App\Controller;

use App\Entity\Stacks;
use App\Repository\StacksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/stacks')]
class StacksController extends AbstractController
{
    #[Route('/{languageId}/{directionId}', name: 'get_technologies', methods: ['GET'])]
    public function getStacks(int $languageId, int $directionId, StacksRepository $stacksRepository): JsonResponse
    {
        // Найдем все технологии для указанного языка и направления
        $stackEntities = $stacksRepository->findBy([
            'lng_id' => $languageId,
            'drc_id' => $directionId
        ]);

        $stacks = [];

        foreach ($stackEntities as $stack) {
            $stacks[] = [
                'id' => $stack->getId(),
                'title' => $stack->getStcTitle(),
            ];
        }

        return new JsonResponse($stacks);
    }
    /**
     * Получить все стеки технологий (GET /api/stacks)
     */
    #[Route('/', name: 'stacks_index', methods: ['GET'])]
    public function index(StacksRepository $stacksRepository): JsonResponse
    {
        $stacks = $stacksRepository->findAll();

        $data = [];
        foreach ($stacks as $stack) {
            $orders = [];
            foreach ($stack->getOrdersStacks() as $orderStack) {
                $orders[] = [
                    'order_id' => $orderStack->getOrdId()?->getId(),
                    'status' => $orderStack->getOrdCntStatus()
                ];
            }

            $data[] = [
                'id' => $stack->getId(),
                'title' => $stack->getStcTitle(),
                'direction_id' => $stack->getDrcId()?->getId(),
                'language_id' => $stack->getLngId()?->getId(),
                'orders' => $orders
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новый стек технологий (POST /api/stacks)
     */
    #[Route('/', name: 'stacks_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['title', 'direction_id', 'language_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $stack = new Stacks();
        $stack->setStcTitle($data['title']);

        // В реальном приложении нужно загрузить сущности Directions и Languages
        // $direction = $entityManager->getRepository(Directions::class)->find($data['direction_id']);
        // $language = $entityManager->getRepository(Languages::class)->find($data['language_id']);
        // $stack->setDrcId($direction);
        // $stack->setLngId($language);

        // Временно используем сеттеры с ID
        $stack->setDrcId($data['direction_id']);
        $stack->setLngId($data['language_id']);

        $entityManager->persist($stack);
        $entityManager->flush();

        return $this->json([
            'id' => $stack->getId(),
            'title' => $stack->getStcTitle(),
            'direction_id' => $stack->getDrcId()?->getId(),
            'language_id' => $stack->getLngId()?->getId()
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить стек по ID (GET /api/stacks/{stc_id})
     */
    #[Route('/{stc_id}', name: 'stacks_show', methods: ['GET'])]
    #[ParamConverter('stack', options: ['mapping' => ['stc_id' => 'id']])]
    public function show(Stacks $stack): JsonResponse
    {
        $orders = [];
        foreach ($stack->getOrdersStacks() as $orderStack) {
            $orders[] = [
                'order_id' => $orderStack->getOrdId()?->getId(),
                'status' => $orderStack->getOrdCntStatus()
            ];
        }

        return $this->json([
            'id' => $stack->getId(),
            'title' => $stack->getStcTitle(),
            'direction_id' => $stack->getDrcId()?->getId(),
            'language_id' => $stack->getLngId()?->getId(),
            'orders' => $orders
        ]);
    }

    /**
     * Обновить стек (PUT /api/stacks/{stc_id})
     */
    #[Route('/{stc_id}', name: 'stacks_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('stack', options: ['mapping' => ['stc_id' => 'id']])]
    public function edit(Request $request, Stacks $stack, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $stack->setStcTitle($data['title']);
        }

        if (isset($data['direction_id'])) {
            // Загрузить сущность Directions и установить
            $stack->setDrcId($data['direction_id']);
        }

        if (isset($data['language_id'])) {
            // Загрузить сущность Languages и установить
            $stack->setLngId($data['language_id']);
        }

        $entityManager->flush();

        $orders = [];
        foreach ($stack->getOrdersStacks() as $orderStack) {
            $orders[] = [
                'order_id' => $orderStack->getOrdId()?->getId(),
                'status' => $orderStack->getOrdCntStatus()
            ];
        }

        return $this->json([
            'id' => $stack->getId(),
            'title' => $stack->getStcTitle(),
            'direction_id' => $stack->getDrcId()?->getId(),
            'language_id' => $stack->getLngId()?->getId(),
            'orders' => $orders
        ]);
    }

    /**
     * Удалить стек (DELETE /api/stacks/{stc_id})
     */
    #[Route('/{stc_id}', name: 'stacks_delete', methods: ['DELETE'])]
    #[ParamConverter('stack', options: ['mapping' => ['stc_id' => 'id']])]
    public function delete(Stacks $stack, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($stack);
        $entityManager->flush();

        return $this->json(['message' => 'Stack deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}

