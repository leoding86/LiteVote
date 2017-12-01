<?php
namespace EasyPermit\Contract;

interface AdminContract
{
  /**
   * 管理员登陆
   *
   * @param string $password 输入的密码
   * @return boolean
   */
  public function checkPassword($password);

  /**
   * 检查登陆
   *
   * @return boolean
   */
  public function isLogin();

  /**
   * 获得许可信息
   *
   * @return array
   */
  public function getPermits();

  public function addOne();

  public function updateOne();

  public function deleteOne();

  /**
   * 是否是超管
   *
   * @return boolean
   */
  public function isSuper();
}
