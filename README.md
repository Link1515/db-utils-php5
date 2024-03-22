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
DB::use(string $id): void;

// Get the PDO with the specified id. If no id is passed, the default connection PDO will be returned.
DB::PDO(?string $id): PDO;
```

### BaseORM

The BaseORM class has some common methods for creating, querying, updating and deleting. In simple applications, stataic methods can be called directly for specified table operation.

```php
BaseORM::createForTable(string $tableName, array $data): bool;

BaseORM::getAllForTable(string $tableName, int $page = 1, int $perPage = 20): array;

BaseORM::getByIdForTable(string $tableName, int|string $id, ?array $columns = null): array|null;

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

DB::use('conn_1');
BaseORM::createForTable('orders', ['user_id' => 1, 'price' => 100]);

DB::use('conn_2');
BaseORM::getAllForTable('user');
```

### Customized ORM

For more complex applications, you can create your own ORM class to inherit BasicORM. Complete complex logic by writing sql statement.

In this case, you need to get the instance by getInstance() method and operate on it. The following methods are built into the instance:

```php
create(array $data): bool;

getAll(int $page = 1, ?int $perPage = null): array;

getById(int|string $id, ?int $columns = null): array|null;

updateById(int|string $id, array $data): bool;

deleteById(int|string $id): bool;
```

##### Example

```php
use Link1515\DbUtilsPhp5\ORM\BaseORM;
use Link1515\DbUtilsPhp5\DB;

class OrderORM extends BaseORM
{
  // Required. Automatially use the connection with the specified id.
  protected $connectionId = 'conn_1';
  // Required. Automatially pass the tableName pararmeter to build-in methods (getAll(), getById(), create(), deleteById()).
  protected $tableName = 'orders';
  // Optional. Automatially pass the perPage parameter to getAll() method. The default value is 20.
  protected $perPage = 20;

  // your own method
  public function getByIdWithUserInfo($id) {
    $query = "
      SELECT * FROM " . $this->tableName . "
      LEFT JOIN users ON users.id = orders.user_id
      WHERE orders.id = :id
    ";
    $stmt = DB::PDO()->prepare($query);
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
