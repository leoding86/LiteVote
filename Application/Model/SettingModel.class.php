<?php
namespace Model;

class SettingModel
{
    const VERIFY_NONE_TYPE = 0;
    const VERIFY_CAPTCHA_TYPE = 1;
    const VERIFY_CUSTOM_TYPE = 2;

  /**
   * 设置名称
   *
   * @var string
   */
    private $name;

  /**
   * 保存路径
   *
   * @var string
   */
    private $savePath;

  /**
   * 设置键
   *
   * @var string
   */
    private $key;

  /**
   * 设置值
   *
   * @var string
   */
    private $value;

  /**
   * 不能设置的设置名称
   *
   * @var array
   */
    private $excludeNames = ['core', 'config'];

  /**
   * 构造函数
   *
   * @param string $name 设置名称
   * @param string $save_path 保存路径
   * @throws \Exception
   */
    public function __construct($name = null, $save_path = null)
    {
        if (!is_null($name)) {
            $this->setName($name);
        }

        $save_path = is_null($save_path) ? (COMMON_PATH . 'Conf') : $save_path;
        $this->setSavePath($save_path);
    }

  /**
   * __get方法
   *
   * @param [type] $property
   * @return void
   */
    public function __get($property)
    {
        return $this->$property;
    }

  /**
   * __set方法
   *
   * @param [type] $property
   * @param [type] $value
   */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            if ($property == 'name') {
                $this->setName($value);
            } elseif ($property == 'savePath') {
                $this->setSavePath($value);
            } else {
                $this->$property = $value;
            }
        }
    }

  /**
   * 设置设置名称
   *
   * @param string $name
   * @return void
   */
    public function setName($name)
    {
        if (!preg_match('/^[a-z][a-z\d]*$/', $name) ||
        in_array($name, $this->excludeNames)
        ) {
            throw new \Exception('INVALID_SETTING_NAME');
        }

        $this->name = $name;
    }

  /**
   * 设置设置保存路径
   *
   * @param string $save_path
   * @return void
   */
    public function setSavePath($save_path)
    {
        if (!is_dir($save_path)) {
            throw new \Exception('INVALID_DIR');
        }

        $this->savePath = preg_replace('/[\/]$/', '', $save_path) . '/';
    }

  /**
   * 构建设置内容
   *
   * @param array $settings
   * @return string
   */
    private function buildSettingsContent(array $settings)
    {
        $content = "<?php\n\nreturn [\n";
    
        foreach ($settings as $setting) {
            if ($setting instanceof SettingModel) {
                $content .= str_repeat(' ', 4) . "'{$setting->key}' => '{$setting->value}',\n";
            }
        }

        $content .= "];\n";

        return $content;
    }

  /**
   * 构建设置文件
   *
   * @return string
   */
    private function buildSettingFile()
    {
        return $this->savePath . $this->name . '.php';
    }

  /**
   * 保存设置
   *
   * @param array $settings
   * @return void
   */
    public function write(array $settings)
    {
        $content = $this->buildSettingsContent($settings);
        $setting_file = $this->buildSettingFile();

        file_put_contents($setting_file, $content);
    }

  /**
   * 读取配置
   *
   * @throws \Exception
   * @return void
   */
    public function read()
    {
        $setting_file = $this->buildSettingFile();

        if (!is_file($setting_file)) {
            throw new \Exception('INVALID_SETTING_FILE');
        }

        return include $setting_file;
    }

    public static function verifyTypeOptions()
    {
        return [
            [self::VERIFY_NONE_TYPE, '禁用'],
            [self::VERIFY_CAPTCHA_TYPE, '验证码'],
        ];
    }
}
