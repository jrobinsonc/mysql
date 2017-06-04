<?php
namespace jrdev\MySQL;

class Table
{
    private $tableName = '';

    private $pkName = 'id';

    private $cMySQL = null;

    public function __construct(\jrdev\MySQL &$cMySQL, $tableName, $tableArgs = [])
    {
        $this->cMySQL = $cMySQL;
        $this->tableName = $tableName;

        foreach (['pkName', 'tableName'] as $value) {
            if (isset($tableArgs[$value])) {
                $this->$value = $tableArgs[$value];
            }
        }
    }

    public function error()
    {
        return $this->cMySQL->error();
    }

    public function insert($fields)
    {
        return $this->cMySQL->insert($this->tableName, $fields);
    }

    public function update($fields, $where, $limit = null)
    {
        return $this->cMySQL->update($this->tableName, $fields, $where, $limit);
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
        $method = isset($fields[$this->pkName])? 'update' : 'insert';

        if ($method === 'update') {
            $updated = $this->update($fields, $fields[$this->pkName], 1);

            if (is_int($updated) && $updated === 1) {
                $result = $fields[$this->pkName];
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
        return $this->cMySQL->delete($this->tableName, $where, $limit);
    }
}
