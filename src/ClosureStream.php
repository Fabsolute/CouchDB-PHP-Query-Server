<?php

namespace Fabstract\CouchQueryServer;

class ClosureStream
{
    private static $is_registered = false;

    protected $content = null;
    protected $length = 0;
    protected $pointer = 0;

    public static function toClosure($code)
    {
        static::register();
        return include 'closure://' . $code;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->content = '<?php
        return ' . substr($path, strlen('closure://')) . ';';
        $this->length = strlen($this->content);
        return true;
    }

    public function stream_read($count)
    {
        $value = substr($this->content, $this->pointer, $count);
        $this->pointer += $count;
        return $value;
    }

    public function stream_eof()
    {
        return $this->pointer >= $this->length;
    }

    public function stream_stat()
    {
        $stat = stat(__FILE__);
        $stat[7] = $stat['size'] = $this->length;
        return $stat;
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        $crt = $this->pointer;

        switch ($whence) {
            case SEEK_SET:
                $this->pointer = $offset;
                break;
            case SEEK_CUR:
                $this->pointer += $offset;
                break;
            case SEEK_END:
                $this->pointer = $this->length + $offset;
                break;
        }

        if ($this->pointer < 0 || $this->pointer >= $this->length) {
            $this->pointer = $crt;
            return false;
        }

        return true;
    }

    public function stream_tell()
    {
        return $this->pointer;
    }

    public function url_stat($path, $flags)
    {
        $stat = stat(__FILE__);
        $stat[7] = $stat['size'] = $this->length;
        return $stat;
    }

    public static function register()
    {
        if (!static::$is_registered) {
            static::$is_registered = stream_wrapper_register('closure', static::class);
        }
    }

    private function getFunctions()
    {
        return file_get_contents(__DIR__ . '/functions.php');
    }
}
