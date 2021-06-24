<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use frontend\widgets\FilterWidget;
use common\models\Pages;
use common\models\Filter;
use common\models\Slices;
use common\models\elastic\ItemsFilterElastic;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Seo;
use common\models\RestaurantsModule;
use common\models\siteobject\SiteObjectSeo;
//use common\models\elastic\LeadLogElastic;

class SiteController extends Controller
{
    //public function getViewPath()
    //{
    //    return Yii::getAlias('@app/modules/svadbanaprirode/views/site');
    //}

    public function actionIndex()
    {
        //$leadLogElastic = LeadLogElastic::updateMapping();
        //$siteobject_seo = SiteObjectSeo::find()->joinwith('siteObject')->where(['site_object.table_name' => 'pages'])->all();
//
        //foreach ($siteobject_seo as $key => $seo) {
        //  $page = Pages::find()->where(['id' => $seo->siteObject->row_id])->one();
        //  $seo->title = $page->title;
        //  $seo->description = $page->description;
        //  $seo->keywords = $page->keywords;
        //  $seo->img_alt = $page->img_alt;
        //  $seo->heading = $page->h1;
        //  $seo->text1 = $page->text_top;
        //  $seo->text2 = $page->text_bottom;
        //  $seo->pagination_heading = $page->h1_pag;
        //  $seo->pagination_title = $page->title_pag;
        //  $seo->pagination_description = $page->description_pag;
        //  $seo->pagination_keywords = $page->keywords_pag;
//
        //  $seo->save();
        //}
        //Pages::createSiteObjects();


        $filter_model = Filter::find()->with('items')->where(['active' => 1])->orderBy(['sort' => SORT_ASC])->all();
        $slices_model = Slices::find()->all();
        $seo = $this->getSeo('index');
        $this->setSeo($seo);


        $elastic_model = new ElasticItems;
        $items = new ItemsFilterElastic([], 30, 1, false, 'restaurants', $elastic_model);

        $mainWidget = $this->renderPartial('//components/generic/profitable_offer.twig', [
            'items' => $items->items,
            'city_rod' => Yii::$app->params['subdomen_rod'],
        ]);

        // echo '<pre>';
        // print_r(count($items->items));
        // exit;

        $filter = FilterWidget::widget([
            'filter_active' => [],
            'filter_model' => $filter_model,
			'minPrice' => Yii::$app->params['min_restaurant_price']
        ]);

        return $this->render('index.twig', [
            'filter' => $filter,
            'count' => $items->total,
            'mainWidget' => $mainWidget,
            'seo' => $seo,
            'subid' => isset(Yii::$app->params['subdomen_id']) ? Yii::$app->params['subdomen_id'] : false
        ]);
    }

    public function actionError()
    {
        return $this->render('error.twig');
    }

    public function actionRobots()
    {
        header('Content-type: text/plain');
        if(Yii::$app->params['subdomen_alias']){
            $subdomen_alias = Yii::$app->params['subdomen_alias'].'.';
        }
        else{
            $subdomen_alias = '';
        }
        echo 'User-agent: *
Disallow: /*rest_type=
Disallow: /*chelovek=
Disallow: /*price=
Disallow: /*firework=
Disallow: /*svoy-alko=
Disallow: /blog/preview-post/
Sitemap: https://'.$subdomen_alias.'korporativ-ng.ru/sitemap/';
        exit;
    }

    private function getSeo($type, $page=1, $count = 0){
        $seo = new Seo($type, $page, $count);

        return $seo->seo;
    }

    private function setSeo($seo){
        $this->view->title = $seo['title'];
        $this->view->params['desc'] = $seo['description'];
        $this->view->params['kw'] = $seo['keywords'];
    }

    public function actionApitest()
    {
        $api_url = 'https://v.wedding.net/api/banket_wedding/inquiry/put';
        //$payload = [
        //    'city_id' => 15789641,
        //    'name' => 'G',
        //    'phone' => '+91 8563587525',
        //    'event_type' => 'Wedding',
        //    'guests' => 150,
        //    'price' => '1000 - 1399 ₹',
        //    'is_phone_preferred' => 1
        //];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);
        curl_close($curl);

        echo '<pre>';
        print_r($api_url);
        //print_r($payload);
        print_r($response);
        return 1;
    }
}
