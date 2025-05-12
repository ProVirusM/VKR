<?php

namespace App\Controller;

use App\Entity\Directions;
use App\Repository\DirectionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/directions')] // Префикс /api для REST API
class DirectionsController extends AbstractController
{
    /**
     * Получить список всех направлений (GET /api/directions)
     */
    #[Route('/', name: 'directions_index', methods: ['GET'])]
    public function index(DirectionsRepository $directionsRepository): JsonResponse
    {
        // Получаем все языки
        $directions = $directionsRepository->findAll();

        // Преобразуем в JSON-формат
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
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Декодируем JSON
        $data = json_decode($request->getContent(), true);

        // Проверяем, есть ли переданный параметр title
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Создаем новый объект
        $direction = new Directions();
        $direction->setDrcTitle($data['title']);

        // Сохраняем в базе данных
        $entityManager->persist($direction);
        $entityManager->flush();

        return $this->json([
            'id' => $direction->getId(),
            'title' => $direction->getDrcTitle(),
        ], JsonResponse::HTTP_CREATED);
    }
    /**
     * Получить одно направление по ID (GET /api/directions/{drc_id})
     */
    #[Route('/{drc_id}', name: 'direction_show', methods: ['GET'])]
    #[ParamConverter('direction', options: ['mapping' => ['drc_id' => 'drc_id']])]
    public function show(Directions $direction): JsonResponse
    {
        return $this->json([
            'id'=>$direction->getId(),
            'title' => $direction->getDrcTitle(),
        ]);
    }
    /**
     * Обновить язык (PUT /api/directions/{drc_id})
     */
    #[Route('/{drc_id}', name: 'direction_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('direction', options: ['mapping' => ['drc_id' => 'drc_id']])]
    public function edit(Request $request, Directions $direction, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $direction->setDrcTitle($data['title']);
        //$entityManager->persist($direction);
        $entityManager->flush();
        return $this->json([
            'id' => $direction->getId(),
            'title' => $direction->getDrcTitle(),
        ]);
    }
    /**
     * Удалить направление (DELETE /api/directions/{drc_id})
     */
    #[Route('/{drc_id}', name: 'direction_delete', methods: ['DELETE'])]
    #[ParamConverter('direction', options: ['mapping' => ['drc_id' => 'drc_id']])]
    public function delete(Directions $direction, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($direction);
        $entityManager->flush();
        return $this->json(['message' => 'Directions deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }

}