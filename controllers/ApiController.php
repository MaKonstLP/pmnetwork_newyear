<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\api\MapAll;

class ApiController extends Controller
{

	public function actionMapall()
	{

		$map_all = new MapAll();

		echo '<pre>';
		print_r($map_all->coords);
		echo '</pre>';
		exit;

		return json_encode($map_all->coords);
	}

}