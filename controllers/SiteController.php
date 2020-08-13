<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use common\widgets\FilterWidget;
use common\models\Pages;
use common\models\Filter;
use common\models\Slices;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;

class SiteController extends Controller
{
    //public function getViewPath()
    //{
    //    return Yii::getAlias('@app/modules/svadbanaprirode/views/site');
    //}

    public function actionIndex()
    {
        $filter_model = Filter::find()->with('items')->all();
        $slices_model = Slices::find()->all();

        $seo = Pages::find()->where(['name' => 'index'])->one();
        $this->setSeo($seo);

        $filter = FilterWidget::widget([
            'filter_active' => [],
            'filter_model' => $filter_model
        ]);

        $elastic_model = new ElasticItems;
        $items = new ItemsFilterElastic([], 10, 1, false, 'restaurants', $elastic_model);
        $mainWidget = $this->renderPartial('//components/generic/profitable_offer.twig', [
            'items' => $items->items
        ]);

        return $this->render('index.twig', [
            'filter' => $filter,
            'count' => $items->total,
            'mainWidget' => $mainWidget,
            'seo' => $seo,
            'subid' => isset(Yii::$app->params['subdomen_id']) ? Yii::$app->params['subdomen_id'] : false
        ]);
    }

    private function setSeo($seo){
        $this->view->title = $seo['title'];
        $this->view->params['desc'] = $seo['description'];
        $this->view->params['kw'] = $seo['keywords'];
    }
}
