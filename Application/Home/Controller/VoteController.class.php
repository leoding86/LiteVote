<?php
namespace Home\Controller;

use Model\VoteModel as Vote;
use Model\VoteItemModel as VoteItem;
use Model\ParticipatorModel as Participator;
use Model\VoteLogModel as VoteLog;
use Vendor\Sms\Api as Sms;
use Think\Verify;

class VoteController extends EntryController
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * 投票页面
   *
   * @return void
   */
  public function index()
  {
    $unid = I('GET.unid/s');
    $Vote = new Vote();
    
    if (!$Vote->getOneByUnid($unid)) {
      $this->error('指定的投票不存在');
    }

    $vote_items_list = $Vote->getVoteItemsList();

    $this->assign('verify', new Verify());
    $this->assign('vote', $Vote->data());
    $this->assign('vote_items_list', $vote_items_list);
    $this->assign('now', new \DateTime());
    $this->display();
  }

  /**
   * 提交投票
   *
   * @return void
   */
  public function submit()
  {
    $post = I('POST.');
    $Vote = new Vote();
    $VoteLog = new VoteLog();

    if (!$Vote->getOneByUnid((int)$post['unid'])) {
      $this->AjaxResponse->returnErr('NOT_EXISTS', '投票不存在');
    }

    $VoteLog->setVoteId($Vote->id); // 设置日志数据表

    $Participator = new Participator();

    if ($Vote->verify_type == $Vote::VERIFY_MOBILE) {
      $Sms = new Sms();

      if (!$Sms->verifyCode($post['mobile'], $post['code'], $Sms::SMS_DEFAULT_CODE_TYPE, true)) {
        $this->AjaxResponse->returnErr('SMS_CODE_ERR', '短信验证码错误');
      }

      if (!$Participator->getOneByMobile($post['mobile'])) {
        $Participator->addOne([
          'mobile' => $post['mobile'],
        ]);
      }
    } else {
      E('系统错误[INVALID_VOTE_VERIFY_TYPE:' . $Vote->verify_type . ']');
    }

    /* 如果获得日志信息，进一步检查 */
    if ($VoteLog->getOneByParticipatorId($Participator->id)) {
      switch ($Vote->interval) {
        case Vote::ONCE_INTERVAL_TYPE:
          $this->AjaxResponse->returnErr('HAD_VOTED', '已经投票过票了');
          break;
        case Vote::DALIY_INTERVAL_TYPE:
          $VoteLog->vote_time->setTime(23, 59, 59);
          if ($VoteLog->vote_time->getTimestamp() > NOW_TIME) {
            $this->AjaxResponse->returnErr('HAD_VOTED_TODAY', '今天已经投过票了');
          }
          break;
      }
    }

    $vote_item_unids = array_unique($post['vote_item_unids']); // 投票项目去重

    $VoteItem = new VoteItem();
    $VoteItem->getList(['unid' => ['in', $vote_item_unids]]); // 获得投票项目数据列表
    
    /* 检查投票项目是否有效 */
    if (!$Vote->checkVoteItems($VoteItem->list)) {
      $this->AjaxResponse->returnErr('INVALID_SUBMIT', $Vote->getError());
    }

    M()->startTrans();

    $VoteItem->increaseVoteItemsVote(); // 增加票数

    $vote_item_ids = $vote_item_titles = [];
    foreach ($VoteItem->list as $vote_item) {
      $vote_item_ids[] = $vote_item['id'];
      $vote_item_titles[] = $vote_item['title'];
    }

    /* 添加日志记录 */
    $VoteLog->data([
      'participator_id' => $Participator->id,
      'vote_item_ids'   => serialize($vote_item_ids),
      'vote_item_titles' => serialize($vote_item_titles),
      'vote_time'        => new \DateTime(),
    ])->addOne();

    M()->commit();

    $this->AjaxResponse->returnOk();
  }
}