<?php

require_once 'autoload.php';

use \ActiveRecord\Adapter\Mysql;
use \ActiveRecord\Config\Config;
use \ActiveRecord\Config\Parameters;

class AdapterTest extends PHPUnit_Framework_TestCase
{

	public function testAdapterMysql()
	{

		$mysqlAdapter = new \ActiveRecord\Db\Adapter\Mysql(new Parameters('mysql', array(
			"type"     => "mysql",
			'username' => "root",
			"password" => "hea101",
			"name"     => "kumbia_test",
			"charset"  => "utf8"
		)));

		$describe = $mysqlAdapter->describe('personas');

		$exceptedDescribe = array(
			0 => ActiveRecord\Db\Column::__set_state(array(
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
			1 => ActiveRecord\Db\Column::__set_state(array(
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
			2 => ActiveRecord\Db\Column::__set_state(array(
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
			3 => ActiveRecord\Db\Column::__set_state(array(
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
			4 => ActiveRecord\Db\Column::__set_state(array(
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
			5 => ActiveRecord\Db\Column::__set_state(array(
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
			6 => ActiveRecord\Db\Column::__set_state(array(
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
			7 => ActiveRecord\Db\Column::__set_state(array(
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
			8 => ActiveRecord\Db\Column::__set_state(array(
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
			9 => ActiveRecord\Db\Column::__set_state(array(
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
			10 => ActiveRecord\Db\Column::__set_state(array(
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
  		);

		$this->assertEquals($exceptedDescribe, $describe);
	}

}
