<?php
namespace Admin\Controller;

use Think\Page;
use Model\AdminGroupModel as AdminGroup;

class AdministratorGroupController extends EntryController
{
  /**
   * 管理员列表
   *
   * @return void
   * [permit = AdministratorGroup/index; permitDescription = 管理员组列表]
   */
    public function index()
    {
        $adminGroup = new AdminGroup();
        $count = $adminGroup->count();
        $page = new Page($count, 10);
        $lists = $adminGroup->getList($page);
  
        $this->assign('groups', $lists);
        $this->assign('page', $page->show());

        $this->display();
    }

  /**
   * 新建管理员
   *
   * @return void
   * [permit = AdministratorGroup/create; permitDescription = 创建管理员组]
   */
    public function create()
    {
        if (IS_POST) {
            $adminGroup = new adminGroup();
            $adminGroup->data([
            'title' => I('post.title'),
            ]);

            if ($adminGroup->addOne()) {
                $this->redirect('AdministratorGroup/index');
                return;
            } else {
                $this->assign('error', $adminGroup->getError());
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
   * [permit = AdministratorGroup/edit; permitDescription = 编辑管理员组]
   */
    public function edit()
    {
        $id = I('post.id', null) === null ? I('get.id/d') : I('post.id/d');
        $adminGroup = new AdminGroup();

        if (!$adminGroup->getOneById($id)) {
            E('指定的管理员不存在或已删除');
        }

        $data = $adminGroup->data();

        if (IS_POST) {
            $adminGroup->data([
            'id'    => I('post.id/d'),
            'title' => I('post.title/s'),
            ]);

            if ($adminGroup->updateOne()) {
                $this->assign('success', '编辑完成');
                $this->assign('post', $adminGroup->data());
            } else {
                $this->assign('error', $adminGroup->getError());
                $this->assign('post', $data);
            }
        } else {
            $this->assign('post', $adminGroup->data());
        }

        $this->display();
    }

  /**
   * 删除管理员
   *
   * @return void
   * [permit = AdministratorGroup/delete; permitDescription = 删除管理员组]
   */
    public function delete()
    {
        $id = I('get.id/d');
        $adminGroup = new AdminGroup();

        if (!$adminGroup->getOneById($id)) {
            E('指定的管理员不存在或已删除');
        }

        if (!$adminGroup->deleteOne()) {
            E($adminGroup->getError());
        } else {
            redirect($this->referer);
        }
    }

  /**
   * 应用权限
   *
   * @return void
   * [permit = AdministratorGroup/applyPermit; permitDescription = 分配权限]
   */
    public function applyPermit()
    {
        $id = I('post.id', null) === null ? I('get.id/d') : I('post.id/d');
        $adminGroup = new AdminGroup();

        if (!$adminGroup->getOneById($id)) {
            E('指定的管理员不存在或已删除');
        }

        $permits_list = $this->getPermitsList();

        if (IS_POST) {
            $permits_collection = [];
            foreach ($permits_list as $permits) {
                foreach ($permits as $permit) {
                    $permits_collection[] = $permit['permit'];
                }
            }

            $post = I('post.');
      
            foreach ($post['permits'] as $post_permit) {
                if (!in_array($post_permit, $permits_collection)) {
                    $this->assign('error', '存在未知权限');
                    break;
                }
            }

            if ($adminGroup->updatePermits($post['permits'])) {
                $this->assign('success', '更新权限完成');
            }
        }

        $this->assign('group_permits', explode(',', $adminGroup->permits));
        $this->assign('permits_list', $permits_list);
        $this->assign('group', $adminGroup->data());
        $this->display();
    }

  /**
   * 获得所有有效的权限列表
   *
   * @return array
   */
    private function getPermitsList()
    {
        $permits = [];

        $controller_folder = dirname(__FILE__);
        $files = \scandir($controller_folder);

        $i = 0;
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $file = $controller_folder . '/' . $file;
            $content = file_get_contents($file);
            $matches = [];
            if (preg_match_all('/\[permit\s*=\s*([a-z\/]+)(?:;\s*permitDescription\s*=\s*(.+))?\]/i', $content, $matches, PREG_SET_ORDER)) {
                $permits[$i] = [];
                foreach ($matches as $match) {
                    $permits[$i][] = [
                    'permit'      => $match[1],
                    'description' => $match[2] ? $match[2] : $match[1],
                    ];
                }
            }
            $i++;
        }

        return $permits;
    }
}
