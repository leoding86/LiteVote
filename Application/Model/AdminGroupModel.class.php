<?php
namespace Model;

use Think\Model as BaseModel;
use Model\AdminModel as Admin;

class AdminGroupModel extends BaseModel
{
    protected $_validate = [
    ['id', '/^\d+$/', '管理员组ID出错', self::EXISTS_VALIDATE, 'regex'],
    ['title', 'require', '组名不能为空', self::MUST_VALIDATE, 'regex'],
    ['title', '2, 20', '组名需要在2-20字', self::MUST_VALIDATE, 'length'],
    ];

  /**
   * 获得数据列表
   *
   * @return array
   */
    public function getList(Page $page = null, array $where = [])
    {
        $this->where($where)->order('id desc');
        if (is_null($page)) {
            return $this->select();
        } else {
            return $this->limit($page->firstRow . ',' . $page->listRows)->select();
        }
    }

  /**
   * 获得管理组信息
   *
   * @param array $where
   * @return void
   */
    public function getOne($where)
    {
        return $this->where($where)->find();
    }

    public function getOneById($id)
    {
        return $this->getOne(['id' => (int)$id]);
    }

  /**
   * 根据指定的管理组ID集合获得权限集合
   *
   * @param array $ids
   * @return array
   */
    public function getPermitsByIds($ids)
    {
        $where = [
        'id' => ['in', $ids],
        ];

        $result = $this->where($where)->find();

        $permits = explode(',', $result['permits']);
        return $permits;
    }

    public function addOne()
    {
        if (!$this->create($this->data)) {
            return false;
        }
        $data = $this->data;

        if ($this->getOne(['title' => ['eq', $this->title]])) {
            $this->error = '管理员组已存在';
            return false;
        }

        $this->data = $data;

        try {
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
        $data = $this->data;
    
        if ($this->getOne(['id' => ['eq', $this->id]])) {
            if ($this->title == $data['title']) {
                $this->error = '请输入新的组名';
                return false;
            }
        }

        if (!$this->id) {
            $this->error = '管理员组ID出错';
            return false;
        }

        $this->data = $data;
        $id = $this->id;

        try {
            $where = [
            'id' => ['eq', $this->id],
            ];

            $this->where($where)->save();
            $this->getOneById($id);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

  /**
   * 删除管理员组
   *
   * @return boolean
   */
    public function deleteOne()
    {
        if ($this->id == 1) {
            $this->error = '不能删除默认组';
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
   * 添加一个管理员到用户组
   *
   * @param Admin $admin
   * @throws \Think\Exception
   * @return void
   */
    public function addAdmin(Admin $admin)
    {
        $where = [
        'admin_group_id' => ['eq', $this->id],
        'admin_id'       => ['eq', $admin->id],
        ];

        $adminGroupAdmin = M('AdminGroupAdmin');
        if ($adminGroupAdmin->where($where)->find()) {
            E('管理员已在当前组');
        }

        $data = [
        'admin_group_id' => $this->id,
        'admin_id' => $admin->id,
        ];

        $adminGroupAdmin->data($data)->add();
    }
  
  /**
   * 移除一个管理员
   *
   * @param Admin $admin
   * @throws \Think\Exception
   * @return void
   */
    public function removeAdmin(Admin $admin)
    {
        $where = [
        'admin_group_id' => $this->id,
        'admin_id' => $admin->id,
        ];

        $adminGroupAdmin = D('AdminGroupAdmin');
        $adminGroupAdmin->where($where)->delete();
    }

  /**
   * 更新问题组权限
   *
   * @param array $permits
   * @return boolean
   */
    public function updatePermits(array $permits)
    {
        $data = [
        'permits' => implode(',', $permits),
        ];

        $where = [
        'id' => ['eq', $this->id],
        ];

        try {
            $this->where($where)->save($data);
            $this->getOne($where);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
