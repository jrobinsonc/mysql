<?php
namespace jrdev\MySQL;

class Result extends \MySQLi_Result
{
    public function __construct(\jrdev\MySQL $cMySQL)
    {
        parent::__construct($cMySQL);
    }
}
