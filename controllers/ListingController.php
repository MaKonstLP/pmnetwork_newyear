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
use frontend\components\ParamsFromQuery;
use frontend\components\QueryFromSlice;
use frontend\components\Declension;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use common\models\Pages;
use frontend\components\RoomsFilter;
use common\models\Filter;
use common\models\Slices;
use common\models\GorkoApi;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Seo;


class ListingController extends Controller
{
	protected $per_page = 24;

	public $filter_model,
		   $slices_model;

	public function beforeAction($action)
	{
		$this->filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
		$this->slices_model = Slices::find()->all();

	    return parent::beforeAction($action);
	}

	public function actionSlice($slice)
	{
		$slice_obj = new QueryFromSlice($slice);
		if($slice_obj->flag){
			$this->view->params['menu'] = $slice;
			$params = $this->parseGetQuery($slice_obj->params, Filter::find()->with('items')->orderBy(['sort' => SORT_ASC])->all(), $this->slices_model);
			isset($_GET['page']) ? $params['page'] = $_GET['page'] : $params['page'];

			$canonical = $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
			if($params['page'] > 1){
				$canonical .= $params['canonical'];
			}

			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$breadcrumbs 	=	Breadcrumbs::get_breadcrumbs(3, $slice),
				$canonical 		= 	$canonical,
				$type 			=	$slice
			);
		}
		else{
			throw new \yii\web\NotFoundHttpException();
		}				
	}

	public function actionIndex()
	{
		$getQuery = $_GET;
		unset($getQuery['q']);
		if(count($getQuery) > 0){
			$params = $this->parseGetQuery($getQuery, $this->filter_model, $this->slices_model);
			$canonical = $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
			if($params['page'] > 1){
				$canonical .= $params['canonical'];
			}

			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$breadcrumbs 	=	Breadcrumbs::get_breadcrumbs(2),
				$canonical 		= 	$canonical
			);	
		}
		else{
			$canonical = $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];

			return $this->actionListing(
				$page 			=	1,
				$per_page		=	$this->per_page,
				$params_filter	= 	[],
				$breadcrumbs 	= 	Breadcrumbs::get_breadcrumbs(2),
				$canonical 		= 	$canonical
			);
		}
	}

	public function actionListing($page, $per_page, $params_filter, $breadcrumbs, $canonical, $type = false)
	{
		$elastic_model = new ElasticItems;
		$items = new ItemsFilterElastic($params_filter, $per_page, $page, false, 'restaurants', $elastic_model);

		if($page > 1){
			$seo['text_top'] = '';
			$seo['text_bottom'] = '';
		}

		$minPrice = 999999;
		foreach ($items->items as $item){
			if ($item->restaurant_price < $minPrice){
				$minPrice = $item->restaurant_price;
			}
		}

		$filter = FilterWidget::widget([
			'filter_active' => $params_filter,
			'filter_model' => $this->filter_model,
			'minPrice' => $minPrice
		]);

		$pagination = PaginationWidget::widget([
			'total' => $items->pages,
			'current' => $page,
		]);

		$seo_type = $type ? $type : 'listing';
		$seo = $this->getSeo($seo_type, $page, $items->total);
		$seo['breadcrumbs'] = $breadcrumbs;
		$this->setSeo($seo, $page, $canonical);

		if($seo_type == 'listing' and count($params_filter) > 0){
			$seo['text_top'] = '';
			$seo['text_bottom'] = '';
		}

		$main_flag = ($seo_type == 'listing' and count($params_filter) == 0);

		$currentRestType = $this->getRestTypeDeclention($params_filter);

		// echo '<pre>';
		// print_r($params_filter['rest_type']);
		// exit;

		return $this->render('index.twig', array(
			'items' => $items->items,
			'filter' => $filter,
			'pagination' => $pagination,
			'seo' => $seo,
			'currentType' => Declension::get_num_ending($items->total, $currentRestType),
			'count' => $items->total,
			'menu' => $type,
			'main_flag' => $main_flag
		));	
	}

	public function actionAjaxFilter(){
		$params = $this->parseGetQuery(json_decode($_GET['filter'], true), $this->filter_model, $this->slices_model);

		$elastic_model = new ElasticItems;
		$items = new ItemsFilterElastic($params['params_filter'], $this->per_page, $params['page'], false, 'restaurants', $elastic_model);

		$pagination = PaginationWidget::widget([
			'total' => $items->pages,
			'current' => $params['page'],
		]);

		$slice_url = ParamsFromQuery::isSlice(json_decode($_GET['filter'], true));
		substr($params['listing_url'], 0, 1) == '?' ? $breadcrumbs = Breadcrumbs::get_breadcrumbs(2) : $breadcrumbs = Breadcrumbs::get_breadcrumbs(3, $slice_url);
		$seo_type = $slice_url ? $slice_url : 'listing';
		$seo = $this->getSeo($seo_type, $params['page'], $items->total);
		$seo['breadcrumbs'] = $breadcrumbs;

		$currentRestType = $this->getRestTypeDeclention($params['params_filter']);

		$title = $this->renderPartial('//components/generic/title.twig', array(
			'seo' => $seo,
			'count' => $items->total,
			'currentType' => Declension::get_num_ending($items->total, $currentRestType),
		));

		if($params['page'] == 1){
			$text_top = $this->renderPartial('//components/generic/text.twig', array('text' => $seo['text_top']));
			$text_bottom = $this->renderPartial('//components/generic/text.twig', array('text' => $seo['text_bottom']));
		}
		else{
			$text_top = '';
			$text_bottom = '';
		}

		if($seo_type == 'listing' and count($params['params_filter']) > 0){
			$text_top = '';
			$text_bottom = '';
		}

		$minPrice = 999999;
		foreach ($items->items as $item){
			if ($item->restaurant_price < $minPrice){
				$minPrice = $item->restaurant_price;
			}
		}

		return  json_encode([
			'listing' => $this->renderPartial('//components/generic/listing.twig', array(
				'items' => $items->items,
				'img_alt' => $seo['img_alt'],
			)),
			'pagination' => $pagination,
			'url' => $params['listing_url'],
			'title' => $title,
			'text_top' => $text_top,
			'text_bottom' => $text_bottom,
			'seo_title' => $seo['title'],
			'minPrice' => $minPrice,
			'params_filter' => $params['params_filter'],
		]);
	}

	public function actionAjaxFilterSlice(){
		$slice_url = ParamsFromQuery::isSlice(json_decode($_GET['filter'], true));

		return $slice_url;
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

		$temp_params = new ParamsFromQuery($getQuery, $filter_model, $this->slices_model);

		$return['params_api'] = $temp_params->params_api;
		$return['params_filter'] = $temp_params->params_filter;
		$return['listing_url'] = $temp_params->listing_url;
		$return['canonical'] = $temp_params->canonical;
		return $return;
	}

	private function getSeo($type, $page, $count = 0){
		$seo = new Seo($type, $page, $count);

		return $seo->seo;
	}

	private function setSeo($seo, $page, $canonical){
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		if($page != 1){
			$this->view->params['canonical'] = $canonical;
		}
        $this->view->params['kw'] = $seo['keywords'];
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

		if (isset($params_filter['rest_type']) && count($params_filter['rest_type']) === 1){
			$currentRestType = $restTypesList[$params_filter['rest_type'][0]];
		}

		return $currentRestType;
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