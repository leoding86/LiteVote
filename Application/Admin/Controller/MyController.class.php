<?php
namespace Admin\Controller;

use Model\AdminModel as Admin;

class MyController extends EntryController
{
  /**
   * 修改信息
   *
   * [permit = my/security; permitDescription = 修改安全信息]
   * @return void
   */
  public function security()
  {
    if (IS_POST) {
      $post = I('POST.');

      if (empty($post['password'])) {
        $this->assign('error', '密码不能为空');
        $this->display();
        return;
      }

      if ($post['password'] != $post['rpassword']) {
        $this->assign('error', '两次密码不一致');
        $this->display();
        return;
      }

      if (!$this->admin->checkPassword($post['opassword'])) {
        $this->assign('error', '原始密码错误');
        $this->display();
        return;
      }

      $this->admin->data([
        'id'       => $this->admin->id,
        'password' => $post['password'],
      ]);

      if (!$this->admin->updateOne()) {
        $this->assign('error', $this->admin->getError());
        $this->display();
        return;
      }

      $this->assign('success', '更新完成');
    }

    $this->display();
  }
}