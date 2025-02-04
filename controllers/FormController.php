<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\Html;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\components\GorkoLeadApi;

class FormController extends Controller
{
    public function beforeAction($action){
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionSend()
    {
		$payload = [];

        if(isset($_POST['name']))
            $payload['name'] = $_POST['name'];
        if(isset($_POST['phone']))
            $payload['phone'] = $_POST['phone'];
        if(isset($_POST['guests_number']))
            $payload['guests'] = intval($_POST['guests_number']);
        if(isset($_POST['budget']))
            $payload['price'] = intval($_POST['budget']);
        if(isset($_POST['date']))
            $payload['date'] = $_POST['date'];
        if(isset($_POST['cityID']))
            $payload['city_id'] = $_POST['cityID'];
        if(isset($_POST['venue_id']))
            $payload['venue_id'] = $_POST['venue_id'];
        if(isset($_POST['email']))
            $payload['email'] = $_POST['email'];
        $payload['details'] = '';
        $payload['event_type'] = 'Corporate';
        if(isset($_POST['question']))
            $payload['details'] .= $_POST['question'].' ';
        if(isset($_POST['url']))
            $payload['details'] .= 'Заявка отправлена с '.$_POST['url'];
		  if(isset($_POST['restaurant_name']))
		      $payload['details'] .= ' Название ресторана: '.$_POST['restaurant_name'];
        if(!isset($payload['city_id']))
            return false;

        $resp = GorkoLeadApi::send_lead('v.gorko.ru', 'korporativ-ng', $payload);

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resp;
        // return 1;
    }

    public function actionRoom()
    {
        $elastic_model = new ElasticItems;
        $item = $elastic_model::find()
        ->query(['bool' => ['must' => ['match'=>['restaurant_unique_id' => $_POST['room_id']]]]])
        ->limit(1)
        ->search();

        $item = $item['hits']['hits'][0];
        //$special_obj = new ItemSpecials($item->restaurant_special);
        //$item->restaurant_special = $special_obj->special_arr;

        $to   = [$_POST['book_email']];
        $subj = "Информация о ресторане для корпоратива.";
        $msg  = $this->renderPartial('//emails/roominfo.twig', array(
            'url' => Yii::$app->params['subdomen_alias'] ? 'https://'.Yii::$app->params['subdomen_alias'].'.korporativ-ng.ru/ploshhadki/'.$_POST['room_id'].'/'  : 'https://korporativ-ng.ru/ploshhadki/'.$_POST['room_id'].'/',
            'item' => $item,
            'link' => Yii::$app->params['subdomen_alias'] ? 'https://'.Yii::$app->params['subdomen_alias'].'.korporativ-ng.ru' : 'https://korporativ-ng.ru'
        ));



        $message = $this->sendMail($to,$subj,$msg);
        if ($message) {
            $responseMsg = empty($responseMsg) ? 'Успешно отправлено!' : $responseMsg;
            $resp = [
                'error' => 0,
                'msg' => $responseMsg,
            ];              
        } else {
            $resp = ['error'=>1, 'msg'=>'Ошибка'];//.serialize($_POST)
        }       
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resp;
    }

    public function actionAdvertisement()
	{
		if (!isset($_POST['name']) || !isset($_POST['phone']))
			return 1;

		$to = ['so-svoim.ru@yandex.ru'];
		$subj = 'Заявка с сайта';
		$msg  = "";
		$post_string_array = [
			'name'				=>	'Имя и фамилия',
			'position'			=>	'Должность',
			'phone'				=>	'Телефон',
			'email'				=>	'Электропочта',
			'rest_name'			=>	'Название площадки',
			'city'				=>	'Город',
			'address'			=>	'Адрес',
		];

		foreach ($post_string_array as $key => $value) {
			if (isset($_POST[$key]) && $_POST[$key] != '') {
				$msg .= $value . ': ' . $_POST[$key] . '<br/>';
				$payload[$key] = $_POST[$key];
			}
		}

		$message = $this->sendMail($to, $subj, $msg);

		if ($message) {
			$responseMsg = empty($responseMsg) ? 'Успешно отправлено!' : $responseMsg;
			$resp = [
				'error' => 0,
				'msg' => $responseMsg,
				'payload' => $payload,
			];
		} else {
			$resp = ['error' => 1, 'msg' => 'Ошибка']; //.serialize($_POST)
		}
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $resp;
	}

    public function sendMail($to,$subj,$msg) {
        $message = Yii::$app->mailer->compose()
            ->setFrom(['send@korporativ-ng.ru' => 'Новогодний корпоратив.'])
            ->setTo($to)
            ->setSubject($subj)
            ->setCharset('utf-8')
            //->setTextBody('Plain text content')
            ->setHtmlBody($msg.'.');
        if (count($_FILES) > 0) {
            foreach ($_FILES['files']['tmp_name'] as $k => $v) {
                $message->attach($v, ['fileName' => $_FILES['files']['name'][$k]]);
            }
        }
        return $message->send();
    }
}
