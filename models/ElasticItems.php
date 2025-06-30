<?php

namespace frontend\modules\gorko_ny\models;

use Yii;
use common\models\Restaurants;
use common\models\RestaurantsModule;
use common\models\RestaurantsTypes;
use yii\helpers\ArrayHelper;
use common\models\Subdomen;
use common\models\RestaurantsSpec;
use common\models\RestaurantsSpecial;
use common\models\RestaurantsExtra;
use common\models\RestaurantsPremium;
use common\models\RestaurantsLocation;
use common\models\ImagesModule;
use common\components\AsyncRenewImages;
use common\models\RestaurantsUniqueId;
use common\widgets\ProgressWidget;
use common\models\RoomsSpec;
use common\models\premium\PremiumRest;
use frontend\modules\pmnbd\models\SubdomenFilteritem;
use common\models\FilterItems;
use common\models\elastic\FilterQueryConstructorElastic;

class ElasticItems extends \yii\elasticsearch\ActiveRecord
{
	public function attributes()
	{
		return [
			'id',
			'restaurant_id',
			'restaurant_gorko_id',
			'restaurant_min_capacity',
			'restaurant_max_capacity',
			'restaurant_district',
			'restaurant_parent_district',
			'restaurant_city_id',
			'restaurant_alcohol',
			'restaurant_firework',
			'restaurant_name',
			'restaurant_address',
			'restaurant_cover_url',
			'restaurant_latitude',
			'restaurant_longitude',
			'restaurant_own_alcohol',
			'restaurant_cuisine',
			'restaurant_parking',
			'restaurant_extra_services',
			'restaurant_payment',
			'restaurant_special',
			'restaurant_phone',
			'restaurant_images',
			'restaurant_commission',
			'restaurant_types',
			'restaurant_location',
			'restaurant_price',
			'restaurant_text',
			'restaurant_unique_id',
			'restaurant_rating',
			'restaurant_premium',
			'restaurant_premium_features',
			'restaurant_rev_ya',
			'rooms',
			'premium_url',
		];
	} 

	public static function index()
	{
		return \Yii::$app->params['module_api_config']['korporativ']['elastic']['index'];
	}

	public static function type()
	{
		return \Yii::$app->params['module_api_config']['korporativ']['elastic']['type'];
	}

	/**
	 * @return array This model's mapping
	 */
	public static function mapping()
	{
		return [
			static::type() => [
				'properties' => [
					'id'                            => ['type' => 'integer'],
					'restaurant_id'                 => ['type' => 'integer'],
					'restaurant_gorko_id'           => ['type' => 'integer'],
					'restaurant_min_capacity'       => ['type' => 'integer'],
					'restaurant_max_capacity'       => ['type' => 'integer'],
					'restaurant_district'           => ['type' => 'integer'],
					'restaurant_parent_district'    => ['type' => 'integer'],
					'restaurant_city_id'            => ['type' => 'integer'],
					'restaurant_alcohol'            => ['type' => 'integer'],
					'restaurant_firework'           => ['type' => 'integer'],
					'restaurant_price'              => ['type' => 'integer'],
					'restaurant_unique_id'          => ['type' => 'integer'],
					'restaurant_name'               => ['type' => 'text'],
					'restaurant_address'            => ['type' => 'text'],
					'restaurant_cover_url'          => ['type' => 'text'],
					'restaurant_latitude'           => ['type' => 'text'],
					'restaurant_longitude'          => ['type' => 'text'],
					'restaurant_own_alcohol'        => ['type' => 'text'],
					'restaurant_cuisine'            => ['type' => 'text'],
					'restaurant_parking'            => ['type' => 'integer'],
					'restaurant_extra_services'     => ['type' => 'text'],
					'restaurant_payment'            => ['type' => 'text'],
					'restaurant_special'            => ['type' => 'text'],
					'restaurant_phone'              => ['type' => 'text'],
					'restaurant_commission'         => ['type' => 'integer'],
					'restaurant_rating'             => ['type' => 'integer'],
					'restaurant_premium'            => ['type' => 'integer'],
					'restaurant_premium_features'      => ['type' => 'nested', 'properties' => [
						'field'      => ['type' => 'text'],
						'value'      => ['type' => 'object','dynamic' => true],
					]],
					'restaurant_text'               => ['type' => 'text'],
					'restaurant_types'              => ['type' => 'nested', 'properties' => [
						'id'                            => ['type' => 'integer'],
						'name'                          => ['type' => 'text'],
					]],
					'restaurant_location'              => ['type' => 'nested', 'properties' => [
						'id'                            => ['type' => 'integer'],
					]],
					'restaurant_images'             => ['type' => 'nested', 'properties' => [
						'id'                            => ['type' => 'integer'],
						'sort'                          => ['type' => 'integer'],
						'realpath'                      => ['type' => 'text'],
						'subpath'                       => ['type' => 'text'],
						'waterpath'                     => ['type' => 'text'],
						'timestamp'                     => ['type' => 'text'],
					]],
					'restaurant_rev_ya'             => ['type' => 'nested', 'properties' => [
						'id'                            => ['type' => 'long'],
						'rate'                          => ['type' => 'text'],
						'count'                         => ['type' => 'text'],
					]],
					'premium_url'                             	=> ['type' => 'text'],
					'rooms'                         => ['type' => 'nested', 'properties' => [
						'id'                            => ['type' => 'integer'],
						'gorko_id'                      => ['type' => 'integer'],
						'restaurant_id'                 => ['type' => 'integer'],
						'price'                         => ['type' => 'integer'],
						'capacity_reception'            => ['type' => 'integer'],
						'capacity'                      => ['type' => 'integer'],
						'capacity_min'                  => ['type' => 'integer'],
						'type'                          => ['type' => 'integer'],
						'rent_only'                     => ['type' => 'integer'],
						'banquet_price'                 => ['type' => 'integer'],
						'bright_room'                   => ['type' => 'integer'],
						'separate_entrance'             => ['type' => 'integer'],
						'type_name'                     => ['type' => 'text'],
						'name'                          => ['type' => 'text'],
						'features'                      => ['type' => 'text'],
						'cover_url'                     => ['type' => 'text'],
						'images'                        => ['type' => 'nested', 'properties' => [
							'id'                            => ['type' => 'integer'],
							'sort'                          => ['type' => 'integer'],
							'realpath'                      => ['type' => 'text'],
							'subpath'                       => ['type' => 'text'],
							'waterpath'                     => ['type' => 'text'],
							'timestamp'                     => ['type' => 'text'],
						]],
					]]
				]
			],
		];
	}

	/**
	 * Set (update) mappings for this model
	 */
	public static function updateMapping()
	{
		$db = static::getDb();
		$command = $db->createCommand();
		$command->setMapping(static::index(), static::type(), static::mapping());
	}

	/**
	 * Create this model's index
	 */
	public static function createIndex()
	{
		$db = static::getDb();
		$command = $db->createCommand();
		$command->createIndex(static::index(), [
			'settings' => [
				'number_of_replicas' => 0,
				'number_of_shards' => 1,
			],
			'mappings' => static::mapping(),
			//'warmers' => [ /* ... */ ],
			//'aliases' => [ /* ... */ ],
			//'creation_date' => '...'
		]);
	}

	/**
	 * Delete this model's index
	 */
	public static function deleteIndex()
	{
		$db = static::getDb();
		$command = $db->createCommand();
		$command->deleteIndex(static::index(), static::type());
	}

	public static function refreshIndex($params)
	{
		$res = self::deleteIndex();
		$res = self::updateMapping();
		$res = self::createIndex();
		$res = self::updateIndex($params);
	}

	public static function refreshItem($params)
	{
		$item = self::findOne($params['gorko_id']);

		if($item){
			$item->delete();
		}

		$res = self::updateIndex($params);
	}

	public static function updateIndex($params)
	{
		$connection = new \yii\db\Connection($params['main_connection_config']);
		$connection->open();
		Yii::$app->set('db', $connection);

		$restaurants_types = RestaurantsTypes::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_types = ArrayHelper::index($restaurants_types, 'value');

		$restaurants_specials = RestaurantsSpecial::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_specials = ArrayHelper::index($restaurants_specials, 'value');

		$restaurants_extra = RestaurantsExtra::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_extra = ArrayHelper::index($restaurants_extra, 'value');

		$restaurants_spec = RestaurantsSpec::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_spec = ArrayHelper::index($restaurants_spec, 'id');

		$restaurants_location = RestaurantsLocation::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_location = ArrayHelper::index($restaurants_location, 'value');

		$restaurants = Restaurants::find()
			->with('rooms')
			->with('imagesext')
			->with('subdomen')
			->with('yandexReview')
			//->where(['active' => 1, 'commission' => 2])
			//->where(['gorko_id' => [422059]])
			->limit(100000);

		if (!empty($params['gorko_id'])) {
			$restaurants->andWhere(['gorko_id' => $params['gorko_id']]);
		}

		$restaurants = $restaurants->all();

		$subdomens = Subdomen::find()
			->select(['alias', 'city_id'])
			->indexBy('city_id')
			->column();

		$connection = new \yii\db\Connection($params['premium_connection_config']);
		$connection->open();
		Yii::$app->set('db', $connection);

		$now = time();
		$channel_id = 1;
		$restaurants_premium_base = ArrayHelper::map(
			PremiumRest::find()
				->where(['wait' => 0, 'channel' => $channel_id, 'active' => 1])
				// ->andWhere(['>', 'finish', $now])
				->with('premiumFeature')
				->orderBy(['gorko_id'=>SORT_ASC])
				->limit(100000)
				->all(),
			'gorko_id', 
			function($model){ return $model->toArray([], ['premiumFeature.elasticValue']); }
		);

		$connection = new \yii\db\Connection($params['site_connection_config']);
		$connection->open();
		Yii::$app->set('db', $connection);

		$images_module = ImagesModule::find()
			->limit(500000)
			->asArray()
			->all();
		$images_module = ArrayHelper::index($images_module, 'gorko_id');

		$restaurants_unique_id = RestaurantsUniqueId::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_unique_id = ArrayHelper::index($restaurants_unique_id, 'id');

		//foreach ($restaurants as $key => $value) {
		//    if ($value->gorko_id == 479023) {
		//        echo "lalal";
		//    }
		//    
		//}
		//exit;

		$rest_count = count($restaurants);
		$rest_iter = 0;
		foreach ($restaurants as $restaurant) {
			$res = self::addRecord($restaurant, $restaurants_types, $restaurants_spec, $restaurants_specials, $restaurants_extra, $restaurants_location, $images_module, $restaurants_unique_id, $params, $restaurants_premium_base, $subdomens);
			echo ProgressWidget::widget(['done' => $rest_iter++, 'total' => $rest_count]);
		}

		self::subdomenCheck();

		echo 'Обновление индекса ' . self::index() . ' ' . self::type() . ' завершено' . "\n";
	}

	public static function addRecord($restaurant, $restaurants_types, $restaurants_spec, $restaurants_specials, $restaurants_extra, $restaurants_location, $images_module, $restaurants_unique_id, $params, $restaurants_premium_base, $subdomens)
	{
		$premium = isset($restaurants_premium_base[$restaurant->gorko_id]);

		if($restaurant->premium_active == 1 && !isset($restaurants_premium_base[$restaurant->gorko_id]) && $restaurant->gorko_id !== 455577){
            return true;
        }

		if (!$premium) {
			$restaurant_spec_white_list = [17];
			$restaurant_spec_rest = explode(',', $restaurant->restaurants_spec);
			if (count(array_intersect($restaurant_spec_white_list, $restaurant_spec_rest)) === 0) {
				return 'Неподходящий тип мероприятия';
			}

			if (!$restaurant->active) {
				return 'Не активен';
			}

			if (!$restaurant->commission) {
				return 'Не платный';
			}
		}

		$isExist = false;

		try {
			$record = self::get($restaurant->gorko_id);
			if (!$record) {
				$record = new self();
				$record->setPrimaryKey($restaurant->gorko_id);
			} else {
				$isExist = true;
			}
		} catch (\Exception $e) {
			$record = new self();
			$record->setPrimaryKey($restaurant->gorko_id);
			print_r($restaurant->gorko_id);
		}




		//if(!$restaurant->subdomen->active){
		//    return 'Мало ресторанов';
		//}

		//Св-ва ресторана
		$record->id = $restaurant->id;
		$record->restaurant_commission = $restaurant->commission;
		$record->restaurant_id = $restaurant->id;
		$record->restaurant_gorko_id = $restaurant->gorko_id;
		$record->restaurant_min_capacity = $restaurant->min_capacity;
		$record->restaurant_max_capacity = $restaurant->max_capacity;
		$record->restaurant_district = $restaurant->district;
		$record->restaurant_parent_district = $restaurant->parent_district;
		$record->restaurant_city_id = $restaurant->city_id;
		$record->restaurant_alcohol = $restaurant->alcohol;
		switch ($restaurant->gorko_id) {
			case 481090:
				$record->restaurant_alcohol = 0;
				break;
			
			default:
				$record->restaurant_alcohol = $restaurant->alcohol;
				break;
		}
		$record->restaurant_firework = $restaurant->firework;
		$record->restaurant_name = $restaurant->name;
		switch ($restaurant->gorko_id) {
			case 483969:
				$record->restaurant_name = 'Клуб виртуальной реальности ' . $restaurant->name;
				break;
			
			default:
				$record->restaurant_name = $restaurant->name;
				break;
		}
		$record->restaurant_address = $restaurant->address;
		$record->restaurant_cover_url = $restaurant->cover_url;
		$record->restaurant_latitude = $restaurant->latitude;
		$record->restaurant_longitude = $restaurant->longitude;
		$record->restaurant_own_alcohol = $restaurant->own_alcohol;
		
		$record->restaurant_cuisine = $restaurant->cuisine;
		$record->restaurant_parking = $restaurant->parking;
		$record->restaurant_extra_services = $restaurant->extra_services;
		$record->restaurant_payment = $restaurant->payment;
		$record->restaurant_special = $restaurant->special;
		switch ($restaurant->gorko_id) {
			case 456549:
				$record->restaurant_phone = '7 (495) 107-55-22';
				break;
			case 120863:
					$record->restaurant_phone = '7 (969) 193-68-51';
					break;
			case 479023:
				$record->restaurant_phone = '8(8352) 49-30-30';
				break;
			case 449941:
				$record->restaurant_phone = '7 (915) 612-15-15';
				break;
			case 483343:
				$record->restaurant_phone = '7 (963) 716-59-17';
				break;
			case 455577:
				$record->restaurant_phone = '7 (923) 775-08-03';
				break;
			case 477647:
				$record->restaurant_phone = '7 (495) 960-18-76';
				break;
			default:
				$record->restaurant_phone = $restaurant->phone;
				break;
		}

		$premium_restaurant_price = 0;
		$rooms_name = [];
		$rooms_hide = [];
		$rooms_price = [];

		$record->restaurant_premium = 0;
		if ($premium){
			$record->restaurant_premium = 1;
		}

		if(!empty($restaurants_premium_base[$restaurant->gorko_id]['premiumFeature'])){
			$premium_features = [];
			foreach ($restaurants_premium_base[$restaurant->gorko_id]['premiumFeature'] as $premium_feature) {
				$premium_features[$premium_feature['feature_id']] = [
					'field' => $premium_feature['feature_id'],
					'value' => $premium_feature['elasticValue'],
				];
			}
			$record->restaurant_premium_features = $premium_features;

			/** begin - применение свойств из премиум базы */
			if(!empty($premium_features[15]['value'])) $rooms_hide = $premium_features[15]['value']; // скрытие зала
			if(!empty($premium_features[14]['value'])) $rooms_name = $premium_features[14]['value']; // название зала
			if(!empty($premium_features[18]['value'])) $premium_restaurant_price = $premium_features[18]['value'];
			if(!empty($premium_features[19]['value'])) $rooms_price = $premium_features[19]['value'];
			/** end */
		}

		if(!empty($restaurants_premium_base[$restaurant->gorko_id]['proxy_phone'])){
			$proxy_phone = $restaurants_premium_base[$restaurant->gorko_id]['proxy_phone'];
			if(  preg_match( '/^\+\d(\d{3})(\d{3})(\d{2})(\d{2})$/', $proxy_phone,  $matches ) )
            {
                $proxy_phone_pretty = '+7 ('.$matches[1].') '.$matches[2].'-'. $matches[3].'-'. $matches[4];
            }
			$record->restaurant_phone = $proxy_phone_pretty;
		}

		$restaurant->rating ? $record->restaurant_rating = $restaurant->rating : $record->restaurant_rating = 90;

		//массив для сортировки фото: сначала выводятся фото самых больших по вместительности залов
		$rooms_img_sort = array();
		foreach ($restaurant->rooms as $key => $room) {
			$rooms_img_sort[$room->gorko_id] = $room->capacity;
		}

		//Картинки ресторана
		$images = [];

		$group = array();
		foreach ($restaurant->imagesext as $value) {
			$group[$value['room_id']][] = $value;
		}
		$images_sorted = array();
		$room_ids = array();

		//print_r($room_filtered_ids);
		foreach ($group as $room_id => $images_ext) {

			if (!isset($rooms_img_sort[$room_id])) continue;
			$room_ids[$rooms_img_sort[$room_id]] = $room_id;
			foreach ($images_ext as $image) {
				$images_sorted[$room_id][$image['event_id']][] = $image;
			}
		}
		krsort($room_ids);


		$loaded_imgs = [];

		//Уникальные св-ва для ресторанов в модуле
		if (isset($restaurants_unique_id[$restaurant->gorko_id]) && $restaurants_unique_id[$restaurant->gorko_id]['unique_id']) {
			$record->restaurant_unique_id = $restaurants_unique_id[$restaurant->gorko_id]['unique_id'];
		} else {
			$restaurants_unique_id_upd = new RestaurantsUniqueId();
			$new_id = RestaurantsUniqueId::find()->max('unique_id') + 1;
			$restaurants_unique_id_upd->unique_id = $new_id;
			$restaurants_unique_id_upd->id = $restaurant->gorko_id;
			$restaurants_unique_id_upd->save();
			$record->restaurant_unique_id = $new_id;
		}

		if(isset($subdomens[$restaurant->city_id])) {
			$record->premium_url = sprintf(
				'https://%s%s/ploshhadki/%d/', 
				($subdomens[$restaurant->city_id] == '' ? '' : $subdomens[$restaurant->city_id] . '.'), 
				'korporativ-ng.ru',
				$record->restaurant_unique_id
			);
		}

		//Отзывы с Яндекса из общей базы
		$reviews = [];
		if (isset($restaurant->yandexReview)) {
			$reviews['id'] = $restaurant->yandexReview['rev_ya_id'];
			$reviews['rate'] = $restaurant->yandexReview['rev_ya_rate'];
			$reviews['count'] = $restaurant->yandexReview['rev_ya_count'];
		}
		$record->restaurant_rev_ya = $reviews;

		//Тип помещения
		$premium_types = [
			1 => 'Ресторан',
			2 => 'Банкетный зал'
		];
		$restaurant_types = [];
		$restaurant_types_rest = explode(',', $restaurant->type);
		foreach ($restaurant_types_rest as $key => $value) {
			if($restaurant->gorko_id == 477647 && ($value == 17 || $value == 34 || $value == 15 || $value == 1)){
				continue;
			}
			$restaurant_types_arr = [];
			$restaurant_types_arr['id'] = $value;
			$restaurant_types_arr['name'] = isset($restaurants_types[$value]['text']) ? $restaurants_types[$value]['text'] : '';
			array_push($restaurant_types, $restaurant_types_arr);
		}
		if ($premium) {
			foreach ($premium_types as $premium_type => $premium_type_text) {
				if($restaurant->gorko_id == 477647 && $premium_type == 1){
					continue;
				}
				if (!in_array($premium_type, $restaurant_types_rest)) {
					$restaurant_types_arr = [];
					$restaurant_types_arr['id'] = $premium_type;
					$restaurant_types_arr['name'] = $premium_type_text;
					array_push($restaurant_types, $restaurant_types_arr);
				}
			}
		}

		$record->restaurant_types = $restaurant_types;

		//Тип локации
		$restaurant_location = [];
		$restaurant_location_rest = explode(',', $restaurant->location);
		foreach ($restaurant_location_rest as $key => $value) {
			$restaurant_location_arr = [];
			$restaurant_location_arr['id'] = $value;
			array_push($restaurant_location, $restaurant_location_arr);
		}
		$record->restaurant_location = $restaurant_location;

		//Св-ва залов
		$rooms = [];
		$restaurant_price = 9999999999;

		$room_filtered_ids = [];
		$specs = [17, 1, 0]; //Сначала новогодние, потом свадебные, потом по-умолчанию

		foreach ($restaurant->rooms as $key => $room) {
			
			if ($room->gorko_id == 21231) {
				$spec_price = 5000;
			} elseif ($room->gorko_id == 273845) {
				$spec_price = 1500;
			} elseif ($room->gorko_id == 273621) {
				$spec_price = 1500;
			} elseif ($room->gorko_id == 281095) {
				$spec_price = 1800;
			} else {
				if (!RoomsSpec::checkRoomSpecsByRoom($room->id, 17)) continue;
				$spec_price = RoomsSpec::getSpecPriceByRoom($room->id, 17);
			}

			if(is_array($rooms_hide) && !empty($rooms_hide) && in_array($room->gorko_id, $rooms_hide)){
				continue;
			}

			$name = $room->name;
			if(!empty($rooms_name[$room->gorko_id])){
				$name = $rooms_name[$room->gorko_id];
			}

			$room_payment_model = $room->payment_model;
			$room_price = $room->price;
			$room_banquet_price = $room->banquet_price;
			$room_banquet_price_person = $room->banquet_price_person;
			$room_banquet_price_min = $room->banquet_price_min;
			$room_rent_only = $room->rent_only;
			$room_rent_room_only = $room->rent_room_only;
			if(!empty($rooms_price[$room->gorko_id])){
				$rooms_price_item = $rooms_price[$room->gorko_id];

				if(isset($rooms_price_item['payment_model']) and $rooms_price_item['payment_model'] !== '') $room_payment_model = $rooms_price_item['payment_model'];
				if(isset($rooms_price_item['price']) and $rooms_price_item['price'] !== '') $room_price = $rooms_price_item['price'];
				if(isset($rooms_price_item['banquet_price']) and $rooms_price_item['banquet_price'] !== '') $room_banquet_price = $rooms_price_item['banquet_price'];
				if(isset($rooms_price_item['banquet_price_person']) and $rooms_price_item['banquet_price_person'] !== '') $room_banquet_price_person = $rooms_price_item['banquet_price_person'];
				if(isset($rooms_price_item['banquet_price_min']) and $rooms_price_item['banquet_price_min'] !== '') $room_banquet_price_min = $rooms_price_item['banquet_price_min'];
				if(isset($rooms_price_item['rent_only']) and $rooms_price_item['rent_only'] !== '') $room_rent_only = $rooms_price_item['rent_only'];
				if(isset($rooms_price_item['rent_room_only']) and $rooms_price_item['rent_room_only'] !== '') $room_rent_room_only = $rooms_price_item['rent_room_only'];
			}

			
			$room_arr = [];
			$room_arr['id'] = $room->id;
			$room_arr['gorko_id'] = $room->gorko_id;
			$room_arr['restaurant_id'] = $room->restaurant_id;
			$room_arr['capacity_reception'] = $room->capacity_reception;
			$room_arr['capacity'] = $room->capacity;
			$room_arr['capacity_min'] = $room->capacity_min;
			$room_arr['type'] = $room->type;
			$room_arr['rent_only'] = $room_rent_only;
			$room_arr['banquet_price'] = $room_banquet_price;
			$room_arr['bright_room'] = $room->bright_room;
			$room_arr['separate_entrance'] = $room->separate_entrance;
			$room_arr['type_name'] = $room->type_name;
			$room_arr['name'] = $name;
			$room_arr['features'] = $room->features;
			$room_arr['cover_url'] = $room->cover_url;
			$room_arr['price'] = $spec_price ? $spec_price : $room_price;
			if (($room_arr['price'] < $restaurant_price) and $room_arr['price'])
				$restaurant_price = $room_arr['price'];

			//Картинки залов
			$images = [];
			$image_flag = false;
			if($room->gorko_id == 287077){
				array_push($images, [
					'id' => 1,
					'sort' => 1,
					'realpath' => '/img/477647-287077/477647-287077-1.jpeg',
					'subpath' => '/img/477647-287077/477647-287077-1.jpeg',
					'waterpath' => '/img/477647-287077/477647-287077-1.jpeg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 2,
					'sort' => 2,
					'realpath' => '/img/477647-287077/477647-287077-2.jpeg',
					'subpath' => '/img/477647-287077/477647-287077-2.jpeg',
					'waterpath' => '/img/477647-287077/477647-287077-2.jpeg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 3,
					'sort' => 3,
					'realpath' => '/img/477647-287077/477647-287077-3.jpeg',
					'subpath' => '/img/477647-287077/477647-287077-3.jpeg',
					'waterpath' => '/img/477647-287077/477647-287077-3.jpeg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 4,
					'sort' => 4,
					'realpath' => '/img/477647-287077/477647-287077-4.png',
					'subpath' => '/img/477647-287077/477647-287077-4.png',
					'waterpath' => '/img/477647-287077/477647-287077-4.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 5,
					'sort' => 5,
					'realpath' => '/img/477647-287077/477647-287077-5.png',
					'subpath' => '/img/477647-287077/477647-287077-5.png',
					'waterpath' => '/img/477647-287077/477647-287077-5.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 6,
					'sort' => 6,
					'realpath' => '/img/477647-287077/477647-287077-6.png',
					'subpath' => '/img/477647-287077/477647-287077-6.png',
					'waterpath' => '/img/477647-287077/477647-287077-6.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 7,
					'sort' => 7,
					'realpath' => '/img/477647-287077/477647-287077-7.png',
					'subpath' => '/img/477647-287077/477647-287077-7.png',
					'waterpath' => '/img/477647-287077/477647-287077-7.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 8,
					'sort' => 8,
					'realpath' => '/img/477647-287077/477647-287077-8.png',
					'subpath' => '/img/477647-287077/477647-287077-8.png',
					'waterpath' => '/img/477647-287077/477647-287077-8.png',
					'timestamp' => 1,
				]);
			}
			elseif($room->gorko_id == 270219){
				array_push($images, [
					'id' => 1,
					'sort' => 1,
					'realpath' => '/img/477647-270219/477647-270219-1.jpg',
					'subpath' => '/img/477647-270219/477647-270219-1.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-1.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 2,
					'sort' => 2,
					'realpath' => '/img/477647-270219/477647-270219-2.png',
					'subpath' => '/img/477647-270219/477647-270219-2.png',
					'waterpath' => '/img/477647-270219/477647-270219-2.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 3,
					'sort' => 3,
					'realpath' => '/img/477647-270219/477647-270219-3.png',
					'subpath' => '/img/477647-270219/477647-270219-3.png',
					'waterpath' => '/img/477647-270219/477647-270219-3.png',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 4,
					'sort' => 4,
					'realpath' => '/img/477647-270219/477647-270219-4.jpg',
					'subpath' => '/img/477647-270219/477647-270219-4.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-4.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 5,
					'sort' => 5,
					'realpath' => '/img/477647-270219/477647-270219-5.jpg',
					'subpath' => '/img/477647-270219/477647-270219-5.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-5.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 6,
					'sort' => 6,
					'realpath' => '/img/477647-270219/477647-270219-6.jpg',
					'subpath' => '/img/477647-270219/477647-270219-6.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-6.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 7,
					'sort' => 7,
					'realpath' => '/img/477647-270219/477647-270219-7.jpg',
					'subpath' => '/img/477647-270219/477647-270219-7.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-7.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 8,
					'sort' => 8,
					'realpath' => '/img/477647-270219/477647-270219-8.jpg',
					'subpath' => '/img/477647-270219/477647-270219-8.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-8.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 9,
					'sort' => 9,
					'realpath' => '/img/477647-270219/477647-270219-9.jpg',
					'subpath' => '/img/477647-270219/477647-270219-9.jpg',
					'waterpath' => '/img/477647-270219/477647-270219-9.jpg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 10,
					'sort' => 10,
					'realpath' => '/img/477647-270219/477647-270219-10.jpeg',
					'subpath' => '/img/477647-270219/477647-270219-10.jpeg',
					'waterpath' => '/img/477647-270219/477647-270219-10.jpeg',
					'timestamp' => 1,
				]);
				array_push($images, [
					'id' => 11,
					'sort' => 11,
					'realpath' => '/img/477647-270219/477647-270219-11.jpeg',
					'subpath' => '/img/477647-270219/477647-270219-11.jpeg',
					'waterpath' => '/img/477647-270219/477647-270219-11.jpeg',
					'timestamp' => 1,
				]);
			}
			else{
				foreach ($specs as $spec) {
					for ($i = 0; $i < 20; $i++) {
						if (isset($images_sorted[$room->gorko_id]) && isset($images_sorted[$room->gorko_id][$spec]) && isset($images_sorted[$room->gorko_id][$spec][$i])) {
							$image = $images_sorted[$room->gorko_id][$spec][$i];
							$image_arr = [];
							$image_arr['id'] = $image['gorko_id'];
							$image_arr['sort'] = $image['sort'];
							$image_arr['realpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $image['path']);;
							if (isset($images_module[$image['gorko_id']])) {
								$image_arr['subpath']   = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
								$image_arr['waterpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
								$image_arr['timestamp'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
							} else {
								if (!in_array($image['gorko_id'], $loaded_imgs)) {
									$queue_id = Yii::$app->queue->push(new AsyncRenewImages([
										'gorko_id'      => $image['gorko_id'],
										'params'        => $params,
										'rest_flag'     => false,
										'rest_gorko_id' => $restaurant->gorko_id,
										'room_gorko_id' => $room->gorko_id,
										'elastic_index' => static::index(),
										'elastic_type'  => 'rest',
									]));
								}
							}
							array_push($images, $image_arr);
						}
						if (count($images) > 19) {
							$image_flag = true;
							break;
						}
					}
					if ($image_flag) break;
				}
			}
			$room_arr['images'] = $images;
			
		    $room_filtered_ids[] = $room->gorko_id;
			array_push($rooms, $room_arr);
		}

		

		if (empty($rooms)) return 'Нет залов для празднования НГ';
		array_multisort(array_column($rooms, 'capacity'), SORT_DESC, $rooms);
		$record->rooms = $rooms;

		$record->restaurant_price = $restaurant_price;
		
		//массив для сортировки фото: сначала выводятся фото самых больших по вместительности залов
		$rooms_img_sort = array();
		foreach ($restaurant->rooms as $key => $room) {
			$rooms_img_sort[$room->gorko_id] = $room->capacity;
		}

		//Картинки ресторана
		$images = [];

		$group = array();
		foreach ($restaurant->imagesext as $value) {
			$group[$value['room_id']][] = $value;
		}
		$images_sorted = array();
		$room_ids = array();

		foreach ($group as $room_id => $images_ext) {
			if (!isset($rooms_img_sort[$room_id])) continue;

			// $room_ids[$rooms_img_sort[$room_id]] = $room_id;
			$key = $rooms_img_sort[$room_id];
			while (array_key_exists($key, $room_ids)) {
				$key++;
			}
			$room_ids[$key] = $room_id;
			
			foreach ($images_ext as $image) {
				$images_sorted[$room_id][$image['event_id']][] = $image;
			}
		}
		krsort($room_ids);

		$loaded_imgs = [];

		$image_flag = false;
		if ($restaurant->gorko_id == 479023) {
			foreach ($specs as $spec) {
				//for ($i=0; $i < 20; $i++) {
				if (isset($images_sorted[273845]) && isset($images_sorted[273845][$spec])) {
					for ($j = 0; $j < count($images_sorted[273845][$spec]); $j++) {
						$image = $images_sorted[273845][$spec][$j];
						$image_arr = [];
						$image_arr['id'] = $image['gorko_id'];
						$image_arr['sort'] = $image['sort'];
						$search = ['lh3.googleusercontent.com', 'nocdn.gorko.ru'];
						// $image_arr['realpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $image['path']);
						$image_arr['realpath'] = str_replace($search, 'img.korporativ-ng.ru', $image['path']);
						if (isset($images_module[$image['gorko_id']])) {
							//  $image_arr['subpath']   = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
							//  $image_arr['waterpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
							//  $image_arr['timestamp'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
							$image_arr['subpath']   = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
							$image_arr['waterpath'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
							$image_arr['timestamp'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
						} else {
							$queue_id = Yii::$app->queue->push(new AsyncRenewImages([
								'gorko_id'      => $image['gorko_id'],
								'params'        => $params,
								'rest_flag'     => true,
								'rest_gorko_id' => $restaurant->gorko_id,
								'room_gorko_id' => false,
								'elastic_index' => static::index(),
								'elastic_type'  => 'rest',
							]));
							$loaded_imgs[] = $image['gorko_id'];
						}
						array_push($images, $image_arr);

						if (count($images) > 19) {
							$image_flag = true;
							break;
						}
					}
				}
				if (isset($images_sorted[273621]) && isset($images_sorted[273621][$spec])) {
					for ($j = 0; $j < count($images_sorted[273621][$spec]); $j++) {
						$image = $images_sorted[273621][$spec][$j];
						$image_arr = [];
						$image_arr['id'] = $image['gorko_id'];
						$image_arr['sort'] = $image['sort'];
						$search = ['lh3.googleusercontent.com', 'nocdn.gorko.ru'];
						// $image_arr['realpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $image['path']);
						$image_arr['realpath'] = str_replace($search, 'img.korporativ-ng.ru', $image['path']);
						if (isset($images_module[$image['gorko_id']])) {
							//  $image_arr['subpath']   = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
							//  $image_arr['waterpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
							//  $image_arr['timestamp'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
							$image_arr['subpath']   = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
							$image_arr['waterpath'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
							$image_arr['timestamp'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
						} else {
							$queue_id = Yii::$app->queue->push(new AsyncRenewImages([
								'gorko_id'      => $image['gorko_id'],
								'params'        => $params,
								'rest_flag'     => true,
								'rest_gorko_id' => $restaurant->gorko_id,
								'room_gorko_id' => false,
								'elastic_index' => static::index(),
								'elastic_type'  => 'rest',
							]));
							$loaded_imgs[] = $image['gorko_id'];
						}
						array_push($images, $image_arr);

						if (count($images) > 19) {
							$image_flag = true;
							break;
						}
					}
					//   }
					//   if($image_flag) break;
				}
				if ($image_flag) break;
			}
		} 
		elseif ($restaurant->gorko_id == 477647) {
			array_push($images, [
				'id' => 2,
				'sort' => 2,
				'realpath' => '/img/477647-270219/477647-270219-1.jpg',
				'subpath' => '/img/477647-270219/477647-270219-1.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-1.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 1,
				'sort' => 1,
				'realpath' => '/img/477647-287077/477647-287077-1.jpeg',
				'subpath' => '/img/477647-287077/477647-287077-1.jpeg',
				'waterpath' => '/img/477647-287077/477647-287077-1.jpeg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 4,
				'sort' => 4,
				'realpath' => '/img/477647-270219/477647-270219-2.png',
				'subpath' => '/img/477647-270219/477647-270219-2.png',
				'waterpath' => '/img/477647-270219/477647-270219-2.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 3,
				'sort' => 3,
				'realpath' => '/img/477647-287077/477647-287077-2.jpeg',
				'subpath' => '/img/477647-287077/477647-287077-2.jpeg',
				'waterpath' => '/img/477647-287077/477647-287077-2.jpeg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 6,
				'sort' => 6,
				'realpath' => '/img/477647-270219/477647-270219-3.png',
				'subpath' => '/img/477647-270219/477647-270219-3.png',
				'waterpath' => '/img/477647-270219/477647-270219-3.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 5,
				'sort' => 5,
				'realpath' => '/img/477647-287077/477647-287077-3.jpeg',
				'subpath' => '/img/477647-287077/477647-287077-3.jpeg',
				'waterpath' => '/img/477647-287077/477647-287077-3.jpeg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 8,
				'sort' => 8,
				'realpath' => '/img/477647-270219/477647-270219-4.jpg',
				'subpath' => '/img/477647-270219/477647-270219-4.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-4.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 7,
				'sort' => 7,
				'realpath' => '/img/477647-287077/477647-287077-4.png',
				'subpath' => '/img/477647-287077/477647-287077-4.png',
				'waterpath' => '/img/477647-287077/477647-287077-4.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 10,
				'sort' => 10,
				'realpath' => '/img/477647-270219/477647-270219-5.jpg',
				'subpath' => '/img/477647-270219/477647-270219-5.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-5.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 9,
				'sort' => 9,
				'realpath' => '/img/477647-287077/477647-287077-5.png',
				'subpath' => '/img/477647-287077/477647-287077-5.png',
				'waterpath' => '/img/477647-287077/477647-287077-5.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 12,
				'sort' => 12,
				'realpath' => '/img/477647-270219/477647-270219-6.jpg',
				'subpath' => '/img/477647-270219/477647-270219-6.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-6.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 11,
				'sort' => 11,
				'realpath' => '/img/477647-287077/477647-287077-6.png',
				'subpath' => '/img/477647-287077/477647-287077-6.png',
				'waterpath' => '/img/477647-287077/477647-287077-6.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 14,
				'sort' => 14,
				'realpath' => '/img/477647-270219/477647-270219-7.jpg',
				'subpath' => '/img/477647-270219/477647-270219-7.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-7.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 13,
				'sort' => 13,
				'realpath' => '/img/477647-287077/477647-287077-7.png',
				'subpath' => '/img/477647-287077/477647-287077-7.png',
				'waterpath' => '/img/477647-287077/477647-287077-7.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 16,
				'sort' => 16,
				'realpath' => '/img/477647-270219/477647-270219-8.jpg',
				'subpath' => '/img/477647-270219/477647-270219-8.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-8.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 15,
				'sort' => 15,
				'realpath' => '/img/477647-287077/477647-287077-8.png',
				'subpath' => '/img/477647-287077/477647-287077-8.png',
				'waterpath' => '/img/477647-287077/477647-287077-8.png',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 17,
				'sort' => 17,
				'realpath' => '/img/477647-270219/477647-270219-9.jpg',
				'subpath' => '/img/477647-270219/477647-270219-9.jpg',
				'waterpath' => '/img/477647-270219/477647-270219-9.jpg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 18,
				'sort' => 18,
				'realpath' => '/img/477647-270219/477647-270219-10.jpeg',
				'subpath' => '/img/477647-270219/477647-270219-10.jpeg',
				'waterpath' => '/img/477647-270219/477647-270219-10.jpeg',
				'timestamp' => 1,
			]);
			array_push($images, [
				'id' => 19,
				'sort' => 19,
				'realpath' => '/img/477647-270219/477647-270219-11.jpeg',
				'subpath' => '/img/477647-270219/477647-270219-11.jpeg',
				'waterpath' => '/img/477647-270219/477647-270219-11.jpeg',
				'timestamp' => 1,
			]);
		} else {
			
			foreach ($specs as $spec) {
				
				for ($i = 0; $i < 20; $i++) {
					foreach ($room_ids as $room_id) {
						if(!in_array($room_id, $room_filtered_ids)) continue;
						if (isset($images_sorted[$room_id]) && isset($images_sorted[$room_id][$spec]) && isset($images_sorted[$room_id][$spec][$i])) {
							
							$image = $images_sorted[$room_id][$spec][$i];
							$image_arr = [];
							$image_arr['id'] = $image['gorko_id'];
							$image_arr['sort'] = $image['sort'];
							$search = ['lh3.googleusercontent.com', 'nocdn.gorko.ru'];
							// $image_arr['realpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $image['path']);
							$image_arr['realpath'] = str_replace($search, 'img.korporativ-ng.ru', $image['path']);
							if (isset($images_module[$image['gorko_id']])) {
								//  $image_arr['subpath']   = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
								//  $image_arr['waterpath'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
								//  $image_arr['timestamp'] = str_replace('lh3.googleusercontent.com', 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
								$image_arr['subpath']   = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['subpath']);
								$image_arr['waterpath'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['waterpath']);
								$image_arr['timestamp'] = str_replace($search, 'img.korporativ-ng.ru', $images_module[$image['gorko_id']]['timestamp']);
							} else {
								$queue_id = Yii::$app->queue->push(new AsyncRenewImages([
									'gorko_id'      => $image['gorko_id'],
									'params'        => $params,
									'rest_flag'     => true,
									'rest_gorko_id' => $restaurant->gorko_id,
									'room_gorko_id' => false,
									'elastic_index' => static::index(),
									'elastic_type'  => 'rest',
								]));
								$loaded_imgs[] = $image['gorko_id'];
							}
							array_push($images, $image_arr);
						}
						if (count($images) > 19) {
							$image_flag = true;
							break;
						}
					}
					if ($image_flag) break;
				}
				if ($image_flag) break;
			}
		}

		$record->restaurant_images = $images;

		//$restaurant_text = ItemAdds::findOne(['item_id' => $restaurant->gorko_id, 'item_type' => 1, 'value_type' => 'text']);
		//$record->restaurant_text = $restaurant_text;
		


		try {
			if (!$isExist) {
				$result = $record->insert();
			} else {
				$result = $record->update();
			}
		} catch (\Exception $e) {
			$result = $e;
		}

		return $result;
	}

	public static function subdomenCheck()
	{
		SubdomenFilteritem::deactivate();
		$counterActive = 0;
		$counterInactive = 0;
		foreach (Subdomen::find()->all() as $key => $subdomen) {
			$rest_total = self::find()
				->limit(0)
				->query(
					['bool' => ['must' => ['match' => ['restaurant_city_id' => $subdomen->city_id]]]]
				)
				->search();
			$isActive = $rest_total['hits']['total'] > 9;
			// $subdomen->active = $isActive;
			// $subdomen->save();
			if ($subdomen->active) {
				foreach (FilterItems::find()->all() as $filterItem) {
					if ($filterItem->filter_id !== 4) {//пропускаем из проверки тип фильтра "Бюджет на человека"
						$hits = self::getFilterItemsHitsForCity($filterItem, $subdomen->city_id);
						$where = ['subdomen_id' => $subdomen->id, 'filter_items_id' => $filterItem->id];
						$subdomenFilterItem = SubdomenFilteritem::find()->where($where)->one() ?? new SubdomenFilteritem($where);
						$subdomenFilterItem->hits = $hits;
						$subdomenFilterItem->is_valid = 1;
						
						$subdomenFilterItem->save();
						$hits > 0 ? $counterActive++ : $counterInactive++;
					}
				}
			}
		}
		echo "active=$counterActive; inactive=$counterInactive";

		return 1;
	}

	public static function getFilterItemsHitsForCity($filterItem, $city_id)
	{
		$filter_item_arr = json_decode($filterItem->api_arr, true);
		$main_table = 'restaurants';
		$simple_query = [];
		$nested_query = [];
		$type_query = [];
		$location_query = [];
		$specials_query = [];
		$extra_query = [];

		foreach ($filter_item_arr as $filter_data) {

			$filter_query = new FilterQueryConstructorElastic($filter_data, $main_table);

			if ($filter_query->nested) {
				if (!isset($nested_query[$filter_query->query_type])) {
					$nested_query[$filter_query->query_type] = [];
				}
			} elseif ($filter_query->type) {
				if (!isset($type_query[$filter_query->query_type])) {
					$type_query[$filter_query->query_type] = [];
				}
			} elseif ($filter_query->location) {
				if (!isset($location_query[$filter_query->query_type])) {
					$location_query[$filter_query->query_type] = [];
				}
			} elseif ($filter_query->specials) {
				if (!isset($specials_query[$filter_query->query_type])) {
					$specials_query[$filter_query->query_type] = [];
				}
			} elseif ($filter_query->extra) {
				if (!isset($extra_query[$filter_query->query_type])) {
					$extra_query[$filter_query->query_type] = [];
				}
			} else {
				if (!isset($simple_query[$filter_query->query_type])) {
					$simple_query[$filter_query->query_type] = [];
				}
			}

			foreach ($filter_query->query_arr as $filter_value) {
				if ($filter_query->nested) {
					array_push($nested_query[$filter_query->query_type], $filter_value);
				} elseif ($filter_query->type) {
					array_push($type_query[$filter_query->query_type], $filter_value);
				} elseif ($filter_query->location) {
					array_push($location_query[$filter_query->query_type], $filter_value);
				} elseif ($filter_query->specials) {
					array_push($specials_query[$filter_query->query_type], $filter_value);
				} elseif ($filter_query->extra) {
					array_push($extra_query[$filter_query->query_type], $filter_value);
				} else {
					array_push($simple_query[$filter_query->query_type], $filter_value);
				}
			}
		}

		$final_query = [
			'bool' => [
				'must' => [],
			]
		];
		array_push($final_query['bool']['must'], ['match' => ['restaurant_city_id' => $city_id]]);
		foreach ($simple_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
		}

		foreach ($nested_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			if ($main_table == 'rooms') {
				array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
			} else {
				array_push($final_query['bool']['must'], ['nested' => ["path" => "rooms", "query" => ['bool' => ['must' => ['bool' => ['should' => $temp_type_arr]]]]]]);
			}
		}

		foreach ($type_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			if ($main_table == 'rooms') {
				array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
			} else {
				array_push($final_query['bool']['must'], ['nested' => ["path" => "restaurant_types", "query" => ['bool' => ['must' => ['bool' => ['should' => $temp_type_arr]]]]]]);
			}
		}

		foreach ($location_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			if ($main_table == 'rooms') {
				array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
			} else {
				array_push($final_query['bool']['must'], ['nested' => ["path" => "restaurant_location", "query" => ['bool' => ['must' => ['bool' => ['should' => $temp_type_arr]]]]]]);
			}
		}

		foreach ($specials_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			if ($main_table == 'rooms') {
				array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
			} else {
				array_push($final_query['bool']['must'], ['nested' => ["path" => "restaurant_specials", "query" => ['bool' => ['must' => ['bool' => ['should' => $temp_type_arr]]]]]]);
			}
		}

		foreach ($extra_query as $type => $arr_filter) {
			$temp_type_arr = [];
			foreach ($arr_filter as $key => $value) {
				array_push($temp_type_arr, $value);
			}
			if ($main_table == 'rooms') {
				array_push($final_query['bool']['must'], ['bool' => ['should' => $temp_type_arr]]);
			} else {
				array_push($final_query['bool']['must'], ['nested' => ["path" => "restaurant_extra", "query" => ['bool' => ['must' => ['bool' => ['should' => $temp_type_arr]]]]]]);
			}
		}

		$final_query = [
			"function_score" => [
				"query" => $final_query,
				"functions" => [
					[
						"filter" => ["match" => ["restaurant_commission" => "2"]],
						"random_score" => [],
						"weight" => 100
					],
				]
			]
		];

		$query = self::find()->query($final_query)->limit(0);

		return $query->search()['hits']['total'];
	}
}
