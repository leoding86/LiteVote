<?php
namespace Home\Controller;

use Think\Controller as BaseController;

class ErrorController extends BaseController
{
  public function NotFound()
  {
    $this->display('not_found');
  }
}