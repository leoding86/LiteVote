<?php
namespace Admin\Controller;

use Think\Page;
use Model\AdminModel as Admin;
use Model\AdminGroupModel as AdminGroup;

class AdministratorController extends EntryController
{
  /**
   * 管理员列表
   *
   * @return void
   * [permit = Administrator/index; permitDescription = 管理员列表]
   */
  public function index()
  {
    $admin = new Admin();
    $count = $admin->count();
    $page = new Page($count, 10);
    $lists = $admin->getList($page);
  
    $this->assign('administrators', $lists);
    $this->assign('page', $page->show());

    $this->display();
  }

  /**
   * 新建管理员
   *
   * @return void
   * [permit = Administrator/create; permitDescription = 创建管理员]
   */
  public function create()
  {
    if (IS_POST) {
      if (I('post.password') != I('post.rpassword')) {
        $this->assign('error', '两次密码不一致');
      } else {
        $admin = new Admin();
        $admin->data([
          'username' => I('post.username'),
          'password' => I('post.password'),
        ]);
  
        if ($admin->addOne()) {
          $this->redirect('Administrator/index');
          return;
        } else {
          $this->assign('error', $admin->getError());
        }
      }

      $this->assign('post', I('post.'));
    } else {
      $this->assign('post', []);
    }

    $this->display();
  }

  /**
   * 编辑管理员
   *
   * @return void
   * [permit = Administrator/edit; permitDescription = 编辑管理员]
   */
  public function edit()
  {
    $id = I('post.id', null) === null ? I('get.id/d') : I('post.id/d');
    $admin = new Admin();
    $adminGroup = new AdminGroup();

    if (!$admin->getOneById($id)) {
      E('指定的管理员不存在或已删除');
    }

    $data = $admin->data();

    if (IS_POST) {
      if (I('post.password') != I('post.rpassword')) {
        $this->assign('error', '两次密码不一致');
        $this->assign('post', $data);
      } else {
        $admin->data([
          'id'       => I('post.id/d'),
          'password' => I('post.password/s'),
        ]);
  
        if ($admin->updateOne()) {
          $this->assign('success', '编辑完成');
          $this->assign('post', $admin->data());
        } else {
          $this->assign('error', $admin->getError());
          $this->assign('post', $data);
        }
      }
    } else {
      $this->assign('post', $admin->data());
    }

    $this->assign('administrator', $admin->data());
    $this->display();
  }

  /**
   * 删除管理员
   *
   * @return void
   * [permit = Administrator/delete; permitDescription = 删除管理员]
   */
  public function delete()
  {
    $id = I('get.id/d');
    $admin = new Admin();

    if (!$admin->getOneById($id)) {
      E('指定的管理员不存在或已删除');
    }

    if (!$admin->deleteOne()) {
      E($admin->getError());
    } else {
      redirect($this->referer);
    }
  }

  /**
   * 应用管理组
   *
   * @return void
   * [permit = Administrator/applyGroups; permitDescription = 应用管理组]
   */
  public function applyGroups()
  {
    $id = I('post.id', null) === null ? I('get.id/d') : I('post.id/d');
    $admin = new Admin();

    if (!$admin->getOneById($id)) {
      E('指定的管理员不存在或已删除');
    }

    $adminGroup = new AdminGroup();

    if (I('GET.action')) {
      $get = I('get.');
      
      if (!$adminGroup->getOneById($get['admin_group_id'])) {
        E('指定的管理组不存在或已删除');
      }

      try {
        if ($get['action'] == 'add') {
          $adminGroup->addAdmin($admin);
          redirect($this->referer);
          return;
        } else if ($get['action'] == 'remove') {
          $adminGroup->removeAdmin($admin);
          redirect($this->referer);
          return;
        } else {
          $this->assign('error', '无效的操作');
        }
      } catch (\Exception $e) {
        $this->assign('error', $e->getMessage());
      }
    }

    $this->assign('admin_group_ids', $admin->getAdminGroupIds());
    $this->assign('administrator', $admin->data());
    $this->assign('groups', $adminGroup->getList());
    $this->display();
  }
}