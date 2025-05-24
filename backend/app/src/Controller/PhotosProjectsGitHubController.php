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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api/project-photos')]
class PhotosProjectsGitHubController extends AbstractController
{
    private $slugger;
    private $uploadDir;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $parameterBag)
    {
        $this->slugger = $slugger;
        $projectDir = $parameterBag->get('kernel.project_dir');
        $this->uploadDir = $projectDir . '/public/uploads/projects/';
        
        // Создаем директорию, если она не существует
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Получить все фотографии проекта (GET /api/project-photos/project/{project_id})
     */
    #[Route('/project/{project_id}', name: 'photos_projects_github_by_project', methods: ['GET'])]
    public function getByProject(int $project_id, PhotosProjectsGitHubRepository $photosProjectsGitHubRepository): JsonResponse
    {
        $photos = $photosProjectsGitHubRepository->findBy(['pgh_id' => $project_id]);

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
     * Загрузить фотографию проекта (POST /api/project-photos/upload/{project_id})
     */
    #[Route('/upload/{project_id}', name: 'photos_projects_github_upload', methods: ['POST'])]
    public function upload(Request $request, int $project_id, EntityManagerInterface $entityManager): JsonResponse
    {
        $file = $request->files->get('photo');
        
        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Генерируем уникальное имя файла
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->uploadDir, $newFilename);
             // Log successful file move
            error_log('File successfully moved to: ' . $this->uploadDir . $newFilename);
        } catch (\Exception $e) {
             // Log file move error
            error_log('Failed to move file: ' . $e->getMessage());
            return $this->json(['error' => 'Failed to upload file'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $photo = new PhotosProjectsGitHub();
        $photo->setPpghLink('/uploads/projects/' . $newFilename);

        // Получаем сущность ProjectsGitHub по ID
        $project = $entityManager->getRepository(\App\Entity\ProjectsGitHub::class)->find($project_id);

        if (!$project) {
             // Log project not found
            error_log('Project with ID ' . $project_id . ' not found.');
            return $this->json(['error' => 'Project not found'], JsonResponse::HTTP_NOT_FOUND);
        }
         // Log project found
        error_log('Project with ID ' . $project_id . ' found.');

        $photo->setPghId($project);

        try {
            $entityManager->persist($photo);
             // Log photo entity persisted
            error_log('PhotosProjectsGitHub entity persisted.');
            $entityManager->flush();
             // Log entity manager flushed
            error_log('EntityManager flushed successfully.');
        } catch (\Exception $e) {
             // Log database error
            error_log('Database error while saving photo: ' . $e->getMessage());
            return $this->json(['error' => 'Failed to save photo to database'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'id' => $photo->getId(),
            'link' => $photo->getPpghLink(),
            'project_id' => $photo->getPghId()?->getId()
        ], JsonResponse::HTTP_CREATED);
    }

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
