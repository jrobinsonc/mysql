<?php

/**
 * DB_MySQL Class
 *
 * @author 		Jose Robinson <jr@joserobinson.com>
 * @link 		https://github.com/jrobinsonc/DB_MySQL
 * @copyright	Copyright (c) 2013
 * @license 	MIT License
 * @version 	0.1
 **/
class DB_MySQL extends MySQLi
{
	private $db_config = array();
	private $connected = FALSE;
	private $last_error = '';

	public function __construct() 
	{
		$this->db_config = func_get_args();

		// The application does not connect to MySQL unless necessary to make a query.
 		if (count($this->db_config) < 4)
 			$this->error('Invalid number of connection parameters', TRUE);
	}

	private function _connect()
	{
		if (TRUE === $this->connected)
			return TRUE;

		list($host, $user, $pass, $database) = $this->db_config;

		@parent::__construct($host, $user, $pass, $database);

		if ($this->connect_error)
		{
			$this->error('Error connecting to MySQL: ' . $this->connect_errno . ' ' . $this->connect_error, TRUE);

			return FALSE;
		}

		// It's necessary for real_escape_string.
		if (FALSE === $this->set_charset('utf8'))
		{
			$this->error('Error loading character set utf8: ' . $this->error);

			return FALSE;
		}

		return $this->connected = TRUE;
	}

	public function error($str = '', $fatal = FALSE)
	{
		if ('' === $str)
			return $this->last_error;
		else
			if (TRUE === $fatal)
				throw new Exception($str);
			else
				$this->last_error = $str;
	}

	public function query($sql)
	{
		if (FALSE === $this->_connect())
			return FALSE;

		if (FALSE === $this->real_query($sql))
		{
            $this->error('Error performing query ' . $sql . ' - Error message : ' . $this->error);

            return FALSE;
        }

        return new DB_MySQL_Result($this);
	}

	public function insert($table_name, $rows)
	{
		$sql = "INSERT INTO `$table_name`"
		. ' (`' . implode('`,`', array_keys($rows)) . '`)'
		. ' VALUES ';

		$fields = array();

		foreach ($rows as $field_name => $field_value)
			$fields[] = $this->escape($field_value, TRUE);

		$sql .= '(' .implode(',', $fields) . ')';


		if (FALSE === $this->query($sql))
			return FALSE;
		else
			return $this->insert_id;
	}

	public function escape($str, $quoted = FALSE)
	{
		$this->_connect(); // It's necessary for real_escape_string.

		$result = $this->real_escape_string($str);


		return TRUE === $quoted && preg_match('#^-?[0-9\.]+$#', $str) !== 1? "'{$result}'" : $result;
	}

	private function parse_where($where)
	{
		if (is_array($where))
		{
			$fields = array();

			foreach ($where as $field_name => $field_value) 
				$fields[] = "`{$field_name}` = " . $this->escape($field_value, TRUE);

			$sql = implode(' AND ', $fields);

			$limit = NULL;
		}
		else
		{
			if (preg_match('#^-?[0-9]+$#', $where) === 1)
			{
				$sql = "`id` = {$where}";

				$limit = 1;
			}
			else
			{
				$sql = $where;

				$limit = NULL;
			}
		}


		return array($sql, $limit);
	}

	public function update($table_name, $rows, $where, $limit = NULL)
	{
		$sql = "UPDATE `{$table_name}` SET ";

		$fields = array();

		foreach ($rows as $field_name => $field_value)
			$fields[] = "`$field_name` = " . $this->escape($field_value, TRUE);

		$sql .= implode(',', $fields);

		list($sql_where, $_limit) = $this->parse_where($where);

		$sql .= " WHERE {$sql_where}";

		if (NULL === $limit && NULL !== $_limit)
			$limit = $_limit;

		if (NULL !== $limit)
			$sql .= " LIMIT {$limit}";


		if (FALSE === $this->query($sql))
			return FALSE;
		else
			return $this->affected_rows;
	}

	public function delete($table_name, $where, $limit = NULL)
	{
		$sql = "DELETE FROM `{$table_name}`";

		list($sql_where, $_limit) = $this->parse_where($where);

		$sql .= " WHERE {$sql_where}";

		if (NULL === $limit && NULL !== $_limit)
			$limit = $_limit;

		if (NULL !== $limit)
			$sql .= " LIMIT {$limit}";

		$this->query($sql);


		if (FALSE === $this->query($sql))
			return FALSE;
		else
			return $this->affected_rows;
	}

	private function __clone() {}

	public function __destruct()
    {
    	if (FALSE === $this->connected)
    		return;

    	$this->close();
    }
}

class DB_MySQL_Result extends MySQLi_Result implements Countable
{
	/**
	 * Countable's implementation. Count rows in result set.
	 *
	 * @return int
	 */
    public function count()
    {
        return $this->num_rows;
    }
}