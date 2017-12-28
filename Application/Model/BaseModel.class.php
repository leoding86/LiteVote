<?php
namespace Model;

use Think\Model as SuperModel;
use Think\Page;
use Think\Exception;
use DateTime;

class BaseModel extends SuperModel
{
  /**
   * 数据表列日期字段
   * 自动转化指定字段，读取时转换为\DateTime，写入时转化为int
   * 设置字字段时接受有效的日期格式或者\DateTime对象
   *
   * @var array
   */
    protected $datetimeFields = [];

  /**
   * 数据列启用标识符
   *
   * @var string
   */
    protected $enableField = 'enable';

  /**
   * 数据列删除标识符
   *
   * @var string
   */
    protected $deleteFlagField = 'delete_flg';

  /**
   * 对应数据列表
   *
   * @var array
   */
    public $list = [];

  /**
   * 设置数据对象的值
   *
   * @access public
   * @param string $name 名称
   * @param mixed $value 值
   * @return void
   */
    public function __set($name, $value)
    {
        if (in_array($name, $this->datatimeFields) &&
        !is_a($value, DateTime)
        ) {
            $value = new DateTime($value);
        }

        parent::__set($name, $value);
    }

  /**
   * 重写父__call方法
   *
   * @param string $name
   * @param array $arguments
   * @return mixed
   */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'getOneBy') === 0) {
            $part = substr($name, 8);
            $field_name = preg_replace('/[A-Z]/', '_${0}', $part);
            if (strpos($field_name, '_') === 0) {
                $field_name = substr($field_name, 1);
            }

            $field_name = strtolower($field_name);
            $where = [
            $field_name => $arguments[0],
            ];
            return $this->getOne($where);
        } else {
            return parent::__call($name, $arguments);
        }

        return $this;
    }

  /**
   * 转化获得的数据
   *
   * @param array $data
   * @throws Think\Exception
   * @return void
   */
    private function convertReadData(&$data)
    {
        try {
            foreach ($data as $key => $value) {
              /* 转化日期对象 */
                if (in_array($key, $this->datetimeFields)) {
                    $data[$key] = new DateTime(date('Y-m-d H:i:s', $value));
                }
            }
        } catch (Exception $e) {
            E(L('_INVALID_DATETIME_'));
        }
    }

  /**
   * 转化设置的数据
   *
   * @param array $data
   * @return void
   */
    private function convertWriteData(&$data)
    {
        foreach ($data as $key => $value) {
          /* 转化日期对象 */
            if (in_array($key, $this->datetimeFields)) {
                $data[$key] = $value->getTimestamp();
            }
        }
    }

  /**
   * 创建数据前执行方法
   *
   * @param array $data 创建数据前的数据
   * @param int $moment
   * @return boolean
   */
    protected function beforeCreate(&$data, $moment)
    {
        return true;
    }

  /**
   * 创建数据后执行方法
   *
   * @param array $data 创建数据后的数据
   * @param int $moment
   * @return boolean
   */
    protected function afterCreate(&$data, $moment)
    {
        return true;
    }
  
  /**
   * 数据读取后的处理
   * @access protected
   * @param array $data 当前数据
   * @return array
   */
    protected function _read_data($data)
    {
        $data = parent::_read_data($data);
        $this->convertReadData($data);
        return $data;
    }

  /**
   * 设置数据对象值，增加转化层
   * @access public
   * @param mixed $data 数据
   * @return Model
   */
    public function data($data = '')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        } elseif (is_string($data)) {
            parse_str($data, $data);
        } elseif (!is_array($data)) {
            E(L('_DATA_TYPE_INVALID_'));
        }

        $this->convertWriteData($data);
        $this->data = $data;
        return $this;
    }

  /**
   * 获得数据列表
   *
   * @param array $where
   * @param Page $page
   * @return array
   */
    public function getList($where, Page $Page = null)
    {
        if (!is_null($this->deleteFlagField)) {
            $where = array_merge($where, [$this->deleteFlagField => 0]);
        }
        return $this->list = $this->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
    }

  /**
   * 获得一条数据
   *
   * @param array $where
   * @return boolean
   */
    public function getOne($where)
    {
        $result = $this->where($where)->find();
        return !empty($result);
    }

  /**
   * 通过当前模型ID获取一条数据
   *
   * @param int $id
   * @return boolean
   */
    public function getOneById($id)
    {
        return $this->getOne(['id' => ['eq', (int)$id]]);
    }

  /**
   * 添加一条数据
   *
   * @return boolean
   */
    public function addOne()
    {
        if (!$this->beforeCreate($this->data, self::MODEL_INSERT)) {
            return false;
        }

        if (!$this->create($this->data)) {
            return false;
        }

        if (!$this->afterCreate($this->data, self::MODEL_INSERT)) {
            return false;
        }

        try {
            $insert_id = $this->add();
            $this->getOneById($insert_id);
            return true;
        } catch (Exception $e) {
            $this->error = '创建数据时出现问题，请联系管理员';
            return false;
        }
    }

  /**
   * 编辑一条数据
   *
   * @return boolean
   */
    public function updateOne()
    {
        if (!$this->beforeCreate($this->data, self::MODEL_UPDATE)) {
            return false;
        }

        if (!$this->create($this->data)) {
            return false;
        }

        if (!$this->afterCreate($this->data, self::MODEL_UPDATE)) {
            return false;
        }

        try {
            $id = $this->data[$this->getPk()]; // 保存ID数据
            $this->save();
            $this->getOneById($id);
            return true;
        } catch (Exception $e) {
            $this->error = '更新数据时出现问题，请联系管理员';
            return false;
        }
    }

  /**
   * 删除一条数据
   *
   * @return boolean
   */
    public function deleteOne()
    {
        $where = [
        'id' => $this->id
        ];

        try {
            if (is_null($this->deleteFlagField)) {
                $this->where($where)->delete();
            } else {
                $this->where($where)->save([$this->deleteFlagField => 1]);
            }
            return true;
        } catch (Exception $e) {
            $this->error = '删除数据时出现问题，请联系管理员';
            return false;
        }
    }

  /**
   * 启用一条数据
   *
   * @throws Exception
   * @return void
   */
    public function enable()
    {
        try {
            $id = $this->id;
            $data = [
            $this->enableField => 1,
            ];
            $this->where(['id' => $this->id])->save($data);
            $this->getOneById($id);
        } catch (Exeption $e) {
            throw $e;
        }
    }
  
  /**
   * 禁用一条数据
   *
   * @throws Exception
   * @return void
   */
    public function disable()
    {
        try {
            $id = $this->id;
            $data = [
            $this->enableField => 0,
            ];
            $this->where(['id' => $this->id])->save($data);
            $this->getOneById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
