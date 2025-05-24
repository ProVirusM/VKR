<?php

namespace App\Controller;

use App\Entity\Languages;
use App\Repository\LanguagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\StacksRepository;
#[Route('/api/languages')] // Префикс /api для REST API
class LanguagesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LanguagesRepository $languagesRepository
    ) {}

    /**
     * Получить список всех языков (GET /api/languages)
     */
    #[Route('/{directionId}', name: 'get_languages2', methods: ['GET'])]
    public function getLanguages(int $directionId, StacksRepository $stacksRepository): JsonResponse
    {
        $stacks = $stacksRepository->findBy(['drc_id' => $directionId]);

        $uniqueLanguages = [];

        foreach ($stacks as $stack) {
            $language = $stack->getLngId();
            $languageId = $language->getId();

            if (!isset($uniqueLanguages[$languageId])) {
                $uniqueLanguages[$languageId] = [
                    'id' => $languageId,
                    'title' => $language->getLngTitle(),
                ];
            }
        }

        // Возвращаем только уникальные значения
        return new JsonResponse(array_values($uniqueLanguages));
    }

    #[Route('/', name: 'languages_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $languages = $this->languagesRepository->findAll();
        $data = [];
        foreach ($languages as $language) {
            $data[] = [
                'id' => $language->getId(),
                'title' => $language->getLngTitle(),
            ];
        }
        return $this->json($data);
    }

    /**
     * Создать новый язык (POST /api/languages)
     */
    #[Route('/', name: 'languages_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $language = new Languages();
        $language->setLngTitle($data['title']);

        $this->entityManager->persist($language);
        $this->entityManager->flush();

        return $this->json([
            'id' => $language->getId(),
            'title' => $language->getLngTitle(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить один язык по ID (GET /api/languages/{lng_id})
     */
    #[Route('/{lng_id}', name: 'languages_show', methods: ['GET'])]
    #[ParamConverter('language', options: ['mapping' => ['lng_id' => 'lng_id']])]
    public function show(Languages $language): JsonResponse
    {
        return $this->json([
            'id' => $language->getId(),
            'title' => $language->getLngTitle(),
        ]);
    }

    /**
     * Обновить язык (PUT /api/languages/{id})
     */
    #[Route('/{id}', name: 'languages_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        $language = $this->languagesRepository->find($id);
        
        if (!$language) {
            return $this->json(['error' => 'Language not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $language->setLngTitle($data['title']);
            $this->entityManager->flush();

            return $this->json([
                'id' => $language->getId(),
                'title' => $language->getLngTitle(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update language: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Удалить язык (DELETE /api/languages/{lng_id})
     */
    #[Route('/{id}', name: 'languages_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $language = $this->languagesRepository->find($id);
        
        if (!$language) {
            return $this->json(['error' => 'Language not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($language);
            $this->entityManager->flush();
            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete language: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
