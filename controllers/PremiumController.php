<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use backend\modules\premium_api\components\Unique;
use backend\modules\premium_api\components\Click;
use backend\modules\premium_api\components\Callback;

class PremiumController extends Controller
{
    public function beforeAction($action){
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionPremiumUnique(){
        $unique = Unique::save_unique($_POST['gorko_id'], $_POST['channel']);
        return $unique;
    }

    public function actionPremiumClick(){
        $click = Click::save_click($_POST['gorko_id'], $_POST['channel']);
        return $click;
    }

    public function actionPremiumCallback(){
        $callback = Callback::save_callback($_POST['gorko_id'], $_POST['channel'], $_POST['response']);
        return $callback;
    }
}