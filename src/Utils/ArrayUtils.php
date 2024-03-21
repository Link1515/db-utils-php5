<?php

namespace Link1515\DbUtilsPhp5\Utils;

class ArrayUtils
{
  /**
   * @param array $arr
   * @param callback $callback
   * @return mixed
   */
  public static function joinWithComma(array $arr, callable $callback)
  {
    return implode(', ', array_map($callback, $arr));
  }
}