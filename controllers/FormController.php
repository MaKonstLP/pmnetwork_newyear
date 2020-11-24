<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\Html;
use frontend\modules\gorko_ny\models\ElasticItems;

class FormController extends Controller
{
    //public function getViewPath()
    //{
    //    return Yii::getAlias('@app/modules/svadbanaprirode/views/site');
    //}

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionSend()
    {
		if($_POST['type'] == 'main'){
            if(isset($_POST['name']) && isset($_POST['phone']) && isset($_POST['date'])  && isset($_POST['guests_number'])){
    		    $messageApi = $this->sendApi($_POST['name'], $_POST['phone'], $_POST['date'], $_POST['guests_number']);
            }
            else{
                $messageApi = false;
            }
		
		    //return json_encode($messageApi);
		}
		else{
            if(isset($_POST['name']) && isset($_POST['phone']) && isset($_POST['date'])  && isset($_POST['guests_number'])){
        	    $messageApi = $this->sendApi($_POST['name'], $_POST['phone'], $_POST['date'], $_POST['guests_number']);
            }
            else{
                $messageApi = false;
            }
		}

		//return $messageApi;
        //$to   = ['martynov@liderpoiska.ru'];
        $to   = ['martynov@liderpoiska.ru', 'callme@korporativ-ng.ru', 'sites@plusmedia.ru'];

        if($_POST['type'] == 'main' or $_POST['type'] == 'header'){
            $subj = "Заявка на выбор зала.";
        }
        else{
            $subj = "Заявка на бронирование зала.";
        }
        
        $msg  = "";

        $post_string_array = [
            'name'  => 'Имя',
            'phone' => 'Телефон',
            'question' => 'Вопрос',
            'date'  => 'Дата',
            'budget'  => 'Бюджет',
            'guests_number' => 'Количество гостей',
            'url'   => 'Страница отправки' 
        ];

        $post_checkbox_array = [
            'water'  => 'у воды',
            'tent' => 'с шатром',
            'country'  => 'за городом',
            'incity' => 'в черте города',
        ];

        foreach ($post_string_array as $key => $value) {
            if(isset($_POST[$key]) && $_POST[$key] != ''){
                $msg .= $value.': '.$_POST[$key].'<br/>';
            }
        }   
        
        if($_POST['type'] == 'main'){
            $checkbox_msg = '';
            foreach ($post_checkbox_array as $key => $value) {
                if(isset($_POST[$key]) && $_POST[$key] != ''){
                    $checkbox_msg .= $value.', ';
                }
            }
            if($checkbox_msg != '')
                $msg .= 'Зал должен быть: <br/>'.$checkbox_msg;
        }
        

        $message = $this->sendMail($to,$subj,$msg);
        $to = ['536@plusmedia.ru'];
        $message2 = $this->sendMail($to,$subj,$msg);
        if ($message) {
            $responseMsg = empty($responseMsg) ? 'Успешно отправлено!' : $responseMsg;
            $resp = [
                'error' => 0,
                'msg' => $responseMsg,
                'name' => isset($_POST['name']) ? $_POST['name'] : '',
                'phone' => $_POST['phone'],
                //'api' => $messageApi
            ];              
        } else {
            $resp = ['error'=>1, 'msg'=>'Ошибка'];//.serialize($_POST)
        }       
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resp;
    }

    public function actionRoom()
    {
        $elastic_model = new ElasticItems;
        $item = $elastic_model::get($_POST['room_id']);
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

    public function sendApi($name, $phone, $date, $count) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.gorko.ru/api/v2/venues/all/request?model_type=restaurants&model_id=1&city_id='.Yii::$app->params['subdomen_id']);
        $payload = json_encode([
            "name"      => $name,
            "phone"     => $phone,
            "date"      => $date,
            "guests"    => $count,
            //'budget' => null,
            //'details' => null,
            //'drinks' => null,
            //'event_type' => "1",
            //'food' => null,
            //'line' => null,
            //'page_type' => null,
            //'telegram' => null,
            //'viaLine' => null,
            //'viaPhone' => 1,
            //'viaTelegram' => null,
            //'viaViber' => null,
            //'viaWhatsApp' => null,
            //'viber' => null,
            //'whatsapp' => null,
        ]);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);



        return json_encode([
            'response' => $response,
            'info' => $info,
            'payload' => $payload,
        ]);
    }
}
