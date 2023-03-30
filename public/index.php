<?php

namespace Hexlet\Code;

use DI\Container;
use Hexlet\Helpers\Normalize;
use Postgre;
use Postgre\Connection;
use Postgre\InsertValue;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
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

$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);


$app->add(TwigMiddleware::create($app, $twig));


$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    $params = [];
    return $view->render($response, 'index.html.twig', $params);
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
            'class' => 'is-invalid',
            'value' => htmlspecialchars($url)
        ];
        return $view->render($response, 'index.html.twig', $params);
    }
    $normalizedUrl = Normalize::normalizeUrl($url);

    $connection = Connection::get()->connect();
    $selection = new Postgre\SelectValue($connection);
    $countRows = $selection->selectValue($normalizedUrl);

    if ($countRows === 0) {
        $insert = new InsertValue($connection);
        $res = $insert->insertValue('urls', $normalizedUrl);
    }

    $params = [];
    return $view->render($response, 'index.html.twig', $params);
});

$app->run();
