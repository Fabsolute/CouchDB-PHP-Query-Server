<?php

namespace Fabstract\CouchQueryServer\Handler;

use Fabstract\CouchQueryServer\Couch;
use Fabstract\CouchQueryServer\Exception;

class StateHandler extends HandlerBase
{
    private $functions = [];
    private $library = null;
    private $query_config = null;
    private $line_length = 0;

    public function reset($config = [])
    {
        $this->functions = [];
        $this->library = null;
        $this->query_config = $config;
        $this->response(true);
    }

    public function addFun($function)
    {
        $compiled_function = Couch::compileFunction($function, $this->library);
        if ($compiled_function === null) {
            throw Exception::create('error', 'bad_function');
        }

        $this->functions[] = $compiled_function;
        $this->response(true);
    }

    public function addLib($library)
    {
        $this->library = $library;
        $this->response(true);
    }

    public function setLineLength($line_length)
    {
        $this->line_length = $line_length;
    }

    public function getFuns()
    {
        return $this->functions;
    }

    public function getLineHeight()
    {
        return $this->line_length;
    }

    public function getConfig($config_name, $default_value = null)
    {
        if (array_key_exists($config_name, $this->query_config)) {
            return $this->query_config[$config_name];
        }

        return $default_value;
    }
}
