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
 * @category   Kumbia
 * @package    ActiveRecord
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace ActiveRecord\Db;

use \PDO;
use ActiveRecord\Config\Config;
use ActiveRecord\Config\Parameters;

/**
 * \ActiveRecord\Db\Pool
 *
 * Clase que maneja el pool de conexiones
 */
class Pool
{

	/**
	 * Singleton de conexiones a base de datos
	 *
	 * @var array
	 */
	protected static $connections = array();

	/**
	 * Realiza una conexión directa al motor de base de datos
	 * usando el driver de Kumbia
	 *
	 * @param string $connection conexion a la base de datos en databases.ini
	 * @param boolean $new nueva conexion
	 * @return \PDO
	 */
	public static function factory(Parameters $config)
	{
		//Si existe la conexion singleton
		if (isset(self::$connections[$config->getId()])) {
			return self::$connections[$config->getId()];
		}


	}

}
