<?php
namespace Admin\Controller;

class SystemController extends EntryController
{
  /**
   * 列出有效权限
   *
   * @return void
   */
  public function listPermits()
  {
    $permits = [];

    $controller_folder = dirname(__FILE__);
    $files = \scandir($controller_folder);

    foreach ($files as $file) {
      if ($file == '.' || $file == '..') {
        continue;
      }

      $file = $controller_folder . '/' . $file;
      $content = file_get_contents($file);
      $matches = [];
      preg_match_all('/\[permit\s*=\s*([a-z\/]+)\]/i', $content, $matches, PREG_PATTERN_ORDER);

      if (!empty($matches[1])) {
        $permits[] = $matches[1];
      }
    }

    var_dump($permits);
  }
}