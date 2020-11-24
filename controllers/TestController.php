<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use common\models\GorkoApiTest;
use common\models\Subdomen;
use common\models\Restaurants;
use common\models\Rooms;
use common\models\Pages;
use common\models\SubdomenPages;
use frontend\modules\gorko_ny\models\ElasticItems;
use yii\web\Controller;
use common\components\AsyncRenewRestaurants;

class TestController extends Controller
{
	public function actionNewseo()
	{
		$pages = SubdomenPages::find()->all();
		foreach ($pages as $key => $value) {
			$value::createSiteObjects();
		}
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
		$subdomen_model = Subdomen::find()
			//->where(['id' => 57])
			->all();

		foreach ($subdomen_model as $key => $subdomen) {
			GorkoApiTest::renewAllData([
				[
					'params' => 'city_id='.$subdomen->city_id.'&type_id=1&event=17',
					'watermark' => '/var/www/pmnetwork/pmnetwork/frontend/web/img/ny_ball.png',
					'imageHash' => 'newyearpmn'
				]				
			]);
		}

		
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
}