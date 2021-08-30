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
use frontend\modules\gorko_ny\components\GetSlicesForSitemap;
use common\models\blog\BlogPost;

class SitemapController extends Controller
{

	public function actionIndex()
	{
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    	Yii::$app->response->headers->add('Content-Type', 'text/xml');

		$host = $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'];

		$slices = Slices::find('alias')->all(); // вернуть, когда останутся только актуальные срезы
		// $slices = Slices::find('alias')->where(['<', 'id', 8])->all();

		$elastic_model = new ElasticItems;

		$slices = GetSlicesForSitemap::getAggregateResult($slices, $elastic_model);

		$items = new ItemsFilterElastic([], 9999, 1, false, 'rooms', $elastic_model);

		$main_subdomain = Yii::$app->params['subdomen_alias'] == '';

		if($main_subdomain){
			$blog = BlogPost::findWithMedia()->with('blogPostTags')->where(['published' => true])->all();
		}
		else{
			$blog = [];
		}

		return $this->renderPartial('sitemap.twig', [
			'host' => $host,
			'blog' => $blog,
			'slices' => $slices,
			'items' => $items->items,
			'main_subdomain' => $main_subdomain
		]);
	}
}