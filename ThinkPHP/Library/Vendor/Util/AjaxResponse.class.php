<?php
namespace Vendor\Util;

class AjaxResponse
{
  private $data;
  private $status;
  private $code;
  private $msg;

  public function body($status, $code, $data, $msg = '')
  {
    return [
      'status' => $status ? 'ok' : 'err',
      'msg'    => $msg,
      'code'   => intval($code),
      'data'   => $data,
    ];
  }

  public function errBody($code, $msg = '')
  {
    return $this->body(false, $code, null, $msg);
  }

  public function okBody($data, $msg = '')
  {
    return $this->body(true, 0, $data, $msg);
  }

  public function returnErr($code = 99999, $msg = '')
  {
    header("Content-Type: application/json");
    exit(json_encode($this->errBody($code, $msg)));
  }

  public function returnOk($data = null, $msg = '')
  {
    header("Content-Type: application/json");
    exit(json_encode($this->okBody($data, $msg)));
  }

  public function returnResult($status, $code, $data, $msg)
  {
    header("Content-Type: application/json");
    exit(json_decode($this->body($status, $code, $data, $msg)));
  }
}