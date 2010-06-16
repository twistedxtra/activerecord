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
	 * Obtener datos cargados en objeto del Modelo
	 * 
	 */
	const FETCH_MODEL = 1;
	
	/**
	 * Obtener datos cargados en objeto
	 * 
	 */
	const FETCH_OBJ = 2;
	
	/**
	 * Obtener datos cargados en array
	 * 
	 */
	const FETCH_ARRAY = 3;
		
    /**
     * Conexion a base datos que se utilizara
     *
     * @var strings
     */
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
     * @var PDOStatement
     */
    private $_resultSet = NULL;
	
	/**
	 * Modo de obtener datos
	 * 
	 * @var integer
	 */
	protected $_fetchMode = self::FETCH_MODEL;
	
    /**
     * Constructor de la class
	 * 
     */
    public function __construct ($data = null)
    {
        if (is_array($data)) {
            $this->_dump($data);
        }
    }
	
	/**
	 * Carga el array como atributos del objeto
	 * 
	 * @param array $data
	 */
	protected function _dump($data)
	{
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
	}
	
	/**
	 * Modo de obtener datos 
	 * 
	 * @param integer $mode
	 * @return ActiveRecord
	 */
	public function setFetchMode($mode) 
	{
		$this->_fetchMode = $mode;
		return $this;
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
            throw new KumbiaException('No se pude obtener la fila actual');
        }
        return $this->_resultSet->fetch();
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
	
	/**
	 * Indica el modo de obtener datos al ResultSet actual
	 * 
	 */
	protected function _fetchMode()
	{
		switch ($this->_fetchMode) {
			// Obtener instancias del mismo modelo
			case self::FETCH_MODEL:
				$this->_resultSet->setFetchMode(PDO::FETCH_INTO, new self());
				break;
				
			// Obtener instancias de objetos simples
			case self::FETCH_OBJ:
				$this->_resultSet->setFetchMode(PDO::FETCH_OBJ);
				break;
				
			// Obtener arrays
			case self::FETCH_ARRAY:
				$this->_resultSet->setFetchMode(PDO::FETCH_ASSOC);
				break;
		}
	}
	
	/**
	 * Asigna la tabla fuente de datos
	 * 
	 * @param string $table
	 * @return ActiveRecord
	 */
	public function setTable($table)
	{
		$this->_table = $table;
		return $this;
	}
	
	/**
	 * Obtiene la tabla fuente de datos
	 * 
	 * @return string
	 */
	public function getTable()
	{
        // Asigna la tabla
		if(!$this->_table) {
			$this->_table = Util::smallcase(get_class($this));
		}
		
		// Tabla
		return $this->_table;	
	}
	
	/**
	 * Asigna el schema
	 * 
	 * @param string $schema
	 * @return ActiveRecord
	 */
	public function setSchema($schema)
	{
		$this->_schema = $schema;
		return $this;
	}
	
	/**
	 * Obtiene el schema
	 * 
	 * @return string
	 */
	public function getSchema()
	{
		return $this->_schema;	
	}
	
    /**
     * Ejecuta una setencia SQL aplicando Prepared Statement
     * 
     * @param string $sql Setencia SQL
     * @param array $params parametros que seran enlazados al SQL
     * @return ActiveRecord
     */
    public function sql ($sql, $params = NULL)
    {
		// Obtiene una instancia del adaptador
		$adapter = DbAdapter::factory($this->_connection);
		
		try {			
			// Prepara la consulta
            $this->_resultSet = $adapter->prepare($sql);
			
			// Indica el modo de obtener los datos en el ResultSet
			$this->_fetchMode();
			
			// Ejecuta la consulta
            $this->_resultSet->execute($params);
            return $this;
        } catch (PDOException $e) {
            // Aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->_resultSet->errorCode();
        }
		
        return FALSE;
    }
		
    /**
     * Ejecuta una consulta de dbQuery
     * 
     * @param DbQuery $dbQuery Objeto de consulta
     * @return ActiveRecord
     */
	public function query($dbQuery) 
	{        
        $dbQuery->table($this->getTable());
		
        // Asigna el esquema si existe
        if ($this->_schema) {
            $dbQuery->schema($this->_schema);
        }
		     
		// Obtiene una instancia del adaptador
		$adapter = DbAdapter::factory($this->_connection);
			    
		try {			
			// Prepara la consulta
            $this->_resultSet = $adapter->prepareDbQuery($dbQuery);
			
			// Indica el modo de obtener los datos en el ResultSet
			$this->_fetchMode();
			
			// Ejecuta la consulta
            $this->_resultSet->execute($dbQuery->getBind());
            return $this;
        } catch (PDOException $e) {
            // Aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->_resultSet->errorCode();
        }
	}
	
    /**
     * Devuelve la instancia para realizar chain
     * 
     * @return DbQuery
     */
    public function get ()
    {
		// Crea la instancia de DbQuery
        $this->_dbQuery = new DbQuery();
				
        return $this->_dbQuery;
    }
	
    /**
     * Efectua una busqueda
     *
     * @return ActiveRecord
     */
    public function find ()
    {
        if (! $this->_dbQuery) {
            $this->get();
        }
        return $this->query($this->_dbQuery->select());
    }
	
	/**
	 * Obtiene un array con los items resultantes de la busqueda
	 * 
	 * @return array
	 */
    public function all ()
    {
		return $this->find()->_resultSet->fetchAll();
	}
	
	/**
	 * Obtiene el primer elemento de la busqueda
	 * 
	 * @return ActiveRecord
	 */
    public function first ()
    {
        if (! $this->_dbQuery) {
            $this->get();
        }
		
		// Realiza la busqueda y retorna el objeto ActiveRecord
		return $this->query($this->_dbQuery->select()->limit(1)->offset(0))->_resultSet->fetch();
	}
		
	/**
	 * Busca por medio de una columna especifica
	 * 
	 * @param string $column columna de busqueda
	 * @param string $value valor para la busqueda
	 * @return ActiveRecord
	 */
	public function findBy($column, $value)
	{
		$this->get()->where("$column = :value")->bind(array('value' => $value));
		return $this->first();
	}
		
	/**
	 * Busca por medio de una columna especifica y obtiene todas la coincidencias
	 * 
	 * @param string $column columna de busqueda
	 * @param string $value valor para la busqueda
	 * @return ActiveRecord
	 */
	public function findAllBy($column, $value)
	{
		$this->get()->where("$column = :value")->bind(array('value' => $value));
		return $this->find();
	}
	
	/**
	 * Buscar por medio de la clave primaria
	 * 
	 * @param string $value
	 */
	public function findByPK($value)
	{
		// Obtiene una instancia del adaptador
		$adapter = DbAdapter::factory($this->_connection);
		$metadata = $adapter->describe($this->getTable(), $this->_schema);
		
		return $this->findBy($metadata->getPK(), $value);
	}
	
    /**
     * Realiza un insert sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool 
     */
    public function create ($data = NULL)
    {
        // Nuevo contenedor de consulta
        $dbQuery = new DbQuery();
		
		// Ejecuta la consulta
		return $this->query($dbQuery->insert($data));
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
		
		// Ejecuta la consulta
		return $this->query($this->_dbQuery->update($data));
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
		
		// Ejecuta la consulta
		return $this->query($this->_dbQuery->delete());
    }
}
