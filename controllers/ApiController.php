<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\controllers\ApiController as BaseApiController;
use common\models\api\MapAll;
use frontend\modules\gorko_ny\models\ElasticItems;

class ApiController extends BaseApiController
{
	public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

	public function actionMapall()
	{
		$elastic_model = new ElasticItems;
		$map_all = new MapAll($elastic_model, $_POST['subdomain_id']);

		//echo '<pre>';
		//print_r($map_all->coords);
		//echo '</pre>';
		//exit;

		return json_encode($map_all->coords);
	}
}