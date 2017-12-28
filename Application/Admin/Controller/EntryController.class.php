<?php
namespace Admin\Controller;

use Think\Controller as BaseController;
use Vendor\Util\AjaxResponse;
use Model\AdminModel as Admin;
use Model\SettingModel as Setting;

/**
 * 管理页面入口控制器，用于控制访问权限
 */
class EntryController extends BaseController
{
  /**
   * ajax相应对象
   *
   * @var AjaxResponse
   */
    protected $ajaxResponse;

  /**
   * 来源页面
   *
   * @var string
   */
    protected $referer;

  /**
   * 管理员模型对象
   *
   * @var Admin
   */
    protected $admin;

  /**
   * 构造方法
   */
    public function __construct()
    {
        parent::__construct();
        $this->ajaxResponse = new AjaxResponse();
        $this->referer = $_SERVER['HTTP_REFERER'];
        $this->assign('referer', $this->referer);
    
        $setting = new Setting('system');
        C($setting->read());

        $this->admin = new Admin();

        if ($this->admin->isLogin()) {
            if ($this->admin->id == 1) {
                return;
            }

            $permits = $this->admin->getPermits();

            foreach ($permits as $permit) {
                if (strtolower($permit) == $this->getPermit()) {
                    return;
                }
            }
        }

        $this->redirect('Login/index', ['error' => 'permit']);
    }

  /**
   * 获得当前请求的权限值
   *
   * @return string
   */
    private function getPermit()
    {
        static $permit = null;

        if ($permit) {
            return $permit;
        }

        $controller = CONTROLLER_NAME;
        $action = ACTION_NAME;
        $permit = strtolower($controller . '/' . $action);
        return $permit;
    }
  
  /**
   * 获得指定的输入值
   *
   * @param string $name
   * @return void
   */
    protected function getInputVar($name)
    {
        return I('POST.' . $name, null) === null ? I('GET.' . $name) : I('POST.' . $name);
    }
}
