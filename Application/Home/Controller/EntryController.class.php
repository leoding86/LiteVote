<?php
namespace Home\Controller;

use Think\Controller as BaseController;
use Vendor\Util\AjaxResponse;

/**
 * 入口控制器
 */
class EntryController extends BaseController
{
  /**
   * 来源地址
   *
   * @var string
   */
  protected $referer;

  /**
   * ajax响应对象
   *
   * @var AjaxResponse
   */
  protected $AjaxResponse;

  /**
   * 构造方法
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
    $this->referer = $_SERVER['HTTP_REFERER'];
    $this->AjaxResponse = new AjaxResponse();
  }

  /**
   * 默认空行为
   *
   * @return void
   */
  public function _empty()
  {
    echo 'empty page';
  }
}