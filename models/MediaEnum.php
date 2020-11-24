<?php
namespace frontend\modules\gorko_ny\models;

use common\models\Pages;
use common\models\siteobject\BaseMediaEnum;
use common\models\SubdomenPages;

class MediaEnum extends BaseMediaEnum
{
    const HEADER_IMAGE = 'header-image'; //alias по которому будут доступны картинки
    const ADVANTAGES = 'advantages'; //alias по которому будут доступны картинки

    const LABEL_MAP = [
        self::HEADER_IMAGE => 'Изображения шапки', //подпись инпута в админке
        self::ADVANTAGES => 'Изображения преимуществ', //подпись инпута в админке
    ];

    public static function getMediaTypes() //обязательный метод в котором для названия класса накидываем какие нужны типы картинок
    {
        return [
            SubdomenPages::class => [self::HEADER_IMAGE, self::ADVANTAGES],
            Pages::class => [self::HEADER_IMAGE, self::ADVANTAGES],
        ];
    }
}