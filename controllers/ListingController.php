<?php

namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\widgets\FilterWidget;
use frontend\widgets\PaginationWidget;
use frontend\modules\gorko_ny\widgets\PaginationWidgetWithLinks;
use frontend\components\ParamsFromQuery;
use frontend\components\QueryFromSlice;
use frontend\components\Declension;
use frontend\components\RoomsFilter;
use frontend\components\PremiumMixer;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use frontend\modules\gorko_ny\components\FilterOneParamSeoGenerator;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Pages;
use common\models\Filter;
use common\models\Slices;
use common\models\GorkoApi;
use common\models\elastic\ItemsFilterElastic;
use common\models\Seo;
use common\models\SubdomenPages;
use frontend\modules\gorko_ny\models\RestaurantTypeSlice;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use yii\web\Cookie;


class ListingController extends Controller
{
	protected $per_page = 24;

	public $filter_model,
		$slices_model,
		$GetParamWhiteList,
		$paramList,
		$get;

	public function beforeAction($action)
	{
		$this->get = $_GET;
		// $this->filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
		$this->filter_model = Yii::$app->params['filter_model'];
		$this->slices_model = Slices::find()->all();
		$this->GetParamWhiteList = [
			'q' => '',
			'slice' => '',
			'page' => '',
			'rest_type' => '',
			'chelovek' => '',
			'price' => '',
			'svoy-alko' => '',
			'firework' => ''
		];
		$this->paramList = [
			'rest_type' => '',
			'chelovek' => '',
			'price' => '',
			'svoy-alko' => '',
			'firework' => ''
		];


		//проверяем, что указаны корректные get-параметры, а не только состоящие из цифр, например /?3&2&0&2&3&page=9 или /?1&0&6&6&page=10
		foreach ($this->get as $key => $value) {
			if (preg_match('/^\d+$/', $key)) {
				throw new NotFoundHttpException();
				break;
			}
		}

		$page = (int)\Yii::$app->request->getQueryParam('page');
		if ($page == 1) {
			$url = Url::current(['page' => null, 'q' => null]);
			// \Yii::$app->response->redirect('https://korporativ-ng.ru'.$url, 301)->send();
			\Yii::$app->response->redirect(Url::to($url, 'https'), 301);
		}


		return parent::beforeAction($action);
	}

	public function actionSlice($slice)
	{
		$slice_obj = new QueryFromSlice($slice);

		if (count(array_intersect_key($this->paramList, $_GET)) > 0) {
			return $this->actionSliceWhithParams($slice_obj->params);
		}

		if ($slice_obj->flag) {
			$this->view->params['menu'] = $slice;
			// $params = $this->parseGetQuery($slice_obj->params, Filter::find()->with('items')->orderBy(['sort' => SORT_ASC])->all(), $this->slices_model);
			$params = $this->parseGetQuery($slice_obj->params, $this->filter_model, $this->slices_model);
			isset($_GET['page']) ? $params['page'] = $_GET['page'] : $params['page'];

			$canonical = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];

			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$breadcrumbs 	=	Breadcrumbs::get_breadcrumbs(3, $slice),
				$canonical 		= 	$canonical,
				$type 			=	$slice
			);
		} else {
			throw new NotFoundHttpException();
		}
	}

	public function actionSliceWhithParams($paramFromAlias)
	{
		$getQuery = $_GET;
		unset($getQuery['q']);
		$params = $this->parseGetQuery($getQuery, $this->filter_model, $this->slices_model);
		$params['params_filter']['rest_type'] = [];
		array_push($params['params_filter']['rest_type'], $paramFromAlias['rest_type']);
		$canonical = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];

		if ($params['page'] > 1) {
			$canonical .= $params['canonical'];
		}

		return $this->actionListing(
			$page 			=	$params['page'],
			$per_page		=	$this->per_page,
			$params_filter	= 	$params['params_filter'],
			$breadcrumbs 	=	Breadcrumbs::get_breadcrumbs(3),
			$canonical 		= 	$canonical,
			$type = false,
			$sliceWithParams = true,
		);
	}

	public function actionIndex()
	{
		$getQuery = $_GET;
		unset($getQuery['q']);
		if (count($getQuery) > 0) {
			$params = $this->parseGetQuery($getQuery, $this->filter_model, $this->slices_model);
			$canonical = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
			if ($params['page'] > 1) {
				$canonical .= $params['canonical'];
			}

			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$breadcrumbs 	=	Breadcrumbs::get_breadcrumbs(2),
				$canonical 		= 	$canonical
			);
		} else {
			$canonical = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];

			return $this->actionListing(
				$page 			=	1,
				$per_page		=	$this->per_page,
				$params_filter	= 	[],
				$breadcrumbs 	= 	Breadcrumbs::get_breadcrumbs(2),
				$canonical 		= 	$canonical
			);
		}
	}

	public function actionListing($page, $per_page, $params_filter, $breadcrumbs, $canonical, $type = false, $sliceWithParams = false)
	{
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
					'plural' => $sliceObj->h1,
					'count' => $item['doc_count']
				];
			}
			return $acc;
		}, []);

		$elastic_model = new ElasticItems;

		$items = PremiumMixer::getItemsWithPremium($params_filter, $per_page, $page, false, 'restaurants', $elastic_model, false, false, false, false, false, true);
		// $items = PremiumMixer::getItemsWithPremium($params_filter, 70, $page, false, 'restaurants', $elastic_model, false, false, false, false, false, true);
		//$items = new ItemsFilterElastic($params_filter, $per_page, $page, false, 'restaurants', $elastic_model, false, false, false, false, false, true);

		if($items->total < 3){
			return \Yii::$app->response->redirect(Url::to('/ploshhadki/', 'https'), 301);
		}

		$this->setSubdomenCookie();


		// if (($page * $per_page) > ($items->total + $per_page) || $page > 250) {
		if ($page > $items->pages || $page > 250) {
			// throw new NotFoundHttpException();

			// \Yii::$app->response->redirect(Url::to(['index'], 'https'), 301);

			$url = Url::current(['page' => null, 'q' => null]);
			\Yii::$app->response->redirect(Url::to($url, 'https'), 301);
		}

		if ($page > 1) {
			$seo['text_top'] = '';
			$seo['text_bottom'] = '';
		}

		$itemsAllPages = new ItemsFilterElastic($params_filter, 10000, 1, false, 'restaurants', $elastic_model);

		$minPrice = 999999;
		foreach ($itemsAllPages->items as $item) {
			if ($item->restaurant_price < $minPrice && $item->restaurant_price !== 250) { // второе условие - костыль под опечатку в инфе из горько
				$minPrice = $item->restaurant_price;
			}
		}

		if ($minPrice === 999999) {
			$minPrice = 2100;
		}

		$filter = FilterWidget::widget([
			'filter_active' => $params_filter,
			'filter_model' => $this->filter_model,
			'minPrice' => $minPrice
		]);

		$pagination = PaginationWidgetWithLinks::widget([
			'total' => $items->pages,
			'current' => $page,
			'url' => null,
		]);

		$seo_type = $type ? $type : 'listing';
		$seo = $this->getSeo($seo_type, $page, $items->total);
		$seo['breadcrumbs'] = $breadcrumbs;

		if ($sliceWithParams || count(array_intersect_key($this->paramList, $_GET)) > 0 || count($items->items) < 3) {
			$seo['robots'] = true;
		}

		if (!$this->checkGetParams()) {
			$seo['robots'] = true;
		}

		if ($page > 1) {
			$this->getPaginationSeo($seo, $page);
		}

		$this->setSeo($seo, $page, $canonical);

		if ($seo_type == 'listing' and count($params_filter) > 0) {
			$seo['text_top'] = '';
			$seo['text_bottom'] = '';
		}

		$main_flag = ($seo_type == 'listing' and count($params_filter) == 0);

		$currentRestType = $this->getRestTypeDeclention($params_filter);

		$this->view->params['currentUrl'] = $_SERVER['REQUEST_URI'];
		$currentType = Declension::get_num_ending($items->total, $currentRestType);

		// echo ('<pre>');
		// print_r($items->items);
		// exit;

		return $this->render('index.twig', array(
			'title' => $this->renderPartial('//components/generic/title.twig', array(
				'seo' => $seo,
				'count' => $items->total,
				'currentType' => $currentType,
				'active_slices_menu' => $active_slices_menu,
			)),
			'filter' => $filter,
			'items' => $items->items,
			'pagination' => $pagination,
			'seo' => $seo,
			'currentType' => $currentType,
			'count' => $items->total,
			'menu' => $type,
			'main_flag' => $main_flag,
			'filterMinPrice' => isset($params_filter['price']) ? array_shift($params_filter['price']) : 0,
			'active_slices_menu' => $active_slices_menu,
		));
	}

	public function actionAjaxFilter()
	{
		$params = $this->parseGetQuery(json_decode($_GET['filter'], true), $this->filter_model, $this->slices_model);

		$elastic_model = new ElasticItems;
		$items = PremiumMixer::getItemsWithPremium($params['params_filter'], $this->per_page, $params['page'], false, 'restaurants', $elastic_model, false, false, false, false, false, true);
		//$items = new ItemsFilterElastic($params['params_filter'], $this->per_page, $params['page'], false, 'restaurants', $elastic_model, false, false, false, false, false, true);

		$slice_url = ParamsFromQuery::isSlice(json_decode($_GET['filter'], true));

		!$slice_url ? $breadcrumbs = Breadcrumbs::get_breadcrumbs(2) : $breadcrumbs = Breadcrumbs::get_breadcrumbs(3, $slice_url);

		$seo_type = $slice_url ? $slice_url : 'listing';
		$seo = $this->getSeo($seo_type, $params['page'], $items->total);

		$seo['breadcrumbs'] = $breadcrumbs;

		$currentRestType = $this->getRestTypeDeclention($params['params_filter']);

		$title = $this->renderPartial('//components/generic/title.twig', array(
			'seo' => $seo,
			'count' => $items->total,
			'currentType' => Declension::get_num_ending($items->total, $currentRestType),
		));

		if ($params['page'] == 1) {
			$text_top = $this->renderPartial('//components/generic/text.twig', array('text' => $seo['text_top']));
			$text_bottom = $this->renderPartial('//components/generic/text.twig', array('text' => $seo['text_bottom']));
		} else {
			$text_top = '';
			$text_bottom = '';
		}

		if ($seo_type == 'listing' and count($params['params_filter']) > 0) {
			$text_top = '';
			$text_bottom = '';
		}

		$itemsAllPages = new ItemsFilterElastic($params['params_filter'], 10000, 1, false, 'restaurants', $elastic_model);

		$minPrice = 999999;
		foreach ($itemsAllPages->items as $item) {
			if ($item->restaurant_price < $minPrice && $item->restaurant_price !== 250) { // второе условие - костыль под опечатку в инфе из горько
				$minPrice = $item->restaurant_price;
			}
		}

		if ($minPrice === 999999) {
			$minPrice = 2100;
		}

		// $minPrice = 999999;
		// foreach ($items->items as $item){
		// 	if ($item->restaurant_price < $minPrice){
		// 		$minPrice = $item->restaurant_price;
		// 	}
		// }

		// $pagination = PaginationWidget::widget([
		// 	'total' => $items->pages,
		// 	'current' => $params['page'],
		// ]);

		$pagination = PaginationWidgetWithLinks::widget([
			'total' => $items->pages,
			'current' => $params['page'],
			'url' => '/ploshhadki/' . (!$slice_url ? $this->getUrl($params['listing_url'], $params['params_filter']) : $params['listing_url']),
		]);


		return  json_encode([
			'listing' => $this->renderPartial('//components/generic/listing.twig', array(
				'items' => $items->items,
				'img_alt' => $seo['img_alt'],
				'filterMinPrice' => isset($params['params_filter']['price']) ? $params['params_filter']['price'][0] : 0,
			)),
			'pagination' => $pagination,
			'url' => !$slice_url ? $this->getUrl($params['listing_url'], $params['params_filter']) : $params['listing_url'],
			'title' => $title,
			'text_top' => $text_top,
			'text_bottom' => $text_bottom,
			'seo_title' => $seo['title'],
			'minPrice' => $minPrice,
			'params_filter' => $params['params_filter'],
		]);
	}

	public function actionAjaxFilterSlice()
	{
		$slice_url = ParamsFromQuery::isSlice(json_decode($_GET['filter'], true));

		return $slice_url;
	}

	private function parseGetQuery($getQuery, $filter_model, $slices_model)
	{
		$return = [];
		if (isset($getQuery['page'])) {
			$return['page'] = $getQuery['page'];
		} else {
			$return['page'] = 1;
		}

		$temp_params = new ParamsFromQuery($getQuery, $filter_model, $this->slices_model);

		$return['params_api'] = $temp_params->params_api;
		$return['params_filter'] = $temp_params->params_filter;
		$return['listing_url'] = $temp_params->listing_url;
		$return['canonical'] = $temp_params->canonical;
		return $return;
	}

	private function getSeo($type, $page, $count = 0)
	{
		$seo = new Seo($type, $page, $count);

		return $seo->seo;
	}

	private function setSeo($seo, $page, $canonical)
	{
		// if ($page != 1) {
			$this->view->params['canonical'] = $canonical;
		// }

		if (isset($seo['title'])) {
			$this->view->title = $seo['title'];
		}

		if (isset($seo['description'])) {
			$this->view->params['desc'] = $seo['description'];
		}

		if (isset($seo['keywords'])) {
			$this->view->params['kw'] = $seo['keywords'];
		}

		if (isset($seo['robots'])) {
			$this->view->params['robots'] = $seo['robots'];
		}
	}

	private function getRestTypeDeclention($params_filter = [])
	{
		$restTypesList = [
			'1' => ['банкетный зал', 'банкетных зала', 'банкетных залов'],
			'2' => ['ресторан', 'ресторана', 'ресторанов'],
			'3' => ['кафе', 'кафе', 'кафе'],
			'4' => ['клуб', 'клуба', 'клубов'],
			'5' => ['бар', 'бара', 'баров'],
			'6' => ['площадка в городе', 'площадки в городе', 'площадок в городе'],
			'7' => ['площадка на природе', 'площадки на природе', 'площадок на природе'],
		];

		$currentRestType = ['площадка', 'площадки', 'площадок'];

		if (isset($params_filter['rest_type']) && count($params_filter['rest_type']) === 1) {
			$currentRestType = $restTypesList[$params_filter['rest_type'][0]];
		}

		return $currentRestType;
	}

	private function getUrl($listing_url, $params_filter)
	{
		if (isset($params_filter['rest_type']) && count($params_filter['rest_type']) === 1) {
			$currentSlice = Slices::find()->where(['params' => '{"rest_type":' . $params_filter['rest_type'][0] . '}'])->one();
			$newUrl = $currentSlice->alias . '/' . str_replace(('rest_type=' . $params_filter['rest_type'][0] . '&'), '', $listing_url);
			return $newUrl;
		}

		return $listing_url;
	}

	private function getPaginationSeo(&$seo, $page)
	{
		$seo['title'] = $seo['h1'] . " - Страница " . $page;
		$seo['description'] = "Вы смотрите страницу " . $page . " из раздела " . $seo['h1'] . " на портале korporativ-ng.ru. Поиск и аренда помещений для проведения новогоднего корпоратива.";
	}

	private function checkGetParams()
	{
		foreach ($_GET as $key => $value) {
			if (!array_key_exists($key, $this->GetParamWhiteList)) {
				return false;
			}
		}
		return true;
	}

	private function setSubdomenCookie() {
		// сохраняем в куку значение id поддомена, нужно для вывода рандомных рестов соответствующих городу в постах блога
		$cookie = new Cookie([
			'name' => 'subdomen_id',
			'value' => \Yii::$app->params['subdomen_id'],
			'domain' => '.' . Yii::$app->params['domen'],
			'expire' => time() + 36000,
		]);
		Yii::$app->response->cookies->add($cookie);
	}
}

//class ListingController extends Controller
//{
//	public function actionIndex(){
//		GorkoApi::renewAllData([
//			'city_id=4400&type_id=1&event=15',
//			'city_id=4400&type_id=1&event=17'
//		]);
//		return 1;
//	}	
//}