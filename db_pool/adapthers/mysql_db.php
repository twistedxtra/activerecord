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

class MysqlDb extends DbAdapther
{
    /**
     * Obtiene los datos de la tabla
     *
     * @param string $table
     * @param string $schema
     * @return array
     **/
    public function describe($table, $schema=null)
    {
        if($schema) {
            $source = "$schema.$table";
        } else {
            $source = $table;
        }
        
        $tableMetaData = TableMetaData::getInstance($this->_connection, $schema, $table);
        if(!$tableMetaData->isLoaded()) {
            $stmt = $this->pdo()->query("DESCRIBE $source");
            
            $metadata = array();
            foreach($stmt as $row) {
                // aqui falta el codigo para ajustarlo que funcione con el mysql
            }
            
            $tableMetaData->setMetadata($metadata);
        }
        
        return $tableMetaData;
    }
}
