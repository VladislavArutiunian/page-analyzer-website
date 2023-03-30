<?php

namespace Hexlet\Helpers;

use function DI\string;

abstract class Normalize
{
    public function __construct()
    {
    }
    
    public static function normalizeUrl(string $url): string
    {
        $structure = parse_url($url);
        ['scheme' => $scheme, 'host' => $host, ] = $structure;
        return sprintf("%s://%s", $scheme, $host);
    }
}