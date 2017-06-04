<?php
namespace jrdev\DB_MySQL;

class Table 
{
    private $table_name = '';

    private $pk_name = 'id';

    private $db = null;


    public function __construct(\jrdev\DB_MySQL &$db, $table_name, $table_args = [])
    {
        $this->db = $db;
        $this->table_name = $table_name;

        foreach (['pk_name', 'table_name'] as $value)
            if (isset($table_args[$value]))
                $this->$value = $table_args[$value];
    }

    public function insert($fields)
    {
        return $this->db->insert($this->table_name, $fields);
    }

    public function update($fields, $where, $limit = null)
    {
        return $this->db->update($this->table_name, $fields, $where, $limit);
    }

    public function save($fields)
    {
        if (isset($fields[$this->pk_name]))
            return $this->update($fields, $fields[$this->pk_name], 1);
        else
            return $this->insert($fields);
    }

    public function delete($where, $limit = null)
    {
        return $this->db->delete($this->table_name, $where, $limit);
    }

}
