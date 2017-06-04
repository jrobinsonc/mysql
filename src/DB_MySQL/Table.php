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

        foreach (['pk_name', 'table_name'] as $value) {
            if (isset($table_args[$value]))
                $this->$value = $table_args[$value];
        }
    }

    public function error()
    {
        return $this->cMySQL->error();
    }

    public function insert($fields)
    {
        return $this->db->insert($this->table_name, $fields);
    }

    public function update($fields, $where, $limit = null)
    {
        return $this->db->update($this->table_name, $fields, $where, $limit);
    }

    /**
     * save
     *
     * This insert or update a row in the table. If the `id` is in the fields list,
     * it will makes an update, otherwise makes an insert.
     *
     * @param array $fields The fields
     * @return int|false The ID of the row that was inserted or updated. Or False on failure.
     */
    public function save($fields)
    {
        $result = false;
        $method = isset($fields[$this->pk_name])? 'update' : 'insert';

        if ($method === 'update') {
            $updated = $this->update($fields, $fields[$this->pk_name], 1);

            if (is_int($updated) && $updated === 1) {
                $result = $fields[$this->pk_name];
            }
        }

        if ($method === 'insert') {
            $inserted = $this->insert($fields);

            if (is_int($inserted) && $inserted > 0) {
                $result = $inserted;
            }
        }

        return $result;
    }

    public function delete($where, $limit = null)
    {
        return $this->db->delete($this->table_name, $where, $limit);
    }

}
