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
use frontend\components\Breadcrumbs;
use backend\models\Pages;
use frontend\components\RoomsFilter;
use common\models\ItemsFilter;
use backend\models\Filter;
use backend\models\Slices;
use common\models\GorkoApi;

class ListingController extends Controller
{
	protected $per_page = 24;

	public $filter_model,
		   $slices_model;

	public function beforeAction($action)
	{
		$this->filter_model = Filter::find()->with('items')->orderBy(['sort' => SORT_ASC])->all();
		$this->slices_model = Slices::find()->all();

	    return parent::beforeAction($action);
	}

	public function actionSlice($slice)
	{
		$slice_obj = new QueryFromSlice($slice);
		if($slice_obj->flag){
			$this->view->params['menu'] = $slice;
			$params = $this->parseGetQuery($slice_obj->params, $this->filter_model, $this->slices_model);
			isset($_GET['page']) ? $params['page'] = $_GET['page'] : $params['page'];
			$this->setSeo($slice_obj->seo);
			$slice_obj->seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(2);
			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$seo 			=	$slice_obj->seo
			);
		}
		else{
			return $this->goHome();
		}				
	}

	public function actionIndex()
	{
		$getQuery = $_GET;
		unset($getQuery['q']);
		if(count($getQuery) > 0){
			$params = $this->parseGetQuery($getQuery, $this->filter_model, $this->slices_model);
			$seo = $params['seo'];
			$this->setSeo($seo);
			$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(1);
			return $this->actionListing(
				$page 			=	$params['page'],
				$per_page		=	$this->per_page,
				$params_filter	= 	$params['params_filter'],
				$seo 			=	$seo
			);	
		}
		else{
			$seo = Pages::find()->where(['name' => 'listing'])->one();
			$this->setSeo($seo);
			$seo->breadcrumbs = Breadcrumbs::get_breadcrumbs(1);
			return $this->actionListing(
				$page 			=	1,
				$per_page		=	$this->per_page,
				$params_filter	= 	[],
				$seo 			=	$seo
			);
		}
	}

	public function actionListing($page, $per_page, $params_filter, $seo)
	{
		$items = new ItemsFilter($params_filter, $per_page, $page, false, 'restaurants');

		$filter = FilterWidget::widget([
			'filter_active' => $params_filter,
			'filter_model' => $this->filter_model
		]);

		$pagination = PaginationWidget::widget([
			'total' => $items->pages,
			'current' => $page,
		]);

		return $this->render('index.twig', array(
			'items' => $items->items,
			'filter' => $filter,
			'pagination' => $pagination,
			'seo' => $seo,
			'count' => $items->total
		));	
	}

	public function actionAjaxFilter(){
		$params = $this->parseGetQuery(json_decode($_GET['filter'], true), $this->filter_model, $this->slices_model);

		$items = new ItemsFilter($params['params_filter'], $this->per_page, $params['page'], false, 'restaurants');

		$pagination = PaginationWidget::widget([
			'total' => $items->pages,
			'current' => $params['page'],
		]);

		substr($params['listing_url'], 0, 1) == '?' ? $breadcrumbs = Breadcrumbs::get_breadcrumbs(1) : $breadcrumbs = Breadcrumbs::get_breadcrumbs(2);
		$params['seo']['breadcrumbs'] = $breadcrumbs;

		$title = $this->renderPartial('//components/generic/title.twig', array(
			'seo' => $params['seo'],
			'count' => $items->total
		));

		$text_top = $this->renderPartial('//components/generic/text.twig', array('text' => $params['seo']['text_top']));
		$text_bottom = $this->renderPartial('//components/generic/text.twig', array('text' => $params['seo']['text_bottom']));		

		return  json_encode([
			'listing' => $this->renderPartial('//components/generic/listing.twig', array(
				'items' => $items->items,
				'img_alt' => $params['seo']['img_alt'],
			)),
			'pagination' => $pagination,
			'url' => $params['listing_url'],
			'title' => $title,
			'text_top' => $text_top,
			'text_bottom' => $text_bottom,
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
		$return['seo'] = $temp_params->seo;
		return $return;
	}

	private function setSeo($seo){
		$this->view->title = $seo['title'];
        $this->view->params['desc'] = $seo['description'];
        $this->view->params['kw'] = $seo['keywords'];
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