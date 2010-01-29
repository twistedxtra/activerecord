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
 
// @see KumbiaModel
require_once CORE_PATH . 'libs/ActiveRecord/active_record2/kumbia_model.php';

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
class ActiveRecord2 extends KumbiaModel implements Iterator
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
	 * Posicion en el iterador
	 *
	 * @var int
	 */
	private $_pointer = 0;
	
    /**
     * ResulSet PDOStatement
     * 
     * @var Obj
     */
    private $_resultSet = NULL;
	
    /**
     * Constructor de la class
     */
    public function __construct ($data = null)
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
     * @return ResultSet
     */
    public function find ()
    {
        if (! $this->_dbQuery) {
            $this->get();
        }
        return $this->findBySql($this->_dbQuery);
    }
	
    public function all ()
    {}
	
    /**
     * Devuelve la instancia para realizar chain
     * 
     * @return DbQuery
     */
    public function get ()
    {
		// crea la instancia de DbQuery
        $this->_dbQuery = new DbQuery();
		
        // asigna la tabla
        $this->_dbQuery->table($this->_table);
		
        // asigna el esquema si existe
        if ($this->_schema) {
            $this->_dbQuery->schema($this->_schema);
        }
		
        return $this->_dbQuery->select();
    }
	
    /**
     * Efectua una busqueda de una consulta sql
     *
     * @param string | DbQuery $sql
     * @return ResultSet
     **/
    public function findBySql ($sql)
    {
        $bind = $sql->getBind();
        // carga el adaptador especifico para la conexion
        $adapter = DbAdapter::factory($this->_connection);
        // si no es un string, entonces es DbQuery
        if (! is_string($sql)) {
            $sql = $adapter->query($sql);
        }
		
        // ejecuta la consulta
        $this->_resultSet = $adapter->pdo()->prepare($sql);
        if ($this->_resultSet->execute($bind)) {
            return $this;
        }
        return FALSE;
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
        $this->_resultSet = $adapter->pdo()->prepare($sql);
        if ($this->_resultSet->execute($params)) {
            return $this;
        }
        return FALSE;
    }
	
    /**
     * Realiza un insert sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool 
     */
    public function create ($data = NULL)
    {
        // nuevo contenedor de consulta
        $dbQuery = new DbQuery();
        // asigna la tabla
        $dbQuery->table($this->_table);
        // asigna el esquema si existe
        if ($this->_schema) {
            $dbQuery->schema($this->_schema);
        }
        $adapter = DbAdapter::factory($this->_connection);
        try {
            $this->_resultSet = $adapter->pdo()->prepare($adapter->query($dbQuery->insert($data)));
            $this->_resultSet->execute($dbQuery->getBind());
            return $this;
        } catch (PDOException $e) {
            //aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->_resultSet->errorCode();
        }
    }
	
	/**
     * Realiza un update sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool
     */
    public function updateAll ($data)
    {
        if (! $this->_dbQuery) {
            $this->get();
        }
		
        $adapter = DbAdapter::factory($this->_connection);
        try {
            $this->_resultSet = $adapter->pdo()->prepare($adapter->query($this->_dbQuery->update($data)));
            $this->_resultSet->execute($this->_dbQuery->getBind());
            return $this;
        } catch (PDOException $e) {
            //aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->_resultSet->errorCode();
        }
    }
	
	/**
     * Realiza un delete sobre la tabla
     * 
     * @return Bool
     */
    public function deleteAll ()
    {
        if (! $this->_dbQuery) {
            $this->get();
        }
		
        $adapter = DbAdapter::factory($this->_connection);
        try {
            $this->_resultSet = $adapter->pdo()->prepare($adapter->query($this->_dbQuery->delete()));
            $this->_resultSet->execute($this->_dbQuery->getBind());
            return $this;
        } catch (PDOException $e) {
            //aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->_resultSet->errorCode();
        }
    }
	
    /**
     * Fetch Object
     * 
     * @param string Class
     * @return Array
     */
    public function fetchObject ()
    {
        $this->_resultSet->setFetchMode(PDO::FETCH_INTO, $this);
        return $this->_resultSet->fetch();
    }
	
    /**
     * reset result set pointer 
     * (implementation required by 'rewind()' method in Iterator interface)
     */
    public function rewind ()
    {
        $this->_pointer = 0;
    }
	
    /**
     * get current row set in result set 
     * (implementation required by 'current()' method in Iterator interface)
     */
    public function current ()
    {
        if (! $this->valid()) {
            throw new KumbiaException('Unable to retrieve current row.');
        }
        return $this->fetchObject();
    }
	
    /**
     * Obtiene la posición actual del Puntero 
     * 
     */
    public function key ()
    {
        return $this->_pointer;
    }
	
    /**
     * Mueve el puntero a la siguiente posición 
     * 
     */
    public function next ()
    {
        ++ $this->_pointer;
    }
	
    /**
     * Determina si el puntero del ResultSet es valido 
     * 
     */
    public function valid ()
    {
        return $this->_pointer < $this->_resultSet->rowCount();
    }
}
