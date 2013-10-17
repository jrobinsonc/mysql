# DB_MySQL

MySQLi Wrapper for PHP.    
https://github.com/jrobinsonc/DB_MySQL

## How to use (incomplete)

First, connect to a database:

```php
require 'libs/db_mysql.php';

$db = new DB_MySQL('host', 'user', 'pass', 'database');
```

Next, prepare your data, and call the necessary methods.

### Generic query method

```php
$query = $db->query('SELECT ...');
```

### A SELECT statement

```php
$query = $db->query('SELECT first_name FROM people');

if (count($query) > 0)
{
    echo 'Num Rows: ', count($query), '<br>';

    foreach ($query as $row) 
    {
        echo $row['first_name'], '<br>';
    }
}
```

### An INSERT statement

```php
$inserted_id = $db->insert('people', array(
    'name' => "John Doe",
    'age' => 20,
));
```

### An UPDATE statement

```php
$updated_rows = $db->update('test', array(
    'age' => 22,
), array(
    'id' => 58
));

// Or:
$updated_rows = $db->update('test', array(
    'age' => 22,
), 'id=58');

// Or (if the primary key name is "id"):
$updated_rows = $db->update('test', array(
    'age' => 22,
), 58);
```

### A DELETE statement

```php
$deleted_rows = $db->delete('test', array(
    'id' => 22
));

// Or:
$deleted_rows = $db->delete('test', 'id=22');

// Or (if the primary key name is "id"):
$deleted_rows = $db->delete('test', 22);
```

## License

Licensed under the [MIT licence][1].

[1]: https://raw.github.com/jrobinsonc/DB_MySQL/master/LICENSE