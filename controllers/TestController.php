<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\models\GorkoApiTest;
use common\models\Subdomen;
use common\models\Restaurants;
use common\models\Rooms;
use common\models\Pages;
use common\models\SubdomenPages;
use common\models\FilterItems;
use common\models\Slices;
use common\models\siteobject\SiteObject;
use common\models\siteobject\SiteObjectSeo;
use common\models\elastic\ItemsFilterElastic;
use common\components\AsyncRenewRestaurants;
use frontend\modules\gorko_ny\models\ElasticItems;
use frontend\modules\gorko_ny\components\GetSlicesForSitemap;
use yii\helpers\ArrayHelper;

class TestController extends Controller
{
	public function actionNewseo()
	{
		$mysql_config =	\Yii::$app->params['mysql_config'];
		$main_config = \Yii::$app->params['main_api_config'];
		$connection_config = array_merge($mysql_config, $main_config['mysql_config']);
		$connection = new \yii\db\Connection($connection_config);
		$connection->open();
		Yii::$app->set('db', $connection);

		$rest = Restaurants::find()->where(['gorko_id' => 440113])->one();
		$group = array();

		foreach ($rest->imagesext as $value) {
		    $group[$value['room_id']][] = $value;
		}

		$group_sec = array();
		$room_ids = array();
		foreach ($group as $room_id => $images) {
			$room_ids[] = $room_id;
			foreach($images as $image){
				$group_sec[$room_id][$image['event_id']][] = $image;	
			}	    
		}

		//$rest_arr = ArrayHelper::map($rest->imagesext, 'gorko_id', ['rest_id', 'id'], 'room_id');
		echo '<pre>';
		print_r($group_sec);
		exit;
	}

	public function actionSendmessange()
	{
		$to = ['zadrotstvo@gmail.com'];
		$subj = "Тестовая заявка";
		$msg = "Тестовая заявка";
		$message = $this->sendMail($to,$subj,$msg);
		var_dump($message);
		exit;
	}

	public function actionIndex()
	{
		$curl = curl_init();
		$file = '/var/www/pmnetwork/frontend/web/img/watermark-bzm.png';
		$mime = mime_content_type($file);
		$info = pathinfo($file);
		$name = $info['basename'];
		$output = curl_file_create($file, $mime, $name);
		$payload = [
			'mediaId' => 50367999,
			'token'=> '4aD9u94jvXsxpDYzjQz0NFMCpvrFQJ1k',
			'watermark' => $output,
			'hash_key' => 'banketmoscow',
			'watermarkPosition' => 1
		];
		curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/tools/mediaToSatellite');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl, CURLOPT_ENCODING, '');
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
	    $response = curl_exec($curl);
	    $response_obj = json_decode($response);
	    curl_close($curl);

	    echo '<pre>';
	    print_r($response_obj);
	    exit;

		
	}

	public function actionCustom()
	{
		// $rest_slices = Slices::find()->where(['id' => [1,2,3,4,5,6,7]])->all();
		// $capacities = FilterItems::find()->where(['filter_id' => 1])->all();

		// foreach ($rest_slices as $rest_slice){

		// 	$priceList = [700, 1000, 1500, 2000];

			// foreach ($capacities as $capacity){

			// 	$slice = new Slices();
			// 	$slice->alias = 'ploshhadki-' . $capacity->value . '-chelovek';
			// 	$slice->h1 = 'h1';
			// 	$slice->title = 'title';
			// 	$slice->description = 'description';
			// 	$slice->keywords = 'keywords';
			// 	$slice->text_top = '';
			// 	$slice->text_bottom = '';
			// 	$slice->img_alt = '';
			// 	$slice->params = '{"chelovek":"' . $capacity->value . '"}';
			// 	$slice->save();
				// echo '<pre>';
				// print_r($slice);
				// exit;

			// }
		// }
		
		// $rest_slices = Slices::find()->where(['>', 'id', 135])->all();

	// 	foreach ($rest_slices as $rest_slice){
	// 		$page = new Pages();
	// 		$page->name = 'Срез - ' . $rest_slice->alias;
	// 		$page->type = $rest_slice->alias;
	// 		$page->title = 'empty';
	// 		$page->description = 'empty';
	// 		$page->keywords = 'empty';
	// 		$page->img_alt = 'empty';
	// 		$page->h1 = 'empty';
	// 		$page->text_top = 'empty';
	// 		$page->text_bottom = 'empty';
	// 		$page->title_pag = 'empty';
	// 		$page->description_pag = 'empty';
	// 		$page->keywords_pag = 'empty';
	// 		$page->h1_pag = 'empty';
	// 		$page->save();
	// }

	// $rest_pages = Pages::find()->where(['>', 'id', 153])->all();
	// foreach ($rest_pages as $key => $value) {
	// 	$value::createSiteObjects();
	// }
	// $rest_pages = Pages::find()->where(['id' => 119])->one();
	// 	$rest_pages::createSiteObjects();

	// $page = Pages::find()->where(['>', 'id', 147])->andWhere(['<', 'id', 160])->with('seoObject')->all();
	// $page = Pages::find()->where(['id' => [162,163]])->with('seoObject')->all();

	// foreach($page as $p){
	// 	$seo = SiteObjectSeo::find()->where(['id'=> $p->seoObject->id])->one();
	// 	$seo->heading = 'Новогодний корпоратив за ' . explode('-', $p->type)[2] . ' рублей';
	// 	$seo->title = 'Заказать новогодний корпоратив в **city_dec** от ' . explode('-', $p->type)[2] . ' рублей с человека';
	// 	$seo->description = 'Лучшие площадки **city_rod** для новогодних корпоративов на нашем сайте: новогодний корпоратив от ' . explode('-', $p->type)[2] . ' рублей в **city_dec**. Скидки и подарки.';
	// 	$seo->save();
	// }

		// $rest_slices = Slices::find()->where(['id' => [154,155]])->all();

		// foreach ($rest_slices as $slice){
			// $page = Pages::find()->where(['type' => $slice->alias])->with('seoObject')->one();
			// $object = SiteObject::find()->where(['id' => $page->seoObject->site_object_id])->one();
			// $seo = SiteObjectSeo::find()->where(['id' => $page->seoObject->id])->one();

			// $page = new Pages();
			// $page->name = 'Срез - ' . $slice->alias;
			// $page->type = $slice->alias;
			// $page->title = 'empty';
			// $page->description = 'empty';
			// $page->keywords = 'empty';
			// $page->img_alt = 'empty';
			// $page->h1 = 'empty';
			// $page->text_top = 'empty';
			// $page->text_bottom = 'empty';
			// $page->title_pag = 'empty';
			// $page->description_pag = 'empty';
			// $page->keywords_pag = 'empty';
			// $page->h1_pag = 'empty';
			// $page->save();


		// }

	// 	$slices = Slices::find('alias')->all(); // вернуть, когда останутся только актуальные срезы
	// 	$aliasList = GetSlicesForSitemap::getAggregateResult($slices);

	// $slicesSeo = SiteObjectSeo::find()->where(['>', 'id', '129'])->all();

	// foreach ($slicesSeo as $item){
	// 	$item->pagination_heading = $item->heading;
	// 	$item->pagination_title = $item->title;
	// 	$item->pagination_description = $item->description;
	// 	$item->pagination_keywords = $item->keywords;
	// 	$item->save();
	// }


	// $restMainTypeIdsOrder = array_combine($MAIN_REST_TYPE_ORDER, $MAIN_REST_TYPE_ORDER);

	$page = Pages::find()->where(['between', 'id', 20, 167])->with('seoObject')->all();

	foreach ($page as $p){
		$seo = SiteObjectSeo::find()->where(['id'=> $p->seoObject->id])->one();

		// $aliasExploded = explode('-', $p->type);
		// $title = '';
 
		// if (stripos($p->type, 'chelovek')) {
		// 	$slice = array_slice($aliasExploded, -2, 1);
		// 	$title = 'Заказать новогодний корпоратив в **city_dec** на ' . array_pop($slice) . ' человек';
		// } elseif (stripos($p->type, 'price')) {
		// 	$title = 'Заказать новогодний корпоратив в **city_dec** от ' . array_pop($aliasExploded) . ' рублей с человека';
		// } elseif (stripos($p->type, 'svoy-alko')) {
		// 	$title = 'Новогодний корпоратив со своим алкоголем в **city_dec** без пробкового сбора';
		// } elseif (stripos($p->type, 'firework')) {
		// 	$title = 'Заказать новогодний корпоратив в **city_dec** с фейерверками и салютом';
		// }

		$seo->pagination_title = $seo->title . ' - Страница №**page**';
		$seo->save();

		// if ($title === ''){
		// 	exit;
		// }
		// echo '<pre>';
		// print_r($typeList);
		// exit;
	
	}


		echo '<pre>';
		print_r(count($page));
		exit;
	}

	public function actionAll()
	{
		$subdomen_model = Subdomen::find()
			->where(['id' => 57])
			->all();

		foreach ($subdomen_model as $key => $subdomen) {
			GorkoApiTest::showAllData([
				[
					'params' => 'city_id='.$subdomen->city_id.'&type_id=1&event=17',
					'watermark' => '/var/www/pmnetwork/pmnetwork/frontend/web/img/ny_ball.png',
					'imageHash' => 'newyearpmn'
				]				
			]);
		}

		
	}

	public function actionOne()
	{
		$queue_id = Yii::$app->queue->push(new AsyncRenewRestaurants([
			'gorko_id' => 418147,
			'dsn' => Yii::$app->db->dsn,
			'watermark' => '/var/www/pmnetwork/pmnetwork/frontend/web/img/ny_ball.png',
			'imageHash' => 'newyearpmn'
		]));
	}

	public function actionTest()
	{
		GorkoApiTest::showOne([
			[
				'params' => 'city_id=4088&type_id=1&type=30,11,17,14&is_edit=1',
				'watermark' => '/var/www/pmnetwork/pmnetwork/frontend/web/img/ny_ball.png'
			]			
		]);
	}

	public function actionSubdomencheck()
	{
		$subdomen_model = Subdomen::find()->all();

		foreach ($subdomen_model as $key => $subdomen) {
			$restaurants = Restaurants::find()->where(['city_id' => $subdomen->city_id])->all();
			if(count($restaurants) > 9){
				$subdomen->active = 1;
			}
			else{
				$subdomen->active = 0;
			}
			$subdomen->save();
		}
	}

	public function actionRenewelastic()
	{
		ElasticItems::refreshIndex();
	}

	public function actionSoftrenewelastic()
	{
		ElasticItems::softRefreshIndex();
	}

	public function actionCreateindex()
	{
		ElasticItems::softRefreshIndex();
	}

	public function actionTetest()
	{
		$room_where = [
			'rooms.active' => 1,
			'restaurants.city_id' => 4400
		];
		$current_room_models = Rooms::find()
			->joinWith('restaurants')
			->select('rooms.gorko_id')
			->where($room_where)
			->asArray()
			->all();

		print_r(count($current_room_models));
		exit;
	}

	public function actionImgload()
	{
		//header("Access-Control-Allow-Origin: *");
		$curl = curl_init();
		$file = '/var/www/pmnetwork/pmnetwork_konst/frontend/web/img/favicon.png';
		$mime = mime_content_type($file);
		$info = pathinfo($file);
		$name = $info['basename'];
		$output = curl_file_create($file, $mime, $name);
		$params = [
			//'mediaId' => 55510697,
			'url'=>'https://lh3.googleusercontent.com/XKtdffkbiqLWhJAWeYmDXoRbX51qNGOkr65kMMrvhFAr8QBBEGO__abuA_Fu6hHLWGnWq-9Jvi8QtAGFvsRNwqiC',
			'token'=> '4aD9u94jvXsxpDYzjQz0NFMCpvrFQJ1k',
			'watermark' => $output,
			'hash_key' => 'svadbanaprirode'
		];
		curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/tools/mediaToSatellite');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl, CURLOPT_ENCODING, '');
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

	    
		echo '<pre>';
	    $response = curl_exec($curl);

	    print_r(json_decode($response));
	    curl_close($curl);

	    //echo '<pre>';
	    
	    //echo '<pre>';

		


	    
	}

	private function sendMail($to,$subj,$msg) {
        $message = Yii::$app->mailer->compose()
            ->setFrom(['svadbanaprirode@yandex.ru' => 'Свадьба на природе'])
            ->setTo($to)
            ->setSubject($subj)
            //->setTextBody('Plain text content')
            ->setHtmlBody($msg);
        //echo '<pre>';
        //print_r($message);
        //exit;
        if (count($_FILES) > 0) {
            foreach ($_FILES['files']['tmp_name'] as $k => $v) {
                $message->attach($v, ['fileName' => $_FILES['files']['name'][$k]]);
            }
        }
        return $message->send();
    }
    public function actionShowfull(){
    	$log = json_decode(file_get_contents('/var/www/pmnetwork/pmnetwork/log/count.json'), true);
    	uasort($log, function ($a, $b) {
		  	return $b['Всего'] - $a['Всего'];
		});
    	file_put_contents('/var/www/pmnetwork/pmnetwork/log/count.json', json_encode($log));
    }

    public function actionFull()
	{
		$events = [
			1  => 'Свадьба',
            17 => 'Новый год',
            9  => 'День рождения',
            11 => 'Выпускной',
            12 => 'Детский праздник',
            14 => 'Фуршет',
            15 => 'Корпоратив',
            16 => 'Конференция',
            24 => 'Видеоконференция',
            25 => 'Выставка',
            26 => 'Деловая встреча',
            27 => 'Кастинг',
            28 => 'Кинопоказ',
            29 => 'Концерт',
            30 => 'Кулинарный вечер',
            32 => 'Модный показ',
            33 => 'Презентация',
            31 => 'Мальчишник / Девичник',
            34 => 'Пресс-конференция',
            35 => 'Танцы / Бал',
            36 => 'Театральная постановка',
            37 => 'Тренинг / Мастер-класс',
            38 => 'Фокус-группа',
            10 => 'Праздничный банкет'
		];

		$subdomen_model = Subdomen::find()
			//->where(['id' => 1])
			->all();

		foreach ($subdomen_model as $key => $subdomen) {
			$count = GorkoApiTest::showFullNew([
				'params' => 'city_id='.$subdomen->city_id.'&type_id=1'							
			]);
			$log = json_decode(file_get_contents('/var/www/pmnetwork/pmnetwork/log/count.json'), true);
			$log[$subdomen->name] = $count;
			file_put_contents('/var/www/pmnetwork/pmnetwork/log/count.json', json_encode($log));
		}
	}
}