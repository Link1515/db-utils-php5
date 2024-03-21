<?php

namespace Link1515\DbUtilsPhp5\ORM;

require_once __DIR__ . '/../../vendor/autoload.php';

use Link1515\DbUtilsPhp5\DB;
use Link1515\DbUtilsPhp5\Utils\StringUtils;
use Link1515\DbUtilsPhp5\Utils\ArrayUtils;

class ORM
{
  /**
   * @param string $tableName
   * @param int $page
   * @param int $perPage
   * @return array
   */
  public static function getAllForTable($tableName, $page = 1, $perPage = 20)
  {
    $query =
      "SELECT * " .
      " FROM " . $tableName .
      " LIMIT " . self::sqlLimitValue($page, $perPage);

    $stmt = DB::PDO()->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll();

    return $result;
  }

  /**
   * @param string $tableName
   * @param int $id
   * @param array $columns
   * @return array|null
   */
  public static function getByIdForTable($tableName, $id, $columns = null)
  {
    $query =
      "SELECT " . self::sqlSelectColumns($columns) .
      " FROM " . $tableName .
      " WHERE " . self::sqlAssignSingleColumn('id');

    $stmt = DB::PDO()->prepare($query);
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

    $stmt = DB::PDO()->prepare($query);
    $result = $stmt->execute($data);

    return $result;


  }

  /**
   * @param string $tableName
   * @param int $id
   * @param array $data
   * @return bool
   */
  public static function updateByIdForTable($tableName, $id, $data)
  {
    $columns = array_keys($data);

    $query =
      "UPDATE " . $tableName .
      " SET " . self::sqlAssignColumns($columns, true) .
      " WHERE " . self::sqlAssignSingleColumn('id');

    $stmt = DB::PDO()->prepare($query);
    $result = $stmt->execute($data);

    return $result;


  }

  /**
   * @param string $tableName
   * @param int $id
   * @return bool
   */
  public static function deleteByIdForTable($tableName, $id)
  {
    $query = "DELETE FROM " . $tableName . " WHERE " . self::sqlAssignSingleColumn('id');

    $stmt = DB::PDO()->prepare($query);
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
        return StringUtils::camelToSnake($column) . " " . $column;
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
        return StringUtils::camelToSnake($column);
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
    $sqlStr = StringUtils::camelToSnake($key) . " = :$key";

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
        return StringUtils::camelToSnake($column) . " = :$column";
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