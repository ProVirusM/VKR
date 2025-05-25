<?php

// src/Controller/UserController.php

namespace App\Controller;

// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Customers;
use App\Entity\Contractors;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct()
    {
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new JsonEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    // Получить всех пользователей
    #[Route('/api/users', name: 'api_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();

        // Преобразуем пользователей в JSON
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    // Получить одного пользователя по ID
    #[Route('/api/users/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(["message" => "User not found"], Response::HTTP_NOT_FOUND);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // Создание нового пользователя
//    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
//    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
//    {
//        $data = json_decode($request->getContent(), true);
//
//        $user = new User();
//        $user->setUsername($data['username']);
//        $user->setPassword($data['password']); // Здесь вам нужно будет хешировать пароль!
//
//        $em->persist($user);
//        $em->flush();
//
//        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
//
//        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
//    }

    // Удаление пользователя
    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(["message" => "User not found"], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(["message" => "User deleted successfully"], Response::HTTP_NO_CONTENT);
    }
    #[Route('/api/register', name: 'api_create', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Валидируем поля
        if (!isset($data['usr_name'], $data['usr_surname'], $data['usr_patronymic'], $data['email'], $data['password'], $data['role'])) {
            return new JsonResponse(['error' => 'Не все обязательные поля заполнены'], Response::HTTP_BAD_REQUEST);
        }

        // Проверяем, существует ли пользователь с таким email
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Пользователь с таким email уже существует'], Response::HTTP_CONFLICT);
        }

        // Создаем нового пользователя
        $user = new User();
        $user->setUsrName($data['usr_name']);
        $user->setUsrSurname($data['usr_surname']);
        $user->setUsrPatronymic($data['usr_patronymic']);
        $user->setEmail($data['email']);

        // Устанавливаем роль из запроса
        $user->setRoles([$data['role']]);

        // Хэшируем пароль
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Сохраняем пользователя в базе данных
        $entityManager->persist($user);
        $entityManager->flush();

        // Создаем соответствующую запись в зависимости от роли
        if ($data['role'] === 'customer') {
            $customer = new Customers();
            $customer->setUsrId($user);
            $entityManager->persist($customer);
        } elseif ($data['role'] === 'contractor') {
            $contractor = new Contractors();
            $contractor->setUsrId($user);
            // Устанавливаем описание из запроса или пустую строку
            $contractor->setCntText($data['description'] ?? '');
            $entityManager->persist($contractor);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getUsrName(),
                'surname' => $user->getUsrSurname(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ]
        ], Response::HTTP_CREATED);
    }
}
