<?php

namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use frontend\widgets\FilterWidget;
//use common\models\Pages;
//use common\models\SubdomenPages;
use common\models\Filter;
use common\models\Slices;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;
use frontend\modules\gorko_ny\models\RestaurantTypeSlice;
use common\models\Seo;
use common\models\blog\BlogPost;
use frontend\components\PremiumMixer;
use frontend\components\Declension;
use yii\web\Cookie;
use yii\helpers\Url;
//use common\models\RestaurantsModule;
//use common\models\RestaurantsUniqueId;
//use common\models\siteobject\SiteObjectSeo;
//use common\models\elastic\LeadLogElastic;

class SiteController extends Controller
{
	//public function getViewPath()
	//{
	//    return Yii::getAlias('@app/modules/svadbanaprirode/views/site');
	//}
	public $filter_model;

	public function actionIndex()
	{
		if (Yii::$app->request->url === '/site/' || Yii::$app->request->url === '/site/index/') {
			return $this->redirect(Url::to('/', 'https'), 301);
		}

		$aggs = ElasticItems::find()->limit(0)->query(
			['bool' => ['must' => ['match' => ['restaurant_city_id' => Yii::$app->params['subdomen_id']]]]]
		)
			->addAggregate('types', [
				'nested' => [
					'path' => 'restaurant_types',
				],
				'aggs' => [
					'ids' => [
						'terms' => [
							'field' => 'restaurant_types.id',
							'size' => 10000,
						]
					]
				]
			])->search();

		$active_slices_menu = array_reduce($aggs['aggregations']['types']['ids']['buckets'], function ($acc, $item) {
			if (
				$item['doc_count'] > 2 && count($acc) < 5
				&& ($restTypeSlice = RestaurantTypeSlice::find()->with('slice')->with('restaurantType')->where(['restaurant_type_value' => intval($item['key'])])->one())
				&& ($sliceObj = $restTypeSlice->slice)
				&& ($typeObj = $restTypeSlice->restaurantType)
			) {
				$acc[] = [
					'alias' => $sliceObj->alias,
					//'plural' => str_replace(" площадок", "", Declension::get_num_ending($item['doc_count'], array_map('mb_strtolower', [$typeObj->text, $typeObj->plural_2, $typeObj->plural_5]))),
					'plural' => $sliceObj->h1,
					'count' => $item['doc_count']
				];
			}
			return $acc;
		}, []);


		// $filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
		$filter_model = Yii::$app->params['filter_model'];
		$slices_model = Slices::find()->all();
		$seo = $this->getSeo('index');
		$this->setSeo($seo);


		$elastic_model = new ElasticItems;
		$items = PremiumMixer::getItemsWithPremium([], 30, 1, false, 'restaurants', $elastic_model, false, false, false, false, false, true);

		$mainWidget = $this->renderPartial('//components/generic/profitable_offer.twig', [
			'items' => $items->items,
			'city_rod' => Yii::$app->params['subdomen_rod'],
		]);

		// echo '<pre>';
		// print_r(count($items->items));
		// exit;

		$seo = $this->getSeo('index', 1, $items->total);
		$this->setSeo($seo);

		$filter = FilterWidget::widget([
			'filter_active' => [],
			'filter_model' => $filter_model,
			'minPrice' => Yii::$app->params['min_restaurant_price']
		]);

		$blog_posts = BlogPost::findWithMedia()
			->with('blogPostTags')
			->where(['published' => true])
			->andWhere(['show_on_main' => true])
			->orderBy(['published_at' => SORT_DESC])
			->limit(3)
			->all();

		$this->setSubdomenCookie();

		return $this->render('index.twig', [
			'filter' => $filter,
			'count' => $items->total,
			'mainWidget' => $mainWidget,
			'seo' => $seo,
			'subid' => isset(Yii::$app->params['subdomen_id']) ? Yii::$app->params['subdomen_id'] : false,
			'blog_posts' => $blog_posts,
			'active_slices_menu' => $active_slices_menu,
		]);
	}

	public function actionError()
	{
		// Устанавливаем статус 404
		Yii::$app->response->setStatusCode(404);
		return $this->render('error.twig');
	}

	public function actionRobots()
	{
		header('Content-type: text/plain');
		if (Yii::$app->params['subdomen_alias']) {
			$subdomen_alias = Yii::$app->params['subdomen_alias'] . '.';
		} else {
			$subdomen_alias = '';
		}
		echo 'User-agent: *
Disallow: /*rest_type=
Disallow: /*chelovek=
Disallow: /*price=
Disallow: /*firework=
Disallow: /*svoy-alko=
Disallow: /*etext=
Disallow: /blog/preview-post/
Sitemap: https://' . $subdomen_alias . 'korporativ-ng.ru/sitemap/';
		exit;
	}

	private function getSeo($type, $page = 1, $count = 0)
	{
		$seo = new Seo($type, $page, $count);

		return $seo->seo;
	}

	private function setSeo($seo)
	{
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		$this->view->params['kw'] = $seo['keywords'];
	}

	private function setSubdomenCookie() {
		// сохраняем в куки значение id поддомена, нужно для вывода рандомных рестов соответствующих городу в постах блога
		$cookie = new Cookie([
			'name' => 'subdomen_id',
			'value' => \Yii::$app->params['subdomen_id'],
			'domain' => '.' . Yii::$app->params['domen'],
			'expire' => time() + 36000,
		]);
		Yii::$app->response->cookies->add($cookie);
	}

	public function actionApitest()
	{
		$api_url = 'https://v.wedding.net/api/banket_wedding/inquiry/put';
		//$payload = [
		//    'city_id' => 15789641,
		//    'name' => 'G',
		//    'phone' => '+91 8563587525',
		//    'event_type' => 'Wedding',
		//    'guests' => 150,
		//    'price' => '1000 - 1399 ₹',
		//    'is_phone_preferred' => 1
		//];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $api_url);
		curl_setopt($curl, CURLOPT_POST, 1);
		//curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		$response = json_decode(curl_exec($curl), true);
		$info = curl_getinfo($curl);
		curl_close($curl);

		echo '<pre>';
		print_r($api_url);
		//print_r($payload);
		print_r($response);
		return 1;
	}
}
