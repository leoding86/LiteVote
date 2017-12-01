<?php
namespace Vendor\Util;

class ReadableText
{
  public static function questionType($type)
  {
    switch ($type) {
      case 'radio':
        return '单选';
      case 'checkbox':
        return '多选';
      default:
        return '';
    }
  }

  public static function enableType($enable)
  {
    switch ($enable) {
      case 0:
        return '未启用';
      case 1:
        return '已启用';
    }
  }
}