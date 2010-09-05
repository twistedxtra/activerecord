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
		// Obtiene los validadores
		$validators = $model->validators();
		
		// Si no hay validadores definidos
		if(!$validators) {
			return TRUE;
		}
				
		// Columnas con valor por defectos
		$default = array();
		
		// Verifica si existe columnas con valor por defectos
		if(isset($validators['default'])) {
			
			// Itera en cada definicion de validacion
			foreach($validators['default'] as $v) {
				// Verifica las condiciones para cuando la columna es con valor por defecto
				$default[$v] = $this->defaultValidator($model, $v);
			}
			
			// Aprovecha y libera memoria :)
			unset($validators['default']);
			
		}
		
		// Por defecto es valido
		$valid = TRUE;
		
		// Verifica si existe columnas no nulas
		if(isset($validators['notNull'])) {
			
			// Itera en cada definicion de validacion
			foreach($validators['notNull'] as $v) {
				// Si es una columna sin configuracion especial
				if(is_string($v)) {
					// Si es con valor por defecto entonces salta la validacion
					if(isset($default[$v]) && $default[$v]) {
						continue;
					}
					
					$column = $v;					
					$params = NULL;
				} else {
					// Si es con valor por defecto entonces salta la validacion
					if(isset($default[$v[0]]) && $default[$v[0]]) {
						continue;
					}
					
					$column = $v[0];
					unset($v[0]);
					$params = $v;
				}
				
				// Valida si el campo
				$valid = $this->notNullValidator($model, $column, $params) && $valid;
			}
			
			// Aprovecha y libera memoria :)
			unset($validators['notNull']);
			
		}
		
		// Realiza el resto de las validaciones a las columnas
		foreach($validators as $validator => $validations) {
			
			// Itera en cada definicion de validacion
			foreach($validations as $v) {
				
				// Si es una columna sin configuracion especial
				if(is_string($v)) {
					// Si es con valor por defecto entonces salta la validacion
					if(isset($default[$v]) && $default[$v]) {
						continue;
					}

					$column = $v;					
					$params = NULL;
				} else {
					// Si es con valor por defecto entonces salta la validacion
					if(is_string($v[0]) && isset($default[$v[0]]) && $default[$v[0]]) {
						continue;
					}
					
					$column = $v[0];
					unset($v[0]);
					$params = $v;
				}
								
				if(is_array($column) || (isset($model->$column) && $model->$column != '')) {
					$valid = $this->{"{$validator}Validator"}($model, $column, $params, $update) && $valid;
				}
				
			}
			
		}
		
		// Resultado de validacion
		return $valid;
	}
		
	/**
	 * Validator para columnas con valores autogenerados
	 * 
	 * @param ActiveRecord $model
	 * @param string $column columna a validar
	 * @return boolean
	 */
	public function defaultValidator($model, $column)
	{
		// Se ha indicado el campo y no se considera nulo, por lo tanto no se tomara por defecto
		if(isset($model->$column) && $model->$column != '') {
			// Se considera con valor por defecto cuando sea nulo
			return FALSE;
		}
		
		// Valor por defecto
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
		$q = $model->get();
		
		$values = array();
		
		// Si es para actualizar debe verificar que no sea la fila que corresponde
		// a la clave primaria
		if($update) {	
			// Obtiene la clave primaria
			$pk = $model->metadata()->getPK();
			
			if(is_array($pk)) {
				// Itera en cada columna de la clave primaria
				$conditions = array();
				foreach($pk as $k) {
					// Verifica que este definida la clave primaria
					if(!isset($model->$k) || $model->$k === '') {
						throw new KumbiaException("Debe definir valor para la columna $k de la clave primaria");
					}
					
					$conditions[] = "$k = :pk_$k";
					$q->bindValue("pk_$k", $model->$k);
				}
				
				$q->where('NOT (' . implode(' AND ', $conditions) . ')');
			} else {
				// Verifica que este definida la clave primaria
				if(!isset($model->$pk) || $model->$pk === '') {
					throw new KumbiaException("Debe definir valor para la clave primaria $pk");
				}
						
				$q->where("NOT $pk = :pk_$pk");
				$q->bindValue("pk_$pk", $model->$pk);
			}
		}
		
		if(is_array($column)) {	
			// Establece condiciones con with
			foreach($column as $k) {
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
			$values[$column] = $model->$column;
			
			$q->where("$column = :$column")->bind($values);
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
		// Condiciones
		$q = $model->get();
		
		if(is_array($column)) {	
			$values = array();
			
			// Establece condiciones
			foreach($column as $k) {
				// En un indice UNIQUE si uno de los campos es NULL, entonces el indice
				// no esta completo y no se considera la restriccion
				if(!isset($model->$k) || $model->$k === '') {
					return TRUE;
				}
				
				$values[$k] = $model->$k;
				$q->where("$k = :$k");
			}
			
			// Si es para actualizar debe verificar que no sea la fila que corresponde
			// a la clave primaria
			if($update) {	
				$conditions = array();
				foreach($column as $k) {
					$conditions[] = "$k = :pk_$k";
					$q->bindValue("pk_$k", $model->$k);
				}
				
				$q->where('NOT (' . implode(' AND ', $conditions) . ')');
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
			// Si es para actualizar debe verificar que no sea la fila que corresponde
			// a la clave primaria
			if($update) {	
				$q->where("NOT $column = :pk_$column");
				$q->bindValue("pk_$column", $model->$column);
			}
			
			$q->where("$column = :$column")->bindValue($column, $model->$column);
			
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
	
	/**
	 * Validador para longitud de una cadena en un rango determinado
	 * 
	 * @param string $column columna a validar
	 * @param array $params
	 * @return boolean
	 */
	public function lengthBetween($model, $column, $params)
	{
		if(!Validate::between($model->$column, $params['min'], $params['max'])) {
			if(isset($params['message'])) {
				Flash::error($params['message']);
			} else {
				Flash::error("El campo $column debe tener una cantidad de caracteres comprendida entre $min y $max");
			}
			
			return FALSE;
		}
				
		return TRUE;	
	}
	
	/**
	 * Validador para longitud minima de una cadena
	 * 
	 * @param string $column columna a validar
	 * @param array $params
	 * @return boolean
	 */
	public function minLengthValidator($model, $column, $params)
	{
		if(strlen($model->$column) < $params['min']) {
			if(isset($params['message'])) {
				Flash::error($params['message']);
			} else {
				Flash::error("El campo $column debe tener una cantidad de caracteres minima de {$params['min']}");
			}
			
			return FALSE;
		}
				
		return TRUE;	
	}
	
	/**
	 * Validador para longitud maxima de una cadena
	 * 
	 * @param string $column columna a validar
	 * @param array $params
	 * @return boolean
	 */
	public function maxLengthValidator($model, $column, $params)
	{
		if(strlen($model->$column) > $params['max']) {
			if(isset($params['message'])) {
				Flash::error($params['message']);
			} else {
				Flash::error("El campo $column debe tener una cantidad de caracteres maxima de {$params['max']}");
			}
			
			return FALSE;
		}
				
		return TRUE;	
	}
}
