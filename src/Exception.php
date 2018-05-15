<?php

namespace Fabstract\CouchQueryServer;

class Exception extends \Exception
{
    /** @var string */
    private $reason = null;

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public static function create($message, $reason)
    {
        $exception = new static($message);
        $exception->setReason($reason);
        return $exception;
    }
}
