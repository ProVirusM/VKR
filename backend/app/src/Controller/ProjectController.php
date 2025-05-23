<?php

namespace App\Controller;

use App\Entity\ProjectsGitHub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
} 