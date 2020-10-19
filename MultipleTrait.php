<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace common\traits;

use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Trait MultipleTrait
 */
trait MultipleTrait
{
  /**
   * @param $count
   * @param $load
   * @return array
   */
  public function createNewByCount($count, $load)
  {
    $response = [];
    for ($i = 0; $i < $count; $i++) {
      /**
       * @var $m Model
       */
      $m = new self();
      $m->load($load, '');
      $response[] = $m;
    }
    return $response;

  }

  /**
   * @param $model
   * @param array $data
   * @param null $formName
   * @return array
   */
  public static function createLoadMultiple($model, array $data, $formName = null)
  {
    $models = [];
    foreach ($data as $item) {
      $models[] = clone $model;
    }
    /**
     * @var self Model
     */
    self::loadMultiple($models, $data, '');
    return $models;
  }

  /**
   * @param $models Model[]|ActiveRecord[]
   * @return array
   */
  public static function ValidateMultiple($models, $clearErrors = true)
  {
    $errors = [];
    foreach ($models as $key => $item) {
      if (!$item->validate(null, $clearErrors)) {
        $errors[$key] = $item->errors;
      }
    }
    return $errors;
  }

  /**
   * @param $models Model[]
   * @param bool $clearErrors
   * @return array|bool
   * @throws ErrorSaveException
   */

  public static function saveMultiple($models, $runValidation = true)
  {
    $errors = [];
    $f = true;
    try {
      $transaction = \Yii::$app->db->beginTransaction();
      foreach ($models as $key => $model) {
        if (!($f = $f && $model->save($runValidation))) {
          $errors[$key] = $model->errors;
        }
      }
      if ($f && empty($errors)) {
        $transaction->commit();
        return true;
      }
      \Yii::$app->db->transaction->rollBack();
      return $errors;
    } catch
    (\Exception $e) {
      \Yii::$app->db->transaction->rollBack();
      $message = 'Error save. ' . $e->getCode() . ' ' . $e->getMessage();
      throw new ErrorSaveException($message);
    }

  }

  /**
   * устанавливает одинаковые значения для всех моделей
   * @param $models Model[]
   * @param  $values array
   * @param string $forName
   * @return bool
   */
  public function setValuesMultiple(&$models, array $values, string $forName = '')
  {
    $f = true;
    foreach ($models as $key => $model) {
      $f = $f && $model->load($values, $forName);
    }
    return $f;
  }

  /**
   * @param array $data
   * @param Model $model
   * @param array $values
   * @param string $formName
   * @return array
   * @throws ExceptionValidate
   */
  public static function loadValidateMultiple(array $data, Model $model, array $values = [], $formName = '')
  {
    $result = [];
    $flag = true;
    foreach ($data as $items) {
      $m = new self;
      $m->setAttributes($values);
      $flag = $m->load($items, $formName);
      $flag = $flag && $m->validate();
      $result[] = $m;
    }
    if ($flag === false) {
      throw new ExceptionValidate(ArrayHelper::toArray($result, 'errors'));
    }
    return $result;
  }

  /**
   * @param bool $flag
   * @param \yii\db\Transaction $transaction
   * @throws \yii\db\Exception
   */
  protected function transactionFlag(bool $flag, \yii\db\Transaction $transaction)
  {
    if ($flag) {
      $transaction->commit();
    } else {
      $transaction->rollBack();
    }
  }


}