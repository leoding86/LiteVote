<?php
namespace Admin\Controller;

use Think\Controller;
use Model\AdminModel as Admin;
use Model\ActionLimitationModel as ActionLimitation;
use Model\SettingModel as Setting;
use Vendor\Util\AjaxResponse;

class LoginController extends Controller
{
  /**
   * ajax响应
   *
   * @var \Vendor\Util\AjaxResponse
   */
    protected $ajaxResponse;

    /**
     * Setting model
     *
     * @var Setting
     */
    protected $setting;

    protected $settings;

    public function __construct()
    {
        parent::__construct();
        $this->ajaxResponse = new AjaxResponse();
        $this->setting = new Setting('system');
        $this->settings = $this->setting->read();
    }

    public function index()
    {
        $action_limitation = new ActionLimitation(
            get_client_ip(),
            'login',
            $this->settings['admin_login_retry_times'],
            $this->settings['admin_login_retry_duration']
        );

        if (IS_POST) {
            if (!$action_limitation->isAllowed()) {
                $this->ajaxResponse->returnErr(
                    99999,
                    '超过重试限制，请' . $this->settings['admin_login_retry_duration'] . '秒后再试'
                );
            }

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
