<?php
namespace Model;

class SmsModel extends \Think\Model
{

    public function error()
    {
        return $this->_error;
    }

    public function searchForm()
    {
        return $this->_searchForm;
    }

    /**
     * 添加日志信息
     *
     * @param string $sms_id
     * @param string $mobile
     * @param string $type
     * @param string $content
     * @param string $replacements
     * @param string $code
     * @param boolean $status
     * @param string $platform
     * @return void
     */
    public function add(
        $sms_id,
        $mobile,
        $type,
        $content,
        $replacements,
        $status,
        $code = '',
        $used = false,
        $platform = ''
    ) {
        parent::add([
        'smsid'     => $sms_id,
        'mobile'    => $mobile,
        'type'      => $type,
        'content'   => $content,
        'code'      => $code,
        'replacements' => $replacements,
        'is_sended' => $status ? 1 : 0,
        'is_used'   => $used ? 1 : 0,
        'create_time' => time(),
        ]);
    }

    public function verifyCode($time_in, $mobile = null, $code = null, $type = null, $is_use = true)
    {
        $mobile = preg_match('/^1\d{10}$/', $mobile) ? $mobile : '';
        $code   = preg_match('/^\d{4,8}$/', $code) ? $code : '';
        $type   = preg_match('/^[a-z_]+$/i', $type) ? $type : '';
        if (empty($type) or empty($mobile) or empty($code)) {
            $this->_error = '错误的参数';
            return false;
        } else {
            $where = sprintf(
                "`type`='%s' AND `mobile`='%s' AND `code`='%s' AND `create_time`<%d AND `is_sended`=%d AND `is_used`=%d",
                $type,
                $mobile,
                $code,
                $time_in,
                1,
                0
            );
            $order = "`id` DESC";
            $result = $this->where($where)->order($order)->find();
            if (!empty($result)) {
                $is_use ? $this->setUsed($result['id']) : '';
                return true;
            } else {
                $this->_error = '验证码错误';
                return false;
            }
        }
    }

    public function lists()
    {
        throw new \Exception('not implemented');
    }

    private function setUsed($id)
    {
        $data = array('is_used' => 1);
        $where = sprintf("`id` = %d", $id);
        $this->where($where)->data($data)->save();
    }

    /**
     * 检查是否达到发送上线
     * @param  string  $mobile 手机号码
     * @param  string  $limit  限制次数
     * @param  string  $type   检查类型
     * @return boolean
     */
    public function reachSendLimit($mobile, $limit, $type = null)
    {
        $datetime = new \DateTime();
        $datetime->setTime(0, 0, 0);

        if ($type) {
            $where = sprintf(
                "`mobile` = '%s' AND `type` = '%s' AND `create_time` > '%s'",
                $mobile,
                $type,
                $datetime->getTimestamp()
            );
        } else {
            $where = sprintf(
                "`mobile` = '%s' AND `create_time` > '%s'",
                $mobile,
                $datetime->getTimestamp()
            );
        }
        return $this->where($where)->count() > $limit;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function get_one($where)
    {
        return $this->where($where)->find();
    }
}
