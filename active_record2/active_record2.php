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
require_once 'kumbia_model.php';
// @see Validations
require_once '/../validations.php';

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
    protected $_connection = NULL;
    /**
     * Tabla origen de datos
     *
     * @var string
     */
    protected $_table = NULL;
    /**
     * Esquema de datos
     *
     * @var string
     */
    protected $_schema = NULL;
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
    protected $_resultSet = NULL;
    /**
     * Modo de obtener datos
     * 
     * @var integer
     */
    protected $_fetchMode = self::FETCH_MODEL;
    /**
     * 
     * @var Validations
     */
    protected $_validator = NULL;

    /**
     * Constructor de la class
     *
     */
    public function __construct($data = NULL)
    {
        if (is_array($data)) {
            $this->dump($data);
        }

        $this->initialize();
    }

    private function _initValidator()
    {
        //llamamos al metodo que devuelve una instancia del validador;
        $v = $this->validations();
        foreach ($this->metadata()->getAttributes() as $field => $attribute) {
            if ($attribute->PK) {
                //$v->validateNotNull($field);
            } elseif ($attribute->notNull) {
                $v->validateNotNull($field);
            }
            if ($attribute->default) {
                $v->validateDefault($field);
            }
            if ($attribute->unique) {
                $v->validateUnique($field);
            }
        }
    }

    protected function initialize()
    {
        
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
    protected function _beforeCreate()
    {
        
    }

    /**
     * Callback despues de crear
     * 
     * @return boolean
     */
    protected function _afterCreate()
    {

    }

    /**
     * Callback antes de actualizar
     *
     * @return boolean
     */
    protected function _beforeUpdate()
    {
        
    }

    /**
     * Callback despues de actualizar
     * 
     * @return boolean
     */
    protected function _afterUpdate()
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
        $this->_fetchMode = $mode;
        return $this;
    }

    /**
     * reset result set pointer 
     * (implementation required by 'rewind()' method in Iterator interface)
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * get current row set in result set 
     * (implementation required by 'current()' method in Iterator interface)
     */
    public function current()
    {
        if (!$this->valid()) {
            throw new KumbiaException('No se pude obtener la fila actual');
        }
        return $this->_resultSet->fetch();
    }

    /**
     * Obtiene la posición actual del Puntero 
     * 
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Mueve el puntero a la siguiente posición 
     * 
     */
    public function next()
    {
        ++$this->_pointer;
    }

    /**
     * Determina si el puntero del ResultSet es valido 
     * 
     */
    public function valid()
    {
        return $this->_pointer < $this->_resultSet->rowCount();
    }

    /**
     * Indica el modo de obtener datos al ResultSet actual
     * 
     */
    protected function _fetchMode($fetchMode = NULL)
    {
        // Si no se especifica toma el por defecto
        if (!$fetchMode) {
            $fetchMode = $this->_fetchMode;
        }

        switch ($fetchMode) {
            // Obtener instancias del mismo modelo
            case self::FETCH_MODEL:
                // Instancias de un nuevo modelo, por lo tanto libre de los atributos de la instancia actual
                $class = get_class($this);
                $this->_resultSet->setFetchMode(PDO::FETCH_INTO, new $class());
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
        if (!$this->_table) {
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
     * Asigna la conexion
     * 
     * @param string $conn
     * @return ActiveRecord
     */
    public function setConnection($conn)
    {
        $this->_connection = $conn;
        return $this;
    }

    /**
     * Obtiene la conexion
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->_connection;
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
            $this->_resultSet = DbAdapter::factory($this->_connection)->prepare($sql);

            // Indica el modo de obtener los datos en el ResultSet
            $this->_fetchMode($fetchMode);

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
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function query($dbQuery, $fetchMode = NULL)
    {
        $dbQuery->table($this->getTable());

        // Asigna el esquema si existe
        if ($this->_schema) {
            $dbQuery->schema($this->_schema);
        }

        try {
            // Obtiene una instancia del adaptador y prepara la consulta
            $this->_resultSet = DbAdapter::factory($this->_connection)->prepareDbQuery($dbQuery);

            // Indica el modo de obtener los datos en el ResultSet
            $this->_fetchMode($fetchMode);

            // Ejecuta la consulta
            $this->_resultSet->execute($dbQuery->getBind());
            return $this;
        } catch (PDOException $e) {
            // Aqui debemos ir a cada adapter y verificar el código de error SQLSTATE
            echo "error: ", $e->getMessage();
        }
    }

    /**
     * Devuelve la instancia para realizar chain
     * 
     * @return DbQuery
     */
    public function get()
    {
        // Crea la instancia de DbQuery
        $this->_dbQuery = new DbQuery();

        return $this->_dbQuery;
    }

    /**
     * Efectua una busqueda
     *
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function find($fetchMode = NULL)
    {
        if (!$this->_dbQuery) {
            $this->get();
        }
        return $this->query($this->_dbQuery->select(), $fetchMode);
    }

    /**
     * Obtiene un array con los items resultantes de la busqueda
     *
     * @param string $fetchMode
     * @return array
     */
    public function findAll($fetchMode = NULL)
    {
        return $this->find($fetchMode)->_resultSet->fetchAll();
    }

    /**
     * Obtiene el primer elemento de la busqueda
     *
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public function first($fetchMode = NULL)
    {
        if (!$this->_dbQuery) {
            $this->get();
        }

        // Realiza la busqueda y retorna el objeto ActiveRecord
        return $this->query($this->_dbQuery->select()->limit(1)->offset(0), $fetchMode)->_resultSet->fetch();
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
        $this->get()->where("$column = :value")->bindValue('value', $value);
        return $this->find($fetchMode);
    }

    /**
     * Buscar por medio de la clave primaria
     *
     * @param string $value
     * @param string $fetchMode
     * @return ActiveRecord
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
    private function _getTableValues()
    {
        $data = array();

        // Itera en cada atributo
        foreach ($this->metadata()->getAttributesList() as $attr) {

            if (property_exists($this, $attr)) {
                if ($this->$attr === '') {
                    $data[$attr] = NULL;
                } else {
                    $data[$attr] = $this->$attr;
                }
            } else {
                $data[$attr] = NULL;
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
        //inicializamos el validador
        $this->_initValidator();

        // Si es un array, se cargan los atributos en el objeto
        if (is_array($data)) {
            $this->dump($data);
        }

        // @see ActiveRecordValidator
        require_once '/../active_record2/active_record_validator.php';

        // Ejecuta la validacion
        if (ActiveRecordValidator::validateOnCreate($this) === FALSE) {
            return FALSE;
        }

        // Callback antes de crear
        if ($this->_beforeCreate() === FALSE) {
            return FALSE;
        }

        // Nuevo contenedor de consulta
        $dbQuery = new DbQuery();

        // Ejecuta la consulta
        if ($this->query($dbQuery->insert($this->_getTableValues()))) {

            // Convenio patron identidad en activerecord si PK es "id"
            if ($this->metadata()->getPK() === 'id' && (!isset($this->id) || $this->id == '')) {
                // Obtiene el ultimo id insertado y lo carga en el objeto
                $this->id = DbAdapter::factory($this->_connection)->pdo()->lastInsertId();
            }


            // Callback despues de crear
            $this->_afterCreate();
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
        if (!$this->_dbQuery) {
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
    public function deleteAll()
    {
        if (!$this->_dbQuery) {
            $this->get();
        }

        // Ejecuta la consulta
        return $this->query($this->_dbQuery->delete());
    }

    /**
     * Validadores
     * 
     * @return Validations
     */
    public function validations()
    {
        if (!$this->_validator instanceof Validations) {
            $this->_validator = new Validations();
        }
        return $this->_validator;
    }

    /**
     * Cuenta las apariciones de filas
     *
     * @param string $column
     * @return integer
     */
    public function count($column = '*')
    {
        if (!$this->_dbQuery) {
            $this->get();
        }

        $this->_dbQuery->columns("COUNT($column) AS n");
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
    protected function _wherePK($dbQuery)
    {
        // Obtiene la clave primaria
        $pk = $this->metadata()->getPK();

        // Si es clave primaria compuesta
        if (is_array($pk)) {
            foreach ($pk as $k) {
                if (!isset($this->$k)) {
                    throw new KumbiaException("Debe definir valor para la columna $k de la clave primaria");
                }

                $dbQuery->where("$k = :pk_$k")->bindValue("pk_$k", $this->$k);
            }
        } else {
            if (!isset($this->$pk)) {
                throw new KumbiaException("Debe definir valor para la clave primaria");
            }

            $dbQuery->where("$pk = :pk_$pk")->bindValue("pk_$pk", $this->$pk);
        }
    }

    /**
     * Verifica si esta persistente en la BD el objeto actual en la bd
     * 
     * @return boolean
     */
    public function exists()
    {
        // Objeto de consulta
        $dbQuery = $this->get();

        // Establece condicion de busqueda con clave primaria
        $this->_wherePK($dbQuery);

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
        //inicializamos el validador
        $this->_initValidator();

        // Si es un array, se cargan los atributos en el objeto
        if (is_array($data)) {
            $this->dump($data);
        }

        // @see ActiveRecordValidator
        require_once 'active_record_validator.php';

        // Ejecuta la validacion
        if (ActiveRecordValidator::validateOnUpdate($this) === FALSE) {
            return FALSE;
        }

        // Callback antes de actualizar
        if ($this->_beforeUpdate() === FALSE) {
            return FALSE;
        }

        // Si no existe el registro
        if (!$this->exists()) {
            return FALSE;
        }

        // Objeto de consulta
        $dbQuery = new DbQuery();
        // Establece condicion de busqueda con clave primaria
        $this->_wherePK($dbQuery);

        // Ejecuta la consulta con el query utilizado para el exists
        if ($this->query($dbQuery->update($this->_getTableValues()))) {
            // Callback despues de actualizar
            $this->_afterUpdate();
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
        $this->_wherePK($dbQuery);

        // Ejecuta la consulta con el query utilizado para el exists
        if ($this->query($dbQuery->delete())) {
            return $this;
        }

        return FALSE;
    }

    public function save($data = NULL)
    {
        return (isset($this->{$this->metadata()->getPK()}) && $this->exists()) ?
                $this->update($data) : $this->create($data);
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

}
