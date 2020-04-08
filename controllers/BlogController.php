<?php
namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use frontend\widgets\PaginationWidget;

class BlogController extends Controller
{

  public function actionIndex(){

    $pagination = PaginationWidget::widget([
			'total' => 5,
			'current' => 1,
		]);

    return $this->render('index.twig', array(
			'pagination' => $pagination
		));
  }

}