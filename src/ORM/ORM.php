<?php

namespace Link1515\DbUtilsPhp5\ORM;

use PDO;
use Link1515\DbUtilsPhp5\DB;
use Link1515\DbUtilsPhp5\Utils\StringUtils;
use Link1515\DbUtilsPhp5\Utils\ArrayUtils;

class ORM
{
  /**
   * @property ?PDO $_pdo
   */
  protected static $_pdo = null;

  /**
   * @param string $tableName
   * @return int 
   */
  public static function getCountForTable($tableName)
  {
    $query =
      'SELECT COUNT(*) total' . ' FROM ' . $tableName;

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $count = $stmt->fetch()['total'];

    return $count;
  }

  /**
   * @param string $tableName
   * @param ?array $columns
   * @param ?array $options
   * @return array
   */
  public static function getAllForTable($tableName, $columns = null, $options = [])
  {
    $page = isset($options['page']) ? $options['page'] : 1;
    $perPage = isset($options['perPage']) ? $options['perPage'] : 20;
    $orderBy = isset($options['orderBy']) ? $options['orderBy'] : 'id ASC';

    $query =
      'SELECT ' . self::sqlSelectColumns($columns) .
      ' FROM ' . $tableName .
      ' ORDER BY ' . (is_array($orderBy) ? implode(', ', $orderBy) : $orderBy) .
      ' LIMIT ' . self::sqlLimitValue($page, $perPage);

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchAll();

    $count = self::getCountForTable($tableName);

    return [
      'data' => $result,
      'page' => $page,
      'perPage' => $perPage,
      'total' => $count
    ];
  }

  /**
   * @param string $tableName
   * @param int|string $id
   * @param ?array $columns
   * @return ?array
   */
  public static function getByIdForTable($tableName, $id, $columns = null)
  {
    $query =
      'SELECT ' . self::sqlSelectColumns($columns) .
      ' FROM ' . $tableName .
      ' WHERE ' . self::sqlAssignSingleColumn('id');

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
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

    $query =
      'INSERT INTO ' . $tableName . '
          (' . self::sqlInsertColumns($columns) . ') 
        VALUES 
          (' . self::sqlInsertValues($columns) . ')';

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
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
      'UPDATE ' . $tableName .
      ' SET ' . self::sqlAssignColumns($columns, true) .
      ' WHERE ' . self::sqlAssignSingleColumn('id');

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
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
    $query =
      'DELETE FROM ' . $tableName .
      ' WHERE ' . self::sqlAssignSingleColumn('id');

    $pdo = isset(static::$_pdo) ? static::$_pdo : DB::PDO();
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute(['id' => $id]);

    return $result;
  }

  /**
   * @param ?array $columns
   * @return string
   */
  protected static function sqlSelectColumns($columns = null)
  {
    if (is_null($columns) || count($columns) === 0) {
      return ' * ';
    }

    $colKeys = array_keys($columns);
    $sqlStr =
      ArrayUtils::joinWithComma($colKeys, function ($colKey) use ($columns) {
        return is_int($colKey) ?
          $columns[$colKey] :
          $colKey . ' ' . $columns[$colKey];
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
        return ':' . $column;
      });

    return StringUtils::spaceAround($sqlStr);
  }

  /**
   * @param string $key
   * @return string
   */
  protected static function sqlAssignSingleColumn($key)
  {
    $sqlStr = $key . ' = :' . $key;

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
      $columns = array_filter($columns, function ($column) {
        return $column !== 'id';
      });
    }

    $sqlStr =
      ArrayUtils::joinWithComma($columns, function ($column) {
        return $column . ' = :' . $column;
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
    $sqlStr = ($page - 1) * $prePage . ', ' . $prePage;

    return StringUtils::spaceAround($sqlStr);
  }
}