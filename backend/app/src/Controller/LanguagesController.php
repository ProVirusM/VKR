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
    /**
     * Получить список всех языков (GET /api/languages)
     */
    #[Route('/{directionId}', name: 'get_languages2', methods: ['GET'])]
    public function getLanguages(int $directionId, StacksRepository $stacksRepository): JsonResponse
    {
        // Найдем все языки для указанного направления
        $stacks = $stacksRepository->findBy(['drc_id' => $directionId]);
        $languages = [];

        foreach ($stacks as $stack) {
            $language = $stack->getLngId();
            $languages[] = [
                'id' => $language->getId(),
                'title' => $language->getLngTitle(),
            ];
        }

        return new JsonResponse($languages);
    }
    #[Route('/', name: 'languages_index', methods: ['GET'])]
    public function index(LanguagesRepository $languagesRepository): JsonResponse
    {
        // Получаем все языки
        $languages = $languagesRepository->findAll();

        // Преобразуем в JSON-формат
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
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Декодируем JSON
        $data = json_decode($request->getContent(), true);

        // Проверяем, есть ли переданный параметр title
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Создаем новый объект
        $language = new Languages();
        $language->setLngTitle($data['title']);

        // Сохраняем в базе данных
        $entityManager->persist($language);
        $entityManager->flush();

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
     * Обновить язык (PUT /api/languages/{lng_id})
     */
    #[Route('/{lng_id}', name: 'languages_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('language', options: ['mapping' => ['lng_id' => 'lng_id']])]
    public function edit(Request $request, Languages $language, EntityManagerInterface $entityManager): JsonResponse
    {
        // Декодируем JSON
        $data = json_decode($request->getContent(), true);

        // Проверяем наличие поля title
        if (!isset($data['title'])) {
            return $this->json(['error' => 'Missing "title" field'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Обновляем данные
        $language->setLngTitle($data['title']);
        $entityManager->flush();

        return $this->json([
            'id' => $language->getId(),
            'title' => $language->getLngTitle(),
        ]);
    }

    /**
     * Удалить язык (DELETE /api/languages/{lng_id})
     */
    #[Route('/{lng_id}', name: 'languages_delete', methods: ['DELETE'])]
    #[ParamConverter('language', options: ['mapping' => ['lng_id' => 'lng_id']])]
    public function delete(Languages $language, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($language);
        $entityManager->flush();

        return $this->json(['message' => 'Language deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}
