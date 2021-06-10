<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\modules\gorko_ny\assets\AppAsset;
use frontend\modules\gorko_ny\models\ElasticItems;
use common\models\Subdomen;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" type="image/png" href="/img/ny_ball.png">
    <title><?php echo $this->title ?></title>
    <?php $this->head() ?>
    <?php if (!empty($this->params['desc'])) echo "<meta name='description' content='".$this->params['desc']."'>";?>
    <?php if (!empty($this->params['kw'])) echo "<meta name='keywords' content='".$this->params['kw']."'>";?>
    <?= Html::csrfMetaTags() ?>

</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PTTPDSK"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php $this->beginBody() ?>

    <div class="main_wrap">
        
        <header>
            <div class="header_wrap">

                <div class="header_menu">

                    <a href="/" class="header_logo">

                        <div class="header_logo_img"></div>
                        <div class="header_logo_text">
                            <p>
                                <span>Новогодний корпоратив</span><br>
                                <span>корпоративы твоего города <?php echo date('Y') + 1 ?></span>
                            </p>
                        </div>
                        
                    </a>

                    <div class="header_city_select _grey_link">

                        <span><?=Yii::$app->params['subdomen_name']?></span>

                    </div>
                    
                    <div class="city_select_search_wrapper _hide">
                        
                        <p class="back_to_header_menu">Назад в меню</p>

                        <h4>Выберите город</h4>

                        <?php /*<div class="input_search_wrapper">

                            <input type="search" placeholder="Название города">

                        </div> */?>

                        <div class="city_select_list">

                            <?php
                                $subdomen_list = Subdomen::find()
                                    ->where(['active' => 1])
                                    ->orderBy(['name' => SORT_ASC])
                                    ->all();

                                function createCityNameLine($city){
                                    if($city->alias){
                                        $newLine = "<p><a href='https://$city->alias.korporativ-ng.ru'>$city->name</a></p>";
                                    }
                                    else{
                                        $newLine = "<p><a href='https://korporativ-ng.ru'>$city->name</a></p>";
                                    }
                                    return $newLine;
                                }

                                function createLetterBlock($letter){
                                    $newBlock = "<div class='city_select_letter_block' data-first-letter=$letter>";
                                    return $newBlock;
                                }

                                function createCityList($subdomen_list){
                                    $citiesListResult = "";
                                    $currentLetterBlock = "";

                                    foreach ($subdomen_list as $key => $subdomen){
                                        $currentFirstLetter = substr($subdomen->name, 0, 1);
                                        if ($currentFirstLetter !== $currentLetterBlock){
                                            $currentLetterBlock = $currentFirstLetter;
                                            $citiesListResult .= "</div>";
                                            $citiesListResult .= createLetterBlock($currentLetterBlock);
                                            $citiesListResult .= createCityNameLine($subdomen);
                                        } else {
                                            $citiesListResult .= createCityNameLine($subdomen);
                                        }
                                    }
                                        
                                    $citiesListResult .= "</div>";
                                    echo substr($citiesListResult, 6);
                                }

                                createCityList($subdomen_list);
                            ?>

                        </div>

                    </div>

                    <div class="header_menu_wrapper">

                        <div class="header_city_select _grey_link">

                            <span><?=Yii::$app->params['subdomen_name']?></span>

                        </div>

                        <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'banketnye-zaly')echo '_active';?>" href="/ploshhadki/banketnye-zaly/">Банкетные залы</a>
                        <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'restorany')echo '_active';?>" href="/ploshhadki/restorany/">Рестораны</a>
                        <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'kafe')echo '_active';?>" href="/ploshhadki/kafe/">Кафе</a>
                        <a class="header_menu_item _no_wide_screen <?if(!empty($this->params['menu']) and $this->params['menu'] == 'kluby')echo '_active';?>" href="/ploshhadki/kluby/">Клубы</a>
                        <a class="header_menu_item _no_wide_screen <?if(!empty($this->params['menu']) and $this->params['menu'] == 'bary')echo '_active';?>" href="/ploshhadki/bary/">Бары</a>
                        <a class="header_menu_item _no_wide_screen <?if(!empty($this->params['menu']) and $this->params['menu'] == 'v-gorode')echo '_active';?>" href="/ploshhadki/v-gorode/">В городе</a>
                        <a class="header_menu_item _no_wide_screen <?if(!empty($this->params['menu']) and $this->params['menu'] == 'na-prirode')echo '_active';?>" href="/ploshhadki/na-prirode/">На природе</a>
                        <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'contacts')echo '_active';?>" href="/contacts/">Контакты</a>
                        <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'blog')echo '_active';?>" href="https://korporativ-ng.ru/blog/" target="blank">Блог</a>
                    </div>

                    <div class="header_phone">
                        <a href="tel:<?= Yii::$app->params['subdomen_phone'] ?>"><?= Yii::$app->params['subdomen_phone_pretty'] ?></a>
                        <div class="header_phone_button">
                            <div class="header_phone_button_img"></div>
                            <p class="_grey_link">Подберите мне зал</p>
                        </div>
                    </div>

                    <div class="header_burger">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>

                </div>

                <?php
                    $minPrice = ElasticItems::find()->limit(0)->query(
                        ['bool' => ['must' => ['match' => ['restaurant_city_id' => Yii::$app->params['subdomen_id']]]]]
                    )
                        ->addAggregate('min_price', [
                            'min' => [
                                'field' => 'restaurant_price',
                            ]
                        ])->search()['aggregations']['min_price']['value'];
                    
                ?>

                <div class="header_form_popup _hide">
                    <div class="header_form_popup_content">
                    
                        <?= $this->render('../components/generic/form_callback.twig', ['type' => 'header', 'minPrice' => $minPrice]) ?>
                        <div class="close_button"></div>

                        <div class="header_form_popup_message_sent _hide">

                            <h2>Заявка отправлена</h2>
                            <p class="header_form_popup_message">Константин, спасибо за проявленный интерес. Наши менеджеры свяжутся с вами<br>в течение дня и помогут подобрать зал для корпоратива.</p>
                            <p class="header_form_popup_message_close _link">Понятно, закрыть</p>
                            <div class="close_button"></div>

                        </div>

                    </div>
                </div>

            </div>
        </header>

        <div class="content_wrap">
            <?= $content ?>
        </div>

        <footer>
            <div class="footer_container">
                <div class="footer_wrap">
                    <div class="footer_row">
                        <div class="footer_block _left">
                            <a href="/" class="footer_logo">
                                <div class="footer_logo_img"></div>
                                <div class="footer_logo_text">
                                    <p>
                                        <span>Новогодний корпоратив</span><br>
                                        <span>корпоративы твоего города <?php echo date('Y') + 1; ?></span>
                                    </p>
                                </div>
                            </a>
                            <div class="footer_info">
                                <p class="footer_copy">© <?php echo date("Y");?> Новогодний корпоратив</p>
                                <a href="/privacy/" class="footer_pc _link">Политика конфиденциальности</a>
                            </div>                        
                        </div>
                        <div class="footer_block _right">
                            <div class="footer_phone">
                                <a href="tel:<?= Yii::$app->params['subdomen_phone'] ?>"><?= Yii::$app->params['subdomen_phone_pretty'] ?></a>
                            </div>
                            <div class="footer_phone_button">
                            <div class="footer_phone_button_img"></div>
                                <p class="_link">Подберите мне зал</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div> 

<?php $this->endBody() ?>
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,600&amp;display=swap&amp;subset=cyrillic" rel="stylesheet">
</body>
</html>
<?php $this->endPage() ?>
