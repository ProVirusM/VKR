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
                'Authorization' => 'Bearer ' . $this->githubToken,
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
                $languagePercentages[$language] = round($percentage, 2);
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
                'technologies' => $technologies,
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
            'Authorization' => 'Bearer ' . $this->githubToken,
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
            'Authorization' => 'Bearer ' . $this->githubToken,
            'Accept' => 'application/vnd.github.v3+json'
        ];
        
        $technologies = [
            'symfony' => 'Not Found',
            'nodejs' => 'Not Found',
            'docker' => 'Not Found',
            'php' => 'Not Found',
            'composer' => 'Not Found',
            'npm' => 'Not Found',
            'yarn' => 'Not Found',
            'webpack' => 'Not Found',
            'vue' => 'Not Found',
            'react' => 'Not Found',
            'angular' => 'Not Found',
            'typescript' => 'Not Found',
            'python' => 'Not Found',
            'java' => 'Not Found',
            'gradle' => 'Not Found',
            'maven' => 'Not Found',
            'laravel' => 'Not Found',
            'django' => 'Not Found',
            'flask' => 'Not Found',
            'spring' => 'Not Found',
            'express' => 'Not Found',
            'nextjs' => 'Not Found',
            'nuxt' => 'Not Found',
            'svelte' => 'Not Found',
            'tailwind' => 'Not Found',
            'bootstrap' => 'Not Found',
            'material-ui' => 'Not Found',
            'jest' => 'Not Found',
            'phpunit' => 'Not Found',
            'pytest' => 'Not Found',
            'junit' => 'Not Found',
            'postgresql' => 'Not Found',
            'mysql' => 'Not Found',
            'mongodb' => 'Not Found',
            'redis' => 'Not Found',
            'elasticsearch' => 'Not Found',
            'aws' => 'Not Found',
            'azure' => 'Not Found',
            'gcp' => 'Not Found',
            'kubernetes' => 'Not Found',
            'terraform' => 'Not Found',
            'ansible' => 'Not Found',
            'jenkins' => 'Not Found',
            'gitlab-ci' => 'Not Found',
            'travis-ci' => 'Not Found',
            'circle-ci' => 'Not Found',
            'github-actions' => 'Not Found',
            'nginx' => 'Not Found',
            'apache' => 'Not Found',
            'graphql' => 'Not Found',
            'rest' => 'Not Found',
            'grpc' => 'Not Found',
            'websocket' => 'Not Found',
            'rabbitmq' => 'Not Found',
            'kafka' => 'Not Found',
            'swagger' => 'Not Found',
            'openapi' => 'Not Found',
        ];

        foreach ($files as $file) {
            $lowerFile = strtolower($file);
            
            // PHP и Composer
            if (strpos($lowerFile, 'composer.json') !== false) {
                $technologies['composer'] = 'Found';
                try {
                    $composerJsonResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                        'headers' => $headers
                    ]);
                    $composerJsonData = $composerJsonResponse->toArray();
                    $composerJson = json_decode(base64_decode($composerJsonData['content']), true);
                    
                    if (isset($composerJson['require'])) {
                        $technologies['php'] = 'Found';
                        foreach ($composerJson['require'] as $package => $version) {
                            if (strpos($package, 'symfony/') === 0) {
                                $technologies['symfony'] = "Found (using $package)";
                            }
                            if (strpos($package, 'laravel/') === 0) {
                                $technologies['laravel'] = "Found (using $package)";
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Продолжаем выполнение даже при ошибке чтения composer.json
                }
            }

            // Node.js и связанные технологии
            if (strpos($lowerFile, 'package.json') !== false) {
                $technologies['npm'] = 'Found';
                try {
                    $packageJsonResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                        'headers' => $headers
                    ]);
                    $packageJsonData = $packageJsonResponse->toArray();
                    $packageJson = json_decode(base64_decode($packageJsonData['content']), true);
                    
                    if (isset($packageJson['dependencies']) || isset($packageJson['devDependencies'])) {
                        $technologies['nodejs'] = 'Found';
                        $dependencies = array_merge(
                            $packageJson['dependencies'] ?? [],
                            $packageJson['devDependencies'] ?? []
                        );
                        
                        // Frontend frameworks
                        if (isset($dependencies['vue'])) {
                            $technologies['vue'] = 'Found';
                        }
                        if (isset($dependencies['react'])) {
                            $technologies['react'] = 'Found';
                        }
                        if (isset($dependencies['@angular/core'])) {
                            $technologies['angular'] = 'Found';
                        }
                        if (isset($dependencies['next'])) {
                            $technologies['nextjs'] = 'Found';
                        }
                        if (isset($dependencies['nuxt'])) {
                            $technologies['nuxt'] = 'Found';
                        }
                        if (isset($dependencies['svelte'])) {
                            $technologies['svelte'] = 'Found';
                        }
                        
                        // CSS frameworks
                        if (isset($dependencies['tailwindcss'])) {
                            $technologies['tailwind'] = 'Found';
                        }
                        if (isset($dependencies['bootstrap'])) {
                            $technologies['bootstrap'] = 'Found';
                        }
                        if (isset($dependencies['@mui/material'])) {
                            $technologies['material-ui'] = 'Found';
                        }
                        
                        // Testing
                        if (isset($dependencies['jest'])) {
                            $technologies['jest'] = 'Found';
                        }
                        
                        // Backend
                        if (isset($dependencies['express'])) {
                            $technologies['express'] = 'Found';
                        }
                        
                        // Build tools
                        if (isset($dependencies['webpack'])) {
                            $technologies['webpack'] = 'Found';
                        }
                        if (isset($dependencies['typescript'])) {
                            $technologies['typescript'] = 'Found';
                        }
                    }
                } catch (\Exception $e) {
                    // Продолжаем выполнение даже при ошибке чтения package.json
                }
            }

            // Python
            if (strpos($lowerFile, 'requirements.txt') !== false || strpos($lowerFile, 'setup.py') !== false) {
                $technologies['python'] = 'Found';
                try {
                    $requirementsResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                        'headers' => $headers
                    ]);
                    $requirementsData = $requirementsResponse->toArray();
                    $requirements = base64_decode($requirementsData['content']);
                    
                    if (strpos($requirements, 'django') !== false) {
                        $technologies['django'] = 'Found';
                    }
                    if (strpos($requirements, 'flask') !== false) {
                        $technologies['flask'] = 'Found';
                    }
                    if (strpos($requirements, 'pytest') !== false) {
                        $technologies['pytest'] = 'Found';
                    }
                } catch (\Exception $e) {
                    // Продолжаем выполнение даже при ошибке чтения requirements.txt
                }
            }

            // Java
            if (strpos($lowerFile, 'pom.xml') !== false) {
                $technologies['maven'] = 'Found';
                $technologies['java'] = 'Found';
                try {
                    $pomResponse = $this->httpClient->request('GET', "https://api.github.com/repos/$owner/$repo/contents/$file", [
                        'headers' => $headers
                    ]);
                    $pomData = $pomResponse->toArray();
                    $pom = base64_decode($pomData['content']);
                    
                    if (strpos($pom, 'spring-boot') !== false) {
                        $technologies['spring'] = 'Found';
                    }
                    if (strpos($pom, 'junit') !== false) {
                        $technologies['junit'] = 'Found';
                    }
                } catch (\Exception $e) {
                    // Продолжаем выполнение даже при ошибке чтения pom.xml
                }
            }
            if (strpos($lowerFile, 'build.gradle') !== false) {
                $technologies['gradle'] = 'Found';
                $technologies['java'] = 'Found';
            }

            // Docker
            if (strpos($lowerFile, 'dockerfile') !== false || strpos($lowerFile, 'docker-compose') !== false) {
                $technologies['docker'] = 'Found';
            }

            // Databases
            if (strpos($lowerFile, 'postgresql.conf') !== false || strpos($lowerFile, '.postgresql') !== false) {
                $technologies['postgresql'] = 'Found';
            }
            if (strpos($lowerFile, 'my.cnf') !== false || strpos($lowerFile, '.mysql') !== false) {
                $technologies['mysql'] = 'Found';
            }
            if (strpos($lowerFile, 'mongodb.conf') !== false || strpos($lowerFile, '.mongodb') !== false) {
                $technologies['mongodb'] = 'Found';
            }
            if (strpos($lowerFile, 'redis.conf') !== false || strpos($lowerFile, '.redis') !== false) {
                $technologies['redis'] = 'Found';
            }
            if (strpos($lowerFile, 'elasticsearch.yml') !== false || strpos($lowerFile, '.elasticsearch') !== false) {
                $technologies['elasticsearch'] = 'Found';
            }

            // Cloud providers
            if (strpos($lowerFile, 'aws') !== false || strpos($lowerFile, '.aws') !== false) {
                $technologies['aws'] = 'Found';
            }
            if (strpos($lowerFile, 'azure') !== false || strpos($lowerFile, '.azure') !== false) {
                $technologies['azure'] = 'Found';
            }
            if (strpos($lowerFile, 'gcp') !== false || strpos($lowerFile, '.gcp') !== false) {
                $technologies['gcp'] = 'Found';
            }

            // Infrastructure
            if (strpos($lowerFile, 'kubernetes') !== false || strpos($lowerFile, 'k8s') !== false) {
                $technologies['kubernetes'] = 'Found';
            }
            if (strpos($lowerFile, 'terraform') !== false || strpos($lowerFile, '.tf') !== false) {
                $technologies['terraform'] = 'Found';
            }
            if (strpos($lowerFile, 'ansible') !== false || strpos($lowerFile, '.ansible') !== false) {
                $technologies['ansible'] = 'Found';
            }

            // CI/CD
            if (strpos($lowerFile, 'jenkins') !== false || strpos($lowerFile, 'Jenkinsfile') !== false) {
                $technologies['jenkins'] = 'Found';
            }
            if (strpos($lowerFile, '.gitlab-ci.yml') !== false) {
                $technologies['gitlab-ci'] = 'Found';
            }
            if (strpos($lowerFile, '.travis.yml') !== false) {
                $technologies['travis-ci'] = 'Found';
            }
            if (strpos($lowerFile, 'circle.yml') !== false) {
                $technologies['circle-ci'] = 'Found';
            }
            if (strpos($lowerFile, '.github/workflows') !== false) {
                $technologies['github-actions'] = 'Found';
            }

            // Web servers
            if (strpos($lowerFile, 'nginx.conf') !== false || strpos($lowerFile, '.nginx') !== false) {
                $technologies['nginx'] = 'Found';
            }
            if (strpos($lowerFile, 'apache2.conf') !== false || strpos($lowerFile, '.apache') !== false) {
                $technologies['apache'] = 'Found';
            }

            // API
            if (strpos($lowerFile, 'graphql') !== false || strpos($lowerFile, '.graphql') !== false) {
                $technologies['graphql'] = 'Found';
            }
            if (strpos($lowerFile, 'swagger') !== false || strpos($lowerFile, 'openapi') !== false) {
                $technologies['swagger'] = 'Found';
                $technologies['openapi'] = 'Found';
            }
            if (strpos($lowerFile, 'grpc') !== false || strpos($lowerFile, '.proto') !== false) {
                $technologies['grpc'] = 'Found';
            }
            if (strpos($lowerFile, 'websocket') !== false) {
                $technologies['websocket'] = 'Found';
            }

            // Message queues
            if (strpos($lowerFile, 'rabbitmq') !== false) {
                $technologies['rabbitmq'] = 'Found';
            }
            if (strpos($lowerFile, 'kafka') !== false) {
                $technologies['kafka'] = 'Found';
            }
        }

        return $technologies;
    }
}
