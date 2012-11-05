<?php

namespace ActiveRecord\Exception;

use \Exception;
use \PDOStatement;

/**
 * Description of ActiveRecordException
 *
 * @author maguirre
 */
class SqlException extends Exception
{

    function __construct(Exception $e, PDOStatement $st = NULL)
    {
        parent::__construct($e->getMessage());

        if ($st) {
            ob_start();
            $st->debugDumpParams();
            $this->message .= '<pre>' . ob_get_clean() . '</pre>';
        }
    }

}

