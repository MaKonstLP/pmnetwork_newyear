<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\elastic\RestaurantElastic;
use common\models\elastic\ItemsWidgetElastic;
use common\models\Seo;
use frontend\components\QueryFromSlice;
use frontend\modules\gorko_ny\models\ElasticItems;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use frontend\modules\gorko_ny\components\RoomMicrodata;

class ItemController extends Controller
{

	public function actionIndex($id)
	{
		$elastic_model = new ElasticItems;
		$item = $elastic_model::find()
		->query(['bool' => ['must' => ['match'=>['restaurant_unique_id' => $id]]]])
		->limit(1)
		->search();

		if (!isset($item['hits']['hits'][0])){
			// Yii::$app​->response->redirect('https://' . (Yii::$app->params['subdomen_alias'] !== '' ? (Yii::$app->params['subdomen_alias'] . '.') : '') . 'korporativ-ng.ru/ploshhadki/');
			return $this->redirect('https://' . (Yii::$app->params['subdomen_alias'] !== '' ? (Yii::$app->params['subdomen_alias'] . '.') : '') . 'korporativ-ng.ru/ploshhadki/');
			// throw new \yii\web\NotFoundHttpException();
		}


		$item = $item['hits']['hits'][0];

		

		$seo = new Seo('item', 1, 0, $item, 'rest');
		$seo = $seo->seo;

		$this->replaceSeoVariables($item, $seo);

		$this->setSeo($seo);

		//$item = ApiItem::getData($item->restaurants->gorko_id);
		if(isset($_SERVER['HTTP_REFERER'])){
			$slice_obj = new QueryFromSlice(basename($_SERVER['HTTP_REFERER']));
		}
		else{
			$slice_obj = (object)['flag' => false];
		}
        
		if($slice_obj->flag){
			$slice_alias = basename($_SERVER['HTTP_REFERER']);
		}
		else{
			$type = $item->restaurant_types[0]['id'];
			$types = [
				1 => 'restorany',
				2 => 'banketnye-zaly',
				3 => 'kafe',
				4 => 'bary',
				16 => 'kluby',
			];
			if(isset($types[$item->restaurant_types[0]['id']])){
				$slice_alias = $types[$item->restaurant_types[0]['id']];
			}
			else{
				$slice_alias = $types[1];
			}			
		}

		$seo['h1'] = $item->restaurant_name;
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(4, $slice_alias, ['id' => $item->id,'name' => $item->restaurant_name]);
		$seo['desc'] = $item->restaurant_name;
		$seo['address'] = $item->restaurant_address;

		$other_rooms = $item->rooms;

		$microdata = RoomMicrodata::getRoomMicrodata($item);

		$restaurantSpec = '';
		$restaurantMainSpec = '';


		foreach ($item->restaurant_types as $type){
			$restaurantSpec .= $type['name'] . ', ';
			if ($restaurantMainSpec === ''){
				$restaurantMainSpec = $type['name'];
			}
		}	

		$restaurantSpec = substr($restaurantSpec, 0, -2);
		// echo '<pre>';
		// print_r($seo);
		// exit;

		return $this->render('index.twig', array(
			'item' => $item,
			'queue_id' => $id,
			'seo' => $seo,
			'other_rooms' => $other_rooms,
			'microdata' => $microdata,
			'restaurantSpec' => $restaurantSpec,
			'restaurantMainSpec' => $restaurantMainSpec,
		));
	}

	private function setSeo($seo){
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		$this->view->params['kw'] = $seo['keywords'];
	}

	private function replaceSeoVariables($item, &$seo){
		$mainRestTypeDeclention = '';
		$restTypes = ArrayHelper::index($item['restaurant_types'], 'id');
		$MAIN_REST_TYPE_ORDER = [
			1 => "ресторане",
			2 => "банкетном зале",
			3 => "кафе",
			16 => "клубе",
			4 => "баре"
		];
	
		foreach ($MAIN_REST_TYPE_ORDER as $key => $value){

			if ($mainRestTypeDeclention !== ''){
				break;
			}

			$mainRestTypeDeclention = array_key_exists($key, $restTypes) ? $value : '';
		}

		if ($mainRestTypeDeclention === ''){
			$mainRestTypeDeclention = "ресторане";
		}

		$seo['h1'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['h1']);
		$seo['title'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['title']);
		$seo['description'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['description']);
		$seo['keywords'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['keywords']);
	}

}