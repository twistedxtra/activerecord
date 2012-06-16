<?php

class Attribute
{

    /**
     * Alias del campo
     */
    public $alias = NULL;
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
     * Indica si es NOT NULL el campo
     */
    public $notNull = FALSE;
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
     * Formato para fechas
     */
    public $format = NULL;
}