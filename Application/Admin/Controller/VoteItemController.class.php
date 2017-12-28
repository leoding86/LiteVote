<?php
namespace Admin\Controller;

use Model\VoteModel as Vote;
use Model\VoteItemModel as VoteItem;
use Model\LoggerModel as Logger;
use Think\Page;

class VoteItemController extends EntryController
{
    public function __construct()
    {
        parent::__construct();
    }

  /**
   * 投票项目列表
   *
   * [permit = VoteItem/index; permitDescription = 投票项目列表]
   * @return void
   */
    public function index()
    {
        $vote_id = (int)$this->getInputVar('vote_id');
        $Vote = new Vote();

        if (!$Vote->getOneById($vote_id)) {
            $this->error('指定的投票不存在');
        }

        $VoteItem = new VoteItem();
        $count = $VoteItem->where(['delete_flg' => 0])->count();
        $Page = new Page($count, 10);
        $list = $VoteItem->getListByVote($Vote);

        $this->assign('list', $list);
        $this->assign('vote', $Vote->data());
        $this->assign('page', $Page->show());
        $this->assign('page_title', '投票项目');

        $this->display();
    }

  /**
   * 创建投票项目
   *
   * [permit = VoteItem/create; premitDescription = 创建投票项目]
   * @return void
   */
    public function create()
    {
        $vote_id = (int)$this->getInputVar('vote_id');
        $Vote = new Vote();

        if (!$Vote->getOneById($vote_id)) {
            $this->error('指定的投票不存在');
        }

        if (IS_POST) {
            $post = I('POST.');
            $VoteItem = new VoteItem();
            $VoteItem->data($post);
            $VoteItem->admin_id = $this->admin->id;

            if (!$VoteItem->addOne()) {
                $this->assign('error', $VoteItem->getError());
            } else {
                $Logger = new Logger();
                $Logger->info(
                    $this->admin->username . ' 创建了投票 ' . $Vote->title . ' 的项目 ' . $VoteItem->title,
                    [
                        'user'      => $this->admin,
                        'operation' => Logger::ADD_OP,
                        'target'    => 'vote_item',
                    ]
                );
                $this->redirect('VoteItem/index', ['vote_id' => $Vote->id]);
            }
        } else {
            $post = [];
        }

        $this->assign('post', $post);
        $this->assign('vote', $Vote->data());
        $this->assign('content_type_options', [
        [VoteItem::CONTENT_TYPE_BODY, '内容'],
        [VoteItem::CONTENT_TYPE_LINK, '链接'],
        ]);
        $this->display();
    }

  /**
   * 编辑投票项目
   *
   * [permit = VoteItem/edit; premitDescription = 编辑投票项目]
   * @return void
   */
    public function edit()
    {
        $id = (int)$this->getInputVar('id');
        $VoteItem = new VoteItem();

        if (!$VoteItem->getOneById($id)) {
            $this->error('指定的投票项目不存在');
        }

        try {
            $Vote = $VoteItem->getVote();
        } catch (\Exception $e) {
            $this->error('投票项目所属的投票不存在');
        }
    
        if (IS_POST) {
            $post = I('POST.');
            $VoteItem->data($post);

            if (!$VoteItem->updateOne()) {
                $this->assign('error', $VoteItem->getError());
            } else {
                $Logger = new Logger();
                $Logger->info(
                    $this->admin->username . ' 编辑了投票 ' . $Vote->title . ' 的项目 ' . $VoteItem->title,
                    [
                        'user'      => $this->admin,
                        'operation' => Logger::UPDATE_OP,
                        'target'    => 'vote_item',
                    ]
                );
                redirect($this->referer);
            }
        } else {
            $post = $VoteItem->data();
        }

        if (!empty($post['thumb'])) {
            $thumb = C('UPLOAD_BASE') . $post['thumb'];
        } else {
            $thumb = __PUBLIC__ . '/lib/fineuploader/edit.gif';
        }
    
        $this->assign('post', $post);
        $this->assign('thumb', $thumb);
        $this->assign('vote', $Vote->data());
        $this->assign('content_type_options', [
            [VoteItem::CONTENT_TYPE_BODY, '内容'],
            [VoteItem::CONTENT_TYPE_LINK, '链接'],
        ]);
        $this->display();
    }
  
  /**
   * 删除投票项目
   *
   * [permit = VoteItem/delete; premitDescription = 删除投票项目]
   * @return void
   */
    public function delete()
    {
        $id = (int)$this->getInputVar('id');
        $VoteItem = new VoteItem();

        if (!$VoteItem->getOneById($id)) {
            $this->error('指定的投票不存在');
        }

        try {
            $Vote = $VoteItem->getVote();
            $Logger = new Logger();
            $Logger->info(
                $this->admin->username . ' 删除了投票 ' . $Vote->title . ' 的项目 ' . $VoteItem->title,
                [
                    'user'      => $this->admin,
                    'operation' => Logger::DELETE_OP,
                    'target'    => 'vote_item',
                ]
            );
        } catch (\Exception $e) {
            $this->error('投票项目所属的投票不存在');
        }

        $VoteItem->deleteOne();
        redirect($this->referer);
    }
}
