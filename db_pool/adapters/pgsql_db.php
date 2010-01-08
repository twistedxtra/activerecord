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
 * Clase para consultas SQL para PostgreSQL
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class PgsqlDb extends DbAdapter
{
    /**
     * Obtiene la metadata de una Tabla
     * 
     * @param string $schema
     * @param string $table
     * @return Rows
     * 
     */
    public function describe($table, $schema=null)
    {
        $sql = "SELECT c.column_name as name,
                CASE
                WHEN ct.constraint_type='PRIMARY KEY' THEN 'PRI'
                WHEN ct.constraint_type='UNIQUE' THEN 'UNI'
                WHEN ct.constraint_type='FOREIGN KEY' THEN 'FK'
                WHEN ct.constraint_type='CHECK' THEN 'CHK'
                ELSE '' END AS Index,
                c.column_default as Default, 
                c.is_nullable as Null, 
                c.udt_name as Type,
                CASE 
                WHEN c.character_maximum_length is null THEN (c.numeric_precision) ELSE c.character_maximum_length END as length
                FROM information_schema.columns c
                LEFT JOIN information_schema.constraint_column_usage cu ON
                cu.table_catalog = c.table_catalog AND cu.table_schema = c.table_schema AND cu.table_name = c.table_name
                AND cu.column_name = c.column_name
                LEFT JOIN information_schema.table_constraints ct ON
                ct.constraint_name = cu.constraint_name
                WHERE c.table_catalog = :database AND c.table_schema = :schema AND c.table_name = :table
                ORDER BY c.ordinal_position";
        try {
            $prepare = $this->pdo()->prepare($sql);
            //ejecutando la consulta preparada
            $results = $prepare->execute(array('database'=>'test', 'schema'=>'public', 'table'=>'prueba'));
            if ($results) {
                require_once CORE_PATH . 'libs/ActiveRecord/db_pool/rows.php';
                $row = new Rows();
                while ($field = $prepare->fetchObject()) {
                    //Nombre del Campo
                    $column = $row->column($field->name);
                    $column->setAlias($field->name);
                    //valor por defecto
                    if (! is_null($field->default)) {
                        if (strpos($field->default, 'nextval(') !== FALSE) {
                            $column->autoIncrement = TRUE;
                        } elseif ($field->type == 'serial' || $field->type == 'bigserial') {
                            $column->autoIncrement = TRUE;
                        } else {
                            $column->default = $field->default;
                        }
                    }
                    //puede ser null?
                    if($field->null == 'NO'){
                        $column->notNull = FALSE;
                    }
                    //Relaciones
                    if(substr($field->name, strlen($field->name) -3, 3) == '_id'){
                        $column->relation = substr($field->name, 0, -3);
                        $row->setRelation($field->name, $column->relation);
                    }
                    //tipo de dato
                    $column->type = $field->type;
                    //longitud
                    $column->length = $field->length;
                    //indices
                    switch ($field->index){
                        case 'PRI':
                            $row->setPK($field->name);
                            $column->PK = TRUE;
                            break;
                        case 'FK':
                            $row->setFK($field->name);
                            $column->FK = TRUE;
                            break;
                        case 'UNI':
                            $column->unique = TRUE;
                            break;
                    }
                }
            }
        } catch (PDOException $e) {
            throw new KumbiaException($e->getMessage());
        }
        return $row;
    }
}
