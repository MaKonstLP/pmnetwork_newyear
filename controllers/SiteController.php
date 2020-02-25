<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use common\widgets\FilterWidget;
use common\models\ItemsWidget;
use common\models\Pages;
use common\models\Filter;
use common\models\Slices;

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

        $itemsWidget = new ItemsWidget;
        $apiMain = $itemsWidget->getMain($filter_model, $slices_model, 'restaurants');

        $seo = Pages::find()->where(['name' => 'index'])->one();
        $this->setSeo($seo);

        $filter = FilterWidget::widget([
            'filter_active' => [],
            'filter_model' => $filter_model
        ]);

        return $this->render('index.twig', [
            'filter' => $filter,
            'widgets' => $apiMain['widgets'],
            'count' => $apiMain['total'],
            'seo' => $seo,
        ]);
    }

    private function setSeo($seo){
        $this->view->title = $seo['title'];
        $this->view->params['desc'] = $seo['description'];
        $this->view->params['kw'] = $seo['keywords'];
    }
}
