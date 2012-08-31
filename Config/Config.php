<?php

namespace ActiveRecord\Config;

use ActiveRecord\Config\Parameters;

/**
 * Description of Config
 *
 * @author maguirre
 */
class Config
{

    protected static $parameters = array();
    protected static $defaultConectionName;

    public static function add(Parameters $param)
    {
        if ( !self::$defaultConectionName ){
            self::setDefault($param->getId());
        }
        if (!self::has($param->getId())) {
            self::$parameters[$param->getId()] = $param;
        }
    }

    public static function has($id)
    {
        return isset(self::$parameters[$id]);
    }

    /**
     *
     * @param type $id
     * @return Parameters 
     */
    public static function get($id)
    {
        return self::has($id) ? self::$parameters[$id] : NULL;
    }

    public static function initialized()
    {
        return count(self::$parameters) > 0;
    }

    public static function setDefault($id)
    {
        self::$defaultConectionName = $id;
    }

    public static function getDefault()
    {
        return self::$defaultConectionName;
    }

}