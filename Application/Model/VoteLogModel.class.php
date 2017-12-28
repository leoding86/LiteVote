<?php
namespace Model;

use Think\Exception;

class VoteLogModel extends BaseModel
{
    protected $autoCheckFields = false;

    protected $datetimeFields = ['vote_time'];

    private function buildTableName($vote_id)
    {
        $vote_id = (int)$vote_id;
        return "{$this->tablePrefix}vote_log_{$vote_id}";
    }

  /**
   * 检查调查记录表是否存在
   *
   * @param string $table
   * @return boolean
   */
    private function tableExists($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->query($sql);

        if ($result === false) {
            E('UNKOWN_ERROR');
        } else {
            return $result;
        }
    }

  /**
   * 指定调查记录是否存在
   *
   * @param int $vote_id
   * @return boolean
   */
    public function isRecordExists($vote_id)
    {
        $table = $this->buildTableName($vote_id);
        return $this->tableExists($table);
    }

  /**
   * 创建投票日志表
   *
   * @param int $vote_id
   * @throws Exception
   * @return void
   */
    public function createTable($vote_id)
    {
        $table = $this->buildTableName($vote_id);

        if ($this->tableExists($table)) {
            E('TABLE_EXISTS');
        } else {
            $sql = "CREATE TABLE `{$table}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `participator_id` INT(11) NOT NULL,
        `vote_item_ids` TEXT NOT NULL,
        `vote_item_titles` TEXT NOT NULL,
        `vote_time` INT(11) NOT NULL,
        PRIMARY KEY (`id`));";
        
            $this->execute($sql);
        }
    }

    public function archiveTable($vote_id)
    {
        $table = $this->buildTableName($vote_id);
        $rename_table = $table . '_archive_' . time();

        if ($this->tableExists($table)) {
            $sql = "ALTER TABLE `{$table}`
      RENAME TO  `{$rename_table}` ;";

            try {
                $this->execute($sql);
                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        } else {
            $this->error = '没有找到对应的记录表';
            return false;
        }
    }

  /**
   * 设置投票ID并设置对应的数据表
   *
   * @param int $vote_id
   * @return void
   */
    public function setVoteId($vote_id)
    {
        $table = $this->buildTableName($vote_id);

        if (!$this->tableExists($table)) {
            E('TABLE_NOT_EXISTS');
        }

        $this->trueTableName = $table;
    }
}
