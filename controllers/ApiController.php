<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\controllers\ApiController as BaseApiController;
use common\models\api\MapAll;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Filter;
use common\models\Slices;
use frontend\components\ParamsFromQuery;

class ApiController extends BaseApiController
{
	public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

	public function actionMapall()
	{
		$filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
		$slices_model = Slices::find()->all();
		$elastic_model = new ElasticItems;
		$params = $this->parseGetQuery(json_decode($_POST['filter'], true), $filter_model, $slices_model);
		$map_all = new MapAll($elastic_model, $_POST['subdomain_id'], $params['params_filter']);

		//echo '<pre>';
		//print_r($map_all->coords);
		//echo '</pre>';
		//exit;

		return json_encode($map_all->coords);
	}

	private function parseGetQuery($getQuery, $filter_model, $slices_model)
	{
		$return = [];
		if(isset($getQuery['page'])){
			$return['page'] = $getQuery['page'];
		}
		else{
			$return['page'] = 1;
		}

		$temp_params = new ParamsFromQuery($getQuery, $filter_model, $slices_model);

		$return['params_api'] = $temp_params->params_api;
		$return['params_filter'] = $temp_params->params_filter;
		$return['listing_url'] = $temp_params->listing_url;
		$return['canonical'] = $temp_params->canonical;
		return $return;
	}
}