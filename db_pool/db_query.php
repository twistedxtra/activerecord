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
 * Clase para consultas SQL
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

class DbQuery
{
    /**
     * Nombre de tabla asociada al modelo
     *
     * @var string
     **/
    protected $_table;

    /**
     * Nombre del esquema donde se ubica la tabla
     *
     * @var string
     **/
    protected $_schema;

    /**
     * Condicion para WHERE
     *
     * @var string
     **/
    protected $_where = null;

    /**
     * INNER JOIN a realizar
     *
     * @var array
     **/
    protected $_join = array();

    /**
     * LEFT OUTER JOIN a realizar
     *
     * @var array
     **/
    protected $_leftJoin = array();

    /**
     * RIGHT OUTER JOIN a realizar
     *
     * @var array
     **/
    protected $_rightJoin = array();
    
    /**
     * FULL JOIN a realizar
     *
     * @var array
     **/
    protected $_fullJoin = array();

    /**
     * GROUP a realizar
     *
     * @var array
     **/
    protected $_group = null;
    
    /**
     * Condicion para HAVING
     *
     * @var string
     **/
    protected $_having = null;
    
    /**
     * Criterio de ordenamiento
     *
     * @var string
     **/
    protected $_order = null;

    /**
     * OFFSET
     *
     * @var string
     **/
    protected $_offset = null;

    /**
     * LIMIT
     *
     * @var string
     **/
    protected $_limit = null;

    /**
     * Nombre de conexion
     *
     * @var string
     **/
    protected $_connection;

    /**
     * Constructor
     *
     * @param string $connection nombre de conexion en databases.ini
     **/
    public function __construct($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Genera la fuente de datos
     *
     * @return string
     **/
    protected function _getSource()
    {
        $source = $this->_table;
        if($this->_schema) {
            $source = $this->_schema . '.' . $source;
        }
        
        return $source;
    }

    /**
     * Clausula WHERE
     *
     * @param string | array $conditions condiciones
     * @return SqlQuery
     **/
    public function where($conditions) 
    {
        // para cuando se pasa por array
        if(is_array($conditions)) {
            $buff = array();
            foreach($conditions as $column => $value) {
                $buff[] = "$column=\"" . DbPool::factory($this->_connection)->quote($value) . '"'; 
            }
            $conditions = implode(' AND ', $buff);
        }
        
        $this->_where = $conditions;
        return $this;
    }
    
    /**
     * Clausula INNER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return SqlQuery
     **/
    public function join($table, $conditions) 
    {
        $this->_join[] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula LEFT OUTER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return SqlQuery
     **/
    public function leftJoin($table, $conditions) 
    {
        $this->_leftJoin[] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula RIGHT OUTER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return SqlQuery
     **/
    public function rightJoin($table, $conditions) 
    {
        $this->_rightJoin[] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula FULL JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return SqlQuery
     **/
    public function fullJoin($table, $conditions) 
    {
        $this->_fullJoin[] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Columnas de la consulta
     *
     * @param string $table nombre de tabla
     * @return SqlQuery
     **/
    public function table($table) 
    {
        $this->_table = $table;
        return $this;
    }
        
    /**
     * Clausula SELECT
     *
     * @param string $criteria criterio de ordenamiento
     * @return SqlQuery
     **/
    public function order($criteria) 
    {
        $this->_order = $criteria;
        return $this;
    }
    
    /**
     * Clausula GROUP
     *
     * @param string $columns columnas
     * @return SqlQuery
     **/
    public function group($columns) 
    {
        $this->_group = $columns;
        return $this;
    }    

    /**
     * Clausula HAVING
     *
     * @param string $conditions condiciones
     * @return SqlQuery
     **/
    public function having($conditions) 
    {
        $this->_having = $conditions;
        return $this;
    }

    /**
     * Clausula LIMIT
     *
     * @param int $limit
     * @return SqlQuery
     **/
    public function limit($limit) 
    {
        $this->_limit = $limit;
        return $this;
    }   

    /**
     * Clausula OFFSET
     *
     * @param int $offset
     * @return SqlQuery
     **/
    public function offset($offset) 
    {
        $this->_offset = $offset;
        return $this;
    }  

    /**
     * Une con las clausulas adicionales de consulta
     *
     * @param string $sql consulta sql donde se unira las clausulas
     * @return string
     **/
    protected function _joinClausules($sql)
    {
        foreach($this->_join as $join) {
            $sql .= " INNER JOIN {$join['table']} ON ({$join['conditions']})";
        }

        foreach($this->_leftJoin as $join) {
            $sql .= " LEFT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
        }

        foreach($this->_rightJoin as $join) {
            $sql .= " RIGHT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
        }

        foreach($this->_fullJoin as $join) {
            $sql .= " FULL JOIN {$join['table']} ON ({$join['conditions']})";
        }

        if($this->_where) {
            $sql .= " WHERE $this->_where";
        }
    
        if($this->_group) {
            $sql .= " GROUP BY $this->_group";
        }

        if($this->_having) {
            $sql .= " HAVING $this->_having";
        }

        if($this->_order) {
            $sql .= " ORDER BY $this->_order";
        }

        if(!is_null($this->_limit)) {
            $sql .= " LIMIT $this->_limit";
        }

        if(!is_null($this->_offset)) {
            $sql .= " OFFSET $this->_offset";
        }
        
        return $sql;
    }

    /**
     * Construye la consulta SELECT
     *
     * @param string $columns columnas
     * @return string
     **/
    public function select($columns='*') 
    {
        return $this->_joinClausules("SELECT $columns FROM {$this->_getSource()}");
    }
    
    /**
     * Construye la consulta DELETE
     *
     * @return string
     **/
    public function delete() 
    {
        return $this->_joinClausules("DELETE FROM {$this->_getSource()}");
    }
    
    /**
     * Construye la consulta UPDATE
     *
     * @param string | array $values claves/valores
     * @return string
     **/
    public function update($values) 
    {
        // para cuando se pasa por array
        if(is_array($values)) {
            $buff = array();
            foreach($values as $column => $value) {
                $buff[] = "$column=\"" . DbPool::factory($this->_connection)->quote($value) . '"'; 
            }
            $values = implode(', ', $buff);
        }
    
        return $this->_joinClausules("UPDATE {$this->_getSource()} SET $values");
    }
    
    /**
     * Construye la consulta UPDATE
     *
     * @param string | array $columns columnas, o array de claves/valores
     * @param string $values 
     * @return string
     **/
    public function insert($columns, $values=null) 
    {
        // para cuando se pasa por array
        if(is_array($columns)) {
            $buff = array();
            $values = array();
            
            foreach($columns as $column => $value) {
                $buff[] = $column;
                $values[] = '"' . DbPool::factory($this->_connection)->quote($value) . '"';
            }
            
            $columns = implode(', ', $buff);
            $values = implode(', ', $values);
        }
    
        return "INSERT INTO {$this->_getSource()} ($columns) VALUES ($values)";
    }
    
    /**
     * Obtiene instancia de query en funcion de la conexion
     *
     * @param string $connection conexion a base de datos en databases.ini
     * @return DbQuery
     * @throw KumbiaException
     **/
    public static function factory($connection)
    {
        // lee la configuracion de base de datos
        $databases = Config::read('databases.ini');
        if(!isset($databases[$connection])) {
            throw new KumbiaException("No existe la conexion $connection en databases.ini");
        }
    
        // genera el nombre de clase
        $class = ucfirst($databases['type']) . 'Query';
    
        // si no existe la clase la carga
        if(!class_exists($class, false)) {
            // carga la clase
            require CORE_PATH . "libs/ActiveRecord/db_pool/adapthers/{$databases['type']}_query.php";
        }
        
        return new $class($connection);
    }
}
