<?php
class Column 
{
    /**
     * Alias del campo
     */
    private $_alias = NULL;
    /**
     * Nombre del campo en la BD
     */
    public $_name = NULL;
    /**
     * Tipo de dato de cara a la APP
     */
    public $_type = NULL;
    /**
     * Tipo de dato de la BD
     */
    public $_dbType = NULL;
    /**
     * Valor por defecto del campo
     */
    public $_default = NULL;
    /**
     * Longitud del Campo
     */
    public $_length = 50;
    /**
     * Indica si es NULL el campo
     */
    public $_isNull = TRUE;
    /**
     * Indica si es PK el campo
     */
    public $_isPK = FALSE;
    /**
     * Indica si es FK el campo
     */
    public $_isFK = FALSE;
    /**
     * Indica si es Unique el campo
     */
    public $_isUni = FALSE;
    /**
     * Campo con secuencias (serial o auto-increment)
     */
    public $_isAutoIncrement = FALSE;
    
    public $_relation = NULL;
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