<?php

namespace Database;

class Helpers
{
    public static function normalizeFetchAll($fetchAll): array
    {
        if ($fetchAll === []) {
            return [];
        }
        [$row] = $fetchAll;
        return $row;
    }

    public static function getId(array $result): int
    {
        return $result['id'];
    }
}
