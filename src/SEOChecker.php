<?php

namespace Hexlet\Helpers;

use DiDom\Document;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Database\PostgreSQL\postgresqlphpconnect\InsertValue;
use Database\PostgreSQL\postgresqlphpconnect\Select;

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
