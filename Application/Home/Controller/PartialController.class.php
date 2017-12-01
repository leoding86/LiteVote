<?php
namespace Home\Controller;

use Think\Verify;
use Think\Controller as BaseController;
use Model\AjaxResponseModel as AjaxResponse;
use Vendor\Sms as Sms;

class PartialController extends BaseController
{
  public function sendSmsVerifyCode()
  {
    $ajax_response = new AjaxResponse();
    $Verify = new Verify();

    if (!$Verify->check(I('POST.verify_code/s'))) {
      $response = $ajax_response->errBody(99999, '验证码错误');
      $this->ajaxReturn($response);
      return;
    }

    $sms = new Sms\Api();
    $sms->enableDebug()->setAssert(true);
    $sms->defaultCode(I('post.mobile'));

    if ($sms->getErrorCode()) {
      $response = $ajax_response->errBody($sms->getErrorCode(), $sms->getError());
    } else {
      $response = $ajax_response->okBody(null);
    }

    $this->ajaxReturn($response);
  }

  public function verifySmsCode()
  {
    $sms = new Sms\Api();
    if ($sms->verifyCode(I('post.mobile'), I('post.code'), $sms::SMS_DEFAULT_CODE_TYPE, true)) {
      $response = $ajax_response->okBody(null);
    } else {
      $response = $ajax_response->errBody('1', '无效验证码');
    }

    $this->ajaxReturn($response);
  }

  public function verifyCode()
  {
    $Verify = new Verify();
    $Verify->imageH = 32;
    $Verify->imageW = 120;
    $Verify->fontSize = 16;
    $Verify->length = 4;
    $Verify->entry();
  }
}