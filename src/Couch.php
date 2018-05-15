<?php

namespace Fabstract\CouchQueryServer;

class Couch
{
    public static function compileFunction($code, $library)
    {
        $closure = ClosureStream::toClosure($code);
        if (is_callable($closure)) {
            return $closure;
        }

        return null;
    }

    public static function toJSON($object)
    {
        return json_encode($object);
    }

    public static function log($message)
    {
        if (!is_string($message)) {
            $message = Couch::toJSON($message);
        }

        CouchQueryServer::getInstance()
            ->response(['log', $message]);
    }
}


