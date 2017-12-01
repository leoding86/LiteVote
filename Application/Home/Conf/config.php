<?php
return [
  'URL_MODEL' => 1,
  'URL_ROUTER_ON'   => true, // 开启路由
  'URL_ROUTE_RULES' => [
    '/^(sendverifycode)$/'    => 'Partial/sendSmsVerifyCode',

    /* Survey routers */
    '/^(ajax\/survey\/submit)/' => 'Survey/ajaxSubmit',
    '/^survey\/([\da-z]+)/i'  => 'Survey/index/unid/:1',
  ],
];