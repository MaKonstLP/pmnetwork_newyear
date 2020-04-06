<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use frontend\widgets\PaginationWidget;

class BlogController extends Controller
{

  public function actionIndex(){
    return $this->render('index.twig');
  }

}