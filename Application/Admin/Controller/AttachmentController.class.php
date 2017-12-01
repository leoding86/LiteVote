<?php
namespace Admin\Controller;

use Model\AttachmentModel as Attachment;
use Think\Exception;
use Think\Page;

class AttachmentController extends EntryController
{
  private function responseOk($data = [])
  {
    header("Content-Type: application/json");
    exit(json_encode(['success' => true, 'data' => $data]));
  }

  protected function responseErr($message = 'unkown_error')
  {
    header("Content-Type: application/json");
    exit(json_encode(['success' => false, 'error' => $message]));
  }

  /**
   * 附件列表
   *
   * [permit = Attachment/index; permitDescription = 附件列表]
   * @return void
   */
  public function index()
  {
    $Attachment = new Attachment();
    $count = $Attachment->count();
    $Page = new Page($count, 12);
    $list = $Attachment->getList([], $Page);

    var_dump($Page->show());
    $this->assign('page', $Page->show());
    $this->assign('list', $list);
    $this->display();
  }

  /**
   * 上传附件
   *
   * [permit = Attachment/upload; permitDescription = 上传附件]
   * @return void
   */
  public function upload()
  {
    $Attachment = new Attachment();

    try {
      $Attachment->upload();
      $data = $Attachment->data();
      $data['create_time'] = $Attachment->create_time->format('Y-m-d H:i:s');
      $this->responseOk($data);
    } catch (Exception $e) {
      $this->responseErr($e->getMessage());
    }
  }

  /**
   * 删除附件
   *
   * [permit = Attachment/delete; permitDescription = 删除附件]
   * @return void
   */
  public function delete()
  {

  }
}