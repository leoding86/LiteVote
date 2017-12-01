<?php
defined('IN_PHPCMS') or exit('No permission resources.');

class Core {
    private $classDir;

    public function __construct() {
        /* 获得自动加载路径 */
        $this->classDir = dirname(dirname(__FILE__)) . '/classes/';
        /* 载入函数包 */
        pc_base::load_app_func('functions', 'sms');
        /* 注册自动加载 */
        spl_autoload_register(array($this, 'loadClass'));
    }

    private function loadClass($classname) {
        $classPath = $this->classDir . $classname . '.class.php';
        if (!is_file($classPath)) {
            throw new Exception("Class {$classname} is not exists!", 1);
        }
        else {
            include_once($classPath);
        }
    }
}