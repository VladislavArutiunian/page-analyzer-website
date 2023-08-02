<?php

namespace Hexlet\Code;

use Database\Connection;
use Database\DbServiceFactory;
use Database\Helpers;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Exception;
use Hexlet\Helpers\SEOChecker;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Valitron\Validator as V;

$autoloadPath1 = __DIR__ . '/../../../autoload.php';
$autoloadPath2 = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath1)) {
    require_once $autoloadPath1;
} else {
    require_once $autoloadPath2;
}

$root = dirname($_SERVER['DOCUMENT_ROOT']) . '/' ;

Dotenv::createImmutable($root)->safeLoad();

$logger = new Logger($_ENV['APP_NAME'] ?? '');
$logsDir = $_ENV['LOGS_DIR'] ?? 'tmp/main.log';
$logger->pushHandler(new StreamHandler($root . $logsDir, Level::Warning));

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
        'flash' => function () {
            $storage = [];
            return new Messages($storage);
        },
        'connection' => function () use ($logger) {
            try {
                return Connection::get()->connect();
            } catch (Exception $e) {
                $logger->error(
                    'Error on bootstrap database',
                    [$e->getCode(), $e->getMessage(), $e->getTraceAsString()]
                );
                http_response_code(response_code: 422);
                die();
            }
        },
        'logger' => $logger,
]);

AppFactory::setContainer($containerBuilder->build());

$app = AppFactory::create();
$app->add(
    function ($request, $next) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->get('flash')->__construct($_SESSION);
        $dbServiceFactory = new DbServiceFactory($this->get('connection'));

        $this->set('siteUrl', $dbServiceFactory->buildSiteUrl());
        $this->set('seoCheck', $dbServiceFactory->buildSeoCheck());

        $tableCreator = $dbServiceFactory->buildTableCreator();
        $tableCreator->createTables();

        return $next->handle($request);
    }
);

$app->addErrorMiddleware(true, true, true);

$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'index.html.twig', ['headerMainActive' => 'active']);
})->setName('main');

$app->get('/urls', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    $urlList = $this->get('siteUrl')->getAll();
    $headerSitesActive = 'active';

    return $view->render(
        $response,
        'url/index.html.twig',
        compact('urlList', 'headerSitesActive')
    );
})->setName('urls');

$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);

    $flash = $this->get('flash')->getMessages();
    $flashClass = '';
    if (isset($flash)) {
        $flashClass = isset($flash['error']) ? 'danger' : 'success';
    }

    $checks = $this->get('seoCheck')->selectAll($args['id']);
    $siteParamsList = $this->get('siteUrl')->selectById($args['id']);

    return $view->render(
        $response,
        'url/show.html.twig',
        compact('checks', 'siteParamsList', 'flash', 'flashClass')
    );
})->setName('url');

$router = $app->getRouteCollector()->getRouteParser();

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $view = Twig::fromRequest($request);

    $url = $request->getParsedBodyParam('url')['name'];

    $validation = new V(['url' => $url]);

    $validation->rule('required', 'url');
    $validation->rule('lengthMax', 'url', 255);
    $validation->rule('url', 'url');

    if (!$validation->validate()) {
        $params = [
            'inputClass' => 'is-invalid',
            'inputValue' => htmlspecialchars($url)
        ];
        http_response_code(response_code: 422);
        $response->withStatus(422);
        return $view->render($response, 'index.html.twig', $params);
    }

    ['scheme' => $scheme, 'host' => $host] = parse_url($url);
    $normalizedUrl = "$scheme://$host";

    $existingUrls = $this->get('siteUrl')->selectByName($normalizedUrl);
    $this->get('flash')->addMessage('success', 'Страница уже существует');

    if (count($existingUrls) === 0) {
        $lastInsertId = $this->get('siteUrl')->insertValue($normalizedUrl);
        $this->get('flash')->clearMessage('success');
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }
    $urlId = $lastInsertId ?? Helpers::getId($existingUrls);

    return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
});

$app->post('/urls/{id:[0-9]+}/checks', function (Request $request, Response $response, $args) use ($router) {
    $url = $this->get('siteUrl')->selectById($args['id']);

    try {
        $checkParams = (new SEOChecker())->makeCheck($url['name']);
        $this->get('seoCheck')->insertCheck($args['id'], $checkParams);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (Exception $e) {
        $this->get('flash')->addMessage('error', 'Check is failed');
    }

    return $response->withRedirect($router->urlFor('url', ['id' => $args['id']]));
})->setName('check');

$app->run();
