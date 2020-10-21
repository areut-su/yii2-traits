<?php

namespace common\traits;
use Closure;

/**
 * Trait PopulatedTrait
 * @package common\traits
 */
trait PopulatedTrait
{
  private $_related = [];

  /**
   * @param string $name
   * @param mixed $records
   */
  public function populate($name, $records)
  {
    $this->_related[$name] = $records;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function isPopulated($name)
  {
    return array_key_exists($name, $this->_related);
  }

  /**
   * @param string $name
   * @param null $default
   * @return mixed|null
   */
  public function getPopulated($name, $default = null)
  {
    if (array_key_exists($name, $this->_related)) {
      return $this->_related[$name];
    }
    return $default;
  }

  /**
   * @return array
   */
  public function getRelated()
  {
    return $this->_related;
  }

  /**
   * return $this->getSetPopulated('clientqr', function () {
   * return clientqr::findbyaccountidone($this->account_id);
   * });
   * @param string $name
   * @param Closure $param
   * @return mixed|null
   */
  protected function getSetPopulated(string $name, Closure $param)
  {
    $result = $this->getPopulated($name, []);
    if (empty($result)) {
      $result = call_user_func($param);
      $this->populate($name, $result);
    }
    return $result;
  }

}