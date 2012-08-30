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

    public static function add(Parameters $param)
    {
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

}