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
 * Clase que maneja los metadatos de las tablas
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase que maneja los metadatos de las tablas
 *
 */
class TableMetaData
{
    /**
     * Metadatos de la tabla
     *
     * @var array
     **/
    private $_metadata = null;

    /**
     * Instancias de metadata
     *
     * @var array
     **/
    private static $_instances = array(); 

    private function __construct()
    {}
    
    /**
     * Asigna la metadata
     *
     * @param array $metadata
     **/
    public function setMetadata($metadata)
    {
        $this->_metadata = $metadata;
    }
    
    /**
     * Obtiene la metadata de un campo especifico
     *
     * @param string $field nombre de campo
     * @return array metadatos
     **/
    public function getField($field)
    {
        if(!isset($this->_metadata[$field])) {
            throw new KumbiaException("No existe la metadata para el campo $field");
        }
        
        return $this->_metadata[$field];
    }
    
    /**
     * Verifica si los metadatos estan cargados
     *
     * @return boolean
     **/
    public function isLoaded()
    {
        return !is_null($this->_metadata);
    }
    
    /**
     * Obtiene una instancia de la metadata para la tabla
     *
     * @param string $connection conexion a la bd
     * @param string $schema esquema de tabla
     * @param string $table nombre de tabla
     * @return TableMetaData
     **/
    public static function getInstance($connection, $schema, $table)
    {
        if(!isset(self::$_instances[$connection][$schema][$table])) {
            self::$_instances[$connection][$schema][$table] =  new self();
        }
        
        return self::$_instances[$connection][$schema][$table];
    }
}