<?php

class Validations
{

    protected $_valitations = array();

    public function validateNotNull($field, array $params = NULL, $overwrite = FALSE)
    {
        if ($overwrite || !isset($this->_valitations['notNull'][$field])) {
            $this->_valitations['notNull'][$field] = $params;
        }
        return $this;
    }

    public function validateDefault($field, array $params = NULL, $overwrite = FALSE)
    {
        if ($overwrite || !isset($this->_valitations['default'][$field])) {
            $this->_valitations['default'][$field] = $params;
        }
        return $this;
    }

    public function validateInteger($field, array $params = NULL, $overwrite = FALSE)
    {
        if ($overwrite || !isset($this->_valitations['integer'][$field])) {
            $this->_valitations['integer'][$field] = $params;
        }
        return $this;
    }

    public function validateUnique($field, array $params = NULL, $overwrite = FALSE)
    {
        if ($overwrite || !isset($this->_valitations['unique'][$field])) {
            $this->_valitations['unique'][$field] = $params;
        }
        return $this;
    }

    public function getValidations()
    {
        return $this->_valitations;
    }

}