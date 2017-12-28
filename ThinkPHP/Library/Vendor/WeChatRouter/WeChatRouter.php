<?php
namespace Vendor\WeChatRouter;

use GuzzleHttp\Client as HttpClient;

class WeChatRouter
{
  const ROOT = 'http://127.0.0.1/cn.cjn.open/wechat.php/';
  const USER_API = self::ROOT . 'User/';
  const USER_GETID_API = self::USER_API . 'getId';
  
  public function __construct()
  {
    
  }

  private function buildUrl($url, array $params)
  {
    $param_strs = [];
    foreach ($params as $key => $val) {
      $val = urlencode($val);
      $param_strs[$key] = "{$key}={$val}";
    }

    return $url . (strpos($url, '?') > 0 ? '$' : '?') . implode('&', $param_strs);
  }

  public function user($redirect)
  {
    header('Location: ' . $this->buildUrl(self::USER_API, ['redirect' => $redirect]));
    exit;
  }

  public function getId($hash)
  {
    $client = new HttpClient();
    $result = $client->request('GET', $this->buildUrl(self::USER_GETID_API, ['hash' => $hash]));

    $data = json_decode($result->getBody());

    if (is_null($data)) {
      return null;
    } else {
      return $data->id;
    }
  }
}