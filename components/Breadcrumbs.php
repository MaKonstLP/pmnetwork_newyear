<?php

namespace frontend\modules\gorko_ny\components;

use Yii;

class Breadcrumbs {
	public static function get_breadcrumbs($level, $slice_alias = false, $rest = false) {
		switch ($level) {
			case 1:
				return 	['crumbs' =>
							[[
		                        'type' => 'raw',
		                        'link' => '/',
		                        'name' => 'Новый год '.(date("Y")+1)
			                ]]
			            ];
				break;
			case 2:
				return 	['crumbs' =>
							array_merge(
								self::get_breadcrumbs(1)['crumbs'],
								[[
			                        'type' => 'raw',
			                        'link' => '/ploshhadki/',
			                        'name' => 'Каталог'
			                    ]]
							)
						];
				break;
			case 3:
				$slice = self::get_slice_crumb($slice_alias);
				return 	['crumbs' =>
							array_merge(
								self::get_breadcrumbs(2)['crumbs'],
								$slice['crumbs']
							),
						 'crumbs_list' => $slice['crumbs_list']

						];				
				break;
			case 4:
				return 	['crumbs' =>
							array_merge(
								self::get_breadcrumbs(3, $slice_alias)['crumbs'],
								[[
			                        'type' => 'raw',
			                        'link' => '/ploshhadki/'.$rest['id'],
			                        'name' => $rest['name']
				                ]]
							),
						 'crumbs_list' => self::get_slice_list()

						];				
				break;
			case 'blog':
				return 	['crumbs' =>
							array_merge(
								self::get_breadcrumbs(1)['crumbs'],
								[[
			                        'type' => 'raw',
			                        'link' => '/blog/',
			                        'name' => 'Статьи блога'
			                    ]]
							)
			            ];
				break;
			case 'post':
				return 	['crumbs' =>
							array_merge(
								self::get_breadcrumbs('blog')['crumbs'],
								[[
			                        'type' => 'raw',
			                        'link' => '/blog/'.$slice_alias['link'].'/',
			                        'name' => $slice_alias['name']
			                    ]]
							)
			            ];
				break;
		}
	}

	private static function get_slice_list(){
		return [
			'banketnye-zaly' => 'Банкетные залы',
			'restorany' => 'Рестораны',
			'kafe' => 'Кафе',
			'kluby' => 'Клубы',
			'bary' => 'Бары',
			'v-gorode' => 'В городе',
			'na-prirode' => 'На природе'
		];
	}

	private static function get_slice_crumb($slice_alias) {
		$breadcrumbs_slices = self::get_slice_list();
		if (isset($breadcrumbs_slices[$slice_alias])){
			return 	['crumbs' =>
						[[
		                    'type' => 'slices',
		                    'link' => '/ploshhadki/'.$slice_alias.'/',
		                    'name' => $breadcrumbs_slices[$slice_alias]
		                ]],
		             'crumbs_list' => $breadcrumbs_slices
		            ];
		}	else {
			return 	['crumbs' =>
						[[
		                    'type' => 'slices',
		                    'link' => '/ploshhadki/banketnye-zaly/',
		                    'name' => 'Банкетные залы'
		                ]],
		             'crumbs_list' => $breadcrumbs_slices
		            ];
		}
	}
}