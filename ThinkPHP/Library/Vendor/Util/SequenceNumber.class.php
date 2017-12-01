<?php
namespace Vendor\Util;

class SequenceNumber
{
  const LETTER_STYLE = 1;
  const ZHCN_STYLE = 2;
  const ARABIC_STYLE = 3;
  const LETTER_NUMS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const ZHCN_NUMS = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
  private $template = '%s';
  private $style = 3;
  private $index = -1;
  private $savedIndex = [];

  public function init()
  {
    $this->savedIndex[] = [$this->index, $this->template, $this->style];
    $this->index = -1;
    $this->template = '%s';
    return $this;
  }

  public function recover()
  {
    if (empty($this->savedIndex)) {
      return $this;
    }

    $restore = array_pop($this->savedIndex);
    $this->index = $restore[0];
    $this->template = $restore[1];
    $this->style = $restore[2];
    return $this;
  }

  public function template($template)
  {
    if ($this->template == $template) {
      return $this;
    }

    $this->template = $template;
    return $this;
  }

  public function setStyle($style)
  {
    if ($this->style == $style) {
      return $this;
    }

    $this->style = $style;
    return $this;
  }

  public function output()
  {
    $this->index++;
    switch ($this->style) {
      case self::LETTER_STYLE:
        return $this->outputLetter();
      case self::ZHCN_STYLE:
        return $this->outputZhcn();
      case self::ARABIC_STYLE:
        return $this->outputArabic();
      default:
        throw new \Exception('Unkown sequenece number style');
    }
  }

  public function outputLetter()
  {
    return sprintf($this->template, self::LETTER_NUMS[$this->index]);
  }

  public function outputZhcn()
  {
    return sprintf($this->template, self::ZHCN_NUMS[$this->index]);
  }

  public function outputArabic()
  {
    return sprintf($this->template, $this->index + 1);
  }

  public function __toString()
  {
    return '';
  }
}