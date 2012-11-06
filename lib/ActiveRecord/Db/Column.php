<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    ActiveRecord
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace ActiveRecord\Db;

/**
 * \ActiveRecord\Db\Column
 *
 * Describe cada atributo de un modelo
 */
class Column
{

    /**
     * Tipo de dato de cara a la APP
     */
    public $type = NULL;

    /**
     * Valor por defecto del campo
     */
    public $default = NULL;

    /**
     * Longitud del Campo
     */
    public $length = 50;

    /**
     * Indica si es NULL el campo
     */
    public $notNull = TRUE;

    /**
     * Indica si es PK el campo
     */
    public $PK = FALSE;

    /**
     * Indica si es FK el campo
     */
    public $FK = FALSE;

    /**
     * Indica si es Unique el campo
     */
    public $unique = FALSE;

    /**
     * Campo con secuencias (serial o auto-increment)
     */
    public $autoIncrement = FALSE;

    /**
     * Formato para fechas
     */
    public $format = NULL;

    /**
     * Establece el estado interno de un objeto a partir de sus propiedades
     *
     * @param array $properties
     */
    public static function __set_state($properties)
    {
        $attribute = new self();
        foreach ($properties as $property => $value) {
            $attribute->$property = $value;
        }
        return $attribute;
    }

}