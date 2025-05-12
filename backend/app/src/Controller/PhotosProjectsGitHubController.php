<?php

namespace App\Controller;

use App\Entity\PhotosProjectsGitHub;
use App\Repository\PhotosProjectsGitHubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/project-photos')]
class PhotosProjectsGitHubController extends AbstractController
{
    /**
     * Получить все фотографии проектов (GET /api/project-photos)
     */
    #[Route('/', name: 'photos_projects_github_index', methods: ['GET'])]
    public function index(PhotosProjectsGitHubRepository $photosProjectsGitHubRepository): JsonResponse
    {
        $photos = $photosProjectsGitHubRepository->findAll();

        $data = [];
        foreach ($photos as $photo) {
            $data[] = [
                'id' => $photo->getId(),
                'link' => $photo->getPpghLink(),
                'project_id' => $photo->getPghId()?->getId(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новую фотографию проекта (POST /api/project-photos)
     */
    #[Route('/', name: 'photos_projects_github_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['link', 'project_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $photo = new PhotosProjectsGitHub();
        $photo->setPpghLink($data['link']);

        // В реальном приложении нужно загрузить сущность ProjectsGitHub
        // $project = $entityManager->getRepository(ProjectsGitHub::class)->find($data['project_id']);
        // $photo->setPghId($project);

        // Временно используем сеттер с ID
        $photo->setPghId($data['project_id']);

        $entityManager->persist($photo);
        $entityManager->flush();

        return $this->json([
            'id' => $photo->getId(),
            'link' => $photo->getPpghLink(),
            'project_id' => $photo->getPghId()?->getId(),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить фотографию по ID (GET /api/project-photos/{ppgh_id})
     */
    #[Route('/{ppgh_id}', name: 'photos_projects_github_show', methods: ['GET'])]
    #[ParamConverter('photo', options: ['mapping' => ['ppgh_id' => 'id']])]
    public function show(PhotosProjectsGitHub $photo): JsonResponse
    {
        return $this->json([
            'id' => $photo->getId(),
            'link' => $photo->getPpghLink(),
            'project_id' => $photo->getPghId()?->getId(),
        ]);
    }

    /**
     * Обновить фотографию проекта (PUT /api/project-photos/{ppgh_id})
     */
    #[Route('/{ppgh_id}', name: 'photos_projects_github_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('photo', options: ['mapping' => ['ppgh_id' => 'id']])]
    public function edit(Request $request, PhotosProjectsGitHub $photo, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['link'])) {
            $photo->setPpghLink($data['link']);
        }

        if (isset($data['project_id'])) {
            // Загрузить сущность ProjectsGitHub и установить
            $photo->setPghId($data['project_id']);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $photo->getId(),
            'link' => $photo->getPpghLink(),
            'project_id' => $photo->getPghId()?->getId(),
        ]);
    }

    /**
     * Удалить фотографию проекта (DELETE /api/project-photos/{ppgh_id})
     */
    #[Route('/{ppgh_id}', name: 'photos_projects_github_delete', methods: ['DELETE'])]
    #[ParamConverter('photo', options: ['mapping' => ['ppgh_id' => 'id']])]
    public function delete(PhotosProjectsGitHub $photo, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($photo);
        $entityManager->flush();

        return $this->json(['message' => 'Project photo deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}
