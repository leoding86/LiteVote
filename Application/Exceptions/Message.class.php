<?php namespace Exceptions;

use Exceptions\Code;

class Message
{
    public static function string($code)
    {
        switch ($code) {
            case Code::NOT_IMPLEMENTED_EXCEPTION:
                return '此方法未实现';

            case Code::MODEL_CURD_EXCEPTION:
                return '操作模型出错';

            case Code::MODEL_CREATE_DATA_EXCEPTION:
                return '创建模型数据出错';

            case Code::UNKOWN_EXCEPTION:
            default:
                return '未知错误';
        }
    }
}
