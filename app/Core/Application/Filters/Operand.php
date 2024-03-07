<?php

namespace App\Core\Application\Filters;


use Exception;
use JsonSerializable;
use App\Core\Application\Fields\Relation\HasMany;
use App\Core\Application\Fields\Traits\ChangesKeys;

class Operand implements JsonSerializable
{
  use ChangesKeys;

  /**
   * @var \App\Core\Application\Filters\Filter
   */
  public $rule;

  /**
   * @var mixed
   */
  public $value;

  /**
   * @var string
   */
  public $label;

  /**
   * Initialize Operand class
   *
   * @param mixed $value
   * @param string $label
   */
  public function __construct($value, $label)
  {
    $this->value = $value;
    $this->label = $label;
  }

  /**
   * Set the operand filter
   *
   * @param \App\Core\Application\Filters\Filter|string $rule
   *
   * @return \App\Core\Application\Filters\Operand
   */
  public function filter($rule)
  {
    if (is_string($rule)) {
      $rule = $rule::make($this->value);
    }

    if ($rule instanceof HasMany) {
      throw new Exception('Cannot use HasMany filter in operands');
    }

    $this->rule = $rule;

    return $this;
  }

  /**
   * jsonSerialize
   *
   * @return array
   */
  public function jsonSerialize(): array
  {
    return [
      'value'    => $this->value,
      'label'    => $this->label,
      'valueKey' => $this->valueKey,
      'labelKey' => $this->labelKey,
      'rule'     => $this->rule,
    ];
  }
}
