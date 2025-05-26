<?php

namespace App\Controller;

use App\Entity\Chats;
use App\Entity\Messages;
use App\Entity\Contractors;
use App\Entity\Customers;
use App\Entity\User;
use App\Entity\Orders;
use App\Entity\OrderContractors;
use App\Repository\ChatsRepository;
use App\Repository\ContractorsRepository;
use App\Repository\CustomersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/chats')]
class ChatsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/check/{contractorId}', name: 'check_chat', methods: ['GET'])]
    public function checkChat(int $contractorId, UserInterface $user): JsonResponse
    {
        try {
            $customer = $user->getCustomers();
            if (!$customer) {
                return $this->json(['error' => 'Только заказчики могут проверять чаты'], 403);
            }

            $contractor = $this->entityManager->getRepository(Contractors::class)->find($contractorId);
            if (!$contractor) {
                return $this->json(['error' => 'Исполнитель не найден'], 404);
            }

            $chat = $this->entityManager->getRepository(Chats::class)->findOneBy([
                'cst_id' => $customer,
                'cnt_id' => $contractor
            ]);

            return $this->json([
                'exists' => $chat !== null,
                'chatId' => $chat ? $chat->getId() : null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при проверке чата',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/create', name: 'create_chat', methods: ['POST'])]
    public function createChat(Request $request, UserInterface $user): JsonResponse
    {
        $customer = $user->getCustomers();
        
        if (!$customer) {
            return $this->json(['error' => 'Только заказчики могут создавать чаты'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $contractorId = $data['contractorId'] ?? null;

        if (!$contractorId) {
            return $this->json(['error' => 'ID исполнителя обязателен'], 400);
        }

        $contractor = $this->entityManager->getRepository(Contractors::class)->find($contractorId);
        if (!$contractor) {
            return $this->json(['error' => 'Исполнитель не найден'], 404);
        }

        // Проверяем, существует ли уже чат
        $existingChat = $this->entityManager->getRepository(Chats::class)->findOneBy([
            'cst_id' => $customer,
            'cnt_id' => $contractor
        ]);

        if ($existingChat) {
            return $this->json([
                'id' => $existingChat->getId(),
                'message' => 'Чат уже существует'
            ]);
        }

        // Создаем новый чат
        $chat = new Chats();
        $chat->setCstId($customer);
        $chat->setCntId($contractor);

        $this->entityManager->persist($chat);
        $this->entityManager->flush();

        return $this->json([
            'id' => $chat->getId(),
            'message' => 'Чат успешно создан'
        ]);
    }

    #[Route('/{id}', name: 'get_chat', methods: ['GET'])]
    public function getChat(int $id, UserInterface $user): JsonResponse
    {
        try {
            $chat = $this->entityManager->getRepository(Chats::class)->find($id);
            
            if (!$chat) {
                return $this->json(['error' => 'Чат не найден'], 404);
            }

            // Проверяем, имеет ли пользователь доступ к этому чату
            $customer = $user->getCustomers();
            $contractor = $user->getContractors();

//            if (($customer && $chat->getCstId() !== $customer) ||
//                ($contractor && $chat->getCntId() !== $contractor)) {
//                return $this->json(['error' => 'Доступ запрещен'], 403);
//            }

            $contractor = $chat->getCntId();
            $user = $contractor->getUsrId();

            return $this->json([
                'id' => $chat->getId(),
                'contractor' => [
                    'id' => $contractor->getId(),
                    'user' => [
                        'name' => $user->getUsrName(),
                        'surname' => $user->getUsrSurname()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при получении чата',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/messages', name: 'get_messages', methods: ['GET'])]
    public function getMessages(int $id, UserInterface $user): JsonResponse
    {
        try {
            $chat = $this->entityManager->getRepository(Chats::class)->find($id);
            
            if (!$chat) {
                return $this->json(['error' => 'Чат не найден'], 404);
            }

            // Проверяем доступ
            $customer = $user->getCustomers();
            $contractor = $user->getContractors();

//            if (($customer && $chat->getCstId() !== $customer) ||
//                ($contractor && $chat->getCntId() !== $contractor)) {
//                return $this->json(['error' => 'Доступ запрещен'], 403);
//            }

            $messages = $chat->getMessages();
            $messagesArray = [];

            foreach ($messages as $message) {
                $messagesArray[] = [
                    'id' => $message->getId(),
                    'msg_text' => $message->getMsgText(),
                    'msg_timestamp' => $message->getMsgTimestamp()->format('Y-m-d H:i:s'),
                    'cst_id' => $message->getCstId() ? $message->getCstId()->getId() : null,
                    'cnt_id' => $message->getCntId() ? $message->getCntId()->getId() : null
                ];
            }

            return $this->json($messagesArray);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при получении сообщений',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/messages', name: 'send_message', methods: ['POST'])]
    public function sendMessage(int $id, Request $request, UserInterface $user): JsonResponse
    {
        try {
            $chat = $this->entityManager->getRepository(Chats::class)->find($id);
            
            if (!$chat) {
                return $this->json(['error' => 'Чат не найден'], 404);
            }

            // Проверяем доступ
            $customer = $user->getCustomers();
            $contractor = $user->getContractors();

//            if (($customer && $chat->getCstId() !== $customer) ||
//                ($contractor && $chat->getCntId() !== $contractor)) {
//                return $this->json(['error' => 'Доступ запрещен'], 403);
//            }

            $data = json_decode($request->getContent(), true);
            $text = $data['text'] ?? '';

            if (empty($text)) {
                return $this->json(['error' => 'Текст сообщения не может быть пустым'], 400);
            }

            $message = new Messages();
            $message->setChatId($chat);
            $message->setMsgText($text);
            $message->setMsgTimestamp(new \DateTime());

            // Устанавливаем отправителя
            if ($customer) {
                $message->setCstId($customer);
                $message->setCntId(null);
            } else {
                $message->setCstId(null);
                $message->setCntId($contractor);
            }

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            return $this->json([
                'id' => $message->getId(),
                'msg_text' => $message->getMsgText(),
                'msg_timestamp' => $message->getMsgTimestamp()->format('Y-m-d H:i:s'),
                'cst_id' => $message->getCstId() ? $message->getCstId()->getId() : null,
                'cnt_id' => $message->getCntId() ? $message->getCntId()->getId() : null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при отправке сообщения',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('', name: 'get_chats', methods: ['GET'])]
    public function getChats(UserInterface $user): JsonResponse
    {
        try {
            $customer = $user->getCustomers();
            $contractor = $user->getContractors();

            if (!$customer && !$contractor) {
                return $this->json(['error' => 'Доступ запрещен'], 403);
            }

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('c', 'm')
               ->from(Chats::class, 'c')
               ->leftJoin('c.messages', 'm')
               ->where('c.cst_id = :user OR c.cnt_id = :user')
               ->setParameter('user', $customer ?? $contractor)
               ->orderBy('m.msg_timestamp', 'DESC');

            $chats = $qb->getQuery()->getResult();

            $result = [];
            foreach ($chats as $chat) {
                $messages = $chat->getMessages();
                $lastMessage = $messages->count() > 0 ? $messages->last() : null;

                $chatData = [
                    'id' => $chat->getId(),
                    'lastMessage' => $lastMessage ? [
                        'id' => $lastMessage->getId(),
                        'msg_text' => $lastMessage->getMsgText(),
                        'msg_timestamp' => $lastMessage->getMsgTimestamp()->format('Y-m-d H:i:s'),
                        'cst_id' => $lastMessage->getCstId() ? $lastMessage->getCstId()->getId() : null,
                        'cnt_id' => $lastMessage->getCntId() ? $lastMessage->getCntId()->getId() : null
                    ] : null
                ];

                // Добавляем информацию о собеседнике
                if ($customer) {
                    $contractor = $chat->getCntId();
                    $user = $contractor->getUsrId();
                    $chatData['contractor'] = [
                        'id' => $contractor->getId(),
                        'user' => [
                            'name' => $user->getUsrName(),
                            'surname' => $user->getUsrSurname()
                        ]
                    ];
                } else {
                    $customer = $chat->getCstId();
                    $user = $customer->getUsrId();
                    $chatData['customer'] = [
                        'id' => $customer->getId(),
                        'user' => [
                            'name' => $user->getUsrName(),
                            'surname' => $user->getUsrSurname()
                        ]
                    ];
                }

                $result[] = $chatData;
            }

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при получении списка чатов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/check-order/{orderId}', name: 'check_order_chat', methods: ['GET'])]
    public function checkOrderChat(int $orderId, UserInterface $user): JsonResponse
    {
        try {
            error_log("Starting checkOrderChat for orderId: " . $orderId);
            
            $contractor = $user->getContractors();
            error_log("Contractor: " . ($contractor ? $contractor->getId() : 'null'));
            
//            if (!$contractor) {
//                return $this->json(['error' => 'Только подрядчики могут проверять чаты заказов'], 403);
//            }

            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            error_log("Order: " . ($order ? $order->getId() : 'null'));
            
            if (!$order) {
                return $this->json(['error' => 'Заказ не найден'], 404);
            }

            // Проверяем, что заказ принадлежит этому подрядчику
//            $orderContractor = $this->entityManager->getRepository(OrderContractors::class)
//                ->findOneBy(['ord_id' => $order, 'cnt_id' => $contractor]);
//            error_log("OrderContractor: " . ($orderContractor ? 'found' : 'not found'));
//
//            if (!$orderContractor) {
//                return $this->json(['error' => 'Доступ запрещен'], 403);
//            }

            // Получаем заказчика из заказа
            $customer = $order->getCstId();
            error_log("Customer: " . ($customer ? $customer->getId() : 'null'));
            
            if (!$customer) {
                return $this->json(['error' => 'Заказчик не найден'], 404);
            }

            // Проверяем существование чата между подрядчиком и заказчиком
            $chat = $this->entityManager->getRepository(Chats::class)->findOneBy([
                'cst_id' => $customer,
                'cnt_id' => $contractor
            ]);
            error_log("Chat: " . ($chat ? $chat->getId() : 'not found'));

            return $this->json([
                'exists' => $chat !== null,
                'chatId' => $chat ? $chat->getId() : null
            ]);
        } catch (\Exception $e) {
            // Добавляем подробное логирование ошибки
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            
            // Логируем ошибку
            error_log('Error in checkOrderChat: ' . json_encode($errorDetails));
            
            return $this->json([
                'error' => 'Произошла ошибка при проверке чата',
                'details' => $errorDetails
            ], 500);
        }
    }

    #[Route('/create-order', name: 'create_order_chat', methods: ['POST'])]
    public function createOrderChat(Request $request, UserInterface $user): JsonResponse
    {
        try {
            $contractor = $user->getContractors();
//            if (!$contractor) {
//                return $this->json(['error' => 'Только подрядчики могут создавать чаты заказов'], 403);
//            }

            $data = json_decode($request->getContent(), true);
            $orderId = $data['orderId'] ?? null;

//            if (!$orderId) {
//                return $this->json(['error' => 'ID заказа обязателен'], 400);
//            }

            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            if (!$order) {
                return $this->json(['error' => 'Заказ не найден'], 404);
            }

            // Проверяем, что заказ принадлежит этому подрядчику
//            $orderContractor = $this->entityManager->getRepository(OrderContractors::class)
//                ->findOneBy(['ord_id' => $order, 'cnt_id' => $contractor]);
//
//            if (!$orderContractor) {
//                return $this->json(['error' => 'Доступ запрещен'], 403);
//            }

            // Получаем заказчика из заказа
            $customer = $order->getCstId();

            // Проверяем существование чата между подрядчиком и заказчиком
            $existingChat = $this->entityManager->getRepository(Chats::class)->findOneBy([
                'cst_id' => $customer,
                'cnt_id' => $contractor
            ]);

            if ($existingChat) {
                return $this->json([
                    'id' => $existingChat->getId(),
                    'message' => 'Чат уже существует'
                ]);
            }

            // Создаем новый чат
            $chat = new Chats();
            $chat->setCntId($contractor);
            $chat->setCstId($customer);

            $this->entityManager->persist($chat);
            $this->entityManager->flush();

            return $this->json([
                'id' => $chat->getId(),
                'message' => 'Чат успешно создан'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Произошла ошибка при создании чата',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 