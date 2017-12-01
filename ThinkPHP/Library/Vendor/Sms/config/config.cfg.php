<?php
return array(
  // 接口参数
  'appid'       => '',
  'accountsid'  => '',
  'token'       => '',
  // 模版设置
  'templates'   => array(
    'modify_password' => array('template_id' => 0, 'template_name' => '修改密码'),
    'modify_mobile'   => array('template_id' => 0, 'template_name' => '修改手机号'),
    'new_signup'      => array('template_id' => 0, 'template_name' => '用户注册'),
    'post_pwd'        => array('template_id' => 0, 'template_name' => '发送密码'),
    'default_code'        => array('template_id' => 0, 'template_name' => '默认验证码'),
  ),
  // 令牌设置
  'auth_tokens' => array(
    // 域名 => 令牌
  ),
  // 验证码过期时间
  'expired_in'  => 60 * 90,
  /* 能发送的max_time次数 */
  'max_time'    => 99999,
  /* 发送间隔 */
  'interval'    => 120,
);