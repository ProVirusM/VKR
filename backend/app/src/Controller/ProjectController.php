<?php

namespace App\Controller;

use App\Entity\ProjectsGitHub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ProjectController extends AbstractController
{
    #[Route('/projects/{id}', name: 'api_project_get', methods: ['GET'])]
    //#[IsGranted('ROLE_USER')]
    public function getProject(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(ProjectsGitHub::class)->find($id);

        if (!$project) {
            return new JsonResponse(['message' => 'Проект не найден'], Response::HTTP_NOT_FOUND);
        }

        $photos = [];
        foreach ($project->getPhotosProjectsGitHubs() as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'link' => $photo->getPpghLink()
            ];
        }

        $data = [
            'id' => $project->getId(),
            'name' => $project->getPghName(),
            'text' => $project->getPghText(),
            'repository' => $project->getPghRepository(),
            'photos' => $photos
        ];

        return new JsonResponse($data);
    }

    #[Route('/projects/{id}', name: 'api_project_update', methods: ['PUT'])]
    public function updateProject(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(ProjectsGitHub::class)->find($id);

        if (!$project) {
            return new JsonResponse(['message' => 'Проект не найден'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $project->setPghName($data['name']);
        }

        if (isset($data['text'])) {
            $project->setPghText($data['text']);
        }

        if (isset($data['repository'])) {
            $project->setPghRepository($data['repository']);
        }

        $entityManager->flush();

        $photos = [];
        foreach ($project->getPhotosProjectsGitHubs() as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'link' => $photo->getPpghLink()
            ];
        }

        return new JsonResponse([
            'id' => $project->getId(),
            'name' => $project->getPghName(),
            'text' => $project->getPghText(),
            'repository' => $project->getPghRepository(),
            'photos' => $photos
        ]);
    }

    #[Route('/projects/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function deleteProject(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $entityManager->getRepository(ProjectsGitHub::class)->find($id);

        if (!$project) {
            return new JsonResponse(['message' => 'Проект не найден'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($project);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Проект успешно удален'], Response::HTTP_NO_CONTENT);
    }
} 