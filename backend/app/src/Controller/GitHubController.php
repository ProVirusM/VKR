<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubController extends AbstractController
{

    private HttpClientInterface $httpClient;
    private string $githubToken;
    public function __construct(HttpClientInterface $httpClient, string $githubToken)
    {
        $this->httpClient = $httpClient;
        $this->githubToken = $githubToken;
    }

    #[Route('/api/repo/{owner}/{repo}', name: 'github_repo_info', methods: ['GET'])]
    public function getRepositoryInfo(string $owner, string $repo): JsonResponse
    {
        try {
            $headers = [
                'Authorization' => 'Bearer ' . $this->githubToken, // ✅ Правильный формат
                'Accept' => 'application/vnd.github.v3+json'
            ];


            // Запрашиваем основную информацию о репозитории
            $response = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo", [
                'headers' => $headers
            ]);

            // Проверка на превышение лимита запросов
            if ($response->getStatusCode() === 403) {
                $remainingRequests = $response->getHeaders()['X-RateLimit-Remaining'][0];
                if ($remainingRequests == 0) {
                    $resetTime = $response->getHeaders()['X-RateLimit-Reset'][0];
                    return new JsonResponse([
                        'error' => 'Rate limit exceeded. Try again at ' . date('Y-m-d H:i:s', $resetTime)
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            $repoData = $response->toArray();

            // Функция для рекурсивного поиска файлов
            $files = $this->searchFilesInRepo($owner, $repo, '');

            // Извлекаем технологии из найденных файлов
            $technologies = $this->extractTechnologiesFromFiles($files, $owner, $repo);

            // Запрашиваем используемые языки
            $languagesResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/languages", [
                'headers' => $headers
            ]);

            // Проверка на превышение лимита запросов
            if ($languagesResponse->getStatusCode() === 403) {
                $remainingRequests = $languagesResponse->getHeaders()['X-RateLimit-Remaining'][0];
                if ($remainingRequests == 0) {
                    $resetTime = $languagesResponse->getHeaders()['X-RateLimit-Reset'][0];
                    return new JsonResponse([
                        'error' => 'Rate limit exceeded. Try again at ' . date('Y-m-d H:i:s', $resetTime)
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            $languages = $languagesResponse->toArray();

            // Вычисляем общее количество строк
            $totalLines = array_sum($languages);

            // Вычисляем проценты для каждого языка
            $languagePercentages = [];
            foreach ($languages as $language => $lines) {
                $percentage = ($lines / $totalLines) * 100;
                $languagePercentages[$language] = round($percentage, 2); // округляем до 2 знаков после запятой
            }

            // Запрашиваем последние сборки GitHub Actions
            $actionsResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/actions/runs", [
                'headers' => $headers
            ]);

            // Проверка на превышение лимита запросов
            if ($actionsResponse->getStatusCode() === 403) {
                $remainingRequests = $actionsResponse->getHeaders()['X-RateLimit-Remaining'][0];
                if ($remainingRequests == 0) {
                    $resetTime = $actionsResponse->getHeaders()['X-RateLimit-Reset'][0];
                    return new JsonResponse([
                        'error' => 'Rate limit exceeded. Try again at ' . date('Y-m-d H:i:s', $resetTime)
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            $actions = $actionsResponse->toArray();

            // Формируем ответ с процентами по языкам и информацией о технологиях
            $result = [
                'repository' => [
                    'name' => $repoData['name'] ?? 'Unknown',
                    'description' => $repoData['description'] ?? 'No description',
                    'stars' => $repoData['stargazers_count'] ?? 0,
                    'forks' => $repoData['forks_count'] ?? 0,
                    'open_issues' => $repoData['open_issues_count'] ?? 0,
                    'last_update' => $repoData['updated_at'] ?? 'Unknown',
                    'last_commit' => $repoData['pushed_at'] ?? 'Unknown',
                ],
                'languages' => $languagePercentages,
                'ci_cd' => !empty($actions['workflow_runs']) ? 'Enabled' : 'Not Found',
                'technologies' => $technologies,  // Добавляем информацию о технологиях
            ];

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch repository data'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Рекурсивный поиск всех файлов в репозитории
    private function searchFilesInRepo(string $owner, string $repo, string $path): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->githubToken, // ✅ Правильный формат
            'Accept' => 'application/vnd.github.v3+json'
        ];
        $files = [];
        $response = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$path", [
            'headers' => $headers
        ]);

        // Проверка на превышение лимита запросов
        if ($response->getStatusCode() === 403) {
            $remainingRequests = $response->getHeaders()['X-RateLimit-Remaining'][0];
            if ($remainingRequests == 0) {
                $resetTime = $response->getHeaders()['X-RateLimit-Reset'][0];
                throw new \Exception('Rate limit exceeded. Try again at ' . date('Y-m-d H:i:s', $resetTime));
            }
        }

        $items = $response->toArray();
        foreach ($items as $item) {
            if ($item['type'] === 'file') {
                $files[] = $item['path'];
            } elseif ($item['type'] === 'dir') {
                // Рекурсивно ищем в подкаталогах
                $files = array_merge($files, $this->searchFilesInRepo($owner, $repo, $item['path']));
            }
        }

        return $files;
    }

    // Извлекаем технологии из файлов
    private function extractTechnologiesFromFiles(array $files, string $owner, string $repo): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->githubToken, // ✅ Правильный формат
            'Accept' => 'application/vnd.github.v3+json'
        ];
        $technologies = [
            'symfony' => 'Not Found',
            'nodejs' => 'Not Found',
            'docker' => 'Not Found',
        ];

        foreach ($files as $file) {
            // Проверяем на наличие composer.json
            if (strpos($file, 'composer.json') !== false) {
                $composerJsonResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                    'headers' => $headers
                ]);
                $composerJsonData = $composerJsonResponse->toArray();
                $composerJson = json_decode(base64_decode($composerJsonData['content']), true);
                if (isset($composerJson['require']['symfony/symfony'])) {
                    $technologies['symfony'] = $composerJson['require']['symfony/symfony'];
                }
            }

            // Проверяем на наличие package.json
            if (strpos($file, 'package.json') !== false) {
                $packageJsonResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                    'headers' => $headers
                ]);
                $packageJsonData = $packageJsonResponse->toArray();
                $packageJson = json_decode(base64_decode($packageJsonData['content']), true);
                if (isset($packageJson['dependencies'])) {
                    $technologies['nodejs'] = 'Node.js (with dependencies)';
                }
            }

            // Проверяем на наличие Dockerfile
            if (strpos($file, 'Dockerfile') !== false) {
                $technologies['docker'] = 'Docker (Dockerfile found)';
            }
        }

        return $technologies;
    }
}
