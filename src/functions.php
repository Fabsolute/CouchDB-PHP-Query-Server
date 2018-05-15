<?php

use Fabstract\CouchQueryServer\Couch;
use Fabstract\CouchQueryServer\CouchQueryServer;

function emit($key, $value = null)
{
    CouchQueryServer::getInstance()
        ->getViewHandler()
        ->emit($key, $value);
}

function sum($values)
{
    CouchQueryServer::getInstance()
        ->getViewHandler()
        ->sum($values);
}

function couch_log($message)
{
    Couch::log($message);
}

function toJSON($object)
{
    return Couch::toJSON($object);
}
