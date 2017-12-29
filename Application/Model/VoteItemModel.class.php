<?php
namespace Model;

use Ramsey\Uuid\Uuid;
use Model\VoteModel as Vote;
use Exceptions\ModelCURDException;

class VoteItemModel extends BaseModel
{
    const CONTENT_TYPE_BODY = 1;
    const CONTENT_TYPE_LINK = 2;

  /**
   * 可写日期字段
   */
    protected $datetimeFields = ['create_time', 'update_time'];

    /**
     * 排序字段
     *
     * @var string
     */
    protected $orderField = 'order';

  /**
   * 自动验证设置
   *
   * @var array
   */
    protected $_validate = [
        ['title', 'require', '投票标题不能为空', self::MUST_VALIDATE, 'regex'],
        ['title', '1, 30', '投票标题长度不能超过30字', self::MUST_VALIDATE, 'length'],
        ['content_type', [self::CONTENT_TYPE_BODY, self::CONTENT_TYPE_LINK], '项目内容类型出错', self::MUST_VALIDATE, 'in'],
        ['thumb', '/[a-z\d]\.(?:jpe?g|png|gif)$/', '封面链接格式出错', self::VALUE_VALIDATE, 'regex'],
        ['redirect_url', '/^https?:\/\/[a-z\d]+(?:\.[a-z\d])+/', '跳转链接格式出错', self::VALUE_VALIDATE, 'regex'],
    ];

  /**
   * 自动完成设置
   *
   * @var array
   */
    protected $_auto = [
    ['delete_flg', 0, self::MODEL_INSERT],
    ['create_time', NOW_TIME, self::MODEL_INSERT],
    ['update_time', NOW_TIME, self::MODEL_BOTH],
    ];

  /**
   * 创建数据后执行方法
   *
   * @param array $data 创建数据后的数据
   * @param int $moment
   * @return boolean
   */
    protected function afterCreate(&$data, $moment)
    {
        if ($moment == self::MODEL_INSERT) {
            $data['unid'] = str_replace('-', '', Uuid::uuid4());
        }

        return true;
    }

  /**
   * 获得指定投票的投票项目集合
   *
   * @param Vote $vote
   * @param Page $page
   * @return void
   */
    public function getListByVote(Vote $Vote, Page $Page)
    {
        $where = [
        'delete_flg' => ['eq', 0],
        'vote_id'    => $Vote->id
        ];

        return $this->getList($where, $Page);
    }

  /**
   * 获得当前项目所属的投票
   *
   * @throws Think\Exception
   * @return Vote
   */
    public function getVote()
    {
        $Vote = new Vote();
    
        if (!$Vote->getOneById($this->vote_id)) {
            E('没有找到所属的投票');
        }

        return $Vote;
    }

  /**
   * 增加票数
   *
   * @return void
   */
    public function increaseVoteItemsVote()
    {
        if (!empty($this->list)) {
            $vote_item_ids = [];

            foreach ($this->list as $vote_item) {
                $vote_item_ids[] = $vote_item['id'];
            }

            $where = [
            'id' => ['in', $vote_item_ids],
            ];

            $this->where($where)->setInc('votes');
        }
    }

    /**
     * 排序
     *
     * @return void
     */
    public function sort($sorted_list, Vote $vote)
    {
        if (empty($sorted_list)) {
            return;
        }

        $sort_index = count($sorted_list);
        foreach ($sorted_list as $vote_item_id) {
            $where = [
                'vote_id' => ['eq', $vote->id],
                'id'      => ['eq', $vote_item_id],
            ];
            $data = [
                'order' => $sort_index,
            ];
            try {
                $this->data($data)->where($where)->save();
                $sort_index--;
            } catch (\Exception $e) {
                throw new ModelCURDException($e->getMessage());
            }
        }

        return;
    }
}
