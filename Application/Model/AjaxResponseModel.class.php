<?php
namespace Model;

class AjaxResponseModel
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
}