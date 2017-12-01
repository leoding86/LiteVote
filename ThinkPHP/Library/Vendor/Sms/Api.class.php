<?php
namespace Vendor\Sms;

class Api {

  const MODULES_DIR              = __DIR__;
  const CONFIG_DIR               = 'config';
  const CONFIG_EXT               = '.cfg.php';
  const CODE_MIN_LENGTH          = 4;
  const SMS_NEW_SIGNUP_TYPE      = 'new_signup';
  const SMS_MODIFY_PASSWORD_TYPE = 'modify_password';
  const SMS_MODIFY_MOBILE_TYPE   = 'modify_mobile';
  const SMS_DEFAULT_CODE_TYPE        = 'default_code';
  private $configDir;
  private $config;
  private $smsTypes;
  private $code; // 生成的验证码
  private $error;
  private $errorCode;
  private $errorCodes;
  private $debug = false;
  private $assert = null;

  public function __construct() {

    $this->configDir  = self::MODULES_DIR . "/" . self::CONFIG_DIR;
    $this->config     = @include $this->configDir . '/config' . self::CONFIG_EXT;
    $this->smsTypes   = array(
      self::SMS_NEW_SIGNUP_TYPE,
      self::SMS_MODIFY_PASSWORD_TYPE,
      self::SMS_MODIFY_MOBILE_TYPE,
      self::SMS_DEFAULT_CODE_TYPE
    );

    $this->code       = null;
    $this->error      = null;
    $this->errorCode  = null;
    $this->errorCodes = array(
      '-1'     => '已达每日上限' . $this->config['max_time'] . '次',
      '-100'   => '验证码失效',
      '-101'   => '验证码错误',
      '-102'   => '发送间隔过短',
      '-10000' => '发送失败',
      '-10001' => '手机号码格式错误',
      '-99999' => '异常方法'
    );
  }

  public function __call($name, $arguments)
  {
    $this->setError('-99999');
  }

  public static function getNatureDate($seconds)
  {
    $output = '';
    $hour = floor($seconds / 3600);
    $min = floor(($seconds - $hour * 3600) / 60);
    $sec = $seconds - $hour * 3600 - $min * 60;
    return ($hour == 0 ? '' : ($hour . '小时')) . ($min == 0 ? '' : ($min . '分钟')) . ($sec == 0 ? '' : ($sec . '秒'));
  }

  public function enableDebug()
  {
    $this->debug = true;
    return $this;
  }

  public function setAssert($success = true)
  {
    $this->assert = $success;
  }

  /**
   * 获得有效的短信记录模型
   *
   * @return void
   */
  private static function loadSmsModel()
  {
    return new \Model\SmsModel();
  }

  private function setError($error_code) {
    if (isset($this->errorCodes[$error_code])) {
      $this->errorCode = $error_code;
      $this->error     = $this->errorCodes[$error_code];
    } else {
      $this->errorCode = $error_code;
      $this->error     = '发送失败';
    }
  }

  /**
   * 验证令牌的有效性
   * @return boolean
   */
  private function verifyAuthToken() {
    /* 验证令牌格式有效性 */
    $auth = preg_match('/[A-Za-z0-9]{32}/u', $_POST['auth']) ? $_POST['auth'] : '';
    if (!$auth) {
      return false;
    }

    /* 验证令牌有效性 */
    $auths = $this->config['auth_tokens'];
    if ($auths[strtolower($_SERVER['HTTP_HOST'])] !== $auth) {
      return false;
    }
    return true;
  }

  /**
   * 获得指定发送类型
   * @param  string $type 短信类型
   * @return string|null
   */
  private function getType($type)
  {
    return in_array($type, $this->smsTypes) ? $type : null;
  }

  /**
   * 发送短信
   * @param  string $mobile
   * @param  string $type         短信短信模板类型
   * @param  array  $replacements 替换字段
   * @param  string $code         发送的是验证码
   * @return void
   */
  private function sendSms($mobile, $type, $replacements, $code = null)
  {
    /* 载入短信日志模型 */
    $SMSReport = self::loadSmsModel();

    /* 获得对应的类型 */
    if (!isset($this->config['templates'][$type])) {
      throw new Exception("Unkown template", 1);
    }
    $template = $this->config['templates'][$type];

    /* 检查手机号码 */
    $pattern = '/^1\d{10}$/';
    $to      = preg_match($pattern, $mobile) ? $mobile : '';
    if (empty($to)) {
      $this->setError('-10001');
      return false;
    }

    if ($this->debug) {
      if ($this->assert) {
        $result = [
          'respCode' => '000000',
          'templateSMS' => [
            'smsId' => '1'
          ]
        ];
      } else {
        $result = [
          'respCode' => '999999',
          'templateSMS' => [
            'smsId' => '0'
          ]
        ];
      }
    } else {
      $Ucpaas = new Ucpaas;
      $Ucpaas->setAccountSid($this->config['accountsid']);
      $Ucpaas->setToken($this->config['token']);
      $result = $Ucpaas->templateSMS($this->config['appid'], $to, $template['template_id'], implode(',', $replacements));
  
      try {
        $result = json_decode($result, true);
        $result = $result['resp'];
      } catch (Exception $e) {
        $result = array('respCode' => 'unkown', 'templateSMS' => array('smsId' => '0'));
      }
  
      if (!isset($result['respCode']) or !isset($result['templateSMS']['smsId'])) {
        $result = array('respCode' => 'unkown', 'templateSMS' => array('smsId' => '0'));
      }
    }

    /* 判断是否发送成功 */
    $replacements_string = implode('||', $replacements);
    if ($result['respCode'] === '000000') {
      /* 保存成功日志 */
      $SMSReport->add(
        $result['templateSMS']['smsId'],
        $to,
        $type,
        "发送成功",
        $replacements_string,
        true,
        $code,
        false,
        $replacements_string
      );
      return true;
    } else {
      /* 保存失败日志 */
      $this->setError('-10000');
      $SMSReport->add(
        $result['templateSMS']['smsId'],
        $to,
        $type,
        $this->error . '[' . $result['respCode'] . ']',
        $replacements_string,
        false,
        $code,
        false,
        $replacements_string
      );
      return false;
    }
  }

  /**
   * 发送验证短信
   * @param  string $mobile 需要发送的手机号码
   * @param  string $type 模版类型
   * @return boolean
   */
  private function sendCode($mobile, $type) {
    /* 验证令牌有效性
    if (!$this->verifyAuthToken()) {
    $this->error = 'Invalid auth token';
    return false;
    } */

    /* 载入短信日志模型 */
    $SMSReport = self::loadSmsModel();

    if ($SMSReport->reachSendLimit($mobile, $this->config['max_time'])) {
      $this->setError('-1');
      return false;
    }

    if ($this->isSendTooFrequently($mobile)) {
      $this->setError('-102');
      return false;
    }

    /* 生成验证码 */
    $code = self::generateCode();

    /* 发送短信 */
    return $this->sendSms(
      $mobile,
      $type,
      array($code, self::getNatureDate($this->config['expired_in'])),
      $code
    );
  }

  public function sendPassWord($mobile, $type) {
    /* 载入短信日志模型 */

    $templates = $this->config['templates'];
    if (!isset($templates[$type])) {
      throw new Exception("Unkown template", 1);
    }
    $template = $templates[$type];

    /* 检查手机号码 */
    $pattern = '/^\d{11}$/';
    $to      = preg_match($pattern, $mobile) ? $mobile : '';
    if (empty($to)) {
      // $this->setError('-10001');
      return false;
    }
    $Ucpaas = new Ucpaas;
    $Ucpaas->setAccountSid($this->config['accountsid']);
    $Ucpaas->setToken($this->config['token']);
    $result = $Ucpaas->templateSMS($this->config['appid'], $to, $template['template_id'], $to);
    // var_dump($result);
    /* 判断是否发送成功 */
    if ($result['respCode'] === '000000') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * 获得最近的错误
   * @return string
   */
  public function getError() {
    return $this->error;
  }

  public function getErrorCode() {
    return $this->errorCode;
  }

  /**
   * 发送注册短信
   * @return void
   */
  public function signUp($mobile) {
    $this->sendCode($mobile, 'new_signup');
  }

  /**
   * 发送修改密码短信
   * @return void
   */
  public function modifyPassword($mobile) {
    $this->sendCode($mobile, 'modify_password');
  }

  /**
   * 发送修改手机号码短信
   * @return void
   */
  public function modifyMobile($mobile) {
    $this->sendCode($mobile, 'modify_mobile');
  }

  /**
   * 发送问答提问短信验证码
   * @param string $mobile 待接收手机
   */
  public function defaultCode($mobile)
  {
    $this->sendCode($mobile, self::SMS_DEFAULT_CODE_TYPE);
  }

  /**
   * 验证码生成器
   *
   * @param  integer $length 生成长度
   * @return string          验证码
   */
  public static function generateCode($length = 6) {
    if ($length < self::CODE_MIN_LENGTH) {
      throw new Exception("Code is too short", 1);
    }
    $code = '';
    while ($length-- > 0) {
      $code .= mt_rand(0, 9);
    }
    return $code;
  }

  /**
   * 检查验证码
   * @return boolean
   */
  public function verifyCode($mobile, $code, $type, $is_use) {
    if (!in_array($type, $this->smsTypes)) {
      $this->error = 'invalid type';
      return false;
    }

    /* 获得有效期 */
    $time_in = time() + $this->config['expired_in'];

    $SMSReport = self::loadSmsModel();
    if ($SMSReport->verifyCode($time_in, $mobile, $code, $type, $is_use)) {
      return true;
    } else {
      $this->error = 'failed[' . $SMSReport->error() . ']';
      return false;
    }
  }

  /**
   * 检查发送间隔是否过短
   * @param  string  $mobile 手机号码
   * @param  string  $type   检查指定类型短信
   * @return boolean
   */
  public function isSendTooFrequently($mobile, $type = '')
  {
    if (!preg_match('/^1\d{10}$/', $mobile)) {
      throw new Exception('无效手机号码');
    }

    $SMSReport = self::loadSmsModel();

    if ($this->getType($type)) {
      $where = sprintf(
        '`mobile` = \'%s\' AND `create_time` > %d AND `type` = \'%s\' AND `is_sended` = 1',
        $mobile,
        time() - $this->config['interval'],
        $type
      );
    } else {
      $where = sprintf(
        '`mobile` = \'%s\' AND `create_time` > %d AND `is_sended` = 1',
        $mobile,
        time() - $this->config['interval']
      );
    }

    $result = $SMSReport->get_one($where);
    
    return !empty($result);
  }
}