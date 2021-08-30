<?php

namespace frontend\modules\gorko_ny\components;

use Yii;
use yii\helpers\ArrayHelper;
use common\models\Filter;
use common\models\Slices;
use common\models\elastic\ItemsFilterElastic;
use frontend\components\QueryFromSlice;
use frontend\components\ParamsFromQuery;
use frontend\modules\gorko_ny\models\ElasticItems;
use Elasticsearch\ClientBuilder;

class GetSlicesForSitemap
{

  private static function parseGetQuery($getQuery, $filter_model, $slices_model)
	{
		$return = [];
		$temp_params = new ParamsFromQuery($getQuery, $filter_model, $slices_model);
		$return['params_filter'] = $temp_params->params_filter;

		return $return;
	}

  public static function getAggregateResult($slices, $elastic_model)
  {
    $finalAliasList =[];
    $filter_model = Filter::find()->with('items')->orderBy(['sort' => SORT_ASC])->all();
    $slices_model = Slices::find()->all();

    foreach ($slices as $slice){
      $slice_obj = new QueryFromSlice($slice);
      $temp_params = GetSlicesForSitemap::parseGetQuery($slice_obj->params, $filter_model, $slices_model);
      $items = new ItemsFilterElastic($temp_params['params_filter'], 100, 1, false, 'restaurants', $elastic_model);

      if (count($items->items) > 0){
        array_push($finalAliasList, $slice->alias);
      }
    }


    return $finalAliasList;
  }

  public static function getFilter($filterState)
  {
    $enabledFilterItemsList = [];
    $aggregateResult = UpdateFilterItems::getAggregateResult($filterState);
    
    if ($aggregateResult['aggregations']['restaurant_metro_group']['doc_count'] > 0 && !isset($filterState['metro'])){
      $stationList = MetroStations::find()->all();
      $stationList = ArrayHelper::index($stationList, 'table_id');
      $tmp = '';
      foreach ($aggregateResult['aggregations']['restaurant_metro_group']['metro']['buckets'] as $key => $value){
        $tmp .= $stationList[$value['key']]['alias'] . ',';
      }
      $enabledFilterItemsList['metro'] = substr($tmp, 0, -1);
    }
    
    if ($aggregateResult['aggregations']['restaurant_spec_group']['doc_count'] > 0 && !isset($filterState['prazdnik'])){
      $specList = RestaurantSpecFilterRel::find()->all();
      $specList = ArrayHelper::index($specList, 'id');
      $tmp = '';
      foreach ($aggregateResult['aggregations']['restaurant_spec_group']['prazdnik']['buckets'] as $key => $value){
        if (array_key_exists($value['key'], $specList)){
          $tmp .= $specList[$value['key']]['alias'] . ',';
        }
      }
      $enabledFilterItemsList['prazdnik'] = substr($tmp, 0, -1);
    }

    foreach ($aggregateResult['aggregations']['parent_district_group']['buckets'] as $key => $districtAgg){

      if ($districtAgg['doc_count'] > 0 && $districtAgg['key'] === 0 && !isset($filterState['okrug'])){
        $okrugList = Okruga::find()->all();
        $okrugList = ArrayHelper::index($okrugList, 'id');
        $tmp = '';
        foreach ($districtAgg['district_group']['buckets'] as $key => $value){
          if (array_key_exists($value['key'], $okrugList)){
            $tmp .= $okrugList[$value['key']]['alias'] . ',';
          }
        }
        $enabledFilterItemsList['okrug'] = substr($tmp, 0, -1);
      }

      if ($districtAgg['doc_count'] > 0 && $districtAgg['key'] === 547 && !isset($filterState['rayon'])){
        $rayonList = Rayoni::find()->all();
        $rayonList = ArrayHelper::index($rayonList, 'id');
        $tmp = '';
        foreach ($districtAgg['district_group']['buckets'] as $key => $value){
          if (array_key_exists($value['key'], $rayonList)){
            $tmp .= $rayonList[$value['key']]['alias'] . ',';
          }
        }
        $enabledFilterItemsList['rayon'] = substr($tmp, 0, -1);
      }
    }

    if ($aggregateResult['aggregations']['capacity_group']['doc_count'] > 0 && !isset($filterState['lyudey'])){
      $map = [
        '*-15.0' => 'do-15',
        '15.0-20.0' => '15-19',
        '20.0-30.0' => '20-29',
        '30.0-40.0' => '30-39',
        '40.0-50.0' => '40-49',
        '50.0-60.0' => '50-59',
        '60.0-80.0' => '60-79',
        '80.0-100.0' => '80-99',
        '100.0-150.0' => '100-149',
        '150.0-200.0' => '150-199',
        '200.0-300.0' => '200-299',
        '300.0-*' => 'ot-300',
      ];
      $tmp = '';
      foreach ($aggregateResult['aggregations']['capacity_group']['lyudey']['buckets'] as $key => $value){
        if (array_key_exists($value['key'], $map) && $value['doc_count'] > 0){
          $tmp .= $map[$value['key']] . ',';
        }
      }
      $enabledFilterItemsList['lyudey'] = substr($tmp, 0, -1);
    }

    foreach ($aggregateResult['aggregations']['alko']['buckets'] as $key => $alkoItem){
      if ($alkoItem['key'] === 1 && $alkoItem['doc_count'] > 0 && !isset($filterState['alko'])){
        $enabledFilterItemsList['alko'] = 'da';
      }
    }
  
    return $enabledFilterItemsList;
  }
}