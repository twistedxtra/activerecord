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

    function __construct(Exception $e, PDOStatement $st)
    {
        parent::__construct($e->getMessage(), $e->getCode());

        ob_start();
        $st->debugDumpParams();
        $this->message .= '<pre>' . ob_get_clean() . '</pre>';
    }

}

