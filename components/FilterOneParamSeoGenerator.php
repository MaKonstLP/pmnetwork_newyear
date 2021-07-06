<?php

namespace frontend\modules\gorko_ny\components;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use common\models\Filter;
use common\models\Seo;

class FilterOneParamSeoGenerator extends BaseObject
{
  public $seo;

  public function __construct($filter, $filterModel, $type, $page, $count = 0)
  {
    $this->getSeoFromValues($filter, $filterModel, $type, $page, $count);
  }

  public function getSeoFromValues($filter, $filterModel, $type, $page, $count){
    $filterModel = ArrayHelper::index($filterModel, 'alias');
    $paramSring = '';
    $itemsTmp = null;

    foreach ($filter as $key => $item){

      if (count($item) > 1) {
        $this->seo = (new Seo($type, $page, $count))->seo;
        $this->seo['robots'] = true;
        return;
      }
    }

    if (isset($filter['chelovek'])){
      $itemsTmp = ArrayHelper::index($filterModel['chelovek']['items'], 'value');
      if ( mb_strpos($itemsTmp[$filter['chelovek'][0]]['text'], 'от') !== false
        || mb_strpos($itemsTmp[$filter['chelovek'][0]]['text'], 'до') !== false){
          $paramSring .= mb_substr($itemsTmp[$filter['chelovek'][0]]['text'], 0, -4);
      } else {
        $paramSring .= 'на ' . mb_substr($itemsTmp[$filter['chelovek'][0]]['text'], 0, -4);
      }    

      $this->seo['title'] = 'Новогодний корпоратив в ' . Yii::$app->params['subdomen_dec'] . ' ' . $paramSring . ' человек';
      $this->seo['description'] =  'Все площадки ' . Yii::$app->params['subdomen_rod'] . ' для новогодних корпоративов на нашем сайте. Закажите новогодний корпоратив ' . $paramSring . ' человек в ' . Yii::$app->params['subdomen_dec'] . ' у нас и получите скидку!';
      $this->seo['h1'] = 'Новогодний корпоратив ' . $paramSring . ' человек';
    }

    if (isset($filter['price'])){
      // $itemsTmp = ArrayHelper::index($filterModel['price']['items'], 'value');
      $paramSring .= $filter['price'][0] . ' ';

      $this->seo['title'] = 'Новогодний корпоратив в ' . Yii::$app->params['subdomen_dec'] . ' до ' . $paramSring . ' рублей';
      $this->seo['description'] =  'Лучшие площадки ' . Yii::$app->params['subdomen_rod'] . ' для новогодних корпоративов на нашем сайте: новогодний корпоратив до ' . $paramSring . ' рублей в ' . Yii::$app->params['subdomen_dec'] . '. Скидки и подарки.';
      $this->seo['h1'] = 'Новогодний корпоратив от ' . $paramSring . ' рублей';
    }

    if (isset($filter['firework'])){
      $this->seo['title'] = 'Заказать новогодний корпоратив в ' . Yii::$app->params['subdomen_dec'] . ' с фейерверками и салютом';
      $this->seo['description'] =  'Площадки ' . Yii::$app->params['subdomen_rod'] . ' для новогоднего корпоратива с запуском салюта. Заказать проведение корпоратива с фейерверком в ' . Yii::$app->params['subdomen_dec'] . '. Бронируйте место на нашем сайте.';
      $this->seo['h1'] = 'Новогодние корпоративы с запуском салюта';
    }

    if (isset($filter['svoy-alko'])){
      $this->seo['title'] = 'Новогодний корпоратив со своим алкоголем в ' . Yii::$app->params['subdomen_dec'] . ' без пробкового сбора';
      $this->seo['description'] =  'Все заведения ' . Yii::$app->params['subdomen_rod'] . ' для новогоднего корпоратива. Свой алкоголь и выгодные цены на аренду зала для корпоратива. Бронируйте и получайте подарки!';
      $this->seo['h1'] = 'Новогодние корпоративы со своим алкоголем';
    }

    $this->seo['text_top'] = '';
    $this->seo['text_bottom'] = '';
    $this->seo['img_alt'] = '';
  }

}