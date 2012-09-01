<?php

require_once 'autoload.php';

use ActiveRecord\Config\Config;
use ActiveRecord\Config\Parameters;

Config::add(new Parameters('default', array(
            'username' => 'root',
            'password' => '',
            'host' => 'localhost',
            'type' => 'mysql',
            'name' => 'test',
                )
));
