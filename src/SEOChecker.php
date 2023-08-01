<?php

namespace Hexlet\Helpers;

use DiDom\Exceptions\InvalidSelectorException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Postgre\Connection;
use Postgre\InsertValue;
use Postgre\Select;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use DiDom\Document;

class SEOChecker
{
    /**
     * @throws Exception|GuzzleException
     */
    public function makeCheck(string $urlName): array
    {
        $httpResponse = (new Client())->request('GET', $urlName);
        $document = new Document($urlName, true);
        return [
            'status_code' => $httpResponse->getStatusCode(),
            'h1' => optional($document->find('h1')[0])->text(),
            'title' => optional($document->find('title')[0])->text(),
            'description' => optional($document->find('meta[name="description"]')[0])->getAttribute('content'),
        ];
    }
}
