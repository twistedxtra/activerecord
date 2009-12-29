<?php
class Column 
{
    /**
     * Alias del campo
     */
    private $_alias = NULL;
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
     * Relaciones
     */
    public $relation = NULL;
    /**
     * Formato para fechas
     */
    public $format = NULL;
	/**
     * @return $_alias
     */
    public function getAlias ()
    {
        return $this->_alias;
    }
	/**
     * @param String $alias
     */
    public function setAlias ($alias)
    {
        $this->_alias = ucwords(strtr($alias,'_-','  '));
    }
    
}