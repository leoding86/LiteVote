<?php

/**
 * 来源域名
 *
 * @return string
 */
function fromDomain() {
  if (!isset($_SERVER['HTTP_REFERER'])) {
    return '';
  }

  $matches = [];

  if (preg_match('/^https?:\/\/([^\/]+)/', $_SERVER['HTTP_REFERER'], $matches) !== 1) {
    return '';
  }

  return $matches[1];
}

if (!defined('FROM_DOMAIN')) {
  define('FROM_DOMAIN', fromDomain());
}

/**
 * 检查时间范围有效性
 *
 * @param array $datetimes [2017-01-01, 2017-01-01]
 * @return boolean
 */
function validate_date_range($datetimes) {
  $start_datetime = new \DateTime($datetimes[0]);
  $end_datetime = new \DateTime($datetimes[1]);

  return ($start_datetime > $end_datetime);
}