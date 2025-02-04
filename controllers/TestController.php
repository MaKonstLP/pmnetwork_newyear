<?php

namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\models\GorkoApiTest;
use common\models\Subdomen;
use common\models\Restaurants;
use common\models\ImagesExt;
use common\models\Rooms;
use common\models\Pages;
use common\models\ImagesModule;
use common\models\SubdomenPages;
use common\models\FilterItems;
use common\models\Slices;
use common\models\RestaurantsRedirect;
use common\models\RestaurantsUniqueId;
use common\models\RestaurantsOldUniqueId;
use common\models\RestaurantNotFound;
use common\models\siteobject\SiteObject;
use common\models\siteobject\SiteObjectSeo;
use common\models\elastic\ItemsFilterElastic;
use common\components\AsyncRenewRestaurants;
use frontend\modules\gorko_ny\models\ElasticItems;
use frontend\modules\gorko_ny\components\GetSlicesForSitemap;
use yii\helpers\ArrayHelper;
use common\models\RoomsSpec;
use common\models\RestaurantsPremium;
use frontend\modules\pmnbd\models\SubdomenFilteritem;
use common\models\elastic\FilterQueryConstructorElastic;

class TestController extends Controller
{
	public function actionRating()
	{
		$unique_cur = ArrayHelper::index(RestaurantsUniqueId::find()->asArray()->all(), 'id');

		$unique_old = ArrayHelper::index(RestaurantsOldUniqueId::find()->asArray()->all(), 'id');

		$i = 0;

		foreach ($unique_cur as $cur) {
			if (isset($unique_old[$cur['id']]) && $cur['unique_id'] != $unique_old[$cur['id']]['unique_id']) {
				echo $unique_old[$cur['id']]['unique_id'] . ' -- ' . $cur['unique_id'] . '<br/>';
				$i++;
			}
		}

		echo '<pre>' . $i;
		//print_r($unique_cur);
	}

	public function actionTestload()
	{
		$img = file_get_contents('https://instagram.ftxl3-1.fna.fbcdn.net/v/t51.2885-15/343276360_565146819041168_899114538469031824_n.jpg?stp=dst-jpg_e35_p640x640_sh0.08&_nc_ht=instagram.ftxl3-1.fna.fbcdn.net&_nc_cat=108&_nc_ohc=dxaz9-UbNtQAX_WhXUa&edm=AP_V10EBAAAA&ccb=7-5&oh=00_AfB-xI5HyyYGiCzjHlua4bjcNU2XfnqpTqIU761Gp_DKyw&oe=647479E3&_nc_sid=8721cf');
	}

	public function actionTestid()
	{
		$curl = curl_init();
		$file = '/var/www/pmnetwork_dev/frontend/web/img/ny_ball.png';
		$mime = mime_content_type($file);
		$info = pathinfo($file);
		$name = $info['basename'];
		$output = curl_file_create($file, $mime, $name);
		$payload = [
			'mediaId' 			=> 65110103,
			'token'				=> '4aD9u94jvXsxpDYzjQz0NFMCpvrFQJ1k',
			'watermark' 		=> $output,
			'hash_key' 			=> 'newyearpmn',
			'watermarkPosition' => 9
		];
		curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/tools/mediaToSatellite');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		$response = curl_exec($curl);
		$response_obj = json_decode($response);
		curl_close($curl);

		/*$connection = new \yii\db\Connection([
	    	'username' => 'root',
    'password' => 'GxU25UseYmeVcsn5Xhzy',
    'charset' => 'utf8mb4',
    'dsn' => 'mysql:host=localhost;dbname=pmn_gorko_ny']);
        $connection->open();
        Yii::$app->set('db', $connection);
        $timestamp = time();
        $imgModel = ImagesModule::find()
        	->where(['gorko_id' => 65110103])
        	->one();
        if(!$imgModel) $imgModel = new ImagesModule;
        $imgModel->gorko_id = 65110103;
	    $imgModel->subpath = $response_obj->url;
	    $imgModel->waterpath = $response_obj->url_watermark;
	    $imgModel->timestamp = $timestamp;*/

		echo '<pre>';
		print_r($response);
		exit;
	}

	public function actionSendmessange()
	{
		$to = ['zadrotstvo@gmail.com'];
		$subj = "Тестовая заявка";
		$msg = "Тестовая заявка";
		$message = $this->sendMail($to, $subj, $msg);
		var_dump($message);
		exit;
	}

	public function actionIndex()
	{
		/* $curl = curl_init();
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
	    exit; */



		/* $cities = [
			4962 => 'St Petersburg',
			5106 => 'Ekaterinburg',
			4549 => 'Novosibirsk',
			3612 => 'Nizhniy Novgorod',
			5269 => 'Kazan’',
			4079 => 'Krasnodar',
			4917 => 'Samara',
			4848 => 'Rostov-on-Don',
			3538 => 'Voronezh',
			3345 => 'Ufa',
			4720 => 'Perm',
			5539 => 'Chelyabinsk',
			3731 => 'Irkutsk',
			4580 => 'Omsk',
			5395 => 'Tyumen',
			4094 => 'Sochi',
			4149 => 'Krasnoyarsk',
			3472 => 'Volgograd',
			5005 => 'Saratov',
			4617 => 'Orenburg',
			3166 => 'Barnaul',
			5504 => 'Khabarovsk',
			5352 => 'Tula',
			5219 => 'Stavropol',
			4919 => 'Tolyatti',
			5310 => 'Tomsk',
			3770 => 'Kaliningrad',
			4741 => 'Vladivostok',
			5646 => 'Yaroslavl',
			3354 => 'Belgorod',
			5456 => 'Ulyanovsk',
			5619 => 'Cheboksary',
			3823 => 'Tver',
			5414 => 'Izhevsk',
			3283 => 'Astrakhan',
			4238 => 'Lipetsk',
			3933 => 'Kemerovo',
			3963 => 'Kirov',
			4682 => 'Penza',
			3684 => 'Ivanovo',
			4879 => 'Ryazan',
			3376 => 'Bryansk',
			3854 => 'Kaluga',
			3253 => 'Arkhangelsk',
			10251 => 'Sevastopol',
			5391 => 'Surgut',
			5184 => 'Smolensk',
			5281 => 'Naberezhnyye Chelny',
			4036 => 'Kostroma',
			5527 => 'Magnitogorsk',
			5242 => 'Tambov',
			4210 => 'Kursk',
			10252 => 'Simferopol',
			3446 => 'Vladimir',
			4851 => 'Taganrog',
			3170 => 'Biysk',
			4650 => 'Oryol',
			4088 => 'Novorossiysk',
		];

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// $ip = '62.118.73.181';

		$ip_info = json_decode(file_get_contents('http://freegeoip.live/json/'.$ip), true);
		$client_city_name = isset($ip_info['city']) && !empty($ip_info['city']) ? $ip_info['city'] : '';

		$subdomen = '';
		foreach ($cities as $key => $city) {
			if ($client_city_name == $city) {
				Yii::$app->params['subdomen_id'] = $key;
				$subdomen_model = Subdomen::find()->where(['city_id' => $key])->one();
				$subdomen = $subdomen_model['alias'];
				break;
			} else {
				Yii::$app->params['subdomen_id'] = 4400;
			}
		}

		echo ('<pre>');
		print_r($client_city_name);
		echo ('<pre>');
		print_r($subdomen);
		echo ('<pre>');
		print_r(Yii::$app->params['subdomen_id']);
		exit;




		

		$result = json_decode(file_get_contents('http://freegeoip.live/json/' . $ip), true);
		$client_city_name = $result['city'];

		$subdomen = Subdomen::find()->all();

		$subdomen_alias = [];

		foreach ($subdomen as $key => $item) {
			$subdomen_alias[$item['city_id']] = $item['alias'];
		} */



		/* $connection = new \yii\db\Connection([
			'username' => 'root',
			'password' => 'GxU25UseYmeVcsn5Xhzy',
			'charset'  => 'utf8mb4',
			'dsn' => 'mysql:host=localhost;dbname=pmn'
		]);
		$connection->open();
		Yii::$app->set('db', $connection);

		$restaurants = Restaurants::find()
			// ->with('rooms')
			// ->with('imagesext')
			// ->with('subdomen')
			->with('yandexReview')
			// ->where(['active' => 1, 'commission' => 2, 'city_id' => 4682])
			->where(['city_id' => 5646])
			->limit(10000)
			->all();

		$connection = new \yii\db\Connection([
			'username' => 'root',
			'password' => 'GxU25UseYmeVcsn5Xhzy',
			'charset'  => 'utf8mb4',
			'dsn' => 'mysql:host=localhost;dbname=pmn_gorko_ny'
		]);
		$connection->open();
		Yii::$app->set('db', $connection);

		$restaurants_unique_id = RestaurantsUniqueId::find()
			->limit(100000)
			->asArray()
			->all();
		$restaurants_unique_id = ArrayHelper::index($restaurants_unique_id, 'id');


		$restaurants_premium = RestaurantsPremium::find()
			->where(['>', 'finish', time()])
			->limit(100000)
			->asArray()
			->all();
		$restaurants_premium = ArrayHelper::index($restaurants_premium, 'gorko_id');


		$results = [];
		foreach ($restaurants as $key => $restaurant) {
			$proper = true;

			$premium = isset($restaurants_premium[$restaurant->gorko_id]);
			if (!$premium) {
				$restaurant_spec_white_list = [17];
				$restaurant_spec_rest = explode(',', $restaurant->restaurants_spec);
				if (count(array_intersect($restaurant_spec_white_list, $restaurant_spec_rest)) === 0) {
					// return 'Неподходящий тип мероприятия';
					$proper = false;
				}

				if (!$restaurant->active) {
					// return 'Не активен';
					$proper = false;
				}

				if (!$restaurant->commission) {
					// return 'Не платный';
					$proper = false;
				}
			}

			if ($proper) {
				//Уникальные св-ва для ресторанов в модуле
				if (isset($restaurants_unique_id[$restaurant->gorko_id]) && $restaurants_unique_id[$restaurant->gorko_id]['unique_id']) {
					$restaurant_unique_id = $restaurants_unique_id[$restaurant->gorko_id]['unique_id'];
				} else {
					$new_id = RestaurantsUniqueId::find()->max('unique_id') + 1;
					$restaurant_unique_id = $new_id;
				}

				$rating = 0;
				if (isset($restaurant->yandexReview) && !empty($restaurant->yandexReview['rev_ya_rate'])) {
					$rating = $restaurant->yandexReview['rev_ya_rate'];
				}

				$results[$restaurant->gorko_id]['gorko_id'] = $restaurant->gorko_id;
				$results[$restaurant->gorko_id]['url'] = 'https://korporativ-ng.ru/ploshhadki/' . $restaurant_unique_id . '/';
				$results[$restaurant->gorko_id]['city'] = 'Ярославль';
				$results[$restaurant->gorko_id]['rate'] = $rating;
			}
		}


		foreach ($results as $key => $result) {
			$string = $result['gorko_id'] . ' || ' . $result['url'] . ' || ' . $result['city'] . ' || ' . $result['rate'];
			echo ('<pre>');
			print_r($string);
		} */

		// echo ('<pre>');
		// print_r($results);
		// exit;


		//*проверка на петлю редиректов и удаление дублей START
		/* $redirect = RestaurantsRedirect::find()
			->asArray()
			->all();
		$redirect = ArrayHelper::index($redirect, 'old_id');

		$intersect_id = [];
		foreach ($redirect as $old_id => $new_id) {
			$old_id_find = false;
			$new_id_find = false;
			foreach ($redirect as $key => $value) {
				if ($old_id == $value['new_id']) {
					$old_id_find = true;
					break;
				}
			}
			foreach ($redirect as $key => $value) {
				if ($new_id['new_id'] == $value['old_id']) {
					$new_id_find = true;
					break;
				}
			}

			if ($old_id_find && $new_id_find) {
				$intersect_id[] = $new_id;
			}
		}


		foreach ($intersect_id as $value) {
			$restaurant_unique_id = RestaurantsUniqueId::find()
				->where(['unique_id' => $value['old_id']])
				->one();

			if (empty($restaurant_unique_id)) {
				$redirect_double = RestaurantsRedirect::find()
					->where(['old_id' => $value['old_id']])
					->one();

				$redirect_double->delete();
			}
		} */
		//*проверка на петлю редиректов и удаление дублей END

		//урлы из гугл таблицы(https://docs.google.com/spreadsheets/d/1SmVcvlOe9oMmfIvoT-iKEwKG3OWPxw037wi4ySIfiEs/) с 404 ответом
		/* $url_for_redirect = [
			'505'	=> '10115',
			'7671'	=> '9713',
			'7877'	=> '9712',
			'2023'	=> '10396',
			'2025'	=> '10091',
			'6089'	=> '9602',
			'7690'	=> '10090',
			'7866'	=> '9600',
			'7867'	=> '9605',
			'7911'	=> '10092',
			'1752'	=> '10041',
			'7905'	=> '10045',
			'1927'	=> '10380',
			'2225'	=> '10381',
			'7858'	=> '9540',
			'8084'	=> '10075',
			'8144'	=> '9546',
			'8511'	=> '10642',
			'2153'	=> '10409',
			'2158'	=> '10112',
			'8304'	=> '10111',
			'2255'	=> '10387',
			'7662'	=> '9567',
			'7663'	=> '9568',
			'7962'	=> '10386',
			'1521'	=> '10314',
			'338'	=> '10001',
			'339'	=> '10313',
			'4593'	=> '9222',
			'7823'	=> '9214',
			'7951'	=> '10311',
			'8001'	=> '10460',
			'8167'	=> '10002',
			'8192'	=> '10309',
			'8197'	=> '10531',
			'8220'	=> '9855',
			'8237'	=> '10547',
			'8262'	=> '10000',
			'8273'	=> '10312',
			'8274'	=> '10562',
			'8275'	=> '10564',
			'8310'	=> '10575',
			'8391'	=> '9219',
			'8510'	=> '9224',
			'1086'	=> '10227',
			'1098'	=> '10230',
			'230'	=> '10225',
			'7892'	=> '9903',
			'7983'	=> '10439',
			'8055'	=> '8798',
			'8159'	=> '8781',
			'8214'	=> '10229',
			'8238'	=> '10223',
			'8277'	=> '10224',
			'8283'	=> '10568',
			'8309'	=> '10567',
			'8327'	=> '10581',
			'8333'	=> '10566',
			'8334'	=> '8807',
			'8341'	=> '8817',
			'8429'	=> '9904',
			'2056'	=> '10141',
			'2057'	=> '10050',
			'2063'	=> '10049',
			'2064'	=> '10353',
			'403'	=> '10350',
			'404'	=> '10352',
			'409'	=> '10047',
			'5277'	=> '10051',
			'7688'	=> '10048',
			'7844'	=> '9412',
			'7956'	=> '10351',
			'1538'	=> '10004',
			'1549'	=> '10317',
			'1551'	=> '10007',
			'1556'	=> '10315',
			'1563'	=> '10319',
			'1569'	=> '10006',
			'1570'	=> '10323',
			'1833'	=> '10318',
			'345'	=> '10005',
			'349'	=> '10008',
			'351'	=> '10325',
			'4716'	=> '9231',
			'7826'	=> '9248',
			'7952'	=> '10321',
			'7953'	=> '10324',
			'8529'	=> '9232',
			'7874'	=> '9657',
			'437'	=> '10371',
			'8194'	=> '9488',
			'8432'	=> '9693',
			'1177'	=> '9935',
			'1195'	=> '10246',
			'1220'	=> '9938',
			'1232'	=> '10243',
			'2410'	=> '10252',
			'273'	=> '10251',
			'284'	=> '10249',
			'402'	=> '9939',
			'564'	=> '10247',
			'7797'	=> '8944',
			'7799'	=> '8955',
			'7800'	=> '8958',
			'7944'	=> '10242',
			'7946'	=> '10250',
			'7990'	=> '10446',
			'7992'	=> '10448',
			'7993'	=> '10449',
			'7994'	=> '10450',
			'7996'	=> '10452',
			'7998'	=> '10454',
			'7999'	=> '10455',
			'8106'	=> '10484',
			'8182'	=> '10526',
			'8195'	=> '10530',
			'8198'	=> '10532',
			'8207'	=> '10539',
			'8250'	=> '10555',
			'8270'	=> '10563',
			'8305'	=> '10574',
			'8457'	=> '10622',
			'8575'	=> '10663',
			'1263'	=> '10254',
			'1265'	=> '10260',
			'1267'	=> '10256',
			'1293'	=> '10262',
			'415'	=> '10264',
			'565'	=> '9952',
			'7801'	=> '8981',
			'7965'	=> '10425',
			'8090'	=> '10261',
			'8140'	=> '10516',
			'8147'	=> '8997',
			'8176'	=> '10524',
			'8177'	=> '10525',
			'8186'	=> '10527',
			'8196'	=> '8976',
			'8223'	=> '9001',
			'8280'	=> '9006',
			'7977'	=> '10437',
			'8020'	=> '9769',
			'2035'	=> '10093',
			'2044'	=> '10095',
			'2048'	=> '10399',
			'2049'	=> '10401',
			'2051'	=> '10398',
			'8298'	=> '10094',
			'8448'	=> '10616',
			'256'	=> '10239',
			'7626'	=> '8893',
			'7968'	=> '10429',
			'7987'	=> '10443',
			'8389'	=> '10602',
			'1118'	=> '9909',
			'1452'	=> '10235',
			'1735'	=> '9906',
			'1736'	=> '10231',
			'7624'	=> '8854',
			'7785'	=> '8855',
			'7787'	=> '8863',
			'7893'	=> '9911',
			'7897'	=> '9919',
			'7964'	=> '10422',
			'7984'	=> '10440',
			'7985'	=> '10441',
			'7986'	=> '10442',
			'8010'	=> '8861',
			'8031'	=> '10465',
			'8032'	=> '10466',
			'8037'	=> '10471',
			'8038'	=> '10472',
			'8093'	=> '10473',
			'8096'	=> '10476',
			'8152'	=> '10520',
			'8154'	=> '10519',
			'8455'	=> '10620',
			'8526'	=> '10641',
			'4749'	=> '10011',
			'6807'	=> '9856',
			'7640'	=> '9263',
			'7827'	=> '9253',
			'7828'	=> '9267',
			'8081'	=> '10009',
			'8279'	=> '9275',
			'8438'	=> '10012',
			'8502'	=> '9283',
			'8506'	=> '10576',
			'8542'	=> '10650',
			'8553'	=> '9277',
			'1719'	=> '10037',
			'1725'	=> '10347',
			'397'	=> '10349',
			'7702'	=> '10348',
			'8113'	=> '10490',
			'8574'	=> '9373',
			'2101'	=> '10102',
			'2102'	=> '10101',
			'480'	=> '10405',
			'7882'	=> '9845',
			'8085'	=> '10105',
			'8243'	=> '10551',
			'8296'	=> '10571',
			'1473'	=> '10304',
			'1481'	=> '10302',
			'1494'	=> '10307',
			'1498'	=> '10308',
			'335'	=> '10303',
			'7820'	=> '9172',
			'7950'	=> '10305',
			'7969'	=> '10431',
			'8133'	=> '10511',
			'8362'	=> '9171',
			'8146'	=> '10509',
			'8173'	=> '9092',
			'8219'	=> '9078',
			'491'	=> '10408',
			'492'	=> '10407',
			'6398'	=> '10108',
			'7670'	=> '9681',
			'8026'	=> '10110',
			'8439'	=> '10514',
			'8531'	=> '9669',
			'1324'	=> '10143',
			'293'	=> '10269',
			'296'	=> '10266',
			'299'	=> '10272',
			'4229'	=> '9062',
			'4232'	=> '10276',
			'7631'	=> '9033',
			'7632'	=> '9034',
			'7633'	=> '9052',
			'7719'	=> '10426',
			'7812'	=> '9056',
			'7901'	=> '9958',
			'7948'	=> '10275',
			'7966'	=> '10427',
			'8015'	=> '9026',
			'8058'	=> '9014',
			'8187'	=> '10528',
			'8325'	=> '10579',
			'8369'	=> '10596',
			'8433'	=> '10613',
			'8481'	=> '10456',
			'8494'	=> '9850',
			'8497'	=> '9965',
			'8507'	=> '10608',
			'8539'	=> '9021',
			'1700'	=> '10027',
			'1712'	=> '10034',
			'390'	=> '10030',
			'391'	=> '10344',
			'392'	=> '10346',
			'5081'	=> '9859',
			'7972'	=> '10433',
			'8019'	=> '9359',
			'8134'	=> '10513',
			'8231'	=> '10544',
			'8282'	=> '10343',
			'8312'	=> '10434',
			'8330'	=> '10026',
			'8331'	=> '10028',
			'8332'	=> '10146',
			'2190'	=> '10411',
			'511'	=> '10412',
			'2217'	=> '10142',
			'1917'	=> '10337',
			'374'	=> '10339',
			'8381'	=> '9318',
			'1021'	=> '10194',
			'153'	=> '10218',
			'178'	=> '10185',
			'180'	=> '10203',
			'192'	=> '10195',
			'193'	=> '10202',
			'194'	=> '10184',
			'204'	=> '10193',
			'206'	=> '9883',
			'218'	=> '9893',
			'219'	=> '10217',
			'220'	=> '10197',
			'3525'	=> '8776',
			'7697'	=> '10204',
			'7747'	=> '8593',
			'7754'	=> '8654',
			'7757'	=> '8666',
			'7760'	=> '8691',
			'7762'	=> '8697',
			'7765'	=> '8706',
			'7770'	=> '8740',
			'7772'	=> '8768',
			'7890'	=> '9895',
			'7937'	=> '10187',
			'7938'	=> '10190',
			'7939'	=> '10198',
			'7941'	=> '10216',
			'7981'	=> '10438',
			'8027'	=> '10209',
			'8046'	=> '8603',
			'8047'	=> '8614',
			'8052'	=> '8724',
			'8053'	=> '8727',
			'8054'	=> '8753',
			'8086'	=> '10180',
			'8087'	=> '10188',
			'8095'	=> '10475',
			'8100'	=> '10479',
			'8115'	=> '10492',
			'8116'	=> '10493',
			'8117'	=> '10494',
			'8118'	=> '10495',
			'8119'	=> '10496',
			'8121'	=> '10497',
			'8122'	=> '10498',
			'8123'	=> '10499',
			'8124'	=> '10500',
			'8125'	=> '10501',
			'8127'	=> '10503',
			'8128'	=> '10504',
			'8129'	=> '10505',
			'8130'	=> '10506',
			'8131'	=> '10507',
			'8132'	=> '10508',
			'8148'	=> '10518',
			'8180'	=> '10522',
			'8216'	=> '8626',
			'8225'	=> '10161',
			'8230'	=> '10550',
			'8242'	=> '8745',
			'8244'	=> '8746',
			'8245'	=> '10545',
			'8246'	=> '8640',
			'8249'	=> '10554',
			'8257'	=> '10552',
			'8264'	=> '10556',
			'8267'	=> '10222',
			'8300'	=> '8615',
			'8328'	=> '10565',
			'8338'	=> '10585',
			'8344'	=> '10588',
			'8364'	=> '10482',
			'8370'	=> '8579',
			'8371'	=> '8701',
			'8372'	=> '8779',
			'8373'	=> '10170',
			'8374'	=> '10206',
			'8376'	=> '10176',
			'8384'	=> '10179',
			'8395'	=> '10219',
			'8398'	=> '10173',
			'8399'	=> '10171',
			'8401'	=> '9877',
			'8406'	=> '10605',
			'8408'	=> '8761',
			'8415'	=> '10611',
			'8416'	=> '10182',
			'8427'	=> '10199',
			'8443'	=> '8694',
			'8444'	=> '8735',
			'8445'	=> '9898',
			'8452'	=> '8596',
			'8461'	=> '10626',
			'8462'	=> '10627',
			'8464'	=> '10629',
			'8466'	=> '10630',
			'8482'	=> '8709',
			'8488'	=> '10634',
			'8492'	=> '10635',
			'925'	=> '10177',
			'938'	=> '9892',
			'981'	=> '10220',
			'988'	=> '10215',
			'994'	=> '10196',
			'2111'	=> '10058',
			'2112'	=> '10358',
			'418'	=> '10057',
			'6533'	=> '10116',
			'8135'	=> '10515',
			'2304'	=> '10135',
			'2305'	=> '10134',
			'8294'	=> '10133',
			'2130'	=> '10361',
			'2131'	=> '10059',
			'5525'	=> '9465',
			'7849'	=> '9453',
			'7906'	=> '10060',
			'7907'	=> '10061',
			'8532'	=> '10062',
			'8537'	=> '10360',
			'8546'	=> '10647',
			'1853'	=> '10367',
			'1854'	=> '10369',
			'2142'	=> '10363',
			'2497'	=> '10366',
			'426'	=> '10362',
			'430'	=> '10368',
			'431'	=> '10365',
			'7851'	=> '9481',
			'1788'	=> '10355',
			'1792'	=> '10053',
			'1803'	=> '10357',
			'2087'	=> '10356',
			'2548'	=> '10055',
			'7655'	=> '9423',
			'7703'	=> '10354',
			'7845'	=> '9418',
			'8459'	=> '10623',
			'1625'	=> '10329',
			'1631'	=> '10015',
			'1634'	=> '10331',
			'1893'	=> '10330',
			'7644'	=> '9299',
			'7645'	=> '9303',
			'7647'	=> '9304',
			'7701'	=> '10332',
			'7831'	=> '9289',
			'7922'	=> '10153',
			'7955'	=> '10333',
			'8002'	=> '10461',
			'8023'	=> '10013',
			'8029'	=> '10334',
			'8101'	=> '10480',
			'8141'	=> '10512',
			'8268'	=> '10561',
			'1989'	=> '10388',
			'1996'	=> '10390',
			'8174'	=> '9586',
			'1433'	=> '10297',
			'1442'	=> '10293',
			'7636'	=> '9126',
			'7637'	=> '9136',
			'7638'	=> '9155',
			'8091'	=> '10296',
			'8157'	=> '10294',
			'8158'	=> '10295',
			'8161'	=> '10299',
			'8163'	=> '10300',
			'8178'	=> '9169',
			'8179'	=> '9988',
			'8208'	=> '10430',
			'8209'	=> '10540',
			'8224'	=> '10543',
			'8256'	=> '10558',
			'8317'	=> '9156',
			'8321'	=> '9160',
			'8343'	=> '10587',
			'8357'	=> '10510',
			'8359'	=> '10298',
			'8368'	=> '9128',
			'8419'	=> '9989',
			'8519'	=> '9987',
			'2292'	=> '10131',
			'2296'	=> '10420',
			'2297'	=> '10419',
			'2300'	=> '10417',
			'2357'	=> '10130',
			'2358'	=> '10418',
			'528'	=> '10416',
			'7915'	=> '10132',
			'7657'	=> '9509',
			'7959'	=> '10376',
			'1688'	=> '10022',
			'1956'	=> '10342',
			'1958'	=> '10341',
			'7652'	=> '9343',
			'7904'	=> '10021',
			'8236'	=> '10019',
			'8382'	=> '10592',
			'8383'	=> '10597',
			'8442'	=> '10607',
			'8493'	=> '9349',
			'8554'	=> '9335',
			'8556'	=> '10654',
			'1382'	=> '9975',
			'1397'	=> '9978',
			'1400'	=> '10287',
			'1408'	=> '10286',
			'1714'	=> '10288',
			'315'	=> '9973',
			'318'	=> '10289',
			'7634'	=> '9105',
			'7814'	=> '9109',
			'8111'	=> '10488',
			'8477'	=> '10458',
			'8562'	=> '10150',
			'1913'	=> '10377',
			'2198'	=> '10072',
			'2206'	=> '10379',
			'446'	=> '10378',
			'7659'	=> '9531',
			'8392'	=> '10600',
			'8394'	=> '10604',
			'8473'	=> '9520',
		];

		foreach ($url_for_redirect as $old_id => $new_id) {
			$find_old_id = RestaurantsRedirect::find()
				->where(['old_id' => $old_id])
				->one();
			$find_new_id = RestaurantsRedirect::find()
				->where(['new_id' => $new_id])
				->one();

			if (empty($find_old_id) && empty($find_new_id)) {
				$redirect_model = new RestaurantsRedirect();
				$redirect_model->old_id = $old_id;
				$redirect_model->new_id = $new_id;
				$redirect_model->save();
			}
		} */

		//урлы из гугл таблицы с 404 ответом для которых настроен редирект на "/ploshhadki/" (https://docs.google.com/spreadsheets/d/1SmVcvlOe9oMmfIvoT-iKEwKG3OWPxw037wi4ySIfiEs/edit#gid=1599440366)
		/* $urls_with_not_found = [
			'7356',
			'7447',
			'7561',
			'7139',
			'7429',
			'7064',
			'7371',
			'10375',
			'7422',
			'7562',
			'7161',
			'7423',
			'9883',
			'7592',
			'10136',
			'10247',
			'7605',
			'8598',
			'10015',
			'7511',
			'10231',
			'7100',
			'9798',
			'7419',
			'6895',
			'7565',
			'7564',
			'8601',
			'7017',
			'7626',
			'7279',
			'7626',
			'6894',
			'10325',
			'9020',
			'7174',
			'7090',
			'6990',
			'7283',
			'10331',
			'7354',
			'7430',
			'7106',
			'903',
			'6965',
			'7338',
			'7151',
			'6954',
			'7213',
			'7093',
			'6921',
			'7112',
			'7580',
			'8608',
			'7094',
			'7425',
			'50',
			'6801',
			'8087',
			'8296',
			'74',
			'1641',
			'7908',
			'8400',
			'8412',
			'8284',
			'8298',
			'1397',
			'7894',
			'1899',
			'1863',
			'8309',
			'7697',
			'195',
			'63',
			'8542',
			'7637',
			'8251',
			'3',
			'1650',
			'8226',
			'8347',
			'2052',
			'7983',
			'178',
			'508',
			'859',
			'1646',
			'1098',
			'1961',
			'8429',
			'8478',
			'2403',
			'8085',
			'8023',
			'96',
			'4891',
			'1729',
			'2001',
			'8457',
			'2364',
			'1316',
			'8077',
			'8216',
			'8197',
			'8422',
			'800',
			'2526',
			'8410',
			'5081',
			'1502',
			'3007',
			'10220',
			'97',
			'4197',
			'8336',
			'8118',
			'8445',
			'2377',
			'8566',
			'7846',
			'8300',
			'8331',
			'1745',
			'1503',
			'8306',
			'1527',
			'70',
			'1996',
			'7763',
			'7603',
			'7865',
			'1035',
			'8167',
			'2190',
			'8416',
			'8513',
			'8014',
			'7914',
			'1688',
			'2786',
			'7906',
			'8526',
			'8614',
			'2113',
			'8163',
			'7883',
			'8287',
			'7809',
			'186',
			'8427',
			'8415',
			'8403',
			'7778',
			'8165',
			'827',
			'1505',
			'7408',
			'938',
			'2480',
			'7824',
			'7095',
			'8379',
			'8392',
			'8016',
			'8236',
			'1070',
			'1224',
			'7731',
			'8274',
			'8207',
			'2111',
			'2534',
			'8327',
			'7639',
			'1615',
			'368',
			'6803',
			'371',
			'7985',
			'2131',
			'2153',
			'142',
			'7955',
			'8221',
			'1020',
			'8316',
			'8328',
			'3916',
			'307',
			'7674',
			'1939',
			'7767',
			'1807',
			'7828',
			'1350',
			'8220',
			'8072',
			'2273',
			'4777',
			'6007',
			'2394',
			'212',
			'1918',
			'306',
			'7089',
			'2057',
			'1551',
			'972',
			'1638',
			'8001',
			'778',
			'2044',
			'8407',
			'592',
			'8528',
			'10217',
			'7662',
			'7848',
			'8472',
			'2295',
			'8013',
			'1739',
			'2369',
			'2490',
			'4876',
			'7693',
			'807',
			'8406',
			'8503',
			'864',
			'8214',
			'7944',
			'8147',
			'1036',
			'5557',
			'1631',
			'7602',
			'1553',
			'8350',
			'8253',
			'8417',
			'8029',
			'8303',
			'7682',
			'7780',
			'8381',
			'7678',
			'535',
			'7935',
			'7667',
			'1570',
			'7748',
			'8026',
			'8052',
			'626',
			'2017',
			'8101',
			'8028',
			'7792',
			'6580',
			'7781',
			'8004',
			'5302',
			'43',
			'8390',
			'1735',
			'7849',
			'7759',
			'8051',
			'7940',
			'413',
			'522',
			'8313',
			'2189',
			'9946',
			'7676',
			'8259',
			'8223',
			'7653',
			'1816',
			'7745',
			'1340',
			'7757',
			'2045',
			'8086',
			'8430',
			'8091',
			'8002',
			'1355',
			'7652',
			'9559',
			'5525',
			'5537',
			'7783',
			'7756',
			'8108',
			'7768',
			'1353',
			'7769',
			'8212',
			'2148',
			'8319',
			'2046',
			'8195',
			'8304',
			'2034',
			'1607',
			'7702',
			'1710',
			'7195',
			'8428',
			'8201',
			'8371',
			'8480',
			'1848',
			'7699',
			'8492',
			'1132',
			'7643',
			'1299',
			'8185',
			'454',
			'8010',
			'9592',
			'1606',
			'1625',
			'1521',
			'8064',
			'8311',
			'7182',
			'994',
			'328',
			'219',
			'7877',
			'1929',
			'2206',
			'1433',
			'1359',
			'8476',
			'7961',
			'6398',
			'5838',
			'6350',
			'7853',
			'8432',
			'2089',
			'476',
			'7878',
			'2023',
			'16',
			'1356',
			'2232',
			'8490',
			'7972',
			'6823',
			'1489',
			'483',
			'117',
			'5993',
			'7852',
			'2363',
			'1423',
			'626',
			'241',
			'8291',
			'5561',
			'8213',
			'1400',
			'7618',
			'8310',
			'1627',
			'8431',
			'6121',
			'8035',
			'8346',
			'13584',
			'7995',
			'8449',
			'478',
			'8168',
			'2516',
			'204',
			'2050',
			'8121',
			'2063',
			'8375',
			'7682',
			'7851',
			'2186',
			'8038',
			'573',
			'8495',
			'2087',
			'1414',
			'119',
			'1868',
			'2051',
			'8361',
			'477',
			'7670',
			'1317',
			'7994',
			'8241',
			'2515',
			'8156',
			'8398',
			'8374',
			'2484',
			'7974',
			'232',
			'8315',
			'273',
			'451',
			'8250',
			'8047',
			'8289',
			'8189',
			'8092',
			'1634',
			'7156',
			'1004',
			'1846',
			'8264',
			'206',
			'8046',
			'7656',
			'8397',
			'253',
			'8240',
			'2552',
			'2138',
			'8202',
			'5224',
			'8056',
			'2292',
			'8090',
			'1907',
			'205',
			'861',
			'8130',
			'8475',
			'546',
			'7991',
			'3192',
			'1395',
			'3887',
			'4593',
			'1949',
			'7803',
			'1478',
			'8055',
			'7718',
			'462',
			'2286',
			'167',
			'7859',
			'8529',
			'8464',
			'415',
			'7806',
			'1833',
			'592',
			'8125',
			'7904',
			'8246',
			'8308',
			'1479',
			'1725',
			'194',
			'6827',
			'8092',
			'8243',
			'2018',
			'2019',
			'7873',
			'8219',
			'1666',
			'8463',
			'7832',
			'398',
			'8366',
			'8124',
			'8245',
			'7801',
			'2368',
			'4209',
			'8123',
			'8244',
			'121',
			'393',
			'2518',
			'318',
			'6879',
			'8462',
			'2302',
			'8536',
			'2296',
			'315',
			'1784',
			'8507',
			'1080',
			'2116',
			'1723',
			'294',
			'8461',
			'7911',
			'7948',
			'8388',
			'446',
			'1216',
			'2434',
			'8083',
			'2025',
			'7841',
			'1691',
			'7910',
			'2568',
			'8039',
			'5452',
			'8301',
			'2548',
			'8062',
			'1860',
			'8117',
			'8129',
			'8456',
			'1530',
			'2512',
			'8069',
			'2002',
			'290',
			'490',
			'7815',
			'5',
			'8232',
			'2398',
			'136',
			'1721',
			'7819',
			'8128',
			'1674',
			'8249',
			'2346',
			'8443',
			'962',
			'2501',
			'8252',
			'8104',
			'181',
			'193',
			'592',
			'7659',
			'8133',
			'299',
			'8272',
			'1289',
			'8518',
			'1442',
			'7645',
			'1265',
			'2093',
			'2236',
			'8218',
			'2527',
			'8115',
			'8127',
			'1263',
			'8280',
			'8266',
			'8254',
			'2504',
			'7644',
			'8183',
			'1927',
			'1276',
			'1264',
			'2235',
			'1672',
			'988',
			'8344',
			'7838',
			'8126',
			'1906',
			'2011',
			'1060',
			'2008',
			'1807',
			'2286',
			'8284',
			'1759',
			'2092',
			'2471',
			'2121',
			'1129',
			'2285',
			'2210',
			'1666',
			'2209',
			'563',
			'2044',
			'1853',
			'8366',
			'1991',
			'2085',
			'8111',
			'476',
			'2013',
			'1795',
			'1756',
			'486',
			'2232',
			'1220',
			'2049',
			'1679',
			'2302',
			'1087',
			'1719',
			'1631',
			'2009',
			'1801',
			'9939',
			'2434',
			'2341',
			'7791',
			'464',
			'478',
			'8353',
			'573',
			'2063',
			'7603',
			'2035',
			'676',
			'1995',
			'2381',
			'1798',
			'8526',
			'1828',
			'8232',
			'1452',
			'2289',
			'2333',
			'2398',
			'1498',
			'2501',
			'2372',
			'2251',
			'447',
			'1839',
			'2138',
			'192',
			'2004',
			'2570',
			'8254',
			'298',
			'1668',
			'8195',
			'748',
			'1835',
			'2466',
			'255',
			'141',
			'7576',
			'1139',
			'2130',
			'2239',
			'1141',
			'6954',
			'460',
			'7174',
			'7436',
			'2071',
			'1023',
			'7059',
			'351',
			'7421',
			'1823',
			'4818',
			'429',
			'2142',
			'207',
			'2574',
			'2300',
			'6469',
			'2300',
			'4778',
			'7511',
			'7213',
			'1777',
			'1210',
			'7527',
			'708',
			'6923',
			'741',
			'505',
			'5939',
			'979',
			'419',
			'1452',
		];

		foreach ($urls_with_not_found as $key => $id) {
			$rest = RestaurantNotFound::find()->where(['id' => $id])->one();
			if (empty($rest)) {
				$rest_new = new RestaurantNotFound();
				$rest_new->id = $id;
				$rest_new->save();
			}
		} */


		/* $connection = new \yii\db\Connection([
			'username' => 'pmnetwork',
			'password' => 'P6L19tiZhPtfgseN',
			'charset'  => 'utf8mb4',
			'dsn' => 'mysql:host=localhost;dbname=pmn'
		]);
		$connection->open();
		Yii::$app->set('db', $connection);

		$restaurant = Restaurants::find()
			->with('rooms')
			->with('imagesext')
			// ->with('subdomen')
			// ->with('yandexReview')
			// ->where(['active' => 1, 'commission' => 2, 'city_id' => 4682])
			->where(['gorko_id' => 204761])
			->limit(10000)
			->one();


		$connection = new \yii\db\Connection([
			'username' => 'pmnetwork',
			'password' => 'P6L19tiZhPtfgseN',
			'charset'  => 'utf8mb4',
			'dsn' => 'mysql:host=localhost;dbname=pmn_gorko_ny'
		]);
		$connection->open();
		Yii::$app->set('db', $connection);

		$images_module = ImagesModule::find()
			->limit(100000)
			->asArray()
			->all();
		$images_module = ArrayHelper::index($images_module, 'gorko_id'); */




		// $rest_redirects = RestaurantsRedirect::find()->select('old_id')->all();
		// $rest_redirects = ArrayHelper::index($rest_redirects, 'old_id');

		// $arr_keys = array_keys($rest_redirects);

		// echo ('<pre>');
		// print_r($arr_keys);
		// exit;

		// $rests_unique = RestaurantsUniqueId::find()
		// 	->where(['unique_id' => $arr_keys])
		// 	->all();
		// $rests_unique = ArrayHelper::index($rests_unique, 'id');
		// $arr_new_keys = array_keys($rests_unique);

		// echo ('<pre>');
		// print_r($rests_unique);
		// exit;

		// $connection = new \yii\db\Connection([
		// 	'username' => 'pmnetwork',
		// 	'password' => 'P6L19tiZhPtfgseN',
		// 	'charset'  => 'utf8mb4',
		// 	'dsn' => 'mysql:host=localhost;dbname=pmn'
		// ]);
		// $connection->open();
		// Yii::$app->set('db', $connection);


		// $rests = Restaurants::find()
		// 	->where(['gorko_id' => $arr_new_keys])
		// 	->andWhere(['active' => 0])
		// 	->limit(10000)
		// 	->all();

		// echo ('<pre>');
		// print_r(count($rests));
		// exit;


		//* ========= START перенос старых ссылок из таблицы restaurants_redirect в таблицу restaurant_not_found =========
		/* $rests_redirect = RestaurantsRedirect::find()->all();

		foreach ($rests_redirect as $rest_redirect) {
			$exist_not_found_rest = RestaurantNotFound::find()
				->where(['id' => $rest_redirect['new_id']])
				->one();

			if (!$exist_not_found_rest) {
				$rest_new = new RestaurantNotFound();
				$rest_new->id = $rest_redirect['new_id'];
				$rest_new->save();
			}
		}

		echo ('<pre>');
		print_r(22222);
		exit; */
		//* ========= END перенос старых ссылок из таблицы restaurants_redirect в таблицу restaurant_not_found =========



		self::subdomenCheck();

		echo (11111);
		exit;
	}


	// public static function subdomenCheck($connection_core)
	public static function subdomenCheck()
    {
        SubdomenFilteritem::deactivate();
        $counterActive = 0;
        $counterInactive = 0;
        foreach (Subdomen::find()->all() as $key => $subdomen) {
            // $rest_total = self::find()
			$rest_total = (new ElasticItems())::find()
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

        // $query = self::find()->query($final_query)->limit(0);
		$query = (new ElasticItems())::find()->query($final_query)->limit(0);

        return $query->search()['hits']['total'];
    }




	public function actionCustom()
	{

		// $elastic_model = new ElasticItems;
		// $item = $elastic_model::find()
		// ->query(['bool' => ['must' => ['match'=>['restaurant_unique_id' => 254]]]])
		// ->limit(1)
		// ->search();
		// $item = $item['hits']['hits'][0];

		// echo '<pre>';
		// print_r($item);
		// exit;


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

		$restTypesList = [
			'banketnye' => 'в банкетном зале',
			'restorany' => 'в ресторане',
			'kafe' => 'в кафе',
			'kluby' => 'в клубе',
			'bary' => 'в баре',
			'v' => 'на площадке в городе',
			'na' => 'на площадке на природе',
			'ploshhadki' => 'ploshhadki',
		];

		$page = Pages::find()->where(['between', 'id', 20, 167])->with('seoObject')->all();

		foreach ($page as $p) {
			$seo = SiteObjectSeo::find()->where(['id' => $p->seoObject->id])->one();

			$title = '';
			$aliasExploded = explode('-', $p->type);
			$type = $restTypesList[$aliasExploded[0]];

			if ($type === 'ploshhadki') {
				continue;
			}

			if (stripos($p->type, 'chelovek')) {
				$slice = array_slice($aliasExploded, -2, 1);
				$title = 'Заказать новогодний корпоратив ' . $type . ' **city_rod** на ' . array_pop($slice) . ' человек';
			} elseif (stripos($p->type, 'price')) {
				$title = 'Заказать новогодний корпоратив ' . $type . ' **city_rod** от ' . array_pop($aliasExploded) . ' рублей с человека';
			} elseif (stripos($p->type, 'svoy-alko')) {
				$title = 'Новогодний корпоратив со своим алкоголем ' . $type . ' **city_rod** без пробкового сбора';
			} elseif (stripos($p->type, 'firework')) {
				$title = 'Заказать новогодний корпоратив ' . $type . ' **city_rod** с фейерверками и салютом';
			}

			// $seo->pagination_title = $seo->title . ' - Страница №**page**';

			if ($title === '') {
				exit;
			}

			$seo->title = $title;
			// $seo->save();

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
					'params' => 'city_id=' . $subdomen->city_id . '&type_id=1&event=17',
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

	public function actionTestspecs()
	{
		/*$gorko_id = 425325;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/restaurants/'.$gorko_id.'?embed=rooms,contacts&fields=address,params,covers,district,metro,specs,room_specs&is_edit=1');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl, CURLOPT_ENCODING, '');
	    $response = json_decode(curl_exec($curl), true)['restaurant'];
	    curl_close($curl);
	    foreach ($response['rooms'] as $key => $value) {
	    	var_dump($value['params']);
	    }
	    

	    exit;*/
		$mysql_config =	\Yii::$app->params['mysql_config'];
		$main_config = \Yii::$app->params['main_api_config'];
		$connection_config = array_merge($mysql_config, $main_config['mysql_config']);
		$connection = new \yii\db\Connection($connection_config);
		$connection->open();
		Yii::$app->set('db', $connection);
		$gorko_id = 425325;
		$restaurant = Restaurants::find()->where(['gorko_id' => $gorko_id])->one();
		$arr_rooms  = $arr_gorko_rooms = $arr_spec_prices = array();
		foreach ($restaurant->rooms as $room) {
			array_push($arr_rooms, $room->gorko_id);
			$arr_gorko_rooms[$room->gorko_id] = $room->id;
		}
		$arr_room_specs = RoomsSpec::getSpecsForRest($arr_rooms);
		$q = print_r($arr_rooms, 1);
		file_put_contents('/var/www/pmnetwork/frontend/modules/gorko_ny/web/log.txt', 'arr_rooms ' . $q . "\n");
		$q = print_r($arr_room_specs, 1);
		file_put_contents('/var/www/pmnetwork/frontend/modules/gorko_ny/web/log.txt', 'arr_room_specs ' . $q . "\n", FILE_APPEND);
		try {
			if ($curl = curl_init()) {
				curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v3/venue/' . $gorko_id . '?entity[channel]=a&entity[languageId]=1');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_ENCODING, '');
				$response = json_decode(curl_exec($curl), true);
				if (!isset($response['entity'][$gorko_id])) return 1;
				$response = $response['entity'][$gorko_id];
				curl_close($curl);
				$arr_gorko_room_specs = array();
				var_dump($response);
				foreach ($response['room'] as $room) {
					$arr_gorko_room_specs[$room['id']] = array();
					foreach ($room['spec'] as $spec) {
						array_push($arr_gorko_room_specs[$room['id']], $spec['id']);
					}
				}
			}
		} catch (Exception $e) {
			//echo 'Выброшено исключение: '.  $e->getMessage() . "\n";
		}
		$q = print_r($arr_gorko_room_specs, 1);
		file_put_contents('/var/www/pmnetwork/frontend/modules/gorko_ny/web/log.txt', 'arr_gorko_room_specs ' . $q . "\n", FILE_APPEND);
		foreach ($arr_gorko_room_specs as $gorko_id => $spec_ids) {
			$arr_spec_prices = array();

			foreach ($spec_ids as $spec_id) {
				if (!isset($arr_room_specs[$gorko_id]) || !in_array($spec_id, array_keys($arr_room_specs[$gorko_id]))) {
					$arr_spec_prices[$spec_id] = NULL;
				} else {
					$arr_spec_prices[$spec_id] = $arr_room_specs[$gorko_id][$spec_id];
				}
			}
			$q = print_r($arr_spec_prices, 1);
			file_put_contents('/var/www/pmnetwork/frontend/modules/gorko_ny/web/log.txt', 'room_id ' . $gorko_id . ' arr_spec_prices ' . $q . "\n", FILE_APPEND);
			RoomsSpec::updateSpecPrices($arr_gorko_rooms[$gorko_id], $gorko_id, $arr_spec_prices);
		}
	}

	public function actionSubdomencheck()
	{
		$subdomen_model = Subdomen::find()->all();

		foreach ($subdomen_model as $key => $subdomen) {
			$restaurants = Restaurants::find()->where(['city_id' => $subdomen->city_id])->all();
			if (count($restaurants) > 9) {
				$subdomen->active = 1;
			} else {
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
			'url' => 'https://lh3.googleusercontent.com/XKtdffkbiqLWhJAWeYmDXoRbX51qNGOkr65kMMrvhFAr8QBBEGO__abuA_Fu6hHLWGnWq-9Jvi8QtAGFvsRNwqiC',
			'token' => '4aD9u94jvXsxpDYzjQz0NFMCpvrFQJ1k',
			'watermark' => $output,
			'hash_key' => 'svadbanaprirode'
		];
		curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/tools/mediaToSatellite');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
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

	private function sendMail($to, $subj, $msg)
	{
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
	public function actionShowfull()
	{
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
				'params' => 'city_id=' . $subdomen->city_id . '&type_id=1'
			]);
			$log = json_decode(file_get_contents('/var/www/pmnetwork/pmnetwork/log/count.json'), true);
			$log[$subdomen->name] = $count;
			file_put_contents('/var/www/pmnetwork/pmnetwork/log/count.json', json_encode($log));
		}
	}
}
