<?php
namespace Admin\Controller;

import('Vendor.EasyPermit.EasyPermit');

use Think\Controller;
use Model\AdminModel as Admin;
use Vendor\Util\AjaxResponse;
use EasyPermit\EasyPermit;

class LoginController extends Controller
{
  /**
   * ajax响应
   *
   * @var \Vendor\Util\AjaxResponse
   */
  protected $ajaxResponse;

  public function __construct()
  {
    parent::__construct();
    $this->ajaxResponse = new AjaxResponse();
  }

  public function index()
  {
    if (IS_POST) {
      $admin = new Admin();
      if (!$admin->getOneByUsername(I('post.username'))) {
        $this->ajaxResponse->returnErr(99999, '用户不存在');
        return;
      }

      if (!$admin->checkPassword(I('post.password'))) {
        $this->ajaxResponse->returnErr(99999, '用户名或密码错误');
        return;
      }

      $this->ajaxResponse->returnOk(null, '登陆完成');
      return;
    }

    if (I('GET.error') == 'permit') {
      $this->assign('error', '没有相应权限');
    }

    $this->display();
  }

  public function logout()
  {
    $admin = new Admin();
    $admin->clearLogin();
    $this->redirect('Login/index');
  }
}