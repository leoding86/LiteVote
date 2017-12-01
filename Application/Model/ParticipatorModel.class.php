<?php
namespace Model;

class ParticipatorModel extends BaseModel
{
  protected $datetimeFields = ['vote_time'];
  
  /**
   * 重写，获得一条数据
   *
   * @param array $where
   * @return boolean
   */
  public function getOne($where)
  {
    $result = $this->where($where)->order('id desc')->find();
    return !empty($result);
  }
}