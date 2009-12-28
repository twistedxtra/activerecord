<?php
require_once CORE_PATH . 'libs/ActiveRecord/db_pool/column.php';
class Rows implements ArrayAccess
{
    private $_columns = array();
    /**
     * Primary Key's
     */
    private $_PK = NULL;
    /**
     * Foreing Key's
     */
    private $_FK = NULL;
    /**
     * Relaciones
     */
    private $_relations = array();
    public function column ($col = NULL)
    {
        if (! isset($this->_columns[$col])) {
            $this->_columns[$col] = new Column();
            $this->_columns[$col]->_name = $col;
        }
        return $this->_columns[$col];
    }
    /**
     * Obtiene las Columnas
     */
    public function getColumns()
    {
        return $this->_columns;
    }
    /**
     * setea una relacion (asociaciones)
     */
    public function setRelation($column=NULL, $relation=NULL)
    {
        $this->_relations[$column] = $relation;
    }
    /**
     * Obtiene las Relaciones de la columna
     */
    public function getRelations($column=NULL)
    {
        if($column && array_key_exists($column, $this->_relations)){
            return $this->_relations[$column];
        }
        return $this->_relations;
    }
    public function setPK($pk=NULL)
    {
        $this->_PK = $pk;
    }
    public function getPK()
    {
        return $this->_PK;
    }
    public function setFK($fk=NULL)
    {
        $this->_FK = $fk;
    }
    public function getFK()
    {
        return $this->_FK;
    }
    
    //Implementacion de ArrayAccess
    public function offsetExists ($offset)
    {
        return isset($this->_columns[$offset]);
    }
    public function offsetSet ($offset, $value)
    {
        //self::$_columns[$offset] = $value;
    }
    public function offsetGet ($offset)
    {
        //return self::$_columns[$offset];
    }
    public function offsetUnset ($offset)
    {
        unset($this->_columns[$offset]);
    }
}