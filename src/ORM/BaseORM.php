<?php

namespace Link1515\DbUtilsPhp5\ORM;

use Link1515\DbUtilsPhp5\DB;
use PDO;

abstract class BaseORM extends ORM
{
  /**
   * @property self $instance
   */
  protected static $instance = null;

  protected static $_pdo;

  /**
   * @property ?string $connectionId;
   */
  protected $connectionId = null;

  /**
   * @property string $tableName;
   */
  protected $tableName = '';

  /**
   * @property int $perPage;
   */
  protected $perPage = 20;

  private function __construct()
  {
    if (is_null($this->connectionId)) {
      throw new \RuntimeException('The "protected $connectionId" property must be set to a valid connection ID.');
    }

    if (is_null($this->tableName)) {
      throw new \RuntimeException('The "protected $tableName" property must be set to a valid table name.');
    }

    static::$_pdo = DB::PDO($this->connectionId);
  }

  /**
   * @return static
   */
  public static function getInstance()
  {
    if (is_null(static::$instance)) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * @return PDO
   */
  public function getPDO()
  {
    return static::$_pdo;
  }

  /**
   * @param ?array $columns
   * @param ?array $options
   * @return array
   */
  public function getAll($columns = null, $options)
  {
    return parent::getAllForTable($this->tableName, $columns, $options);
  }

  /**
   * @param int|string $id
   * @param ?array $columns
   * @return ?array
   */
  public function getById($id, $columns = null)
  {
    return parent::getByIdForTable($this->tableName, $id, $columns);
  }

  /**
   * @param array $data
   * @return bool
   */
  public function create($data)
  {
    return parent::createForTable($this->tableName, $data);
  }

  /**
   * @param int|string $id
   * @param array $data
   * @return bool
   */
  public function updateById($id, $data)
  {
    return parent::updateByIdForTable($this->tableName, $id, $data);
  }

  /**
   * @param int|string $id
   * @return bool
   */
  public function deleteById($id)
  {
    return parent::deleteByIdForTable($this->tableName, $id);
  }
}