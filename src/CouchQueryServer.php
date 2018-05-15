<?php

namespace Fabstract\CouchQueryServer;

use Clue\React\Stdio\Stdio;
use Fabstract\CouchQueryServer\Handler\DDocHandler;
use Fabstract\CouchQueryServer\Handler\StateHandler;
use Fabstract\CouchQueryServer\Handler\ViewHandler;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;

class CouchQueryServer
{
    /** @var callable[] */
    private $dispatchers = [];
    /** @var DDocHandler */
    private $ddoc_handler = null;
    /** @var StateHandler */
    private $state_handler = null;
    /** @var ViewHandler */
    private $view_handler = null;
    /** @var LoopInterface */
    private $loop = null;
    /** @var Stdio */
    private $io = null;

    private function __construct()
    {
        $this->state_handler = new StateHandler($this);
        $this->view_handler = new ViewHandler($this, $this->state_handler);
        $this->ddoc_handler = new DDocHandler();

        $this->dispatchers['ddoc'] = [$this->ddoc_handler, 'ddoc'];

        $this->dispatchers['reset'] = [$this->state_handler, 'reset'];
        $this->dispatchers['add_fun'] = [$this->state_handler, 'addFun'];
        $this->dispatchers['add_lib'] = [$this->state_handler, 'addLib'];

        $this->dispatchers['map_doc'] = [$this->view_handler, 'mapDoc'];
        $this->dispatchers['reduce'] = [$this->view_handler, 'reduce'];
        $this->dispatchers['rereduce'] = [$this->view_handler, 'rereduce'];
        $this->loop = EventLoopFactory::create();
        $this->io = new Stdio($this->loop);
    }

    public function run()
    {
        $this->io->on('data', function ($line) {
            try {
                $line = rtrim($line, "\r\n");
                $this->log('input', $line);
                $command = json_decode($line, true);
                $this->state_handler->setLineLength(strlen($line));
                $command_key = array_shift($command);
                if (array_key_exists($command_key, $this->dispatchers) === true) {
                    call_user_func_array($this->dispatchers[$command_key], $command);
                } else {
                    throw Exception::create('fatal', 'unknown_command ' . $command_key);
                }
            } catch (\Exception $exception) {
                $this->handleError($exception);
            }

        });

        $this->loop->run();
    }

    /**
     * @param \Exception $exception
     */
    public function handleError($exception)
    {
        if (!($exception instanceof Exception)) {
            $exception = Exception::create('unnamed_error', $exception->getMessage());
        }

        $this->response(['error', $exception->getMessage(), $exception->getReason()]);
        if ($exception->getMessage() === 'fatal') {
            $this->io->end();
        }
    }

    public function response($content)
    {
        $content = Couch::toJSON($content) . PHP_EOL;
        $this->log('output', $content);

        $this->io->write($content);
    }

    /** @var CouchQueryServer */
    protected static $instance = null;

    /**
     * @return CouchQueryServer
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return ViewHandler
     */
    public function getViewHandler()
    {
        return $this->view_handler;
    }

    private function log($method, $content)
    {
        file_put_contents('logs.txt', $method . ':' . $content, FILE_APPEND | LOCK_EX);
    }
}
