<?php
namespace Model;

class ActionLimitationModel
{
    private $id;

    private $actionName;

    private $hashKey;

    private $unlockDuration;

    private $retryTimes;

    private $actions;

    public function __construct($id, $action_name, $retry_times, $unlock_duration)
    {
        $this->id = $id;
        $this->actionName = $action_name;
        $this->retryTimes = $retry_times;
        $this->unlockDuration = $unlockDuration;
        $this->hashKey = md5($this->id . $this->actionName);
        if (!$this->actions = F($this->hashKey)) {
            $this->actions = [];
        }
    }

    public function isAllowed()
    {
        $action = [];

        if (!empty($action = $this->actions[$this->actionName])) {
            $this->initLastest();
            return true;
        }

        if ($action['retry_times'] >= $this->retryTimes && $action['lastest'] + $this->unlockDuration > NOW_TIME) {
            return false;
        }

        $this->initLastest();

        return true;
    }

    public function updateLastest()
    {
        $action = [
            'lastest'     => NOW_TIME,
            'retry_times' => 1,
        ];

        if (isset($this->actions[$this->actionName])) {
            $action['retry_times'] = $this->actions[$this->actionName]['retry_times'] + 1;
        }

        $this->actions[$this->actionName] = $action;
        F($this->hashKey, $this->actions);
    }

    public function initLastest()
    {
        $action = [
            'lastest'     => NOW_TIME,
            'retry_times' => 0,
        ];

        $this->actions[$this->actionName] = $action;
        F($this->hashKey, $this->actions);
    }
}
