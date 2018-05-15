<?php

namespace Fabstract\CouchQueryServer\Handler;

use Fabstract\CouchQueryServer\CouchQueryServer;

abstract class HandlerBase
{
    /** @var CouchQueryServer */
    private $query_server = null;

    public function __construct($query_server)
    {
        $this->query_server = $query_server;
    }

    protected function response($content)
    {
        $this->query_server->response($content);
    }
}
