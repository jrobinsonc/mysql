<?php
namespace jrdev\DB_MySQL;

class Result extends \MySQLi_Result
{
    public function __construct(\jrdev\DB_MySQL $db)
    {
        parent::__construct($db);
    }
}
