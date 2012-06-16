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
 * Clase para consultas SQL para MySQL
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class MysqlDb extends DbAdapter
{

    /**
     * Obtiene los datos de la tabla
     *
     * @param string $table
     * @param string $schema
     * @return array
     * */
    public function describe($table, $schema=null)
    {
        try {
            $results = $this->pdo()->query("DESCRIBE $table");

            if ($results) {
                require_once '/../metadata.php';
                $metadata = new Metadata();
                while ($field = $results->fetchObject()) {
                    //Nombre del Campo
                    $attribute = $metadata->attribute($field->Field);
                    //alias
                    $attribute->alias = ucwords(strtr($field->Field, '_-', '  '));

                    // autoincremental
                    if ($field->Extra === 'auto_increment') {
                        $attribute->autoIncrement = TRUE;
                    }

                    // valor por defecto
                    $attribute->default = $field->Default;

                    //puede ser null?
                    if ($field->Null == 'NO') {
                        $attribute->notNull = TRUE;
                    }

                    //tipo de dato y longitud
                    if (preg_match('/^(\w+)\((\w+)\)$/', $field->Type, $matches)) {
                        $attribute->type = $matches[1];
                        $attribute->length = $matches[2];
                    } else {
                        $attribute->type = $field->Type;
                        $attribute->length = NULL;
                    }

                    //indices
                    switch ($field->Key) {
                        case 'PRI':
                            $metadata->setPK($field->Field);
                            $attribute->PK = TRUE;
                            break;
                        case 'FK':
                            $metadata->setFK($field->Field);
                            $attribute->FK = TRUE;
                            break;
                        case 'UNI':
                            $attribute->unique = TRUE;
                            break;
                    }
                }
            }
        } catch (PDOException $e) {
            throw new KumbiaException($e->getMessage());
        }
        return $metadata;
    }

}
