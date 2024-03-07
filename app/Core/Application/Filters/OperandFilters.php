<?php

namespace App\Core\Application\Filters;

class OperandFilter extends Filter
{
  /**
   * Filter current opereand
   *
   * @var string|null
   */
  protected $operand;

  /**
   * Filter current opereands
   *
   * @var array|null
   */
  protected $operands;

  /**
   * Set the filter operand
   *
   * @param string $operand
   */
  public function setOperand($operand)
  {
    $this->operand = $operand;

    return $this;
  }

  /**
   * Get the filter selected operand
   *
   * @return string|null
   */
  public function getOperand()
  {
    return $this->operand;
  }

  /**
   * Set the filter operands
   *
   * @param array $operand
   */
  public function setOperands(array $operands)
  {
    $this->operands = $operands;

    return $this;
  }

  /**
   * Get the filter operands
   *
   * @return array|null
   */
  public function getOperands()
  {
    return $this->operands;
  }

  /**
   * Check whether the filter has operands
   *
   * @return boolean
   */
  public function hasOperands()
  {
    return is_array($this->operands) && count($this->operands) > 0;
  }

  /**
   * Find operand filter by given value
   *
   * @return \App\Innoclapps\Filters\Operand|null
   */
  public function findOperand($value)
  {
    return collect($this->getOperands())->first(fn ($operand) => $operand->value == $value);
  }

  /**
   * Hide the filter operands
   * Useful when only 1 opereand is used, which is by default pre-selected
   *
   * @param boolean $bool
   *
   * @return \App\Innoclapps\Filters\Operand|null
   */
  public function hideOperands($bool = true)
  {
    $this->withMeta([__FUNCTION__ => $bool]);

    return $this;
  }

  /**
   * Defines a filter type
   *
   * @return string
   */
  public function type(): string
  {
    return 'nullable';
  }
}
