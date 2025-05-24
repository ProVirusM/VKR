<?php

namespace App\Controller;

use App\Entity\Stacks;
use App\Entity\Directions;
use App\Entity\Languages;
use App\Repository\StacksRepository;
use App\Repository\DirectionsRepository;
use App\Repository\LanguagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/stacks')]
class StacksController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StacksRepository $stacksRepository,
        private DirectionsRepository $directionsRepository,
        private LanguagesRepository $languagesRepository
    ) {}

    #[Route('/{languageId}/{directionId}', name: 'get_technologies', methods: ['GET'])]
    public function getStacks(int $languageId, int $directionId): JsonResponse
    {
        try {
            $stackEntities = $this->stacksRepository->findBy([
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

            return $this->json($stacks);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch stacks: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Получить все стеки технологий (GET /api/stacks)
     */
    #[Route('/', name: 'stacks_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $stacks = $this->stacksRepository->findAll();
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
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch stacks: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/all', name: 'stacks_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $stacks = $this->stacksRepository->findAll();
            $data = [];
            
            foreach ($stacks as $stack) {
                $data[] = [
                    'id' => $stack->getId(),
                    'title' => $stack->getStcTitle(),
                    'direction_id' => $stack->getDrcId()?->getId(),
                    'language_id' => $stack->getLngId()?->getId()
                ];
            }

            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch stacks: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Создать новый стек технологий (POST /api/stacks)
     */
    #[Route('/', name: 'stacks_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['title'], $data['direction_id'], $data['language_id'])) {
                return $this->json(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $direction = $this->directionsRepository->find($data['direction_id']);
            if (!$direction) {
                return $this->json(['error' => 'Direction not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $language = $this->languagesRepository->find($data['language_id']);
            if (!$language) {
                return $this->json(['error' => 'Language not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $stack = new Stacks();
            $stack->setStcTitle($data['title']);
            $stack->setDrcId($direction);
            $stack->setLngId($language);

            $this->entityManager->persist($stack);
            $this->entityManager->flush();

            return $this->json([
                'id' => $stack->getId(),
                'title' => $stack->getStcTitle(),
                'direction_id' => $stack->getDrcId()?->getId(),
                'language_id' => $stack->getLngId()?->getId()
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create stack: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Получить стек по ID (GET /api/stacks/{stc_id})
     */
    #[Route('/{id}', name: 'stacks_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $stack = $this->stacksRepository->find($id);
            
            if (!$stack) {
                return $this->json(['error' => 'Stack not found'], JsonResponse::HTTP_NOT_FOUND);
            }

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
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch stack: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Обновить стек (PUT /api/stacks/{stc_id})
     */
    #[Route('/{id}', name: 'stacks_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $stack = $this->stacksRepository->find($id);
            
            if (!$stack) {
                return $this->json(['error' => 'Stack not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['title'])) {
                $stack->setStcTitle($data['title']);
            }

            if (isset($data['direction_id'])) {
                $stack->setDrcId($data['direction_id']);
            }

            if (isset($data['language_id'])) {
                $stack->setLngId($data['language_id']);
            }

            $this->entityManager->flush();

            return $this->json([
                'id' => $stack->getId(),
                'title' => $stack->getStcTitle(),
                'direction_id' => $stack->getDrcId()?->getId(),
                'language_id' => $stack->getLngId()?->getId()
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update stack: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Удалить стек (DELETE /api/stacks/{stc_id})
     */
    #[Route('/{id}', name: 'stacks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $stack = $this->stacksRepository->find($id);
            
            if (!$stack) {
                return $this->json(['error' => 'Stack not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($stack);
            $this->entityManager->flush();

            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete stack: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

