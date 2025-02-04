<?php

namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\elastic\RestaurantElastic;
use common\models\elastic\ItemsWidgetElastic;
use common\models\Seo;
use common\models\Subdomen;
use common\models\RestaurantsRedirect;
use common\models\RestaurantsUniqueId;
use common\models\RestaurantNotFound;
use frontend\components\QueryFromSlice;
use frontend\modules\gorko_ny\models\ElasticItems;
use frontend\modules\gorko_ny\models\RestaurantTypeSlice;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use frontend\modules\gorko_ny\components\RoomMicrodata;


class ItemController extends Controller
{

	public function actionIndex($id)
	{
		//проверяем, что указаны корректные get-параметры, а не только состоящие из цифр, например /?3&2&0&2&3&page=9 или /?1&0&6&6&page=10
		foreach ($_GET as $key => $value) {
			if (preg_match('/^\d+$/', $key)) {
				throw new NotFoundHttpException();
				break;
			}
		}

		if (strlen($id) > 9) {
			throw new NotFoundHttpException;
		}

		$elastic_model = new ElasticItems;
		$item = $elastic_model::find()
			->query(['bool' => ['must' => ['match' => ['restaurant_unique_id' => $id]]]])
			->limit(1)
			->search();

		if (!isset($item['hits']['hits'][0])) {
			//все записи из таблицы restaurants_redirect перенесены в таблицу restaurant_not_found
			// $redirect = RestaurantsRedirect::find()->where(['new_id' => $id])->one();
			// if ($redirect) {
			// 	return $this->redirect('https://' . (Yii::$app->params['subdomen_alias'] !== '' ? (Yii::$app->params['subdomen_alias'] . '.') : '') . 'korporativ-ng.ru/ploshhadki/' . $redirect->old_id . '/', 301);
			// }

			// если ресторан был, но выбыл
			$redirectToListing = RestaurantsUniqueId::find()->where(['unique_id' => $id])->one();
			if ($redirectToListing) {
				return $this->redirect('https://' . (Yii::$app->params['subdomen_alias'] !== '' ? (Yii::$app->params['subdomen_alias'] . '.') : '') . 'korporativ-ng.ru/ploshhadki/');
			}

			// если ресторан стал отдавать 404
			$redirectRestNotFound = RestaurantNotFound::find()->where(['id' => $id])->one();
			if ($redirectRestNotFound) {
				return $this->redirect('https://' . (Yii::$app->params['subdomen_alias'] !== '' ? (Yii::$app->params['subdomen_alias'] . '.') : '') . 'korporativ-ng.ru/ploshhadki/', 301);
			}

			throw new NotFoundHttpException;
		}

		$item = $item['hits']['hits'][0];

		if (!$this->checkSameSubdomen(Yii::$app->params['subdomen_alias'], $item->restaurant_city_id)) {
			throw new NotFoundHttpException;
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
				$item['doc_count'] > 3 && count($acc) < 5
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

		$this->setSubdomenCookie();

		$seo = new Seo('item', 1, 0, $item, 'rest');
		$seo = $seo->seo;

		$this->replaceSeoVariables($item, $seo);

		$this->setSeo($seo);

		//$item = ApiItem::getData($item->restaurants->gorko_id);
		if (isset($_SERVER['HTTP_REFERER'])) {
			$slice_obj = new QueryFromSlice(basename($_SERVER['HTTP_REFERER']));
		} else {
			$slice_obj = (object)['flag' => false];
		}

		if ($slice_obj->flag) {
			$slice_alias = basename($_SERVER['HTTP_REFERER']);
		} else {
			$type = $item->restaurant_types[0]['id'];
			$types = [
				1 => 'restorany',
				2 => 'banketnye-zaly',
				3 => 'kafe',
				4 => 'bary',
				16 => 'kluby',
			];
			if (isset($types[$item->restaurant_types[0]['id']])) {
				$slice_alias = $types[$item->restaurant_types[0]['id']];
			} else {
				$slice_alias = $types[1];
			}
		}

		$seo['h1'] = $item->restaurant_name;
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs(4, $slice_alias, ['id' => $item->id, 'name' => $item->restaurant_name]);
		$seo['desc'] = $item->restaurant_name;
		$seo['address'] = $item->restaurant_address;

		$other_rooms = $item->rooms;

		$microdata = RoomMicrodata::getRoomMicrodata($item);

		$restaurantSpec = '';
		$restaurantMainSpec = '';


		foreach ($item->restaurant_types as $type) {
			$restaurantSpec .= $type['name'] . ', ';
			if ($restaurantMainSpec === '') {
				$restaurantMainSpec = $type['name'];
			}
		}

		$restaurantSpec = substr($restaurantSpec, 0, -2);
		// echo '<pre>';
		// print_r($other_rooms);
		// exit;

		if ($item->restaurant_premium) Yii::$app->params['premium_rest'] = true;
		Yii::$app->params['page_rest'] = true;

		

		return $this->render('index.twig', array(
			'item' => $item,
			'queue_id' => $id,
			'seo' => $seo,
			'other_rooms' => $other_rooms,
			'microdata' => $microdata,
			'restaurantSpec' => $restaurantSpec,
			'restaurantMainSpec' => $restaurantMainSpec,
			'active_slices_menu' => $active_slices_menu,
		));
	}

	private function checkSameSubdomen($currentSubdomenAlias, $restaurantCityId)
	{
		$restaurantAlias = Subdomen::find()->where(['city_id' => $restaurantCityId])->one()->alias;

		if (!($currentSubdomenAlias === $restaurantAlias)) {
			return false;
		}

		return true;
	}

	private function setSeo($seo)
	{
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		$this->view->params['kw'] = $seo['keywords'];
	}

	private function replaceSeoVariables($item, &$seo)
	{
		$mainRestTypeDeclention = '';
		$restTypes = ArrayHelper::index($item['restaurant_types'], 'id');
		$MAIN_REST_TYPE_ORDER = [
			1 => "ресторане",
			2 => "банкетном зале",
			3 => "кафе",
			16 => "клубе",
			4 => "баре"
		];

		foreach ($MAIN_REST_TYPE_ORDER as $key => $value) {

			if ($mainRestTypeDeclention !== '') {
				break;
			}

			$mainRestTypeDeclention = array_key_exists($key, $restTypes) ? $value : '';
		}

		if ($mainRestTypeDeclention === '') {
			$mainRestTypeDeclention = "ресторане";
		}

		$seo['h1'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['h1']);
		$seo['title'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['title']);
		$seo['description'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['description']);
		$seo['keywords'] = str_replace('**about_rest_type**', $mainRestTypeDeclention, $seo['keywords']);

		$mainRestTypeDeclRod = '';
		$MAIN_REST_TYPE_ORDER_ROD = [
			1 => "ресторана",
			2 => "банкетного зала",
			3 => "кафе",
			16 => "клуба",
			4 => "бара"
		];

		foreach ($MAIN_REST_TYPE_ORDER_ROD as $key => $value) {

			if ($mainRestTypeDeclRod !== '') {
				break;
			}

			$mainRestTypeDeclRod = array_key_exists($key, $restTypes) ? $value : '';
		}

		if ($mainRestTypeDeclRod === '') {
			$mainRestTypeDeclRod = "ресторана";
		}

		$seo['h1'] = str_replace('**rest_type_rod**', $mainRestTypeDeclRod, $seo['h1']);
		$seo['title'] = str_replace('**rest_type_rod**', $mainRestTypeDeclRod, $seo['title']);
		$seo['description'] = str_replace('**rest_type_rod**', $mainRestTypeDeclRod, $seo['description']);
		$seo['keywords'] = str_replace('**rest_type_rod**', $mainRestTypeDeclRod, $seo['keywords']);
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
