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
 * Validador para ActiveRecord
 * 
 * @category   Kumbia
 * @package    ActiveRecord
 * @copyright  Copyright (c) 2005-2010 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

class ActiveRecordValidator
{	
	/**
	 * Instancia del validador
	 * 
	 * @var ActiveRecordValidator
	 */ 
	private static $_instance = NULL;
	
	/**
	 * Constructor
	 * 
	 * @param ActiveRecord $model
	 * @param boolean $update
	 */
	private function __construct()
	{}
	
	/**
	 * Obtiene una instancia
	 * 
	 * @return ActiveRecordValidator
	 */
	public static function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	/**
	 * Validar en caso de crear
	 * 
	 * @param ActiveRecord $model
	 * @return boolean
	 */
	public final static function validateOnCreate($model)
	{
		return self::getInstance()->_validate($model);
	}
	
	/**
	 * Validar en caso de actualizar
	 * 
	 * @param ActiveRecord $model
	 * @return boolean
	 */
	public final static function validateOnUpdate($model)
	{
		return self::getInstance()->_validate($model, TRUE);
	}
	
	/**
	 * Efectua las validaciones
	 * 
	 * @param ActiveRecord $model
	 * @param boolean $update
	 * @return boolean
	 */
	private function _validate($model, $update = FALSE)
	{
		// Obtiene la definición de validadores
		$validatorsDefinition = $model->validators();
		
		// Si no hay validadores definidos
		if(!$validatorsDefinition) {
			return TRUE;
		}
		
		// Por defecto es valido
		$valid = TRUE;
		
		// Realiza las validaciones por columna
		foreach($validatorsDefinition  as $column => $validators) {	
			// Si es una validación simple column => validador
			if(is_string($validators)) {
				$valid = $this->_validatorOnColumn($model, $column, $validators, NULL, $update) && $valid;
			} else { // Se trata de un conjunto de validadores
				// Itera en los validadores
				foreach($validators as $k => $v) {
					if(is_int($k)) { // Un validador simple
						$valid = $this->_validatorOnColumn($model, $column, $v, NULL, $update) && $valid;
					} else { // Un validador con parametros de configuracion
						$valid = $this->_validatorOnColumn($model, $column, $k, $v, $update) && $valid;
					}
				}
				
			}
					
		}
		
		// Resultado de validacion
		return $valid;
	}
	
	/**
	 * Aplica un validador a una columna
	 * 
	 * @param ActiveRecord $model
	 * @param string $column
	 * @param string $validator
	 * @param array $params
	 * @param boolean $update
	 * @return boolean
	 */
	private function _validatorOnColumn($model, $column, $validator, $params, $update)
	{
		// Validador notNull es especial
		if($validator == 'notNull') {
			return $this->notNullValidator($model, $column, $params);
		} elseif(isset($model->$column) && $model->$column != '') {
			return $this->{"{$validator}Validator"}($model, $column, $params, $update);
		}
		
		return TRUE;
	}
	
	/**
	 * Validador para campo no nulo
	 * 
	 * @param ActiveRecord $model
	 * @param string $column columna a validar
	 * @param array $params
	 */
	public function notNullValidator($model, $column, $params = NULL) 
	{
		// TODO: Se puede optimizar en conjunto a validate, hay que revisarlo mejor
		if(!isset($model->$column) || Validate::isNull($model->$column)) {
			if($params && isset($params['message'])) {
				Flash::error($params['message']);
			} else {
				Flash::error("El campo $column no debe ser Nulo");
			}
			
			return FALSE;
		}
				
		return TRUE;	
	}
	
	/**
	 * Validador para campo con valor unico
	 * 
	 * @param ActiveRecord $model
	 * @param string $column columna a validar
	 * @param array $params
	 * @param boolean $update
	 * @return boolean
	 */
	public function uniqueValidator($model, $column, $params = NULL, $update = FALSE) 
	{	
		// Condiciones
		$q = $model->get()->where("$column = :$column");			
		$values = array($column => $model->$column);
		
		// Si es para actualizar debe verificar que no sea la fila que corresponde
		// a la clave primaria
		if($update) {
			// Obtiene la metadata
			$metadata = DbAdapter::factory($model->getConnection())->describe($model->getTable(), $model->getSchema());
					
			// Obtiene la clave primaria
			$pk = $metadata->getPK();
					
			// Verifica que este definida la clave primaria
			if(!isset($model->$pk) || $model->$pk !== '') {
				throw new KumbiaException("Debe definir valor para la clave primaria $pk");
			}
					
			$q->where("NOT $pk = :$pk");
			$values[$pk] = $model->$pk;
		}
		
		if($params && isset($params['with'])) {			
			// Establece condiciones con with
			foreach($params['with'] as $k) {
				// En un indice UNIQUE si uno de los campos es NULL, entonces el indice
				// no esta completo y no se considera la restriccion
				if(!isset($model->$k) || $model->$k === '') {
					return TRUE;
				}
				
				$values[$k] = $model->$k;
				$q->where("$k = :$k");
			}
			
			$q->bind($values);
				
			// Verifica si existe
			if($model->existsOne()) {
				if(!isset($params['message'])) {
					$v = implode("', '", array_values($values));
					$c = implode("', '", array_keys($values));
					$msg = "Los valores '$v' ya existen para los campos '$c'";
				} else {
					$msg = $params['message'];
				}
					
				Flash::error($msg);
				return FALSE;
			}
		} else {						 		 
			$q->bind($values);
			
			// Verifica si existe
			if($model->existsOne()) {
				if(!isset($params['message'])) {
					$msg = "El valor '{$model->$column}' ya existe para el campo $column";
				} else {
					$msg = $params['message'];
				}
				
				Flash::error($msg);
				return FALSE;
			}
		}
		
		return TRUE;	
	}
	
	/**
	 * Validador para clave primaria
	 * 
	 * @param string $column columna a validar
	 * @param array $params
	 */
	public function primaryValidator($model, $column, $params = NULL, $update = FALSE)
	{
		
	}
	
	/**
	 * Validador para campo con valor numero entero
	 * 
	 * @param string $column columna a validar
	 * @param array $params
	 * @return boolean
	 */
	public function integerValidator($model, $column, $params = NULL) 
	{
		if(!Validate::int($model->$column)) {
			if($params && isset($params['message'])) {
				Flash::error($params['message']);
			} else {
				Flash::error("El campo $column debe ser un número entero");
			}
			
			return FALSE;
		}
				
		return TRUE;	
	}
}
