<?php
// src/Controller/Api/ProfileController.php

// src/Controller/Api/ProfileController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/api/profile", name="api_profile", methods={"GET"})
     */
    #[Route('/api/profile', name: 'profile', methods: ['GET'])]
    public function getProfile(Request $request, UserInterface $user): JsonResponse
    {
        // Выводим информацию о пользователе
        return $this->json([
            'name' => $user->getUsrName(), // Пример поля для имени
            'email' => $user->getEmail(),
            'id' => $user->getId(),
        ]);
    }
}
