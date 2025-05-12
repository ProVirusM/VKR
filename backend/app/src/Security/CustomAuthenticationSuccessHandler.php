<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthenticationSuccessHandler extends AuthenticationSuccessHandler
{
    // Меняем уровень доступа на protected
    protected JWTTokenManagerInterface $jwtManager;

    // Конструктор для инъекции зависимости
    public function __construct(
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->jwtManager = $jwtManager;
    }

    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): Response
    {
        // Если токен не передан, генерируем его вручную
        if ($jwt === null) {
            $jwt = $this->jwtManager->create($user);
        }

        // Получаем имя и email пользователя
        $data = [
            'token' => $jwt,
            'name' => $user->getUsrName(),
            'email' => $user->getEmail(),
            'id' => $user->getId(),
        ];

        // Возвращаем кастомный JSON ответ
        return new JsonResponse($data);
    }
}