<?php

namespace Link1515\DbUtilsPhp5;

use PDO;

class DB
{
  /**
   * @property array $pdoOptions
   */
  private static $pdoOptions = [];


  /**
   * @property array $pdoCharset
   */
  private static $pdoCharset = 'utf8mb4';

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
    if (isset(self::$pdoList[$id])) {
      throw new \RuntimeException('The id "' . $id . '" is already in use.');
    }

    $defaultOptions = [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    $options = array_merge(self::$pdoOptions, $defaultOptions);

    $pdo = new PDO(
      $driver . ':host=' . $host . ';dbname=' . $database . ';charset=' . self::$pdoCharset,
      $user,
      $passwd,
      $options
    );

    self::$pdoList[$id] = $pdo;
    self::$currentPdo = $pdo;
  }

  /**
   * @param array $options
   */
  public static function setPDOOptions($options)
  {
    if (is_array($options)) {
      self::$pdoOptions = $options;
    }
  }

  /**
   * @param string $charset
   */
  public static function setPDOCharset($charset)
  {
    self::$pdoCharset = $charset;
  }

  /**
   * Use a PDO with a specific id
   * 
   * @param string $id
   */
  public static function useConnection($id)
  {
    if (!isset(self::$pdoList[$id])) {
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
    if (isset($id)) {
      self::useConnection($id);
    }

    return self::$currentPdo;
  }
}