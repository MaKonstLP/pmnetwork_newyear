<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Restaurants;
use common\components\Breadcrumbs;
use common\models\ItemsWidget;

class ItemController extends Controller
{

	public function actionIndex($id)
	{

		$item = Restaurants::find()
			->where(['id' => $id])
			->one();

		//$item = ApiItem::getData($item->restaurants->gorko_id);

		$seo['h1'] = $item->name;
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(2);
		$seo['desc'] = $item->name;
		$seo['address'] = $item->address;

		$itemsWidget = new ItemsWidget;
		$other_rooms = $itemsWidget->getOther($item->id, $id);

		return $this->render('index.twig', array(
			'item' => $item,
			'queue_id' => $id,
			'seo' => $seo,
			'other_rooms' => $other_rooms
		));
	}

}