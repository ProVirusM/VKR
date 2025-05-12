<?php

namespace App\Controller;

use App\Entity\ProjectsGitHub;
use App\Repository\ProjectsGitHubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/projects-github')]
class ProjectsGitHubController extends AbstractController
{
    /**
     * Получить все проекты GitHub (GET /api/projects-github)
     */
    #[Route('/', name: 'projects_github_index', methods: ['GET'])]
    public function index(ProjectsGitHubRepository $projectsGitHubRepository): JsonResponse
    {
        $projects = $projectsGitHubRepository->findAll();

        $data = [];
        foreach ($projects as $project) {
            $photos = [];
            foreach ($project->getPhotosProjectsGitHubs() as $photo) {
                $photos[] = [
                    'id' => $photo->getId(),
                    'link' => $photo->getPpghLink()
                ];
            }

            $data[] = [
                'id' => $project->getId(),
                'name' => $project->getPghName(),
                'repository' => $project->getPghRepository(),
                'text' => $project->getPghText(),
                'contractor_id' => $project->getCntId()?->getId(),
                'photos' => $photos
            ];
        }

        return $this->json($data);
    }

    /**
     * Создать новый проект GitHub (POST /api/projects-github)
     */
    #[Route('/', name: 'projects_github_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверяем обязательные поля
        $requiredFields = ['name', 'repository', 'text', 'contractor_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing \"$field\" field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $project = new ProjectsGitHub();
        $project->setPghName($data['name']);
        $project->setPghRepository($data['repository']);
        $project->setPghText($data['text']);

        // В реальном приложении нужно загрузить сущность Contractors
        // $contractor = $entityManager->getRepository(Contractors::class)->find($data['contractor_id']);
        // $project->setCntId($contractor);

        // Временно используем сеттер с ID
        $project->setCntId($data['contractor_id']);

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getPghName(),
            'repository' => $project->getPghRepository(),
            'text' => $project->getPghText(),
            'contractor_id' => $project->getCntId()?->getId()
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Получить проект по ID (GET /api/projects-github/{pgh_id})
     */
    #[Route('/{pgh_id}', name: 'projects_github_show', methods: ['GET'])]
    #[ParamConverter('project', options: ['mapping' => ['pgh_id' => 'id']])]
    public function show(ProjectsGitHub $project): JsonResponse
    {
        $photos = [];
        foreach ($project->getPhotosProjectsGitHubs() as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'link' => $photo->getPpghLink()
            ];
        }

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getPghName(),
            'repository' => $project->getPghRepository(),
            'text' => $project->getPghText(),
            'contractor_id' => $project->getCntId()?->getId(),
            'photos' => $photos
        ]);
    }

    /**
     * Обновить проект (PUT /api/projects-github/{pgh_id})
     */
    #[Route('/{pgh_id}', name: 'projects_github_edit', methods: ['PUT', 'PATCH'])]
    #[ParamConverter('project', options: ['mapping' => ['pgh_id' => 'id']])]
    public function edit(Request $request, ProjectsGitHub $project, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $project->setPghName($data['name']);
        }

        if (isset($data['repository'])) {
            $project->setPghRepository($data['repository']);
        }

        if (isset($data['text'])) {
            $project->setPghText($data['text']);
        }

        if (isset($data['contractor_id'])) {
            // Загрузить сущность Contractors и установить
            $project->setCntId($data['contractor_id']);
        }

        $entityManager->flush();

        $photos = [];
        foreach ($project->getPhotosProjectsGitHubs() as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'link' => $photo->getPpghLink()
            ];
        }

        return $this->json([
            'id' => $project->getId(),
            'name' => $project->getPghName(),
            'repository' => $project->getPghRepository(),
            'text' => $project->getPghText(),
            'contractor_id' => $project->getCntId()?->getId(),
            'photos' => $photos
        ]);
    }

    /**
     * Удалить проект (DELETE /api/projects-github/{pgh_id})
     */
    #[Route('/{pgh_id}', name: 'projects_github_delete', methods: ['DELETE'])]
    #[ParamConverter('project', options: ['mapping' => ['pgh_id' => 'id']])]
    public function delete(ProjectsGitHub $project, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($project);
        $entityManager->flush();

        return $this->json(['message' => 'GitHub project deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }
}
