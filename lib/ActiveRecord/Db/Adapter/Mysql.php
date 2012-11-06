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
 * Clase base para los adaptadores de Base de Datos
 *
 * @category   Kumbia
 * @package    ActiveRecord
 * @subpackage Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace ActiveRecord\Db\Adapter;

use ActiveRecord\Db\Column;
use ActiveRecord\Db\Adapter\Adapter;

/**
 * \ActiveRecord\Adapter\Adapter\Mysql
 *
 * Adaptador para conectarse a bases de datos MySQL
 */
class Mysql extends Adapter
{

    /**
     * Obtiene los datos de la tabla
     *
     * @param string $table
     * @param string $schema
     * @return array
     */
    public function describe($table, $schema = null)
    {
        try {
            $results = $this->pdo->query("DESCRIBE $table");

            if ($results) {
                $describe = array();
                while ($field = $results->fetchObject()) {

                    //Nombre del Campo
                    $column = new Column();

                    //alias
                    $column->alias = ucwords(strtr($field->Field, '_-', '  '));

                    // autoincremental
                    if ($column->Extra === 'auto_increment') {
                        $column->autoIncrement = TRUE;
                    }

                    // valor por defecto
                    $column->default = $field->Default;

                    //puede ser null?
                    if ($field->Null == 'NO') {
                        $column->notNull = TRUE;
                    }

                    //tipo de dato y longitud
                    if (preg_match('/^(\w+)\((\w+)\)$/', $field->Type, $matches)) {
                        $column->type = $matches[1];
                        $column->length = $matches[2];
                    } else {
                        $column->type = $field->Type;
                        $column->length = NULL;
                    }

                    //indices
                    switch ($field->Key) {
                        case 'PRI':
                            $column->PK = TRUE;
                            break;
                        case 'FK':
                            $column->FK = TRUE;
                            break;
                        case 'UNI':
                            $column->unique = TRUE;
                            break;
                    }
                    $describe[] = $column;
                }
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
        return $describe;
    }

}
