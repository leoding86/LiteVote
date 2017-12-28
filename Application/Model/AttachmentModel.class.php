<?php
namespace Model;

use Ramsey\Uuid\Uuid;
use Think\Upload;

class AttachmentModel extends BaseModel
{
  /**
   * 验证设置
   *
   * @var array
   */
    protected $_validate = [
    ['title', 'require', '附件标题不能为空', self::MUST_VALIDATE, 'regex'],
    ['title', '1,30', '附件标题不能超过30字', self::MUST_VALIDATE, 'length'],
    ];

  /**
   * 自动完成设置
   *
   * @var array
   */
    protected $_auto = [
    ['create_time', NOW_TIME, self::MODEL_INSERT],
    ];

    protected $datetimeFields = ['create_time'];

  /**
   * 保存上传文件
   *
   * @param string $file
   * @throws Think\Exception
   * @return void
   */
    public function upload($file = 'file')
    {
        $unid = str_replace('-', '', (string)Uuid::uuid4());
        $Upload = new Upload(C('TRADITIONAL_UPLOAD'));
        $Upload->saveName = $unid;

        if (!$upload_result = $Upload->uploadOne($_FILES[$file])) {
            E($Upload->getError());
        }

        $this->data([
        'title' => mb_substr($upload_result['name'], 0, 30),
        'unid'  => $unid,
        'path'  => $upload_result['savepath'] . $upload_result['savename'],
        ]);

        if (!$this->addOne()) {
            E($this->getError());
        }
    }
}
