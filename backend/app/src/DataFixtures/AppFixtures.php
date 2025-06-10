<?php

namespace App\DataFixtures;

use App\Entity\Directions;
use App\Entity\Languages;
use App\Entity\Stacks;
use App\Entity\Contractors;
use App\Entity\Customers;
use App\Entity\User;
use App\Entity\Feedbacks;
use App\Entity\Orders;
use App\Entity\OrdersStacks;
use App\Entity\OrdersContractors;
use App\Entity\ProjectsGitHub;
use App\Entity\PhotosProjectsGitHub;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Создаем направления разработки
        $directions = ['Веб-разработка', 'Мобильная разработка', 'Data Science', 'DevOps', 'Дизайн'];
        $directionEntities = [];
        foreach ($directions as $dir) {
            $direction = new Directions();
            $direction->setDrcTitle($dir);
            $manager->persist($direction);
            $directionEntities[] = $direction;
        }

        // Создаем языки программирования
        $languages = ['PHP', 'JavaScript', 'Python', 'Java', 'C#'];
        $languageEntities = [];
        foreach ($languages as $lang) {
            $language = new Languages();
            $language->setLngTitle($lang);
            $manager->persist($language);
            $languageEntities[] = $language;
        }

        // Создаем стеки технологий
        $stacks = [
            ['Symfony', $directionEntities[0], $languageEntities[0]],
            ['Laravel', $directionEntities[0], $languageEntities[0]],
            ['React', $directionEntities[0], $languageEntities[1]],
            ['Node.js', $directionEntities[0], $languageEntities[1]],
            ['Django', $directionEntities[2], $languageEntities[2]],
            ['Flask', $directionEntities[2], $languageEntities[2]],
            ['Spring', $directionEntities[1], $languageEntities[3]],
            ['.NET', $directionEntities[1], $languageEntities[4]],
        ];
        $stackEntities = [];
        foreach ($stacks as $stack) {
            $stackEntity = new Stacks();
            $stackEntity->setStcTitle($stack[0]);
            $stackEntity->setDrcId($stack[1]);
            $stackEntity->setLngId($stack[2]);
            $manager->persist($stackEntity);
            $stackEntities[] = $stackEntity;
        }

        // Создаем пользователей
        $users = [
            ['Иван', 'Иванов', 'Иванович', 'ivan@example.com', 'password123', true, ["admin"]],
            ['Петр', 'Петров', 'Петрович', 'petr@example.com', 'password123', true, ["contractor"]],
            ['Алексей', 'Сидоров', 'Алексеевич', 'alex@example.com', 'password123', false, ["customer"]],
            ['Мария', 'Смирнова', 'Олеговна', 'maria@example.com', 'password123', false, ["customer"]],
        ];

        $userEntities = [];
        $contractorEntities = [];
        $customerEntities = [];

        foreach ($users as $userData) {

            $user = new User();
            $user->setUsrName($userData[0]);
            $user->setUsrSurname($userData[1]);
            $user->setUsrPatronymic($userData[2]);
            $user->setEmail($userData[3]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userData[4]));
            $user->setRoles($userData[6]);
            $manager->persist($user);
            $userEntities[] = $user;

            if ($userData[5]) { // Если это исполнитель
                $contractor = new Contractors();
                $contractor->setCntText("Опытный разработчик в области " . $directionEntities[rand(0, count($directionEntities)-1)]->getDrcTitle());
                $contractor->setUsrId($user);
                $user->setContractors($contractor);
                $manager->persist($contractor);
                $contractorEntities[] = $contractor;
            } else { // Если это заказчик
                $customer = new Customers();
                $customer->setUsrId($user);
                $user->setCustomers($customer);
                $manager->persist($customer);
                $customerEntities[] = $customer;
            }
        }

        // Создаем проекты GitHub для исполнителей
        $githubProjects = [
            ['Интернет-магазин', 'https://github.com/user/shop', 'Полнофункциональный интернет-магазин'],
            ['Блог платформа', 'https://github.com/user/blog', 'Платформа для ведения блогов'],
            ['CRM система', 'https://github.com/user/crm', 'Система управления клиентами'],
        ];

        $projectEntities = [];
        foreach ($githubProjects as $i => $project) {
            $githubProject = new ProjectsGitHub();
            $githubProject->setPghName($project[0]);
            $githubProject->setPghRepository($project[1]);
            $githubProject->setPghText($project[2]);
            $githubProject->setCntId($contractorEntities[$i % count($contractorEntities)]);
            $manager->persist($githubProject);
            $projectEntities[] = $githubProject;

            // Добавляем фото к проектам
            $photo = new PhotosProjectsGitHub();
            $photo->setPpghLink('https://example.com/project'.$i.'/image1.jpg');
            $photo->setPghId($githubProject);
            $manager->persist($photo);
        }

        // Создаем заказы
        $orders = [
            ['Разработка сайта', 'Нужен сайт для компании', 'В работе', 50000, '2 месяца'],
            ['Мобильное приложение', 'Требуется приложение для iOS и Android', 'Новый', 150000, '3 месяца'],
            ['Чат бот', 'Необходим телеграм бот для поддержки', 'Завершен', 30000, '1 месяц'],
        ];

        $orderEntities = [];
        foreach ($orders as $i => $order) {
            $orderEntity = new Orders();
            $orderEntity->setOrdTitle($order[0]);
            $orderEntity->setOrdText($order[1]);
            $orderEntity->setOrdStatus($order[2]);
            $orderEntity->setOrdPrice($order[3]);
            $orderEntity->setOrdTime($order[4]);
            $orderEntity->setCstId($customerEntities[$i % count($customerEntities)]);
            $manager->persist($orderEntity);
            $orderEntities[] = $orderEntity;

            // Добавляем стеки к заказам
            $orderStack = new OrdersStacks();
            $orderStack->setOrdId($orderEntity);
            $orderStack->setStcId($stackEntities[rand(0, count($stackEntities)-1)]);
            $manager->persist($orderStack);

            // Назначаем исполнителей на заказы
            if ($i < count($contractorEntities)) {
                $orderContractor = new OrdersContractors();
                $orderContractor->setOrdId($orderEntity);
                $orderContractor->setCntId($contractorEntities[$i]);
                $orderContractor->setOrdCntStatus('Назначен');
                $manager->persist($orderContractor);
            }
        }

        // Создаем отзывы
        foreach ($orderEntities as $i => $order) {
            if ($order->getOrdStatus() === 'Завершен' && isset($customerEntities[$i]) && isset($contractorEntities[$i])) {
                $feedback = new Feedbacks();
                $feedback->setFdbText('Отличная работа, все сделано в срок!');
                $feedback->setFdbEstimation(5);
                $feedback->setFdbTimestamp(new \DateTime());
                $feedback->setCntId($contractorEntities[$i]);
                $feedback->setCstId($customerEntities[$i]);
                $manager->persist($feedback);
            }
        }

        $manager->flush();
    }
}