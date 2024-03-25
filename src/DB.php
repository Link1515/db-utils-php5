<?php

namespace Link1515\DbUtilsPhp5;

use PDO;

class DB
{
  /**
   * @property array $pdoList
   */
  private static $pdoList;

  /**
   * @property PDO $currentPdo
   */
  private static $currentPdo = null;

  private function __construct()
  {
  }

  /**
   * Connect to db and set it to current PDO
   * 
   * @param string $id
   * @param string $driver
   * @param string $host
   * @param string $database
   * @param string $user
   * @param string $passwd
   */
  public static function connect($id, $driver, $host, $database, $user, $passwd)
  {
    if (isset (self::$pdoList[$id])) {
      throw new \RuntimeException('The id "' . $id . '" is already in use.');
    }

    $defaultOptions = [
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    $pdo = new PDO(
      $driver . ':host=' . $host . ';dbname=' . $database,
      $user,
      $passwd,
      $defaultOptions
    );

    self::$pdoList[$id] = $pdo;
    self::$currentPdo = $pdo;
  }

  /**
   * Use a PDO with a specific id
   * 
   * @param string $id
   */
  public static function use($id)
  {
    if (!isset (self::$pdoList[$id])) {
      throw new \RuntimeException('PDO with ID "' . $id . '" does not exist');
    }

    if (self::$currentPdo !== self::$pdoList[$id]) {
      self::$currentPdo = self::$pdoList[$id];
    }
  }

  /**
   * Returns the specified PDO if the id is passed, and the current PDO if id is not passed
   * 
   * @param ?string $id
   * @return PDO
   */
  public static function PDO($id = null)
  {
    if (isset ($id)) {
      self::use($id);
    }

    return self::$currentPdo;
  }
}