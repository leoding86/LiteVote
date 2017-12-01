<?php
namespace Think\Template\TagLib; // 当前文件所在的目录

use Think\Template\TagLib; // Template目录下的TagLib.class.php文件
use Model\AdminModel;

class Admin extends TagLib
{
  protected $tags = [
      'grant'  => ['attr' => 'url'],
      'select' => ['attr' => 'class,style,id,name,options,value', 'close' => 0],
  ];

  public function _grant($tag, $content)
  {
    $permit = strtolower($tag['permit']);
    $match = false;

    if (AdminModel::$udata['id'] != 1) {
      foreach (AdminModel::$upermits as $p) {
        if (strtolower($p) == $permit) {
          $match = true;
          break;
        }
      }
  
      if (!$match) {
        return null;
      }
    }

    $href = U($tag['permit']);
    $content = str_replace('###', $href, $content);

    $html = <<<HTML
{$content}
HTML;

    return $html;
  }

  public function _select($tag)
  {
    if (strpos($tag['value'], '$') !== 0) {
      $tag['value'] = "'{$tag['value']}'";
    }

    $option_parser = <<<OPTS
<?php foreach (\${$tag['options']} as \$option) {
  if (!empty({$tag['value']}) && \$option[0] == {$tag['value']}) {
    echo '<option value="' . \$option[0] . '" selected>' . \$option[1] . '</option>';
  } else {
    echo '<option value="' . \$option[0] . '">' . \$option[1] . '</option>';
  }
} ?>
OPTS;

    $attrs = [];
    if (isset($tag['name'])) {
      $attrs['name'] = 'name="' . $tag['name'] . '"';
    }

    if (isset($tag['class'])) {
      $attrs['class'] = 'class="' . $tag['class'] . '"';
    }

    if (isset($tag['id'])) {
      $attrs['id'] = 'id="' . $tag['id'] . '"';
    }

    if (isset($tag['style'])) {
      $attrs['style'] = 'style="' . $tag['style'] . '"';
    }

    $attr = implode(' ', $attrs);
    
    $str = <<<HTML
<select {$attr}>
  {$option_parser}
</select>
HTML;

    return $str;
  }
}
