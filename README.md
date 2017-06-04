# DB_MySQL

MySQLi Wrapper for PHP.    
https://github.com/jrobinsonc/db-mysql

## Requirements

1. MySQL 5.0.7 or newer.
2. PHP 5.3.0 or newer.

## Installation

Install the latest version with:

```
$ composer require jrdev/db-mysql
```

## How to use

First, connect to a database:

```php
$db = new \jrdev\DB_MySQL('host', 'user', 'pass', 'database');
```

Next, prepare your data, and call the necessary methods.

### Generic query method

This method accepts only one param, the SQL to execute.   
And returns a DB_MySQL_Result object.

```php
$query = $db->query('SELECT ...');
```

### A SELECT statement

This method accepts:

1. `table_name`: The name of the table.
2. `fields`: *(Optional)* The fields you want to obtain in the result. Accepts array or string
3. `where`: *(Optional)* The where. Accepts array, string or intenger
4. `order_by` *(Optional)* The order by.
5. `limit` *(Optional)* The limit.

Returns a DB_MySQL_Result object.

```php
$query = $db->select('table_name', 'field1, field2');

if ($query)
{
    echo 'Num Rows: ', $query->num_rows, '<br>';

    foreach ($query as $row) 
    {
        echo $row['first_name'], '<br>';
    }
}
else
{
    echo $db->error();
}

// The $where (third param) accepts array, string or integer:
$query = $db->select('table_name', 'field1', array('name' => 'Pepe')); // With array.
$query = $db->select('table_name', 'field1', 'name = "Pepe"'); // With string.
$query = $db->select('table_name', 'field1', 1); // With integer. In this case, the resulting sql for the "WHERE" is "id = 1".
```

### An INSERT statement

This method accepts:

1. `table_name`: The name of the table.
2. `fields`: The fields you want to insert.

Returns the ID of the inserted row, or FALSE on error.

```php
$inserted_id = $db->insert('table_name', array(
    'field1' => "Value 1",
    'field2' => 2,
));
```

### An UPDATE statement

This method accepts:

1. `table_name`: The name of the table.
2. `fields`: The fields to update.
3. `where`: The where. Accepts array, string or intenger.
5. `limit` *(Optional)* The limit of rows to update.

Returns the number of affected rows, or FALSE on error.

```php
// NOTE: The $where (third param) like the select method accepts array, string or integer.

$post = array(
    'field1' => 'Value',
);

$updated_rows = $db->update('table_name', $post, array('id' => 58); // With array.
$updated_rows = $db->update('table_name', $post, 'id=58'); // With string.
$updated_rows = $db->update('table_name', $post, 58); // With integer.
```

### A DELETE statement

This method accepts:

1. `table_name`: The name of the table.
3. `where`: The where. Accepts array, string or intenger.
5. `limit` *(Optional)* The limit of rows to delete.

Returns the number of affected rows, or FALSE on error.

```php
// NOTE: The $where (second param) like the select method accepts array, string or integer.

$updated_rows = $db->delete('table_name', array('id' => 58); // With array.
$updated_rows = $db->delete('table_name', 'id=58'); // With string.
$updated_rows = $db->delete('table_name', 58); // With integer.
```

## License

Licensed under the [MIT licence](https://raw.github.com/jrobinsonc/db-mysql/master/LICENSE).
