#!/usr/bin/env php
<?php

use Fabstract\CouchQueryServer\CouchQueryServer;

require "vendor/autoload.php";

CouchQueryServer::getInstance()->run();
