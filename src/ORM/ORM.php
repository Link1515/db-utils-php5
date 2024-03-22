<?php

namespace Link1515\DbUtilsPhp5\ORM;

require_once __DIR__ . '/../../vendor/autoload.php';

use PDO;
use Link1515\DbUtilsPhp5\DB;
use Link1515\DbUtilsPhp5\Utils\StringUtils;
use Link1515\DbUtilsPhp5\Utils\ArrayUtils;

class ORM
{
  /**
   * @property ?PDO $pdo
   */
  protected static $pdo = null;

  /**
   * @param string $tableName
   * @param ?array $columns
   * @param int $page
   * @param int $perPage
   * @return array
   */
  public static function getAllForTable($tableName, $columns = null, $page = 1, $perPage = 20)
  {
    $query =
      "SELECT " . self::sqlSelectColumns($columns) .
      " FROM " . $tableName .
      " LIMIT " . self::sqlLimitValue($page, $perPage);

    $pdo = isset (static::$pdo) ? static::$pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll();

    return $result;
  }

  /**
   * @param string $tableName
   * @param int|string $id
   * @param array $columns
   * @return array|null
   */
  public static function getByIdForTable($tableName, $id, $columns = null)
  {
    $query =
      "SELECT " . self::sqlSelectColumns($columns) .
      " FROM " . $tableName .
      " WHERE " . self::sqlAssignSingleColumn('id');

    $pdo = isset (static::$pdo) ? static::$pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);

    $result = $stmt->fetch();

    return is_array($result) ? $result : null;
  }

  /**
   * @param string $tableName
   * @param array $data
   * @return bool
   */
  public static function createForTable($tableName, $data)
  {
    $columns = array_keys($data);

    $query = "
        INSERT INTO " . $tableName . " 
          (" . self::sqlInsertColumns($columns) . ") 
        VALUES 
          (" . self::sqlInsertValues($columns) . ")";

    $pdo = isset (static::$pdo) ? static::$pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($data);

    return $result;


  }

  /**
   * @param string $tableName
   * @param int|string $id
   * @param array $data
   * @return bool
   */
  public static function updateByIdForTable($tableName, $id, $data)
  {
    $columns = array_keys($data);

    $query =
      "UPDATE " . $tableName .
      " SET " . self::sqlAssignColumns($columns) .
      " WHERE " . self::sqlAssignSingleColumn('id');

    $pdo = isset (static::$pdo) ? static::$pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute(array_merge($data, ['id' => $id]));

    return $result;
  }

  /**
   * @param string $tableName
   * @param int|string $id
   * @return bool
   */
  public static function deleteByIdForTable($tableName, $id)
  {
    $query = "DELETE FROM " . $tableName . " WHERE " . self::sqlAssignSingleColumn('id');

    $pdo = isset (static::$pdo) ? static::$pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute(['id' => $id]);

    return $result;
  }

  /**
   * @param array|null $columns
   * @return string
   */
  protected static function sqlSelectColumns($columns = null)
  {
    $getAllColumn = is_null($columns) || count($columns) === 0;

    $sqlStr = $getAllColumn ?
      '*' :
      ArrayUtils::joinWithComma($columns, function ($column) {
        return $column . " " . $column;
      });

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param array $columns
   * @return string
   */
  protected static function sqlInsertColumns($columns)
  {
    $sqlStr =
      ArrayUtils::joinWithComma($columns, function ($column) {
        return $column;
      });

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param array $columns
   * @return string
   */
  protected static function sqlInsertValues($columns)
  {
    $sqlStr =
      ArrayUtils::joinWithComma($columns, function ($column) {
        return ":$column";
      });

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param string $key
   * @return string
   */
  protected static function sqlAssignSingleColumn($key)
  {
    $sqlStr = $key . " = :$key";

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param array $columns
   * @param bool $filterId
   * @return string
   */
  protected static function sqlAssignColumns($columns, $filterId = false)
  {
    if ($filterId) {
      $columns = array_filter($columns, function ($key) {
        return $key !== "id";
      }, ARRAY_FILTER_USE_KEY);
    }

    $sqlStr =
      ArrayUtils::joinWithComma($columns, function ($column) {
        return $column . " = :$column";
      });

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param int $page
   * @param int $perPage
   * @return string
   */
  protected static function sqlLimitValue($page, $prePage)
  {
    $sqlStr = ($page - 1) * $prePage . ", " . $prePage;

    return StringUtils::spaceAround($sqlStr);
  }
}