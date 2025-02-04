<?php

namespace app\modules\gorko_ny\controllers;

use Yii;
use yii\web\Controller;
use common\models\Seo;

class ContactsController extends Controller
{

	public function actionIndex()
	{

		$this->view->params['menu'] = 'contacts';
		$seo = $this->getSeo('contacts');
		$this->setSeo($seo);

		return $this->render('index.twig', array(
			'seo' => $seo,
			'year' => date('Y') + 1,
			'current_year' => date('Y'),
			'city_rod' => Yii::$app->params['subdomen_rod']
		));
	}

	private function getSeo($type, $page = 1, $count = 0)
	{
		$seo = new Seo($type, $page, $count);

		return $seo->seo;
	}

	private function setSeo($seo)
	{
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		$this->view->params['kw'] = $seo['keywords'];
	}
}
