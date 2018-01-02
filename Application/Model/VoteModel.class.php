<?php
namespace Model;

use Model\VoteLogModel as VoteLog;
use Model\VoteItemModel as VoteItem;
use Model\TemplateModel as Template;
use Ramsey\Uuid\Uuid;
use Think\Exception;

class VoteModel extends BaseModel
{
  /**
   * 验证方式手机
   */
    const VERIFY_MOBILE = 1;

  /**
   * 验证方式微信
   */
    const VERIFY_WECHAT = 2;

  /**
   * 一次性投票
   */
    const ONCE_INTERVAL_TYPE = 1;

  /**
   * 每日投票
   */
    const DALIY_INTERVAL_TYPE = 2;

  /**
   * 默认模板类型
   */
    const DEFAULT_TEMPLATE_TYPE = 1;

  /**
   * 自定义模板类型
   */
    const CUSTOM_TEMPLATE_TYPE = 2;

  /**
   * 重写，没有删除标识符
   *
   * @var null
   */
    protected $deleteFlagField = null;
  
  /**
   * 重写，可写日期字段
   */
    protected $datetimeFields = ['start_time', 'end_time', 'create_time', 'update_time'];

    /**
     * 模板模型
     *
     * @var Template
     */
    protected static $template = null;

  /**
   * 重写，自动验证设置
   *
   * @var array
   */
    protected $_validate = [
    ['title', 'require', '投票标题不能为空', self::MUST_VALIDATE, 'regex'],
    ['title', '1, 30', '投票标题长度不能超过30字', self::MUST_VALIDATE, 'length'],
    ['select_max_limits', '/^[1-9]\d*$/', '投票选择上限需要大于0的数字', self::MUST_VALIDATE, 'regex'],
    ['select_min_limits', '/^[1-9]\d*$/', '投票选择下限需要大于0的数字', self::MUST_VALIDATE, 'regex'],
    ['verify_type', [self::VERIFY_MOBILE, self::VERIFY_WECHAT], '验证方式出错', self::MUST_VALIDATE, 'in'],
    ['enable', '0,1', '启用选项出错', self::EXISTS_VALIDATE, 'in'],
    ['enable_api', '0,1', '启用API选项出错', self::MUST_VALIDATE, 'in'],
    ['interval', [self::ONCE_INTERVAL_TYPE, self::DALIY_INTERVAL_TYPE], '投票间隔出错', self::MUST_VALIDATE, 'in'],
    ['template_type', [self::DEFAULT_TEMPLATE_TYPE, self::CUSTOM_TEMPLATE_TYPE], '模板类型出错', self::MUST_VALIDATE, 'in'],
    ];

  /**
   * 重写，自动完成设置
   *
   * @var array
   */
    protected $_auto = [
    ['enable', 0, self::MODEL_INSERT],
    ['create_time', NOW_TIME, self::MODEL_INSERT],
    ['update_time', NOW_TIME, self::MODEL_BOTH],
    ];

  /**
   * 投票的项目
   *
   * @var array
   */
    public $votedVoteItemsList = [];

  /**
   * 创建数据后执行方法，验证数据逻辑
   *
   * @param array $data 创建数据后的数据
   * @param int $moment
   * @return boolean
   */
    protected function afterCreate(&$data, $moment)
    {
        if ($data['start_time'] >= $data['end_time']) {
            $this->error = '结束时间需要晚于开始时间';
            return false;
        }

        if ($data['select_max_limits'] < $data['select_min_limits']) {
            $this->error = '选择上限不能小于选择下限';
        }

        if (!empty($data['enable_domains'])) {
            $domains = preg_split('/\r\n|[\r\n]/', $data['enable_domains']);
            foreach ($domains as $domain) {
                if (!preg_match('/^[a-z]+[a-z\d_\-]*(?:\.[a-z]+[a-z\d_\-]*)+$/i', $domain)) {
                    $this->error = '来源域名存在无效的域名[' . htmlspecialchars($domain) . ']';
                    return false;
                }
            }
        }

        if ($moment == self::MODEL_INSERT) {
            $data['unid'] = str_replace('-', '', Uuid::uuid4());
        }

        return true;
    }

  /**
   * 重写，添加数据方法
   *
   * @return boolean
   */
    public function addOne()
    {
        if (!parent::addOne()) {
            return false;
        }

        $VoteLog = new VoteLog();

        try {
            $VoteLog->createTable($this->id); // 创建日志表
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

  /**
   * 不实现删除投票
   *
   * @return void
   */
    public function deleteOne()
    {
        E('不能删除投票');
    }

  /**
   * 获得当前投票的项目
   *
   * @return array
   */
    public function getVoteItemsList()
    {
        $where = [
        'vote_id' => ['eq', $this->id],
        ];
        $VoteItem = new VoteItem();
        return $VoteItem->getList($where);
    }

  /**
   * 检查调查是否拥有前台提交的所有项目
   *
   * @param array $vote_items
   * @return boolean
   */
    public function ownVoteItems(array $vote_items)
    {
        foreach ($vote_items as $vote_item) {
            if ($vote_item['vote_id'] != $this->id) {
                return false;
            }
        }
        return true;
    }

  /**
   * 检查投票项目，调用前先去重
   *
   * @param array $vote_items
   * @return boolean
   */
    public function checkVoteItems(array $vote_items)
    {
        $domains = C('DOMAINS');
        if ($this->enable_api == 1) {
            $append_domains = preg_split('/\r\n|[\r\n]/', $this->enable_domains);
            $domains = array_merge($domains, $append_domains);
        }

        if (!in_array(FROM_DOMAIN, $domains)) {
            $this->error = '提交来源出错';
            return false;
        }

        if (!$this->ownVoteItems($vote_items)) {
            $this->error = '提交的项目出错';
            return false;
        }

        $count = count($vote_items);

        if ($count < $this->select_min_limits) {
            $this->error = '至少需要选择' . $this->select_min_limits . '项目';
            return false;
        }

        if ($count > $this->select_max_limits) {
            $this->error = '最多需要选择' . $this->select_max_limits . '项目';
            return false;
        }

        return true;
    }

  /**
   * 获得模板内容
   *
   * @return string
   */
    public function getTemplateFile()
    {
        if (is_null($this->template)) {
            $this->template = new Template('vote');
        }

        if (!$template_file = $this->template->getFile('vote_' . $this->id)) {
            $template_file = $this->template->getFile('vote_default');
        }

        return $template_file;
    }

    public static function templateTypeOptions()
    {
        return [
          [self::DEFAULT_TEMPLATE_TYPE, '默认模板'],
          [self::CUSTOM_TEMPLATE_TYPE, '自定义模板']
        ];
    }

    public static function verifyTypeOptions()
    {
        return [
          [self::VERIFY_MOBILE, '手机号'],
          [self::VERIFY_WECHAT, '微信'],
        ];
    }

    public static function intervalOptions()
    {
        return [
          [self::ONCE_INTERVAL_TYPE, '仅一次'],
          [self::DALIY_INTERVAL_TYPE, '每日一次'],
        ];
    }

    public static function enableApiOptions()
    {
        return [
          [0, '不启用'],
          [1, '启用'],
        ];
    }
}
