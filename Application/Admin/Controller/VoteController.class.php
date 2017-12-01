<?php
namespace Admin\Controller;

use Think\Page;
use Model\VoteModel as Vote;
use DateTime;

class VoteController extends EntryController
{
  /**
   * 构造方法
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * 投票管理列表
   *
   * [permit = Vote/index; permitDescription = 投票列表]
   * @return void
   */
  public function index()
  {
    $Vote = new Vote();
    $count = $Vote->count();
    $Page = new Page($count, 10);
    $list = $Vote->getList([], $Page);

    $this->assign('list', $list);
    $this->assign('page', $Page->show());
    $this->assign('page_title', '投票列表');
    $this->display();
  }

  /**
   * 创建投票
   *
   * [permit = Vote/create; permitDescription = 创建投票]
   * @return void
   */
  public function create()
  {
    if (IS_POST) {
      $post = I('POST.');
      $post['start_time'] = new DateTime($post['start_time']);
      $post['end_time'] = new DateTime($post['end_time']);
      $Vote = new Vote();
      $Vote->data($post);
      $Vote->admin_id = $this->admin->id;

      if (!$Vote->addOne()) {
        $this->assign('error', $Vote->getError());
      }

      $this->redirect('Vote/index');
    } else {
      $post = [];
    }

    if (!$post['start_time']) {
      $post['start_time'] = new DateTime();
    }

    if (!$post['end_time']) {
      $post['end_time'] = new DateTime();
    }

    $this->assign('verify_options', [
      [Vote::VERIFY_MOBILE, '手机'],
      [Vote::VERIFY_WECHAT, '微信']
    ])->assign('enable_api_options', [
      [0, '不启用'],
      [1, '启用'],
    ])->assign('interval_options', [
      [Vote::ONCE_INTERVAL_TYPE, '仅一次'],
      [Vote::DALIY_INTERVAL_TYPE, '每日一次'],
    ])->assign('post', $post)->display();
  }

  /**
   * 编辑投票
   *
   * [permit = Vote/edit; permitDescription = 编辑投票]
   * @return void
   */
  public function edit()
  {
    $id = I('POST.id', null) === null ? I('GET.id/d') : I('POST.id/d');
    $Vote = new Vote();
    
    if (!$Vote->getOneById($id)) {
      $this->error('指定的投票不存在');
    }

    if (IS_POST) {
      $post = I('POST.');
      $post['start_time'] = new DateTime($post['start_time']);
      $post['end_time'] = new DateTime($post['end_time']);
      $Vote->data($post);

      if (!$Vote->updateOne()) {
        $this->assign('error', $Vote->getError());
      } else {
        $this->assign('success', '编辑投票完成');
      }
    } else {
      $post = $Vote->data();
    }

    $this->assign('verify_options', [
      [Vote::VERIFY_MOBILE, '手机'],
      [Vote::VERIFY_WECHAT, '微信']
    ])->assign('enable_api_options', [
      [0, '不启用'],
      [1, '启用'],
    ])->assign('interval_options', [
      [Vote::ONCE_INTERVAL_TYPE, '仅一次'],
      [Vote::DALIY_INTERVAL_TYPE, '每日一次'],
    ])->assign('post', $post)->assign('vote', $Vote->data())->display();
  }

  /**
   * 删除投票
   *
   * [permit = Vote/delete; permitDescription = 删除投票]
   * @return void
   */
  public function delete()
  {
    $this->display();
  }

  /**
   * 启用投票
   *
   * @return void
   */
  public function enable()
  {
    $id = $this->getInputVar('id');
    $Vote = new Vote();

    if (!$Vote->getOneById($id)) {
      $this->error('指定投票不存在');
    }

    $Vote->enable();
    $this->assign('vote', $Vote->data())->display();
  }

  /**
   * 禁用投票
   *
   * @return void
   */
  public function disable()
  {
    $id = $this->getInputVar('id');
    $Vote = new Vote();

    if (!$Vote->getOneById($id)) {
      $this->error('指定投票不存在');
    }

    $Vote->disable();
    $this->assign('vote', $Vote->data())->display();
  }
}