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
use \Countable;
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
class Model implements Iterator, Countable
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
    protected static $table = NULL;

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
    protected static $dbQuery = NULL;

    /**
     * Posicion en el iterador
     *
     * @var int
     */
    private $pointer = NULl;

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
     *
     * @var array 
     */
    protected static $relations = array();

    /**
     * Constructor de la class
     * 
     */
    public final function __construct($data = NULL)
    {
        if (is_array($data)) {
            $this->dump($data);
        }
        $this->initialize();
        if (!isset(static::$relations[get_called_class()])) {
            $this->createRelations();
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

    protected function initialize()
    {
        
    }

    protected function createRelations()
    {
        
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
     */
    public static function setTable($table)
    {
        static::$table[get_called_class()] = $table;
    }

    /**
     * Obtiene la tabla fuente de datos
     * 
     * @return string
     */
    public static function getTable()
    {
        // Asigna la tabla
        $modelName = get_called_class();
        if (!isset(static::$table[$modelName])) {
            static::$table[$modelName] = strtolower(basename($modelName));
            static::$table[$modelName][0] = strtolower(static::$table[$modelName][0]);
            static::$table[$modelName] = strtolower(preg_replace('/([A-Z])/', "_$1", static::$table[$modelName]));
        }

        // Tabla
        return static::$table[$modelName];
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
        $dbQuery->table(static::getTable());

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
    public static function createQuery()
    {
        // Crea la instancia de DbQuery
        return self::$dbQuery[get_called_class()] = new DbQuery();
    }

    /**
     * Efectua una busqueda
     *
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public static function find($fetchMode = NULL)
    {
        $model = new static();
        return $model->query(self::getDbQuery()->select(), $fetchMode);
    }

    /**
     * Obtiene un array con los items resultantes de la busqueda
     * 
     * @param string $fetchMode
     * @return array
     */
    public static function findAll($fetchMode = NULL)
    {
        return self::find($fetchMode);
    }

    /**
     * Obtiene el primer elemento de la busqueda
     * 
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public static function first($fetchMode = NULL)
    {
        $model = new static();
        // Realiza la busqueda y retorna el objeto ActiveRecord
        $query = self::getDbQuery()
                ->select()
                ->limit(1)
                ->offset(0);
        return $model->query($query, $fetchMode)->resultSet->fetch();
    }

    /**
     * Busca por medio de una columna especifica
     * 
     * @param string $column columna de busqueda
     * @param string $value valor para la busqueda
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public static function findBy($column, $value, $fetchMode = NULL)
    {
        self::createQuery()
                ->where("$column = :value")
                ->bindValue('value', $value);
        return self::first($fetchMode);
    }

    /**
     * Busca por medio de una columna especifica y obtiene todas la coincidencias
     * 
     * @param string $column columna de busqueda
     * @param string $value valor para la busqueda
     * @param string $fetchMode
     * @return ActiveRecord
     */
    public static function findAllBy($column, $value, $fetchMode = NULL)
    {
        if (is_array($value)) {
            $query = self::createQuery();
            $in = array();
            foreach ($value as $k => $v) {
                $in[] = ":in_$k";
                $query->bindValue("in_$k", $v);
            }
            $query->where("$column IN (" . join(',', $in) . ")");
        } else {
            self::createQuery()
                    ->where("$column = :value")
                    ->bindValue('value', $value);
        }
        return self::find($fetchMode)->resultSet->fetchAll();
    }

    /**
     * Buscar por medio de la clave primaria
     * 
     * @param string $value
     * @param string $fetchMode
     * @return Model
     */
    public static function findByPK($value, $fetchMode = NULL)
    {
        $model = new static();

        $model = new static();

        $pk = $model->metadata()->getPK();

        $query = self::createQuery()
                ->select()
                ->where("$pk = :pk")
                ->bindValue('pk', $value);
        // Realiza la busqueda y retorna el objeto ActiveRecord
        return $model->query($query, $fetchMode)->resultSet->fetch();
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
    public static function updateAll($data)
    {
        $model = new static();
        // Ejecuta la consulta
        return $model->query(self::getDbQuery()->update($data));
    }

    /**
     * Realiza un delete sobre la tabla
     * 
     * @return Bool
     */
    public static function deleteAll()
    {
        $model = new static();
        // Ejecuta la consulta
        return $model->query(self::getDbQuery()->delete());
    }

    /**
     * Cuenta las apariciones de filas
     * 
     * @param string $column
     * @return integer
     */
    public function count()
    {
        if (NULL !== $this->pointer) {
            return $this->pointer;
        }

        self::getDbQuery()->columns("COUNT(*) AS n");
        return $this->pointer = $this->first(self::FETCH_OBJ)->n;
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
        // Establece condicion de busqueda con clave primaria
        $this->wherePK(self::getDbQuery());

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

    public static function paginate($page, $per_page = 10, $fetchMode = NULL)
    {
        $model = new static();

        $model->setFetchMode($fetchMode);

        return Paginator::paginate($model, self::getDbQuery(), $page, $per_page);
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

    /**
     * Crea una relacion 1-1 inversa entre dos modelos
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function belongsTo($model, $fk)
    {
        $fk || $fk = $model::getTable() . '_id';
        static::$relations[get_called_class()]['belongsTo'][$model] = $fk;
    }

    /**
     * Crea una relacion 1-1 entre dos modelos
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function hasOne($model, $fk = NULL)
    {
        $fk || $fk = static::getTable() . "_id";
        static::$relations[get_called_class()]['hasOne'][$model] = $fk;
    }

    /**
     * Crea una relacion 1-n entre dos modelos
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function hasMany($model, $fk = NULL)
    {
        $fk || $fk = static::getTable() . "_id";
        static::$relations[get_called_class()]['hasMany'][$model] = $fk;
    }

    /**
     * Crea una relacion n-n o 1-n inversa entre dos modelos
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     * key: campo llave que identifica al propio modelo
     * through : atrav�s de que tabla
     */
    protected function hasAndBelongsToMany($model, $through, $fk = NULL, $key = NULL)
    {
        $fk || $fk = $model::getTable() . '_id';
        $key || $key = static::getTable() . '_id';
        static::$relations[get_called_class()]['hasAndBelongsToMany']
                [$model] = compact('through', 'fk', 'key');
    }

    /**
     * Devuelve los registros del modelo al que se está asociado.
     *
     * @param string $mmodel nombre del modelo asociado
     * @return array|NULL|FALSE si existen datos devolverá un array,
     * NULL si no hay datos asociados aun, y false si no existe ninguna asociación.
     */
    public function get($model)
    {
        if (!isset(static::$relations[get_called_class()])) {
            return FALSE;
        }

        if (isset(static::$relations[get_called_class()]['belongsTo']) &&
                isset(static::$relations[get_called_class()]['belongsTo'][$model])) {

            $fk = static::$relations[get_called_class()]['belongsTo'][$model];
            $model = new $model();

            return $model->findBy($fk, $this->{$fk});
        }

        if (isset(static::$relations[get_called_class()]['hasOne']) &&
                isset(static::$relations[get_called_class()]['hasOne'][$model])) {

            $fk = static::$relations[get_called_class()]['hasOne'][$model];
            $model = new $model();

            return $model->findBy($model->metadata()->getPK(), $this->{$fk});
        }

        if (isset(static::$relations[get_called_class()]['hasMany']) &&
                isset(static::$relations[get_called_class()]['hasMany'][$model])) {

            $fk = static::$relations[get_called_class()]['hasMany'][$model];
            $model = new $model();

            return $model->findAllBy($fk, $this->{$this->metadata()->getPK()});
        }

//        if (array_key_exists($mmodel, $this->_has_and_belongs_to_many)) {
//            $relation = $this->_has_and_belongs_to_many[$mmodel];
//            $relation_model = self::get($relation->model);
//            $source = ($this->schema ? "{$this->schema}." : NULL ) . $this->source;
//            $relation_source = ($relation_model->schema ? "{$relation_model->schema}." : NULL ) . $relation_model->source;
//            /**
//             * Cargo atraves de que tabla se efectuara la relacion
//             *
//             */
//            if (!isset($relation->through)) {
//                if ($source > $relation_source) {
//                    $relation->through = "{$this->source}_{$relation_source}";
//                } else {
//                    $relation->through = "{$relation_source}_{$this->source}";
//                }
//            } else {
//                $through = explode('/', $relation->through);
//                $relation->through = end($through);
//            }
//            if ($this->{$this->primary_key[0]}) {
//                return $relation_model->find_all_by_sql("SELECT $relation_source.* FROM $relation_source, {$relation->through}, $source
//                    WHERE {$relation->through}.{$relation->key} = {$this->db->add_quotes($this->{$this->primary_key[0]}) }
//                    AND {$relation->through}.{$relation->fk} = $relation_source.{$relation_model->primary_key[0]}
//                    AND {$relation->through}.{$relation->key} = $source.{$this->primary_key[0]}
//                    ORDER BY $relation_source.{$relation_model->primary_key[0]}");
//            } else {
//                return array();
//            }
//        }
    }

    /**
     *
     * @return DbQuery 
     */
    private static function getDbQuery()
    {
        return isset(self::$dbQuery[get_called_class()]) ? self::$dbQuery[get_called_class()] : static::createQuery();
    }

}
