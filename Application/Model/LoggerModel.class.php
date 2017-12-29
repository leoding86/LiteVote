<?php namespace Model;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use StringTemplate\Engine as StringTemplate;
use Exceptions\NotImplementedException;
use Exceptions\ModelCURDException;
use Exceptions\ModelCreateDataException;

class LoggerModel extends BaseModel implements LoggerInterface
{
    /**
     * 创建操作
     */
    const ADD_OP = 1;

    /**
     * 编辑操作
     */
    const UPDATE_OP = 2;

    /**
     * 读取操作
     */
    const READ_OP = 3;

    /**
     * 删除操作
     */
    const DELETE_OP = 4;

    /**
     * 启用操作
     */
    const ENABLE_OP = 5;

    /**
     * 禁用操作
     */
    const DISABLE_OP = 6;

    /**
     * 设置时间戳字段
     *
     * @var array
     */
    protected $datetimeFields = [
        'create_time',
    ];

    /**
     * 禁用enable字段
     *
     * @var string
     */
    protected $enableField = null;

    /**
     * 禁用 delete 字段
     *
     * @var string
     */
    protected $deleteFlagField = null;

    /**
     * 数据表
     *
     * @var string
     */
    protected $tableName = null;

    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        parent::__construct($name, $tablePrefix, $connection);
        $this->setTable();
    }

    /**
     * 设置指定的数据表
     *
     * @param string $table
     * @return void
     */
    public function setTable($table = null)
    {
        $this->tableName = is_null($table) ? 'logger' : $table;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        throw new NotImplementedException();
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws ModelCreateDataException
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $string_template = new StringTemplate;
        $data = [
            'user_id'   => $context['user']->id,
            'username'  => $context['user']->username,
            'operation' => strtolower($context['operation']),
            'target'    => $context['target'],
            'create_time'   => NOW_TIME,
        ];
        $data['content'] = $string_template->render($message, $data);

        if (!$this->create($data)) {
            throw new ModelCreateDataException($this->getError());
        }

        try {
            $this->add();
        } catch (\Exception $e) {
            throw new ModelCURDException($this->_sql());
        }
    }
}
