<?php

namespace app\modules\gorko_ny\controllers;

use common\models\blog\BlogPost;
use common\models\blog\BlogTag;
use common\models\Seo;
use common\models\Subdomen;
use frontend\modules\gorko_ny\components\Breadcrumbs;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\widgets\LinkPager;
use yii\web\Controller;
use Yii;
use app\modules\gorko_ny\Module;

class BlogController extends Controller
{
	public function actionIndex()
	{
		$this->view->params['menu'] = 'blog';
		if (Yii::$app->params['subdomen_alias'] != '') {
			// throw new \yii\web\NotFoundHttpException();

			return $this->redirect('https://' . Yii::$app->params['subdomen_alias'] . '.korporativ-ng.ru/', 301);
		}

		$this->getSubdomenCookie();

		$query = BlogPost::findWithMedia()->with('blogPostTags')->where(['published' => true])->orderBy(['published_at' => SORT_DESC,]);
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 100,
				'forcePageParam' => false,
				'totalCount' => $query->count()
			],
		]);

		$seo = (new Seo('blog', $dataProvider->getPagination()->page + 1))->seo;
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs('blog');
		$this->setSeo($seo);

		$topPosts = (clone $query)->where(['featured' => true])->limit(5)->all();

		$listConfig = [
			'dataProvider' => $dataProvider,
			'itemView' => '_list-item.twig',
			'layout' => "{items}\n<div class='pagination_wrapper items_pagination' data-pagination-wrapper>{pager}</div>",
			'pager' => [
				'class' => LinkPager::class,
				'disableCurrentPageButton' => true,
				'nextPageLabel' => 'Следующая →',
				'prevPageLabel' => '← Предыдущая',
				'maxButtonCount' => 4,
				'activePageCssClass' => '_active',
				'pageCssClass' => 'items_pagination_item',
			],

		];

		return $this->render('index.twig', compact('listConfig', 'topPosts', 'seo'));
	}

	public function actionPost($alias)
	{
		$this->view->params['menu'] = 'blog';
		if (Yii::$app->params['subdomen_alias'] != '') {
			// throw new \yii\web\NotFoundHttpException();

			return $this->redirect('https://' . Yii::$app->params['subdomen_alias'] . '.korporativ-ng.ru/', 301);
		}

		$subdomen_model = $this->getSubdomenCookie();

		$post = BlogPost::findWithMedia()->with('blogPostTags')->where(['published' => true, 'alias' => $alias])->one();
		if (empty($post)) {
			throw new \yii\web\NotFoundHttpException();
		}
		$seo = ArrayHelper::toArray($post->seoObject);
		$seo['breadcrumbs'] = Breadcrumbs::get_breadcrumbs('post', ['link' => $alias, 'name' => $post->name]);

		$this->setSeo($seo);

		return $this->render('post.twig', [
			'post' => $post,
			'seo' => $seo,
			'subdomen' => $subdomen_model,
			'cookie_subdomain' => Yii::$app->params['cookie-domen'],
		]);
	}

	public function actionPreview($id)
	{
		if (Yii::$app->params['subdomen_alias'] != '') {
			throw new \yii\web\NotFoundHttpException();
		}
		// $post = BlogPost::findWithMedia()->with('blogPostTags')->where(['published' => true, 'id' => $id])->one();
		$post = BlogPost::findWithMedia()->with('blogPostTags')->where(['id' => $id])->one();
		if (empty($post)) {
			throw new NotFoundHttpException();
		}
		$seo = ArrayHelper::toArray($post->seoObject);
		$this->setSeo($seo);
		$this->view->params['robots'] = true;
		return $this->render('post.twig', compact('post'));
	}

	private function setSeo($seo)
	{
		$this->view->title = $seo['title'];
		$this->view->params['desc'] = $seo['description'];
		$this->view->params['kw'] = $seo['keywords'];
		$canonical = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
		$this->view->params['canonical'] = $canonical;
	}

	private function getSubdomenCookie()
	{
		//получаем значение id поддомена из куки переменной
		$request = Yii::$app->request;
		$subdomen_id = 4400;
		if ($request->cookies->getValue('subdomen_id')) {
			$subdomen_id = $request->cookies->getValue('subdomen_id');
		}
		$subdomen_model = Subdomen::find()->where(['city_id' => $subdomen_id])->one();

		if ($subdomen_model) {
			$module = Module::getInstance();
			$module->setAppParams($subdomen_model);
		}

		return $subdomen_model;
	}
}
