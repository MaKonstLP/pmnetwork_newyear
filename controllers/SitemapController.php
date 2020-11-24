<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use common\models\Slices;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;

class SitemapController extends Controller
{

	public function actionIndex()
	{
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    	Yii::$app->response->headers->add('Content-Type', 'text/xml');

		$host = $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'];

		$slices = Slices::find('alias')->all();

		$elastic_model = new ElasticItems;
		$items = new ItemsFilterElastic([], 9999, 1, false, 'rooms', $elastic_model);

		return $this->renderPartial('sitemap.twig', [
			'host' => $host,
			'slices' => $slices,
			'items' => $items->items
		]);
	}
}