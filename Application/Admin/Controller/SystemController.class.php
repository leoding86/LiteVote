<?php
namespace Admin\Controller;

use Think\Page;
use Model\SettingModel as Setting;
use Model\LoggerModel as Logger;

class SystemController extends EntryController
{
    private $settingKeys = [
        'wechat_app_key',
        'wechat_app_secret',
        'wechat_router',
        'wechat_router_redirect_arg',
        'wechat_router_openid_arg',
        'admin_login_captcha_type',
        'admin_login_retry_times',
        'admin_login_retry_duration',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 系统设置
     *
     * @return void
     */
    public function setting()
    {
        $setting = new Setting('system');

        if (IS_POST) {
            $post = I('POST.');

            $settings = [];
            foreach ($post as $key => $val) {
                if (in_array($key, $this->settingKeys)) {
                    if (!isset($settings[$key])) {
                        $settings[$key] = new Setting('system');
                        $settings[$key]->key = $key;
                        $settings[$key]->value = $val;
                    }
                }
            }

            $setting->write($settings);
        }

        $settings = $setting->read();
        $this->assign('settings', $settings);
        $this->assign('verify_type_options', Setting::verifyTypeOptions());
        $this->display();
    }

    /**
     * 操作日志
     *
     * [permit=system/operationLog; permitDescription=操作日志]
     * @return void
     */
    public function operationLog()
    {
        $logger = new Logger;
        $count = $logger->count();
        $page = new Page($count, 3);
        $list = $logger->getList([], $page);

        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->assign('page_title', '日志列表');
        $this->display();
    }
}
