<?php
namespace EasyPermit;

class EasyPermit
{
  /**
   * 自动注册记录
   *
   * @var boolean
   */
  private static $autoload = false;

  private $currentPermit;

  /**
   * 构造方法
   *
   * @param string $currentPermit
   */
  public function __construct($currentPermit)
  {
    $this->currentPermit = $currentPermit;
  }

  /**
   * 注册自动载入
   *
   * @return void
   */
  public static function registerAutoload()
  {
    if (self::$autoload === false) {
      self::$autoload = spl_autoload_register(__NAMESPACE__ . '\EasyPermit::autoloadRegister');
    }
  }

  /**
   * 指定文件注册
   *
   * @param string $class
   * @return void
   */
  private static function autoloadRegister($class)
  {
    $path = [];
    $path = explode('\\', $class);
    array_shift($path);
    array_unshift($path, dirname(__FILE__));

    $file = implode($path, '/') . '.php';

    if (is_file($file)) {
      include_once $file;
    }
  }

  /**
   * 验证是否拥有指定权限
   *
   * @param Contract\PermitContract $permit
   * @return boolean
   */
  public function hasPermit(Contract\AdminContract $admin, $permit) {
    if ($admin->id == 1) {
      return true;
    }
    
    return in_array((string)$permit, $admin->getPermits());
  }

  /**
   * 验证是否同时拥有一组权限
   *
   * @param array $permits
   * @return boolean
   */
  public function hasPermits(Contract\AdminContract $admin, array $permits)
  {
    foreach ($permits as $permit) {
      if (!$this->hasPermit($admin, $permit)) {
        return false;
      }
    }

    return true;
  }

  /**
   * 登陆管理员
   *
   * @param Contarct\AdminContract $admin
   * @param string $input_password
   * @return boolean
   */
  public function checkPassword(Contarct\AdminContract $admin, $input_password)
  {
    return $admin->checkPassword($input_password);
  }

  /**
   * 检查管理员是否已登陆
   *
   * @return boolean
   */
  public function isLogin(Contract\AdminContract $admin)
  {
    return $admin->isLogin();
  }

  public function testException()
  {
    new Exception\PermitException();
  }
}

EasyPermit::registerAutoload();