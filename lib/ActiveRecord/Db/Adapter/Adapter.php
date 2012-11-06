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
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

namespace ActiveRecord\Db\Adapter;

use PDO;
use ActiveRecord\Config\Config;
use ActiveRecord\Query\DbQuery;
use ActiveRecord\Config\Parameters;
use ActiveRecord\Exception;

/**
 * \ActiveRecord\Db\Adapter\Adapter
 *
 * Clase base para adaptadores
 */
abstract class Adapter
{

    /**
     * Nombre de conexión
     *
     * @var string
     */
    protected $config;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Constructor
     *
     * @param \ActiveRecord\Config\Parameters $config
     */
    public function __construct(Parameters $config)
    {

        try {

            $pdo = new PDO(
                $config->getType() . ':host=' . $config->getHost() . ';dbname=' . $config->getDbName(),
                $config->getUsername(),
                $config->getPassword(), $config->getAttributes());

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }

        if ($charset = $config->getCharset()) {
            $pdo->exec("SET CHARACTER SET $charset");
        }

        $this->config = $config;
        $this->pdo = $pdo;
    }

    /**
     * Genera la descripción de una tabla
     *
     * @param string $table tabla
     * @param string $schema schema
     * @return array
     */
    abstract public function describe($table, $schema = NULL);

    /**
     * Genera la consulta sql concreta
     *
     * @param DbQuery $dbQuery
     * @return string
     * @throws Exception
     */
    public function query($dbQuery)
    {
        $sqlArray = $dbQuery->getSqlArray();

        // Verifica si se indico una table
        if (!isset($sqlArray['table'])) {
            throw new \Exception("Debe indicar una tabla para efectuar la consulta");
        }

        if (isset($sqlArray['command'])) {
            return $this->{"_{$sqlArray['command']}"}($sqlArray);
        }

        throw new \Exception("Debe indicar un comando de consulta SQL");
    }

    /**
     * Genera una consulta sql SELECT
     *
     * @param array $sqlArray
     * @return string
     */
    protected function _select($sqlArray)
    {
        // Verifica si esta definido el esquema
        if (isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }

        $select = 'SELECT';
        if (isset($sqlArray['distinct']) && $sqlArray['distinct']) {
            $select .= ' DISTINCT';
        }

        // Columnas en consulta
        $columns = isset($sqlArray['columns']) ? $sqlArray['columns'] : '*';

        return $this->_joinClausules($sqlArray, "$select $columns FROM $source");
    }

    /**
     * Genera una consulta sql INSERT
     *
     * @param array $sqlArray
     * @return string
     */
    protected function _insert($sqlArray)
    {
        // Obtiene las columns
        $columns = implode(', ', array_keys($sqlArray['data']));
        // Parámetros enlazados para SQL PS
        $values = implode(', ', array_keys($sqlArray['bind']));

        // Verifica si esta definido el eschema
        if (isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }
        return "INSERT INTO $source ($columns) VALUES ($values)";
    }

    /**
     * Genera una consulta sql INSERT
     *
     * @param array $sqlArray
     * @return string
     */
    protected function _update($sqlArray)
    {
        // Construye la pareja clave, valor para SQL PS
        $values = array();
        foreach (array_keys($sqlArray['data']) as $k) {
            $values[] = "$k = :$k";
        }
        $values = implode(', ', $values);

        // Verifica si esta definido el eschema
        if (isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }

        return $this->_joinClausules($sqlArray, "UPDATE $source SET $values");
    }

    /**
     * Genera una consulta sql DELETE
     *
     * @param array $sqlArray
     * @return string
     */
    protected function _delete($sqlArray)
    {
        // verifica si esta definido el eschema
        if (isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }

        return $this->_joinClausules($sqlArray, "DELETE FROM $source");
    }

    /**
     * Une con las clausulas adicionales de consulta
     *
     * @param array $sqlArray array de condiciones
     * @param string $sql consulta sql donde se unira las clausulas
     * @return string
     */
    protected function _joinClausules($sqlArray, $sql)
    {
        // Para inner join
        if (isset($sqlArray['join'])) {
            foreach ($sqlArray['join'] as $join) {
                $sql .= " INNER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // Para left outer join
        if (isset($sqlArray['leftJoin'])) {
            foreach ($sqlArray['leftJoin'] as $join) {
                $sql .= " LEFT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // Para right outer join
        if (isset($sqlArray['rightJoin'])) {
            foreach ($sqlArray['rightJoin'] as $join) {
                $sql .= " RIGHT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // Para full join
        if (isset($sqlArray['fullJoin'])) {
            foreach ($sqlArray['fullJoin'] as $join) {
                $sql .= " FULL JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        if (isset($sqlArray['where'])) {
            if (is_array($sqlArray['where'])) {
                $where = NULL;
                $where = ' ' . implode(' ', $sqlArray['where']);
            } else {
                $where = $sqlArray['where'];
            }
            $sql .= " WHERE $where";
        }

        if (isset($sqlArray['group'])) {
            $sql .= " GROUP BY {$sqlArray['group']}";
        }

        if (isset($sqlArray['having'])) {
            $sql .= " HAVING {$sqlArray['having']}";
        }

        if (isset($sqlArray['order'])) {
            $sql .= " ORDER BY {$sqlArray['order']}";
        }

        if (isset($sqlArray['limit'])) {
            $sql .= " LIMIT {$sqlArray['limit']}";
        }

        if (isset($sqlArray['offset'])) {
            $sql .= " OFFSET {$sqlArray['offset']}";
        }

        return $sql;
    }

    /**
     * Genera el objeto PDO para la conexion
     *
     * @return PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Prepara la consulta SQL
     *
     * @param string $sql
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        // PDOStatement
        return $this->pdo->prepare($sql);
    }

    /**
     * Prepara la consulta SQL asociada al objeto dbQuery
     *
     * @param DbQuery objeto de consulta
     * @return PDOStatement
     */
    public function prepareDbQuery($dbQuery)
    {
        // Prepara el dbQuery
        return $this->pdo->prepare($this->query($dbQuery));
    }

    /**
     *
     * @return \ActiveRecord\Query\DbQuery
     */
    public function createQuery()
    {
        return new DbQuery();
    }

    /**
     *
     * @param DbQuery $query
     * @return \PDOStatement
     */
    public function execute(DbQuery $query)
    {
        $statement = $this->pdo->query($this->query($query));

        $statement->execute($query->getBind());

        return $statement;
    }

}
