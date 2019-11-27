<?php

namespace com\carlgo11\tempfiles\api;

use com\carlgo11\tempfiles\DataStorage;
use Exception;

class Cleanup extends API
{

    public function __construct(string $method) {
        if ($method !== 'PURGE') throw new Exception("Bad HTTP method. Use PURGE.");

        $status = filter_var(DataStorage::deleteOldFiles(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        parent::addMessage('status', $status);
        parent::outputJSON(202);
    }
}