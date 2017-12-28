<?php
namespace Admin\Controller;

use Think\Page;
use Model\VoteModel as Vote;
use Model\TemplateModel as Template;
use Model\LoggerModel as Logger;
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

            $logger = new Logger();
            $logger->info(
                '{username} 创建了投票 《' . $Vote->title . '》',
                [
                    'user'      => $this->admin,
                    'operation' => Logger::CREATE_OP,
                    'target'    => 'vote',
                ]
            );
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

        $this->assign('verify_options', Vote::verifyTypeOptions())
         ->assign('enable_api_options', Vote::enableApiOptions())
         ->assign('interval_options', Vote::intervalOptions())
         ->assign('post', $post)
         ->display();
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
                $logger = new Logger();
                $logger->info(
                    '{username} 编辑了投票 《' . $Vote->title . '》',
                    [
                        'user'      => $this->admin,
                        'operation' => Logger::UPDATE_OP,
                        'target'    => 'vote',
                    ]
                );
            }
        } else {
            $post = $Vote->data();
        }

        $this->assign('verify_options', Vote::verifyTypeOptions())
         ->assign('enable_api_options', Vote::enableApiOptions())
         ->assign('interval_options', Vote::intervalOptions())
         ->assign('template_type_options', Vote::templateTypeOptions())
         ->assign('post', $post)
         ->assign('vote', $Vote->data())
         ->display();
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

        $logger = new Logger();
        $logger->info(
            '{username} 启用了投票 《' . $Vote->title . '》',
            [
                'user'      => $this->admin,
                'operation' => Logger::ENABLE_OP,
                'target'    => 'vote',
            ]
        );

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

        $logger = new Logger();
        $logger->info(
            '{username} 禁用了投票 《' . $Vote->title . '》',
            [
                'user'      => $this->admin,
                'operation' => Logger::ENABLE_OP,
                'target'    => 'vote',
            ]
        );

        $this->assign('vote', $Vote->data())->display();
    }

  /**
   * 投票模板编辑器
   *
   * @return void
   */
    public function template()
    {
        $id = $this->getInputVar('id');
        $vote = new Vote();

        if (!$vote->getOneById($id)) {
            $this->error('指定投票不存在');
        }

        $template = new Template('vote');

        if (IS_POST) {
            $template->addOne('vote_' . $vote->id, $_POST['template']);
            $logger = new Logger();
            $logger->info(
                '{username} 编辑了投票 《' . $vote->title . '》 的模板',
                [
                    'user'      => $this->admin,
                    'operation' => Logger::UPDATE_OP,
                    'target'    => 'vote',
                ]
            );
            $this->ajaxResponse->returnOk();
            return;
        }

        $template_content = $template->getOne('vote_' . $vote->id);

        if (!$template_content) {
            $template_content = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{$vote['title']}</title>
</head>
<body>
    <div>投票标题：{$vote['title']}</div>
    <div>投票说明：{$vote['summary']}</div>
    <div>投票帮助：{$vote['help']}</div>
    <div>开始时间：{$vote['start_time']->format('Y-m-d H:i:s')}</div>
    <div>结束时间：{$vote['end_time']->format('Y-m-d H:i:s')}</div>
    <div>投票间隔：{$vote['interval']} [1：仅一次；2：每日一次]</div>
    <div>选择上限：{$vote['select_max_limits']}</div>
    <div>选择下限：{$vote['select_min_limits']}</div>
    <div>验证方式：{$vote['verify_type']} [1：手机；2：微信]</div>
    
    <div>
        <form action="__SELF__" method="POST">
        <volist name="vote_items_dataset" id="vote_item">
            <div>投票项目标题：{$vote_item['title']}</div>
            <div>投票图片链接：{:C('UPLOAD_BASE')}{$vote_item['thumb']}</div>
            <div>投票项目unid：{$vote_item['unid']}</div>
            <div>勾选框：<input type="checkbox" name="vote_item_unids[]" value="{$vote_item['unid']}"></div>
        </volist>
            <div>投票unid：<input type="hidden" name="unid" value="{$vote['unid']}"></div>

            <div>如果是手机验证，添加手机验证模板</div>
            <if condition="$vote['verify_type'] eq 1">
                <include file="Partial/mobile_verify" />
            </if>

            <button type="submit">提交</button>
        </form>
    </div>
</body>
</html>
HTML;
        }

        $this->assign('template', $template_content);
        $this->display();
    }
}
