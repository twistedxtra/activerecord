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
 * Implementacion del patron de diseño ActiveRecord
 * 
 * @category   Kumbia
 * @package    ActiveRecord
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see KumbiaModel
 */
require_once CORE_PATH . 'libs/ActiveRecord/active_record2/kumbia_model.php';
/**
 * @see ResultSet
 */
require_once CORE_PATH . 'libs/ActiveRecord/db_pool/result_set.php';
/**
 * ActiveRecord Clase para el Mapeo Objeto Relacional
 *
 * Active Record es un enfoque al problema de acceder a los datos de una
 * base de datos en forma orientada a objetos. Una fila en la
 * tabla de la base de datos (o vista) se envuelve en una clase,
 * de manera que se asocian filas únicas de la base de datos
 * con objetos del lenguaje de programación usado.
 * Cuando se crea uno de estos objetos, se añade una fila a
 * la tabla de la base de datos. Cuando se modifican los atributos del
 * objeto, se actualiza la fila de la base de datos.
 */
class ActiveRecord2 extends KumbiaModel
{
    /**
     * Conexion a base datos que se utilizara
     *
     * @var strings
     **/
    protected $_connection = null;
    /**
     * Tabla origen de datos
     *
     * @var string
     */
    protected $_table = null;
    /**
     * Esquema de datos
     *
     * @var string
     */
    protected $_schema = null;
    /**
     * Objeto DbQuery para implementar chain
     * 
     * @var Obj
     */
    protected $_dbQuery = NULL;
    /**
     * Constructor de la class
     */
    public function __constructor ($data = null)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->$k = $v;
            }
        }
    }
    /**
     * Efectua una busqueda
     *
     * @param string|array parametros de busqueda 
     **/
    public function find ($params = NULL)
    {
        if(!$this->_dbQuery){
            // nuevo contenedor de consulta
            $dbQuery = new DbQuery();
        }
        
        // asigna la tabla
        $dbQuery->table($this->_table);
        // asigna el esquema si existe
        if ($this->_schema) {
            $dbQuery->schema($this->_schema);
        }
        // obtiene los parametros de consulta indicados
        if (! is_array($params)) {
            $params = Util::getParams(func_get_args());
            $dbQuery->select(implode(', ', $params));
            return $this->findBySql($dbQuery);
        }
    }
    public function all ()
    {}
    /**
     * Devuelve la instancia para realizar chain
     * 
     * @return Obj DbQuery
     */
    public function get ()
    {
        return $this->_dbQuery = new DbQuery();
    }
    /**
     * Efectua una busqueda de una consulta sql
     *
     * @param string | DbQuery $sql
     **/
    public function findBySql ($sql)
    {
        // carga el adaptador especifico para la conexion
        $adapter = DbAdapter::factory($this->_connection);
        // si no es un string, entonces es DbQuery
        if (! is_string($sql)) {
            $sql = $adapter->query($sql);
        }
        // ejecuta la consulta
        return $adapter->pdo()->query($sql, PDO::FETCH_OBJ);
    }
    /**
     * Ejecuta una setencia SQL aplicando Prepared Statement
     * 
     * @param string $sql Setencia SQL
     * @param array $params parametros que seran enlazados al SQL
     * @return ResulSet
     */
    public function sql ($sql, $params = NULL)
    {
        // carga el adaptador especifico para la conexion
        $adapter = DbAdapter::factory($this->_connection);
        $prepare = $adapter->pdo()->prepare($sql);
        if ($prepare->execute($params)) {
            return new ResultSet($prepare);
        }
        return FALSE;
    }
    /**
     * Realiza un insert sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool 
     */
    public function insert ($data = null)
    {
        // nuevo contenedor de consulta
        $dbQuery = new DbQuery();
        // asigna la tabla
        $dbQuery->table($this->_table);
        // asigna el esquema si existe
        if ($this->_schema) {
            $dbQuery->schema($this->_schema);
        }
        $dbQuery->insert($data);
        $adapter = DbAdapter::factory($this->_connection);
        try {
            $prepare = $adapter->pdo()->prepare($adapter->query($dbQuery));
            return $prepare->execute($data);
        } catch (PDOException $e) {    //echo $prepare->errorCode();die;
        }
        //return FALSE;
    }
}
