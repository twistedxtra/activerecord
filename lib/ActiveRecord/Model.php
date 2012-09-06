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

namespace ActiveRecord;

use \PDO;
use \Iterator;
use ActiveRecord\Query\DbQuery;
use ActiveRecord\Adapter\Adapter;
use ActiveRecord\Metadata\Metadata;
use ActiveRecord\Paginator\Paginator;
use ActiveRecord\Exception\ActiveRecordException;

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
class Model implements Iterator
{
    /**
     * Obtener datos cargados en objeto del Modelo
     * 
     */

    const FETCH_MODEL = 'model';

    /**
     * Obtener datos cargados en objeto
     * 
     */
    const FETCH_OBJ = 'obj';

    /**
     * Obtener datos cargados en array
     * 
     */
    const FETCH_ARRAY = 'array';

    /**
     * Conexion a base datos que se utilizara
     *
     * @var strings
     */
    protected $connection = NULL;

    /**
     * Tabla origen de datos
     *
     * @var string
     */
    protected $table = NULL;

    /**
     * Esquema de datos
     *
     * @var string
     */
    protected $schema = NULL;

    /**
     * Objeto DbQuery para implementar chain
     * 
     * @var Obj
     */
    protected $dbQuery = NULL;

    /**
     * Posicion en el iterador
     *
     * @var int
     */
    private $pointer = 0;

    /**
     * ResulSet PDOStatement
     * 
     * @var \PDOStatement
     */
    protected $resultSet = NULL;

    /**
     * Modo de obtener datos
     * 
     * @var integer
     */
    protected $fetchMode = self::FETCH_MODEL;

    /**
     * Instancias de metadata de modelos
     *
     * @var array
     */
    private static $metadata = array();

    /**
     * Constructor de la class
     * 
     */
    public function __construct($data = NULL)
    {
        if (is_array($data)) {
            $this->dump($data);
        }
    }

    /**
     * Obtiene la metatada de un modelo
     *
     * @return Metadata
     */
    public function metadata()
    {
        $model = get_class($this);

        if (!isset(self::$metadata[$model])) {
            self::$metadata[$model] = Adapter::factory($this->getConnection())
                    ->describe($this->getTable(), $this->getSchema());
        }

        return self::$metadata[$model];
    }

    /**
     * Carga el array como atributos del objeto
     * 
     * @param array $data
     */
    public function dump($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Callback antes de crear
     * 
     * @return boolean
     */
    protected function beforeCreate()
    {
        
    }

    /**
     * Callback despues de crear
     * 
     * @return boolean
     */
    protected function afterCreate()
    {
        
    }

    /**
     * Callback antes de actualizar
     * 
     * @return boolean
     */
    protected function beforeUpdate()
    {
        
    }

    /**
     * Callback para realizar validaciones
     * 
     * @return boolean
     */
    protected function validate($update = FALSE)
    {
        
    }

    /**
     * Callback despues de actualizar
     * 
     * @return boolean
     */
    protected function afterUpdate()
    {
        
    }

    /**
     * Modo de obtener datos 
     * 
     * @param integer $mode
     * @return ActiveRecord
     */
    public function setFetchMode($mode)
    {
        $this->fetchMode = $mode;
        return $this;
    }

    /**
     * reset result set pointer 
     * (implementation required by 'rewind()' method in Iterator interface)
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * get current row set in result set 
     * (implementation required by 'current()' method in Iterator interface)
     */
    public function current()
    {
        if (!$this->valid()) {
            throw new ActiveRecordException('No se pude obtener la fila actual');
        }
        return $this->resultSet->fetch();
    }

    /**
     * Obtiene la posición actual del Puntero 
     * 
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * Mueve el puntero a la siguiente posición 
     * 
     */
    public function next()
    {
        ++$this->pointer;
    }

    /**
     * Determina si el puntero del ResultSet es valido 
     * 
     */
    public function valid()
    {
        return $this->pointer < $this->resultSet->rowCount();
    }

    /**
     * Indica el modo de obtener datos al ResultSet actual
     * 
     */
    protected function fetchMode($fetchMode = NULL)
    {
        // Si no se especifica toma el por defecto
        if (!$fetchMode) {
            $fetchMode = $this->fetchMode;
        }

        switch ($fetchMode) {
            // Obtener arrays
            case self::FETCH_ARRAY:
                $this->resultSet->setFetchMode(PDO::FETCH_ASSOC);
                break;

            // Obtener instancias de objetos simples
            case self::FETCH_OBJ:
                $this->resultSet->setFetchMode(PDO::FETCH_OBJ);
                break;

            // Obtener instancias del mismo modelo
            case self::FETCH_MODEL:
            default:
                // Instancias de un nuevo modelo, por lo tanto libre de los atributos de la instancia actual
                $this->resultSet->setFetchMode(PDO::FETCH_INTO, new static());
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
        $this->table = $table;
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
        if (!$this->table) {
            $this->table = strtolower(basename(get_class($this)));
            $this->table[0] = strtolower($this->table[0]);
            $this->table = strtolower(preg_replace('/([A-Z])/', "_$1", $this->table));
        }

        // Tabla
        return $this->table;
    }

    /**
     * Asigna el schema
     * 
     * @param string $schema
     * @return ActiveRecord
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * Obtiene el schema
     * 
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Asigna la conexion
     * 
     * @param string $conn
     * @return ActiveRecord
     */
    public function setConnection($conn)
    {
        $this->connection = $conn;
        return $this;
    }

    /**
     * Obtiene la conexion
     * 
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Ejecuta una setencia SQL aplicando Prepared Statement
     * 
     * @param string $sql Setencia SQL
     * @param array $params parametros que seran enlazados al SQL
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function sql($sql, $params = NULL, $fetchMode = NULL)
    {
        try {
            // Obtiene una instancia del adaptador y prepara la consulta
            $this->resultSet = Adapter::factory($this->connection)
                    ->prepare($sql);

            // Indica el modo de obtener los datos en el ResultSet
            $this->fetchMode($fetchMode);

            // Ejecuta la consulta
            $this->resultSet->execute($params);
            return $this;
        } catch (PDOException $e) {
            // Aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo $this->resultSet->errorCode();
        }

        return FALSE;
    }

    /**
     * Ejecuta una consulta de dbQuery
     * 
     * @param DbQuery $dbQuery Objeto de consulta
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function query($dbQuery, $fetchMode = NULL)
    {
        $dbQuery->table($this->getTable());

        // Asigna el esquema si existe
        if ($this->schema) {
            $dbQuery->schema($this->schema);
        }

//        try {
        // Obtiene una instancia del adaptador y prepara la consulta
        $this->resultSet = Adapter::factory($this->connection)
                ->prepareDbQuery($dbQuery);

        // Indica el modo de obtener los datos en el ResultSet
        $this->fetchMode($fetchMode);

        // Ejecuta la consulta
        $this->resultSet->execute($dbQuery->getBind());
        return $this;
//        } catch (\PDOException $e) {
//            // Aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
//        }
    }

    /**
     * Devuelve la instancia para realizar chain
     * 
     * @return DbQuery
     */
    public function get()
    {
        // Crea la instancia de DbQuery
        $this->dbQuery = new DbQuery();

        return $this->dbQuery;
    }

    /**
     * Efectua una busqueda
     *
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function find($fetchMode = NULL)
    {
        $this->dbQuery || $this->get();
        return $this->query($this->dbQuery->select(), $fetchMode);
    }

    /**
     * Obtiene un array con los items resultantes de la busqueda
     * 
     * @param string $fetchMode
     * @return array
     */
    public function findAll($fetchMode = NULL)
    {
        return $this->find($fetchMode);
    }

    /**
     * Obtiene el primer elemento de la busqueda
     * 
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function first($fetchMode = NULL)
    {
        $this->dbQuery || $this->get();

        // Realiza la busqueda y retorna el objeto ActiveRecord
        return $this->query($this->dbQuery->select()->limit(1)
                                ->offset(0), $fetchMode)->resultSet->fetch();
    }

    /**
     * Busca por medio de una columna especifica
     * 
     * @param string $column columna de busqueda
     * @param string $value valor para la busqueda
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function findBy($column, $value, $fetchMode = NULL)
    {
        $this->get()->where("$column = :value")->bindValue('value', $value);
        return $this->first($fetchMode);
    }

    /**
     * Busca por medio de una columna especifica y obtiene todas la coincidencias
     * 
     * @param string $column columna de busqueda
     * @param string $value valor para la busqueda
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function findAllBy($column, $value, $fetchMode = NULL)
    {
        if (is_array($value)) {
            $query = $this->get();
            $in = array();
            foreach ($value as $k => $v) {
                $in[] = ":in_$k";
                $query->bindValue("in_$k", $v);
            }
            $query->where("$column IN (" . join(',', $in) . ")");
        } else {
            $this->get()->where("$column = :value")->bindValue('value', $value);
        }
        return $this->find($fetchMode)->resultSet->fetchAll();
    }

    /**
     * Buscar por medio de la clave primaria
     * 
     * @param string $value
     * @param string $fetchMode
     * @return Model
     */
    public function findByPK($value, $fetchMode = NULL)
    {
        return $this->findBy($this->metadata()->getPK(), $value, $fetchMode);
    }

    /**
     * Obtiene un array de los atributos que corresponden a columnas
     * en la tabla
     * 
     * @return array
     */
    private function getTableValues()
    {
        $data = array();

        // Itera en cada atributo
        foreach ($this->metadata()->getAttributes() as $fieldName => $attr) {

            if (property_exists($this, $fieldName)) {
                if ($this->$fieldName === '') {
                    if (!$attr->default) {
                        $data[$fieldName] = NULL;
                    }
                } else {
                    $data[$fieldName] = $this->$fieldName;
                }
            } else {
                if (!$attr->default) {
                    $data[$fieldName] = NULL;
                }
            }
        }

        return $data;
    }

    /**
     * Realiza un insert sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return ActiveRecord 
     */
    public function create($data = NULL)
    {
        // Si es un array, se cargan los atributos en el objeto
        if (is_array($data)) {
            $this->dump($data);
        }

        // Callback de validaciónes
        if ($this->validate(FALSE) === FALSE) {
            return FALSE;
        }

        // Callback antes de crear
        if ($this->beforeCreate() === FALSE) {
            return FALSE;
        }

        // Nuevo contenedor de consulta
        $dbQuery = new DbQuery();

        // Ejecuta la consulta
        if ($this->query($dbQuery->insert($this->getTableValues()))) {

            // Convenio patron identidad en activerecord si PK es "id"
            if ($this->metadata()->getPK() === 'id' && (!isset($this->id) || $this->id == '')) {
                // Obtiene el ultimo id insertado y lo carga en el objeto
                $this->id = Adapter::factory($this->connection)
                                ->pdo()->lastInsertId();
            }

            // Callback despues de crear
            $this->afterCreate();
            return $this;
        }

        return FALSE;
    }

    /**
     * Realiza un update sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool
     */
    public function updateAll($data)
    {
        $this->dbQuery || $this->get();

        // Ejecuta la consulta
        return $this->query($this->dbQuery->update($data));
    }

    /**
     * Realiza un delete sobre la tabla
     * 
     * @return Bool
     */
    public function deleteAll()
    {
        $this->dbQuery || $this->get();
        // Ejecuta la consulta
        return $this->query($this->dbQuery->delete());
    }

    /**
     * Cuenta las apariciones de filas
     * 
     * @param string $column
     * @return integer
     */
    public function count($column = '*')
    {
        $this->dbQuery || $this->get();

        $this->dbQuery->columns("COUNT($column) AS n");
        return $this->first(self::FETCH_OBJ)->n;
    }

    /**
     * Verifica si existe al menos una fila con las condiciones indicadas
     * 
     * @return boolean
     */
    public function existsOne()
    {
        return $this->count() > 0;
    }

    /**
     * Establece condicion de busqueda con clave primaria
     * 
     * @param DbQuery $dbQuery
     */
    protected function wherePK($dbQuery)
    {
        // Obtiene la clave primaria
        $pk = $this->metadata()->getPK();

        // Si es clave primaria compuesta
        if (is_array($pk)) {
            foreach ($pk as $k) {
                if (!isset($this->$k)) {
                    throw new ActiveRecordException("Debe definir valor para la columna $k de la clave primaria");
                }

                $dbQuery->where("$k = :pk_$k")->bindValue("pk_$k", $this->$k);
            }
        } else {
            if (!isset($this->$pk)) {
                throw new ActiveRecordException("Debe definir valor para la clave primaria");
            }

            $dbQuery->where("$pk = :pk_$pk")->bindValue("pk_$pk", $this->$pk);
        }
    }

    /**
     * Verifica si esta persistente en la BD el objeto actual
     * 
     * @return boolean
     */
    public function exists()
    {
        // Objeto de consulta
        $dbQuery = $this->get();

        // Establece condicion de busqueda con clave primaria
        $this->wherePK($dbQuery);

        return $this->existsOne();
    }

    /**
     * Realiza un update del registro sobre la tabla
     * 
     * @param array $data información a ser guardada
     * @return Bool 
     */
    public function update($data = NULL)
    {
        // Si es un array, se cargan los atributos en el objeto
        if (is_array($data)) {
            $this->dump($data);
        }

        // Callback de validaciónes
        if ($this->validate(TRUE) === FALSE) {
            return FALSE;
        }

        // Callback antes de actualizar
        if ($this->beforeUpdate() === FALSE) {
            return FALSE;
        }

        // Si no existe el registro
        if (!$this->exists()) {
            return FALSE;
        }

        // Objeto de consulta
        $dbQuery = new DbQuery();
        // Establece condicion de busqueda con clave primaria
        $this->wherePK($dbQuery);

        // Ejecuta la consulta con el query utilizado para el exists
        if ($this->query($dbQuery->update($this->getTableValues()))) {
            // Callback despues de actualizar
            $this->afterUpdate();
            return $this;
        }

        return FALSE;
    }

    /**
     * Elimina el registro correspondiente al objeto
     * 
     * @return Bool 
     */
    public function delete()
    {
        // Objeto de consulta
        $dbQuery = new DbQuery();
        // Establece condicion de busqueda con clave primaria
        $this->wherePK($dbQuery);

        // Ejecuta la consulta con el query utilizado para el exists
        if ($this->query($dbQuery->delete())) {
            return $this;
        }

        return FALSE;
    }

    /**
     * Elimina el registro por medio de la clave primaria
     * 
     * @param string $value
     * @return Bool 
     */
    public function deleteByPK($value)
    {
        // Objeto de consulta
        $dbQuery = new DbQuery();

        // Obtiene la clave primeria
        $pk = $this->metadata()->getPK();

        // Establece la condicion
        $dbQuery->where("$pk = :pk_$pk")->bindValue("pk_$pk", $value);

        // Ejecuta la consulta con el query utilizado para el exists
        if ($this->query($dbQuery->delete())) {
            return $this;
        }

        return FALSE;
    }

    public function paginate($page, $per_page = 10, $fetchMode = NULL)
    {
        $this->setFetchMode($fetchMode);

        $this->dbQuery || $this->get();

        return Paginator::paginate($this, $this->dbQuery, $page, $per_page);
    }

    public function save(array $data = array())
    {
        if (count($data)) {
            $this->dump($data);
        }

        if (isset($this->{$this->metadata()->getPK()}) && $this->exists()) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    /**
     * Inicia una transacci&oacute;n si es posible
     *
     */
    public function begin()
    {
        return DbAdapter::factory($this->_connection)->pdo()->beginTransaction();
    }

    /**
     * Cancela una transacci&oacute;n si es posible
     *
     */
    public function rollback()
    {
        return DbAdapter::factory($this->_connection)->pdo()->rollBack();
    }

    /**
     * Hace commit sobre una transacci&oacute;n si es posible
     *
     */
    public function commit()
    {
        return DbAdapter::factory($this->_connection)->pdo()->commit();
    }

}
