<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\controllers\ApiController as BaseApiController;
use common\models\api\MapAll;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Filter;
use common\models\Slices;
use frontend\components\ParamsFromQuery;

class ApiController extends BaseApiController
{
	public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

	public function actionMapall()
	{
		$filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
		$slices_model = Slices::find()->all();
		$elastic_model = new ElasticItems;
		$params = $this->parseGetQuery(json_decode($_POST['filter'], true), $filter_model, $slices_model);
		// $map_all = new MapAll($elastic_model, $_POST['subdomain_id'], $params['params_filter']);
		$map_all = new MapAll($elastic_model, $_POST['subdomain_id'], $params['params_filter'], 'restaurants', '/ploshhadki/', 'id', true);

		//echo '<pre>';
		//print_r($map_all->coords);
		//echo '</pre>';
		//exit;

		return json_encode($map_all->coords);
	}

	public function actionBanketkazan()
	{
		$post_data = json_decode(json_encode($_POST), true);

		$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
	    $log .= json_encode($post_data);
	    file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		if($post_data['event'] != 'lead')
			return 1;

		$payload = [];

		//МАССИВ КЛЮЧЕЙ ДЛЯ ТИПОВ МЕРОПРИЯТИЯ
		$event_type = [
			'Свадьба' => 'Wedding',
			'Корпоратив' => 'Corporate',
			'Юбилей' => 'Birthday',
			'День рождения' => 'Birthday',
			'Выпускной' => 'High school graduation',
			'Вечеринка' => 'Party',
			'Встреча' => 'A meeting',
			'Детский день рождения' => 'Kids party',
			'Презентация' => 'Presentation',
			'Деловая встреча' => 'A business meeting',
			'Юбилей компании' => 'Corporate',
			'Семинар' => 'Training',
		];

		//echo '<pre>';
		//print_r($post_data);
		//exit;

		//ПАРСИНГ ДАННЫХ ДЛЯ КУРЛЫКОВ В API ЛИДОВ
		$payload['city_id'] = 5269;
		if(isset($post_data['data']['utm']['utm_medium'])){
			if($post_data['data']['utm']['utm_medium'] == 'samara')
				$payload['city_id'] = 4917;
			if($post_data['data']['utm']['utm_medium'] == 'voronezh')
				$payload['city_id'] = 3538;
		}			
		$payload['name'] = $post_data['data']['client']['name'];
		$payload['phone'] = $post_data['data']['client']['phone'];
		foreach ($post_data['data']['form_data'] as $key => $value) {
			switch ($value['name']) {
				case 'Мероприятие':
					$payload['event_type'] = $event_type[$value['value']];
					break;
				case 'Дата':
					$payload['date'] = $newDate = date("Y.m.d", strtotime($value['value']));
					break;
				case 'Средний чек':
					$price = ['min' => 0, 'max' => 0];
					$min_pos = stripos($value['value'], 'от');
					$min_pos !== false ? $price['min'] = substr($value['value'], $min_pos+3, 4) : $price['min'] = 0;
					$max_pos = stripos($value['value'], 'до');
					$max_pos !== false ? $price['max'] = substr($value['value'], $max_pos+3, 4) : $price['max'] = 0;
					if($price['min'] != 0 && $price['max'] != 0){
						$price_api_str = $price['min'].' - '.$price['max'].' ₽';
					}
					else{
						$price['min'] != 0 ? $price_api_str = 'от '.$price['min'].' ₽' : $price_api_str = 'до '.$price['max'].' ₽';
					}
					
					$payload['price'] = $price_api_str;
					break;
				case 'Гостей':
					if(is_numeric($value['value'])){
						$payload['guests'] = $value['value'];
					}					
					break;
				case 'Способ связи':
					if($value['value'] == 'По телефону'){
						$payload['is_phone_preferred'] = 1;
					}
					else{
						$payload['is_phone_preferred'] = 0;
						$payload['whatsapp'] = $post_data['data']['client']['phone'];
					}
					break;
				
				default:
					break;
			}
		}

		//ОТПРАВКА КУРЛЫКОВ В API ЛИДОВ
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://v.gorko.ru/api/banket_gorko/inquiry/put');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

		//ЛОГИРОВАНИЕ ОТВЕТА КУРЛЫКОВ В API ЛИДОВ
        $log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		$log .= json_encode([
            'response' => $response,
            'info' => $info,
            'payload' => $payload,
        ]);
		file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		//ВЕРНУТЬ ОДИН
        return 1;
	}

	public function actionBanketwedding()
	{
		$post_data = json_decode(json_encode($_POST), true);

		$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
	    $log .= json_encode($post_data);
	    file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		if($post_data['event'] != 'lead')
			return 1;

		//$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		//$log .= json_encode($post_data);
		//file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		$payload = [];

		//МАССИВ КЛЮЧЕЙ ДЛЯ ТИПОВ МЕРОПРИЯТИЯ
		$price_ranges = [
			'Less than ₹ 1000' => 'до 1000 ₹',
			'₹ 1000 – 1,399' => '1000 - 1399 ₹',
			'₹ 1,400 – 1,799' => '1400 - 1799 ₹',
			'₹ 1,800 – 2,199' => '1800 - 2199 ₹',
			'from ₹ 2,200' => 'от 2200 ₹'
 		];

 		$guests_ranges = [
			'Up to 20 people' => 20,
			'20 — 49 people' => 40,
			'50 — 69 people' => 60,
			'70 — 99 people' => 80,
			'100 — 199 people' => 150,
			'200 — 299 people' => 250,
			'300 — 499 people' => 400,
			'More then 500 people' => 600,
 		];

		//echo '<pre>';
		//print_r($post_data);
		//exit;

		//ПАРСИНГ ДАННЫХ ДЛЯ КУРЛЫКОВ В API ЛИДОВ
		$payload['city_id'] = 15789641;
		$payload['name'] = $post_data['data']['client']['name'];
		$payload['phone'] = $post_data['data']['client']['phone'];
		foreach ($post_data['data']['form_data'] as $key => $value) {
			switch ($value['name']) {
				case 'Event':
					if($value['value'] == 'Another event'){
						$payload['event_type'] = 'Unknown';
					}
					else{
						$payload['event_type'] = 'Wedding';
					}
					break;
				case 'Event date':
					if($value['value'] != '—'){
						if($value['value'])
							$payload['date'] = $newDate = date("Y.m.d", strtotime($value['value']));
					}					
					break;
				case 'Budget':
					if(isset($price_ranges[$value['value']]))
						$payload['price'] = $price_ranges[$value['value']];
					break;
				case 'How many guests':
					if(isset($guests_ranges[$value['value']]))
						$payload['guests'] = $guests_ranges[$value['value']];
					break;
				case 'Best way to reach you':
					if($value['value'] == 'By phone'){
						$payload['is_phone_preferred'] = 1;
					}
					else{
						$payload['is_phone_preferred'] = 0;
						$payload['whatsapp'] = $post_data['data']['client']['phone'];
					}
					break;
				
				default:
					break;
			}
		}

		$api_url = 'https://v.wedding.net/api/banket_wedding/inquiry/put';
		if(isset($post_data['data']['utm']['utm_medium']) and $post_data['data']['utm']['utm_medium'] == 'Gurugram'){
			$api_url = 'https://v.wedding.net/api/banket_wedding_gurugram/inquiry/put';
		}
		//ОТПРАВКА КУРЛЫКОВ В API ЛИДОВ
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

		//ЛОГИРОВАНИЕ ОТВЕТА КУРЛЫКОВ В API ЛИДОВ
        $log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		$log .= json_encode([
            'response' => $response,
            'info' => $info,
            'payload' => $payload,
        ]);
		file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		//ВЕРНУТЬ ОДИН
        return 1;
	}

	public function actionDelhiphoto()
	{
		$post_data = json_decode(json_encode($_POST), true);

		$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
	    $log .= json_encode($post_data);
	    file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		if($post_data['event'] != 'lead')
			return 1;

		//$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		//$log .= json_encode($post_data);
		//file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		$payload = [];

		//МАССИВ КЛЮЧЕЙ ДЛЯ ТИПОВ МЕРОПРИЯТИЯ
		$price_ranges = [
			'Less than ₹ 20,000' => 'до 20000 ₹',
			'₹ 20,000 – 39,999' => '20000 - 39999 ₹',
			'₹ 40,000 – 59,999' => '40000 - 59999 ₹',
			'₹ 60,000 – 99,999' => '60000 - 99999 ₹',
			'from ₹ 1,00,000' => 'от 100000 ₹'
 		];

 		$guests_ranges = [
			'Up to 20 people' => 20,
			'20 — 49 people' => 40,
			'50 — 69 people' => 60,
			'70 — 99 people' => 80,
			'100 — 199 people' => 150,
			'200 — 299 people' => 250,
			'300 — 499 people' => 400,
			'More then 500 people' => 600,
 		];

		//echo '<pre>';
		//print_r($post_data);
		//exit;

		//ПАРСИНГ ДАННЫХ ДЛЯ КУРЛЫКОВ В API ЛИДОВ
		$payload['city_id'] = 15789641;
		$payload['name'] = $post_data['data']['client']['name'];
		$payload['phone'] = $post_data['data']['client']['phone'];
		foreach ($post_data['data']['form_data'] as $key => $value) {
			switch ($value['name']) {
				case 'Services required':
					if($value['value'] == 'Another event'){
						$payload['event_type'] = 'Unknown';
					}
					else{
						$payload['event_type'] = $value['value'];
					}
					break;
				case 'Event date':
					if($value['value'] != '—'){
						if($value['value']){
							$date = str_replace("/", "-", $value['value']);
							$payload['date'] = $newDate = date("Y.m.d", strtotime($date));
						}
					}					
					break;
				case 'Budget for photographer':
					if(isset($price_ranges[$value['value']]))
						$payload['price'] = $price_ranges[$value['value']];
					break;
				case 'How many guests':
					if(isset($guests_ranges[$value['value']]))
						$payload['guests'] = $guests_ranges[$value['value']];
					break;
				case 'Best way to reach you':
					if($value['value'] == 'By phone'){
						$payload['is_phone_preferred'] = 1;
					}
					else{
						$payload['is_phone_preferred'] = 0;
						$payload['whatsapp'] = $post_data['data']['client']['phone'];
					}
					break;
				
				default:
					break;
			}
		}

		$payload['inquiry_type'] = 2;

		$api_url = 'https://v.wedding.net/api/delhi_photo/inquiry/put';
		//ОТПРАВКА КУРЛЫКОВ В API ЛИДОВ
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

		//ЛОГИРОВАНИЕ ОТВЕТА КУРЛЫКОВ В API ЛИДОВ
        $log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		$log .= json_encode([
            'response' => $response,
            'info' => $info,
            'payload' => $payload,
        ]);
		file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		//ВЕРНУТЬ ОДИН
        return 1;
	}

	public function actionDelhiphotomanual()
	{
		$post_data = json_decode('{"event":"lead","data":{"id":"20191670","num":"1917","time":"1621347008","status":{"code":"0","name":"New"},"client":{"name":"Ankit","phone":"+91 (926) 8139729","email":""},"note":"","form_name":"20000-60000","form_data":{"f9eae8de8":{"id":"f9eae8de8","name":"Name","value":"Ankit","type":"name","orig_name":"Your name"},"f400830ed":{"id":"f400830ed","name":"Phone","value":"+91 (926) 8139729","type":"phone","orig_name":"Phone number"},"123553":{"id":"123553","type":"checkbox","name":"Services required","value":"Wedding, Ring ceremony"},"fa1763dc7":{"id":"fa1763dc7","type":"date","name":"Event date","value":""},"f2031bbf0":{"id":"f2031bbf0","type":"checkbox","name":"Date not fix yet","value":"Date not fix yet"},"f37ae004c":{"id":"f37ae004c","type":"radio","name":"Budget","value":"\u20b9 20,000 \u2013 39,999"},"fb9e5a06b":{"id":"fb9e5a06b","type":"radio","name":"Best way to reach you","value":"By phone"}},"page":{"url":"http:\/\/delhiphoto.wedding.net\/","name":"wedding photographer in Delhi"},"utm":{"utm_source":"inst_lenta_stories_convers","utm_medium":"Delhi","utm_campaign":"Delhi_shirokaya_svadebn_int","utm_content":"Delhi_shirokaya_svadebn_int_im1","url":"http:\/\/instagram.com\/","ip":"103.99.199.4","ga_client_id":"1625609966.1621346919"},"pay":{"pay_link":"http:\/\/delhiphoto.wedding.net\/?pay_id=0&h=bdf1d4acf1e7b590bf2c61872155fa02"},"custom":""},"site":{"id":"546730","sub_id":"446013","domain":"delhiphoto.wedding.net","name":"\u041b\u0435\u043d\u0434\u0438\u043d\u0433"}}', true);

		$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
	    $log .= json_encode($post_data);
	    file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		if($post_data['event'] != 'lead')
			return 1;

		//$log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		//$log .= json_encode($post_data);
		//file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		$payload = [];

		//МАССИВ КЛЮЧЕЙ ДЛЯ ТИПОВ МЕРОПРИЯТИЯ
		$price_ranges = [
			'Less than ₹ 20,000' => 'до 20000 ₹',
			'₹ 20,000 – 39,999' => '20000 - 39999 ₹',
			'₹ 40,000 – 59,999' => '40000 - 59999 ₹',
			'₹ 60,000 – 99,999' => '60000 - 99999 ₹',
			'from ₹ 1,00,000' => 'от 100000 ₹'
 		];

 		$guests_ranges = [
			'Up to 20 people' => 20,
			'20 — 49 people' => 40,
			'50 — 69 people' => 60,
			'70 — 99 people' => 80,
			'100 — 199 people' => 150,
			'200 — 299 people' => 250,
			'300 — 499 people' => 400,
			'More then 500 people' => 600,
 		];

		//echo '<pre>';
		//print_r($post_data);
		//exit;

		//ПАРСИНГ ДАННЫХ ДЛЯ КУРЛЫКОВ В API ЛИДОВ
		$payload['city_id'] = 15789641;
		$payload['name'] = $post_data['data']['client']['name'];
		$payload['phone'] = $post_data['data']['client']['phone'];
		foreach ($post_data['data']['form_data'] as $key => $value) {
			switch ($value['name']) {
				case 'Services required':
					if($value['value'] == 'Another event'){
						$payload['event_type'] = 'Unknown';
					}
					else{
						$payload['event_type'] = $value['value'];
					}
					break;
				case 'Event date':
					if($value['value'] != '—'){
						if($value['value']){
							$date = str_replace("/", "-", $value['value']);
							$payload['date'] = $newDate = date("Y.m.d", strtotime($date));
						}
					}					
					break;
				case 'Budget for photographer':
					if(isset($price_ranges[$value['value']]))
						$payload['price'] = $price_ranges[$value['value']];
					break;
				case 'How many guests':
					if(isset($guests_ranges[$value['value']]))
						$payload['guests'] = $guests_ranges[$value['value']];
					break;
				case 'Best way to reach you':
					if($value['value'] == 'By phone'){
						$payload['is_phone_preferred'] = 1;
					}
					else{
						$payload['is_phone_preferred'] = 0;
						$payload['whatsapp'] = $post_data['data']['client']['phone'];
					}
					break;
				
				default:
					break;
			}
		}

		$payload['inquiry_type'] = 2;

		$api_url = 'https://v.wedding.net/api/delhi_photo/inquiry/put';
		//ОТПРАВКА КУРЛЫКОВ В API ЛИДОВ
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

		//ЛОГИРОВАНИЕ ОТВЕТА КУРЛЫКОВ В API ЛИДОВ
        $log = file_get_contents('/var/www/pmnetwork/pmnetwork/log/manual.log');
		$log .= json_encode([
            'response' => $response,
            'info' => $info,
            'payload' => $payload,
        ]);
		file_put_contents('/var/www/pmnetwork/pmnetwork/log/manual.log', $log);

		//ВЕРНУТЬ ОДИН
        return 1;
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

		$temp_params = new ParamsFromQuery($getQuery, $filter_model, $slices_model);

		$return['params_api'] = $temp_params->params_api;
		$return['params_filter'] = $temp_params->params_filter;
		$return['listing_url'] = $temp_params->listing_url;
		$return['canonical'] = $temp_params->canonical;
		return $return;
	}
}