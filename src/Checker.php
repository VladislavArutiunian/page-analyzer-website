<?php

namespace Hexlet\Helpers;

use GuzzleHttp\Client;
use Postgre\Connection;
use Postgre\InsertValue;
use Postgre\Select;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use DiDom\Document;

class Checker
{
    private array $errors = [];
    public function __construct()
    {
    }

    public function makeCheck($url_id): void
    {

        $client = new Client();
        $connection = Connection::get()->connect();
        $url = Select::selectUrlById($connection, $url_id);
        try {
            $httpResponse = $client->request('GET', $url['name']);
            $document = new Document($url['name'], true);

            $statusCode = $httpResponse->getStatusCode();
            $h1 = optional($document->find('h1')[0])
                ->text();
            $title = optional($document->find('title')[0])
                ->text();
            $description = optional($document->find('meta[name="description"]')[0])
                ->getAttribute('content');

            $insert = new InsertValue($connection);
            $lastCheckId = $insert->insertCheck($url_id, $statusCode, $h1, $title, $description);
        } catch (ConnectException | ServerException $e) {
            //log this
            $this->errors[] = $e;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
