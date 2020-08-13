<?php

namespace app\modules\gorko_ny;


use Yii;
use common\models\Subdomen;
/**
 * svadbanaprirode module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\gorko_ny\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $subdomen = explode('.', $_SERVER['HTTP_HOST'])[0];
        if($subdomen != 'newyearpmn'){
            Yii::$app->params['subdomen'] = $subdomen;

            $subdomen_model = Subdomen::find()
                ->where(['alias' => $subdomen])
                ->one();

            if($subdomen_model){
                Yii::$app->params['subdomen_id'] = $subdomen_model->city_id;
            }
        }
            
        //Yii::$app->setLayoutPath('@app/modules/svadbanaprirode/layouts');
        //Yii::$app->layout = 'svadbanaprirode';
        //$this->viewPath = '@app/modules/svadbanaprirode/views/';
        parent::init();
        //$this->viewPath = '@app/modules/svadbanaprirode/views/';


        // custom initialization code goes here
    }
}
