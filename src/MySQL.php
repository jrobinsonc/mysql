<?php
namespace jrdev;

class MySQL extends \MySQLi
{
    private $dbConfig = array();

    private $connected = false;

    private $lastError = '';

    private $tables = [];

    public function __construct(
        $host = null,
        $username = null,
        $password = null,
        $dbname = null,
        $port = null,
        $socket = null
    ) {
        $this->dbConfig = [
            'host' => $host ?: ini_get("mysqli.default_host"),
            'username' => $username ?: ini_get("mysqli.default_user"),
            'password' => $password ?: ini_get("mysqli.default_pw"),
            'dbname' => $dbname ?: '',
            'port' => $port ?: ini_get("mysqli.default_port"),
            'socket' => $socket ?: ini_get("mysqli.default_socket")
        ];
    }

    public function connect(
        $host = null,
        $username = null,
        $password = null,
        $dbname = null,
        $port = null,
        $socket = null
    ) {
        if (true === $this->connected) {
            return true;
        }

        parent::__construct($host ?: $this->dbConfig['host'],
            $username ?: $this->dbConfig['username'],
            $password ?: $this->dbConfig['password'],
            $dbname ?: $this->dbConfig['dbname'],
            $port ?: $this->dbConfig['port'],
            $socket ?: $this->dbConfig['socket']);

        if ($this->connect_error) {
            $this->error('MySQL Error: ' . $this->connect_errno . ' ' . $this->connect_error, true);

            return false;
        }

        // It's necessary for real_escape_string.
        if (false === $this->set_charset('utf8')) {
            $this->error('Error loading character set utf8: ' . $this->error);

            return false;
        }

        return $this->connected = true;
    }

    public function error($str = '', $fatal = false)
    {
        if ('' === $str) {
            return $this->lastError;
        } else {
            if (true === $fatal) {
                throw new \Exception($str);
            } else {
                $this->lastError = $str;
            }
        }
    }

    /**
     * Performs a generic query
     *
     * @param string $sql
     * @return MySQL_Result
     */
    public function query($sql)
    {
        if (false === $this->connect()) {
            return false;
        }

        if (false === $this->real_query($sql)) {
            $this->error('Error performing query ' . $sql . ' - Error message : ' . $this->error);

            return false;
        }

        return new MySQL\Result($this);
    }

    /**
     * Performs a INSERT statement
     *
     * @param string $tableName
     * @param array $fields
     * @return int Returns the ID of the inserted row, or false on error
     */
    public function insert($tableName, $fields)
    {
        $sql = "INSERT INTO `$tableName`"
        . ' (`' . implode('`,`', array_keys($fields)) . '`)'
        . ' VALUES ';

        $preparedFields = array();

        foreach ($fields as $fieldValue) {
            $preparedFields[] = $this->escape($fieldValue, true);
        }

        $sql .= '(' .implode(',', $preparedFields) . ')';

        if (false === $this->query($sql)) {
            return false;
        } else {
            return $this->insert_id;
        }
    }

    public function escape($str, $quoted = false)
    {
        $this->connect(); // It's necessary for real_escape_string.

        $result = $this->real_escape_string($str);

        return true === $quoted && preg_match('#^-?[0-9\.]+$#', $str) !== 1? "'{$result}'" : $result;
    }

    private function parseWhere($where)
    {
        if (is_array($where)) {
            $fields = array();

            foreach ($where as $fieldName => $fieldValue) {
                $fields[] = "`{$fieldName}` = " . $this->escape($fieldValue, true);
            }

            $whereSQL = implode(' AND ', $fields);

            $limit = null;
        } else {
            if (preg_match('#^-?[0-9]+$#', $where) === 1) {
                $whereSQL = "`id` = {$where}";

                $limit = 1;
            } else {
                $whereSQL = $where;

                $limit = null;
            }
        }

        return array($whereSQL, $limit);
    }

    /**
     * Performs an UPDATE statement
     *
     * @param string $tableName The name of the table
     * @param array $fields The fields to update
     * @param mixed $where Accepts array, string and integer
     * @param int $limit (Optional) The limit of rows to update
     * @return int Returns the number of affected rows, or false on error
     */
    public function update($tableName, $fields, $where, $limit = null)
    {
        $sql = "UPDATE `{$tableName}` SET ";

        $preparedFields = array();

        foreach ($fields as $fieldName => $fieldValue) {
            $preparedFields[] = "`$fieldName` = " . $this->escape($fieldValue, true);
        }

        $sql .= implode(',', $preparedFields);

        list($pWhere, $pLimit) = $this->parseWhere($where);

        $where = $pWhere;

        $sql .= " WHERE {$where}";

        if (null === $limit && null !== $pLimit) {
            $limit = $pLimit;
        }

        if (null !== $limit) {
            $sql .= " LIMIT {$limit}";
        }

        if (false === $this->query($sql)) {
            return false;
        } else {
            return $this->affected_rows;
        }
    }

    /**
     * Performs a DELETE statement
     *
     * @param string $tableName The name of the table
     * @param string $where The where
     * @param int $limit (Optional) The limit
     * @return int Returns the number of affected rows, or false on error
     */
    public function delete($tableName, $where, $limit = null)
    {
        $sql = "DELETE FROM `{$tableName}`";

        list($pWhere, $pLimit) = $this->parseWhere($where);

        $where = $pWhere;

        $sql .= " WHERE {$where}";

        if (null === $limit && null !== $pLimit) {
            $limit = $pLimit;
        }

        if (null !== $limit) {
            $sql .= " LIMIT {$limit}";
        }

        if (false === $this->query($sql)) {
            return false;
        } else {
            return $this->affected_rows;
        }
    }

    /**
     * Performs a SELECT statement
     *
     * @param string $tableName The name of the table
     * @param mixed $fields (Optional) The fields you want to obtain in the result. Accepts array or string
     * @param mixed $where (Optional) The where. Accepts array, string or intenger
     * @param string $orderBy (Optional) The order by
     * @param int $limit (Optional) The limit
     * @return MySQL_Result
     */
    public function select($tableName, $fields = null, $where = null, $orderBy = null, $limit = null)
    {
        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                $fields[$key] = "`{$value}`";
            }

            $fields = implode(',', $fields);
        } elseif (is_null($fields)) {
            $fields = '*';
        }

        $sql = "SELECT {$fields} FROM `{$tableName}`";

        if (!is_null($where)) {
            list($pWhere, $pLimit) = $this->parseWhere($where);

            $where = $pWhere;

            if (null === $limit && null !== $pLimit) {
                $limit = $pLimit;
            }

            $sql .= " WHERE {$where}";
        }

        if (!is_null($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if (!is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->query($sql);
    }

    public function table($tableName, $tableArgs = [])
    {
        if (! isset($this->tables[$tableName])) {
            $this->tables[$tableName] = new MySQL_Table($this, $tableName, $tableArgs);
        }

        return $this->tables[$tableName];
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Close the connection when instance is destroyed.
     */
    public function __destruct()
    {
        if (false === $this->connected) {
            return;
        }

        $this->close();
    }
}
