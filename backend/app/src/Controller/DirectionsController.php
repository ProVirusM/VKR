<?php

namespace App\Controller;

use App\Entity\Directions;
use App\Repository\DirectionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/directions')]
class DirectionsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DirectionsRepository $directionsRepository
    ) {}

    /**
     * Получить список всех направлений (GET /api/directions)
     */
    #[Route('/', name: 'directions_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $directions = $this->directionsRepository->findAll();
        $data = [];
        foreach ($directions as $direction) {
            $data[] = [
                'id' => $direction->getId(),
                'title' => $direction->getDrcTitle(),
            ];
        }
        return $this->json($data);
    }

    /**
     * Создать новое направение (POST /api/directions)
     */
    #[Route('/', name: 'directions_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $direction = new Directions();
            $direction->setDrcTitle($data['title']);

            $this->entityManager->persist($direction);
            $this->entityManager->flush();

            return $this->json([
                'id' => $direction->getId(),
                'title' => $direction->getDrcTitle(),
            ], JsonResponse::HTTP_CREATED);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'Direction with this title already exists'], JsonResponse::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create direction'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Получить одно направление по ID (GET /api/directions/{drc_id})
     */
    #[Route('/{id}', name: 'direction_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $direction = $this->directionsRepository->find($id);
        
        if (!$direction) {
            return $this->json(['error' => 'Direction not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $direction->getId(),
            'title' => $direction->getDrcTitle(),
        ]);
    }

    /**
     * Обновить язык (PUT /api/directions/{drc_id})
     */
    #[Route('/{id}', name: 'direction_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        $direction = $this->directionsRepository->find($id);
        
        if (!$direction) {
            return $this->json(['error' => 'Direction not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $direction->setDrcTitle($data['title']);
            $this->entityManager->flush();

            return $this->json([
                'id' => $direction->getId(),
                'title' => $direction->getDrcTitle(),
            ]);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'Direction with this title already exists'], JsonResponse::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update direction'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Удалить направление (DELETE /api/directions/{drc_id})
     */
    #[Route('/{id}', name: 'direction_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $direction = $this->directionsRepository->find($id);
        
        if (!$direction) {
            return $this->json(['error' => 'Direction not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($direction);
            $this->entityManager->flush();
            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete direction: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}