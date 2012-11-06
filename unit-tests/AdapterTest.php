<?php

require_once 'autoload.php';

use \ActiveRecord\Adapter\Mysql;
use \ActiveRecord\Config\Config;
use \ActiveRecord\Config\Parameters;

class AdapterTest extends PHPUnit_Framework_TestCase
{

	protected function _getMysqlParameters()
	{
		return new Parameters('mysql', array(
			"type"     => "mysql",
			'username' => "root",
			"password" => "hea101",
			"name"     => "kumbia_test",
			"charset"  => "utf8"
		));
	}

	public function testAdapterMysql()
	{
		$mysqlAdapter = new \ActiveRecord\Db\Adapter\Mysql($this->_getMysqlParameters());
		$describe = $mysqlAdapter->describe('personas');

		$exceptedDescribe = ActiveRecord\Metadata\Metadata::__set_state(array(
			'_attributesList' => array(
				0 => 'cedula',
				1 => 'tipo_documento_id',
				2 => 'nombres',
				3 => 'telefono',
				4 => 'direccion',
				5 => 'email',
				6 => 'fecha_nacimiento',
				7 => 'ciudad_id',
				8 => 'creado_at',
				9 => 'cupo',
				10 => 'estado',
			),
			'_PK' => 'cedula',
			'_FK' => NULL,
			'_attributes' => array(
				'cedula' => ActiveRecord\Metadata\Attribute::__set_state(array(
					'alias' => 'Cedula',
					'type' => 'char',
					'default' => NULL,
					'length' => '15',
					'notNull' => true,
					'PK' => true,
					'FK' => false,
					'unique' => false,
					'autoIncrement' => false,
					'format' => NULL,
				)),
				'tipo_documento_id' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Tipo Documento Id',
				    'type' => 'int(3) unsigned',
				    'default' => NULL,
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'nombres' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Nombres',
				    'type' => 'varchar',
				    'default' => '',
				    'length' => '100',
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'telefono' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Telefono',
				    'type' => 'varchar',
				    'default' => NULL,
				    'length' => '20',
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'direccion' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Direccion',
				    'type' => 'varchar',
				    'default' => NULL,
				    'length' => '100',
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'email' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Email',
				    'type' => 'varchar',
				    'default' => NULL,
				    'length' => '50',
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'fecha_nacimiento' => ActiveRecord\Metadata\Attribute::__set_state(array(
				   	'alias' => 'Fecha Nacimiento',
				    'type' => 'date',
				    'default' => '1970-01-01',
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'ciudad_id' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Ciudad Id',
				    'type' => 'int(10) unsigned',
				    'default' => '0',
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'creado_at' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Creado At',
				    'type' => 'date',
				    'default' => NULL,
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'cupo' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Cupo',
				    'type' => 'decimal(16,2)',
				    'default' => NULL,
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
				'estado' => ActiveRecord\Metadata\Attribute::__set_state(array(
				    'alias' => 'Estado',
				    'type' => 'enum(\'A\',\'I\',\'X\')',
				    'default' => NULL,
				    'length' => NULL,
				    'notNull' => true,
				    'PK' => false,
				    'FK' => false,
				    'unique' => false,
				    'autoIncrement' => false,
				    'format' => NULL,
				)),
			),
		));

	}

}
