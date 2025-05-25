<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\ChatServer;
use Symfony\Component\Dotenv\Dotenv;

// Загружаем переменные окружения
$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__).'/.env');

// Создаем сервер
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

echo "WebSocket сервер запущен на порту 8080\n";
$server->run(); 