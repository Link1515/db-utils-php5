# db-utils-php5

For users working with PHP 5.x environments, this package provides a convenient solution. It provides encapsulated utilities for database operation.

## Features

- Easy to use
- Compatible with PHP 5.x

## Installation

```bash
composer require link1515/db-utils-php5
```

## Usage

### DB

The DB class will help you manage the database connection. You can connect to multiple databases and switch between them.

```php
use Link1515\DbUtilsPhp5\DB;

// Conect to the database. The current connection is used as the default.
DB::connect(string $id, string $drive, string $host, string $database, string $user, string $password): void;

// Use the database with the specified id.
DB::useConnection(string $id): void;

// Get the PDO with the specified id. If no id is passed, the default connection PDO will be returned.
DB::PDO(?string $id): PDO;
```

### BaseORM

The BaseORM class has some common methods for creating, querying, updating and deleting. In simple applications, stataic methods can be called directly for specified table operation.

```php
// options
// - page (int)
// - perPage (int)
// - orderBy (string|array)
// default value
// $options = [
//   'page' => 1,
//   'perPage' => 20,
//   'orderBy' => 'id ASC'
// ]
BaseORM::getAllForTable(string $tableName, ?array $columns = null, ?array $options): array;

BaseORM::getCountForTable(string $tableName): int;

BaseORM::getByIdForTable(string $tableName, int|string $id, ?array $columns = null): array|null;

BaseORM::createForTable(string $tableName, array $data): bool;

BaseORM::updateByIdForTable(string $tableName, int|string $id, array $data): bool;

BaseORM::deleteByIdForTable(string $tableName, int|string $id): bool;
```

#### Example:

Single connection:

```php
DB::connect('conn', 'mysql', 'localhost:3306', 'shop', 'root', 'root');

BaseORM::updateByIdForTable('orders', 4, ['price' => 300]);
```

Multiple connection:

```php
DB::connect('conn_1', 'mysql', 'localhost:3306', 'shop', 'root', 'root');
DB::connect('conn_2', 'mysql', 'localhost:3306', 'app', 'root', 'root');

DB::useConnection('conn_1');
BaseORM::createForTable('orders', ['user_id' => 1, 'price' => 100]);

DB::useConnection('conn_2');
BaseORM::getAllForTable('user');
```

BaseORM build-in methods:

```php
DB::connect('conn', 'mysql', 'localhost:3306', 'bookstore', 'root', 'root');

// create
BaseORM::createForTable('users', [
  'name' => 'Lynk',
  'phone' => '0922333444',
  'created_at' => date('Y-m-d H:i:s'),
  'updated_at' => date('Y-m-d H:i:s'),
]);

// query all
BaseORM::getAllForTable('users');
// query all and specified field
BaseORM::getAllForTable('users', ['name', 'phone']);
// query all and specified field with alias
BaseORM::getAllForTable('users', ['name' => 'username', 'phone' => 'cellphone']);
// query all and set pagination
BaseORM::getAllForTable('users', null, [
  'page' => 2,
  'perPage' => 30
]);
// query all and order by
BaseORM::getAllForTable('users', null, [
  'orderBy' => ['department_id DESC', 'id ASC'],
]);

BaseORM::getCountForTable('users');

// query by id
BaseORM::getByIdForTable('users', 1);
// query by id and specified field
BaseORM::getByIdForTable('users', 1, ['name', 'created_at']);
// query by id and specified field with alias
BaseORM::getByIdForTable('users', 1, ['name', 'created_at' => 'createdAt']);

// update by id
BaseORM::updateByIdForTable('users', 1, [
  'phone' => '0933555999',
  'updated_at' => date('Y-m-d H:i:s')
]);

// delete by id
BaseORM::deleteByIdForTable('users', 1);
```

### Customized ORM

For more complex applications, you can create your own ORM class to inherit BasicORM. Complete complex logic by writing sql statement.

In this case, you need to get the instance by getInstance() method and operate on it. The following methods are built into the instance:

```php
getAll(?array $columns = null, ?array $options): array;

getCount(): int;

getById(int|string $id, ?int $columns = null): array|null;

create(array $data): bool;

updateById(int|string $id, array $data): bool;

deleteById(int|string $id): bool;
```

##### Example

```php
use Link1515\DbUtilsPhp5\ORM\BaseORM;
use Link1515\DbUtilsPhp5\DB;

class OrderORM extends BaseORM
{
  // Required. Automatially use the connection with the specified id. Use $this->getPDO() to get connection.
  protected $connectionId = 'conn_1';
  // Required. Automatially pass the tableName pararmeter to build-in methods (getAll(), getById(), create(), deleteById()).
  protected $tableName = 'orders';
  // Optional. Automatially pass the perPage parameter to getAll() method. The default value is 20.
  protected $perPage = 20;

  // your own method
  public function getByIdWithUserInfo($id) {
    $query =
      'SELECT * FROM ' . $this->tableName . '
      LEFT JOIN users ON users.id = orders.user_id
      WHERE orders.id = :id
    ';
    // use getPDO method to make sure we are using correct connection
    $stmt = $this->getPDO()->prepare($query);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
  }
}
```

```php
$orderORM = OrderORM::getInstance();

// build-in method
$orderORM->getById(1);

// customized method
$orderORM->getByIdWithUserInfo(1);
```
