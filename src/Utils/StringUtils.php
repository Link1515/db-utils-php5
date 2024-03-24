<?php

namespace Link1515\DbUtilsPhp5\Utils;

class StringUtils
{
  /**
   * @param string $input
   * @return string
   */
  public static function camelToSnake($input)
  {
    return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $input));
  }

  /**
   * @param string $input
   * @return string
   */
  public static function spaceAround($input)
  {
    return ' ' . $input . ' ';
  }
}