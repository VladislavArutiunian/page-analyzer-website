<?php

namespace Hexlet\Code;

use DI\ContainerBuilder;
use Hexlet\Helpers\Normalize;
use Postgre;
use Postgre\Connection;
use Postgre\InsertValue;
use Postgre\Select;
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

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(
    [
        'flash' => function () {
            $storage = [];
            return new Messages($storage);
        }
    ]
);


AppFactory::setContainer($containerBuilder->build());

$app = AppFactory::create();
$app->add(
    function ($request, $next) {
        // Start PHP session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Change flash message storage
        $this->get('flash')->__construct($_SESSION);

        return $next->handle($request);
    }
);

$app->addErrorMiddleware(true, true, true);


$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));


$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    $flash = $this->get('flash')->getMessages();
    $params = ['headerMainActive' => 'active'];
    if (count($flash['errors'] ?? []) !== 0) {
        $params['inputError'] = 'visible';
    }

    return $view->render($response, 'index.html.twig', $params);
});

$app->get('/urls', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    $connection = Connection::get()->connect();


    $urlList = Select::selectAllUrls($connection);
    $params = [
        'urlList' => $urlList,
        'headerSitesActive' => 'active'
    ];
    return $view->render($response, 'urls.html.twig', $params);
});

$app->get('/urls/{id}', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);

    $id = $args['id'];

    $connection = Connection::get()->connect();
    $siteParamsList = Select::selectUrlById($connection, $id);
    $params = [
        'siteParamsList' => $siteParamsList
    ];
    return $view->render($response, 'url-id.html.twig', $params);
});

$app->post('/urls', function (Request $request, Response $response) {
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
        $response->withStatus(422);
        return $view->render($response, 'index.html.twig', $params);
    }
    $normalizedUrl = Normalize::normalizeUrl($url);

    $connection = Connection::get()->connect();
    $existingUrls = Select::selectUrlByName($connection, $normalizedUrl);

    if (count($existingUrls) === 0) {
        $insert = new InsertValue($connection);
        $lastInsertId = $insert->insertValue('urls', $normalizedUrl);
    }
    $urlId = $lastInsertId ?? Select::getId($existingUrls);
    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response->withRedirect("/urls/" . $urlId);
});

$app->run();
