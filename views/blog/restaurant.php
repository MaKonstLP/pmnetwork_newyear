<?php
use frontend\modules\gorko_ny\models\ElasticItems;

$elastic_model = new ElasticItems;
$item = $elastic_model::get($text_id);

echo $this->render('//components/generic/restaurant_adv_test.twig', ['item' => $item]);
?>