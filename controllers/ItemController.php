<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\elastic\RestaurantElastic;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use common\models\elastic\ItemsWidgetElastic;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Seo;
use frontend\components\QueryFromSlice;

class ItemController extends Controller
{

	public function actionIndex($id)
	{
		$elastic_model = new ElasticItems;
		$item = $elastic_model::get($id);

		if(!$item)
			throw new \yii\web\NotFoundHttpException();

		$seo = new Seo('item', 1, 0, $item, 'rest');
		$seo = $seo->seo;
        $this->setSeo($seo);

		//$item = ApiItem::getData($item->restaurants->gorko_id);
        $slice_obj = new QueryFromSlice(basename($_SERVER['HTTP_REFERER']));
		if($slice_obj->flag){
			$slice_alias = basename($_SERVER['HTTP_REFERER']);
		}
		else{
			$type = $item->restaurant_types[0]->id;
			$types = [
				1 => 'restorany',
				2 => 'banketnye-zaly',
				3 => 'kafe',
				4 => 'bary',
				16 => 'kluby',
			];
			$slice_alias = $types[$item->restaurant_types[0]['id']];
		}

		$seo['h1'] = $item->restaurant_name;
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(4, $slice_alias, ['id' => $item->id,'name' => $item->restaurant_name]);
		$seo['desc'] = $item->restaurant_name;
		$seo['address'] = $item->restaurant_address;

		$other_rooms = $item->rooms;

		//echo '<pre>';
		//print_r($item);
		//exit;

		return $this->render('index.twig', array(
			'item' => $item,
			'queue_id' => $id,
			'seo' => $seo,
			'other_rooms' => $other_rooms
		));
	}

	private function setSeo($seo){
        $this->view->title = $seo['title'];
        $this->view->params['desc'] = $seo['description'];
        $this->view->params['kw'] = $seo['keywords'];
    }

}