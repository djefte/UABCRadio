<?php

namespace Drupal\visitors\Service;

/**
 * Fills missing sequence in the view results.
 */
class SequenceService {

  /**
   * Fills the view results missing sequence.
   */
  public static function fill($result) {
    if (empty($result)) {
      return $result;
    }

    $row = $result[0];
    foreach (get_object_vars($row) as $key => $value) {
      switch ($key) {

        case 'visitors_hour':
        case 'visitors_visitor_localtime':
          $result = self::hours($result, $key);
          break;

        case 'visitors_day_of_month':
          $result = self::dayOfMonth($result, $key);
          break;

        case 'visitors_day_of_week':
          $result = self::daysOfWeek($result, $key);
          break;

        case 'visitors_day':
          $result = self::days($result, $key);
          break;

        case 'visitors_week':
          $result = self::weeks($result, $key);
          break;

        case 'visitors_month':
          $result = self::months($result, $key);
          break;

        default:
          break;
      }
    }

    return $result;
  }

  /**
   * Fills the missing hours in the result.
   */
  protected static function hours($result, $field) {
    return self::integer($result, $field, 24);
  }

  /**
   * Fills the missing date in the result.
   */
  protected static function date($result, $field, $max) {

    $rows = [];
    $exclude_fields = [
      'index',
      '_entity',
      '_relationship_entities',
      $field,
    ];
    $index = 0;
    $expected = $result[0]->{$field};
    $year = (int) substr($expected, 0, 4);
    $month = (int) substr($expected, 4, 2);
    foreach ($result as $i => $row) {
      while ($expected < $row->{$field}) {
        $clone = clone $row;
        $clone->{$field} = $expected;
        $clone->index = $index;

        foreach (get_object_vars($clone) as $key => $value) {
          if (in_array($key, $exclude_fields)) {
            continue;
          }
          $clone->{$key} = '0';
        }

        $rows[] = $clone;
        $index += 1;
        $month += 1;
        if ($month > $max) {
          $month = 1;
          $year += 1;
        }
        $expected = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

      }
      $row->index = $index;

      $index += 1;
      $month += 1;
      if ($month > $max) {
        $month = 1;
        $year += 1;
      }
      $expected = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * Fills the missing days in the result.
   */
  protected static function days($result, $field) {
    $format = 'Y-m-d';
    $increment = '+1 day';
    $rows = [];
    $exclude_fields = [
      'index',
      '_entity',
      '_relationship_entities',
      $field,
    ];
    $index = 0;
    $expected = $result[0]->{$field};
    foreach ($result as $i => $row) {
      while ($expected < $row->{$field}) {
        $clone = clone $row;
        $clone->{$field} = $expected;
        $clone->index = $index;

        foreach (get_object_vars($clone) as $key => $value) {
          if (in_array($key, $exclude_fields)) {
            continue;
          }
          $clone->{$key} = '0';
        }

        $rows[] = $clone;
        $expected = date($format, strtotime($increment, strtotime($expected)));
        $index += 1;
      }

      $expected = date($format, strtotime($increment, strtotime($expected)));
      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * Fills the missing weeks in the result.
   */
  protected static function weeks($result, $field) {
    return self::date($result, $field, 52);
  }

  /**
   * Fills the missing months in the result.
   */
  protected static function months($result, $field) {
    return self::date($result, $field, 12);
  }

  /**
   * Fills the missing days of week in the result.
   */
  protected static function daysOfWeek($result, $field) {
    return self::integer($result, $field, 7);
  }

  /**
   * Fills the missing days of month in the result.
   */
  protected static function dayOfMonth($result, $field) {
    return self::integer($result, $field, 32, 1);
  }

  /**
   * Fills the missing integer in the result.
   */
  protected static function integer($result, $field, $end, $start = 0) {

    $exclude_fields = [
      'index',
      '_entity',
      '_relationship_entities',
      $field,
    ];

    $rows = [];
    $expected = $start;
    $index = 0;
    $row = NULL;
    foreach ($result as $i => $row) {
      while ($expected < (int) $row->{$field}) {
        $clone = clone $row;
        $clone->{$field} = str_pad($expected, 2, '0', STR_PAD_LEFT);
        $clone->index = $index;
        foreach (get_object_vars($clone) as $key => $value) {
          if (in_array($key, $exclude_fields)) {
            continue;
          }
          $clone->{$key} = '0';
        }

        $rows[] = $clone;
        $index += 1;
        $expected += 1;
      }
      $row->index = $index;
      $row->{$field} = str_pad($row->{$field} ?? '', 2, '0', STR_PAD_LEFT);
      $rows[] = $row;
      $index += 1;
      $expected += 1;
    }

    while (!is_null($row) && $expected < $end) {
      $clone = clone $row;
      $clone->{$field} = str_pad($expected, 2, '0', STR_PAD_LEFT);
      $clone->index = $index;
      foreach (get_object_vars($clone) as $key => $value) {
        if (in_array($key, $exclude_fields)) {
          continue;
        }
        $clone->{$key} = '0';
      }

      $rows[] = $clone;
      $index += 1;
      $expected += 1;
    }

    return $rows;
  }

}
