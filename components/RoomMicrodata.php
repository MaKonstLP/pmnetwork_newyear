<?php

namespace frontend\modules\gorko_ny\components;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;

class RoomMicrodata
{
  public static function getRoomMicrodata($restaurant)
  {
    $address = [];
    $addressPartsList = explode(', ', $restaurant->restaurant_address);
    $city = $addressPartsList[0];
    unset($addressPartsList[0]);
    $street = implode(', ', $addressPartsList);

    if (Yii::$app->params['subdomen_name'] !== 'Москва' && Yii::$app->params['subdomen_name'] !== 'Санкт-Петербург'){
      $address = [
        "@type" => "PostalAddress",
        "addressLocality" => $city,
        "addressRegion" => $city,
        // "postalCode" => "80209",
        "streetAddress" => $street
      ];
    } else {
      $address = [
        "@type" => "PostalAddress",
        "addressLocality" => $city,
        // "postalCode" => "80209",
        "streetAddress" => $street
      ];
    }

    $offers = [
      "@type" => "Offer",
      "price" => array_key_exists(0, $restaurant->rooms) ? $restaurant->rooms[0]['price'] : '',
      "priceCurrency" => "RUB",
      "url" => "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ];

    $capacityString = '';

    foreach ($restaurant->rooms as $room){
      $capacityString .= $room['capacity'] . ', ';
    }

    $description = 'Зажигательный новогодний корпоратив в ' . Yii::$app->params['subdomen_dec'] . ' в заведении ' . $restaurant->restaurant_name . ' по адресу ' . $restaurant->restaurant_address . '. Залы на ' . substr($capacityString, 0, -2) . ' человек.';

    $finalJSON = Html::script(
      Json::encode([
        "@context" => "https://schema.org/",
        "@type" => "Event",
        "location" => [
          "@type" => "Place",
          "address" => $address,
          "name" => $restaurant->restaurant_name
        ],
        "name" => "Новогодний корпоратив",
        "offers" => $offers,
        "description" => $description,
        "startDate" => date('Y') . '-11-20',
        "image" => $restaurant->restaurant_cover_url

        ]), [
        'type' => 'application/ld+json',
      ]);

    return $finalJSON;
  }
}