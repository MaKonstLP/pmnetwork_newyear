<?php

namespace app\modules\gorko_ny;


use Yii;
use common\models\Subdomen;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Filter;

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
        $fullDomain = $_SERVER['HTTP_HOST'];
        $domainParts = explode('.', $fullDomain);
        $numParts = count($domainParts);
        Yii::$app->params['domen'] = $domainParts[$numParts - 2] . '.' . $domainParts[$numParts - 1];

        $subdomen = explode('.', $_SERVER['HTTP_HOST'])[0];
        if ($subdomen != \Yii::$app->params['module_api_config']['korporativ']['url']) {
            Yii::$app->params['subdomen'] = $subdomen;

            $subdomen_model = Subdomen::find()
                ->where(['alias' => $subdomen])
                ->one();

            if (!$subdomen_model)
                throw new \yii\web\NotFoundHttpException();
        } else {
            Yii::$app->params['subdomen'] = '';

            $subdomen_model = Subdomen::find()
                ->where(['alias' => ''])
                ->one();
        }

        if ($subdomen_model) {
            $this->setAppParams($subdomen_model);
        }

        Yii::$app->params['uploadFolder'] = 'upload';

        // Yii::$app->params['cur_year'] =  (date('Y') + 1) .' ('. date('Y').')';
        Yii::$app->params['cur_year'] =  date('Y');

        $noindex_global = false;
        foreach ($_GET as $key => $value) {
            if ($key != 'page' && $key != 'q') {
                // $noindex_global = true;
            }
        }
        Yii::$app->params['noindex_global'] = $noindex_global;

        $subdomenId = Yii::$app->params['subdomen_baseid'];
        Yii::$app->params['filter_model'] = Filter::find()
            ->with(['items' => function ($query) use ($subdomenId) {
                $query->leftJoin(
                    'subdomen_filteritem',
                    "subdomen_filteritem.filter_items_id = filter_items.id AND subdomen_filteritem.subdomen_id = $subdomenId"
                )
                    ->where("subdomen_filteritem.is_valid=0 OR (subdomen_filteritem.is_valid=1 AND subdomen_filteritem.hits>2)")
                    ->select('*');
            }])
            ->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC])
            ->all();

        // Yii::$app->params['cookie-domen'] = !empty(Yii::$app->params['subdomen_alias']) ? Yii::$app->params['subdomen_alias'] . '.' . Yii::$app->params['domen'] : Yii::$app->params['domen'];

        //Yii::$app->setLayoutPath('@app/modules/svadbanaprirode/layouts');
        //Yii::$app->layout = 'svadbanaprirode';
        //$this->viewPath = '@app/modules/svadbanaprirode/views/';
        parent::init();
        //$this->viewPath = '@app/modules/svadbanaprirode/views/';


        // custom initialization code goes here
    }

    public function setAppParams($subdomen_model)
    {
        Yii::$app->params['subdomen_alias'] = $subdomen_model->alias;
        Yii::$app->params['subdomen_id'] = $subdomen_model->city_id;
        Yii::$app->params['subdomen_baseid'] = $subdomen_model->id;
        Yii::$app->params['subdomen_name'] = $subdomen_model->name;
        Yii::$app->params['subdomen_dec'] = $subdomen_model->name_dec;
        Yii::$app->params['subdomen_rod'] = $subdomen_model->name_rod;
        Yii::$app->params['subdomen_phone'] = $subdomen_model->phone;
        $subdomen_phone_pretty = null;

        if (preg_match('/^\+\d(\d{3})(\d{3})(\d{2})(\d{2})$/', $subdomen_model->phone,  $matches)) {
            $subdomen_phone_pretty = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
        }
        Yii::$app->params['subdomen_phone_pretty'] = $subdomen_phone_pretty;

        $elastic_model = new ElasticItems;
        $items = new ItemsFilterElastic([], 9999, 1, false, 'restaurants', $elastic_model);
        $minPrice = 999999;

        foreach ($items->items as $item) {
            if ($item->restaurant_price < $minPrice && $item->restaurant_price !== 250) { // второе условие - костыль под опечатку в инфе из горько
                $minPrice = $item->restaurant_price;
            }
        }
        Yii::$app->params['min_restaurant_price'] = $minPrice;

        Yii::$app->params['cookie-domen'] = !empty(Yii::$app->params['subdomen_alias']) ? Yii::$app->params['subdomen_alias'] . '.' . Yii::$app->params['domen'] : Yii::$app->params['domen'];
    }
}
