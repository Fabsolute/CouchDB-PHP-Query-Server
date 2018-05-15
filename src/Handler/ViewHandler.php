<?php

namespace Fabstract\CouchQueryServer\Handler;

use Fabstract\CouchQueryServer\Couch;
use Fabstract\CouchQueryServer\Exception;

class ViewHandler extends HandlerBase
{
    /** @var StateHandler */
    private $state_handler = null;

    private $map_results = [];

    public function __construct($query_server, $state_handler)
    {
        parent::__construct($query_server);
        $this->state_handler = $state_handler;
    }

    public function mapDoc($doc)
    {
        $buffer = [];
        foreach ($this->state_handler->getFuns() as $fun) {
            try {
                $this->map_results = [];
                call_user_func($fun, $doc);
                $buffer[] = $this->map_results;
            } catch (\Exception $exception) {
                $this->handleViewError($exception, $doc);
                $buffer[] = [];
            }
        }

        $this->response([$buffer]);
    }

    public function reduce($reduce_functions, $key_values)
    {
        $keys = [];
        $values = [];
        for ($i = 0; $i < count($key_values); $i++) {
            $keys[$i] = $key_values[$i][0];
            $values[$i] = $key_values[$i][1];
        }

        $this->runReduce($reduce_functions, $keys, $values, false);
    }

    public function rereduce($reduce_functions, $values)
    {
        $this->runReduce($reduce_functions, null, $values, true);
    }

    public function emit($key, $value = null)
    {
        $this->map_results[] = [$key, $value];
    }

    public function sum($values)
    {
        $response = 0;
        foreach ($values as $value) {
            $response += $value;
        }

        return $response;
    }

    private function runReduce($reduce_functions, $keys, $values, $rereduce)
    {
        foreach ($reduce_functions as $key => $function) {
            $reduce_functions[$key] = Couch::compileFunction($function, null);
        }

        $reductions = [];

        foreach ($reduce_functions as $key => $function) {
            try {
                $reductions[$key] = call_user_func_array($function, [$keys, $values, $rereduce]);
            } catch (\Exception $exception) {
                $this->handleViewError($exception);
                $reductions[$key] = null;
            }
        }

        $reduce_content = Couch::toJSON($reductions);
        $reduce_length = strlen($reduce_content);

        $reduce_limit = $this->state_handler->getConfig('reduce_limit', false);

        if ($reduce_limit && $reduce_length > 200 && ($reduce_length * 2) > $this->state_handler->getLineHeight()) {
            throw Exception::create('reduce_overflow_error', 'Reduce output must shrink more rapidly');
        }

        $this->response([true, $reductions]);
    }

    /**
     * @param \Exception $exception
     * @param mixed $doc
     */
    private function handleViewError($exception, $doc = null)
    {
//        if ($exception->getMessage() == "fatal_error") {
//            // Only if it's a "fatal_error" do we exit. What's a fatal error?
//            // That's for the query to decide.
//            //
//            // This will make it possible for queries to completely error out,
//            // by catching their own local exception and rethrowing a
//            // fatal_error. But by default if they don't do error handling we
//            // just eat the exception and carry on.
//            //
//            // In this case we abort map processing but don't destroy the
//            // JavaScript process. If you need to destroy the JavaScript
//            // process, throw the error form matched by the block below.
//            throw(["error", "map_runtime_error", "function raised 'fatal_error'"]);
//        } else if (err[0] == "fatal") {
//            // Throwing errors of the form ["fatal","error_key","reason"]
//            // will kill the OS process. This is not normally what you want.
//            throw(err);
//        }
//        var message = "function raised exception " +
//        (err.toSource ? err.toSource() : err.stack);
//        if (doc) message += " with doc._id " + doc._id;
//        log(message);
    }
}
