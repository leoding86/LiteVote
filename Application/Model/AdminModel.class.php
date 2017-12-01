<?php
namespace Model;

use Think\Model as BaseModel;
use Model\AdminGroupModel as AdminGroup;
use Model\AdminGroupAdminModel as AdminGroupAdmin;

class AdminModel extends BaseModel
{
  /**
   * 存储登陆信息的key
   */
  static $loginHash = 'uhash';

  /**
   * 存储管理员权限的key
   */
  static $permitsHash = 'upermit';

  /**
   * 管理员数据
   */
  static $udata = [];

  /**
   * 管理员权限
   */
  static $upermits = [];

  /**
   * 保存管理员权限
   *
   * @var array
   */
  private $permits = [];

  /**
   * 是否是超管
   *
   * @var boolean
   */
  private $isSuper = false;

  /**
   * 自动验证设置
   *
   * @var array
   */
  protected $_validate = [
    ['username', 'require', '用户名不能为空', self::EXISTS_VALIDATE, 'regex'],
    ['username', '2,20', '用户名需要在6-20字符', self::EXISTS_VALIDATE, 'length'],
    ['password', 'require', '密码不能为空', self::MUST_VALIDATE, 'regex'],
    ['password', '8,20', '密码长度至少8位', self::MUST_VALIDATE, 'length'],
  ];

  /**
   * 自动完成设置
   *
   * @var array
   */
  protected $_auto = [
    ['create_time', NOW_TIME, self::MODEL_INSERT],
    ['update_time', NOW_TIME, self::MODEL_BOTH],
  ];

  /**
   * 验证登陆信息
   *
   * @param string $password
   * @return void
   */
  public function checkPassword($password)
  {
    if (empty($password)) {
      $this->error = '密码不能为空';
      return false;
    }

    if (password_verify($password, $this->password)) {
      $this->updateLogin();
      $this->storeLogin();
      return true;
    } else {
      $this->clearLogin();
      return false;
    }
  }

  /**
   * 检查是否登陆
   *
   * @return boolean
   */
  public function isLogin()
  {
    self::restoreLogin();

    $this->data = self::$udata;
    $this->permits = self::$upermits;

    if (empty($this->data)) {
      $this->clearLogin();
      return false;
    }

    $this->isSuper = ($this->id == 1);
    $this->getPermits();
    return true;
  }

  /**
   * 保存登陆信息
   *
   * @return void
   */
  public function storeLogin()
  {
    session(self::$loginHash, serialize($this->data()));

    /* 获取权限信息 */
    $adminGroupAdmin = D('AdminGroupAdmin');
    $where = [
      'admin_id' => $this->id,
    ];

    $admin_group_ids = $adminGroupAdmin->where($where)->getField('admin_group_id', true);

    if (empty($admin_group_ids)) {
      $this->permits = [];
    } else {
      $adminGroup = new AdminGroup();
      $this->permits = $adminGroup->getPermitsByIds($admin_group_ids);
    }

    session(self::$permitsHash, $this->permits);
  }

  /**
   * 还原登陆信息
   *
   * @return void
   */
  public static function restoreLogin()
  {
    if (!$uhash = session(self::$loginHash)) {
      self::$udata = null;
    } else {
      self::$udata = unserialize($uhash);
    }

    self::$upermits = session(self::$permitsHash);
  }

  /**
   * 清空登陆信息
   *
   * @return void
   */
  public function clearLogin()
  {
    session(self::$loginHash, null);
    session(self::$permitsHash, null);
  }

  /**
   * 获得数据列表
   *
   * @return array
   */
  public function getList(Page $page, array $where = [])
  {
    return $this->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
  }

  public function getOne($where)
  {
    return $this->where($where)->find();
  }

  public function getOneById($id)
  {
    return $this->getOne(['id' => (int)$id]);
  }

  public function getOneByUsername($username)
  {
    return $this->getOne(['username' => $username]);
  }

  /**
   * 添加一个用户
   *
   * @return void
   */
  public function addOne()
  {
    if (!$this->create($this->data)) {
      return false;
    }
    $data = $this->data; // 暂存

    if ($this->getOneByUsername($this->username)) {
      $this->error = '用户名已存在';
      return false;
    }

    $this->data = $data; // 还原数据

    try {
      $this->password = password_hash($this->password, PASSWORD_DEFAULT);
      $insert_id = $this->add();
      $this->getOneById($insert_id);
      return true;
    } catch (\Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function updateOne()
  {
    if (!$this->create($this->data)) {
      return false;
    }

    if (!$this->id) {
      $this->error = '管理员ID出错';
      return false;
    }

    $id = $this->id;
    $this->password = password_hash($this->password, PASSWORD_DEFAULT);

    try {
      $where = [
        'id' => ['eq', $id],
      ];

      $this->where($where)->save();
      $this->getOneById($id);
      return true;
    } catch (\Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  public function deleteOne()
  {
    if ($this->id == 1) {
      $this->error = '不能删除超级管理员';
      return false;
    }

    try {
      $where = [
        'id' => ['eq', $this->id],
      ];
      $this->where($where)->delete();
      return true;
    } catch (\Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * 更新登陆时间
   *
   * @return void
   */
  public function updateLogin()
  {
    $data = ['login_time' => NOW_TIME];
    $where = ['id' => $this->id];

    $this->where($where)->save($data);
  }

  /**
   * 检查是否是超级管理员
   *
   * @return boolean
   */
  public function isSuper()
  {
    return $this->isSuper;
  }

  /**
   * 获得当前管理员的相关许可
   *
   * @return array
   */
  public function getPermits()
  {
    return $this->permits;
  }

  /**
   * 将管理员添加到管理组
   *
   * @param AdminGroup $adminGroup
   * @throws \Think\Exception
   * @return void
   */
  public function addToAdminGroup(AdminGroup $adminGroup)
  {
    $adminGroup->addAdmin($this->id);
  }

  /**
   * 获得当前管理所在管理组ID集合
   *
   * @return array
   */
  public function getAdminGroupIds()
  {
    $adminGroupAdmin = D('AdminGroupAdmin');;
    $dataset = $adminGroupAdmin->where(['admin_id' => ['eq', $this->id]])->select();

    $group_ids = [];

    foreach ($dataset as $item) {
      $group_ids[] = $item['admin_group_id'];
    }

    return $group_ids;
  }
}