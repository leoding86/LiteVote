<?php
namespace Model;

use Exception;

class TemplateModel
{
    private $namespace;

    private $templateRoot;

    private $prefix;

    private $ext = '.tpl';

    /**
     * 构造方法
     *
     * @param string $namespace 模板保存空间
     */
    public function __construct($namespace, $prefix = '')
    {
        $this->setNamespace($namespace);
        $this->setPrefix($prefix);
        $this->templateRoot = APP_PATH . '../Template/' . $this->namespace . '/';
    }

    private function buildFilename($name)
    {
        return $this->templateRoot . $this->prefix . $name . $this->ext;
    }

    /**
     * 设置模板保存空间
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        if (!preg_match('/^[a-z]+$/i', $namespace)) {
            throw new Exception('INVALID_TEMPLATE_NAMESPACE');
        }

        $this->namespace = strtolower($namespace);
    }

    public function setPrefix($prefix)
    {
        if (empty($prefix)) {
            $this->prefix = '';
            return;
        }

        if (!preg_match('/^[a-z]+$/i', $prefix)) {
            throw new Exception('INVALID_TEMPLATE_PREFIX');
        }

        $this->prefix = strtolower($prefix);
    }

    /**
     * 新增模板
     *
     * @param string $name
     * @param string $content
     * @throws Execption
     * @return void
     */
    public function addOne($name, $content)
    {
        if (!preg_match('/^[a-z\d_\-]+$/', $name)) {
            throw new Exception('TEMPLATE_NAME CAN ONLY INCLUDE [A-Z, 0-9, _, 0]');
        }

        if (empty($content)) {
            $this->deleteOne($name);
            return;
        }

        $content = preg_replace(
            [
                '/<\?php.*\?>/i',
                '/<\?=.*\?>/i',
                '/<\?php/i',
            ],
            '',
            $content
        );

        if (!is_dir($this->templateRoot) && !mkdir($this->templateRoot)) {
            throw new Exception('CANNOT_CREATE_TEMPLATE_ROOT');
        }

        $file = fopen($this->buildFilename($name), 'w');
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * 删除模板
     *
     * @param string $name
     * @return void
     */
    public function deleteOne($name)
    {
        $file = $this->buildFilename($name);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * 获得指定模板内容
     *
     * @param string $name
     * @return string
     */
    public function getOne($name)
    {
        $file = $this->buildFilename($name);
        
        if (!is_file($file)) {
            return '';
        } else {
            return file_get_contents($file);
        }
    }

    /**
     * 获得指定模板的文件路径
     *
     * @param string $name
     * @return null|string
     */
    public function getFile($name)
    {
        $file = $this->buildFilename($name);

        if (is_file($file)) {
            return $file;
        } else {
            return null;
        }
    }
}
