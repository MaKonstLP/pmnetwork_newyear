<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\modules\gorko_ny\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <title><?php echo $this->title ?></title>
    <?php $this->head() ?>
    <?php if (!empty($this->params['desc'])) echo "<meta name='description' content='".$this->params['desc']."'>";?>
    <?php if (!empty($this->params['kw'])) echo "<meta name='keywords' content='".$this->params['kw']."'>";?>
    <?= Html::csrfMetaTags() ?>

</head>
<body>
<?php $this->beginBody() ?>

    <div class="main_wrap">
        
        <header>
            <div class="header_wrap">
                <a href="/" class="header_logo">
                    <div class="header_logo_img"></div>
                </a>

                <div class="header_menu">

                    <div class="header_city_select _grey_link">

                        <span>Москва</span>

                    </div>

                    <div class="city_select_search_wrapper _hide">

                        <p class="back_to_header_menu">Назад в меню</p>

                        <h4>Выберите город</h4>

                        <div class="input_search_wrapper">

                            <input type="search" placeholder="Название города">

                        </div>

                        <div class="city_select_list">

                            <?php
                                $citiesList = explode(",", 
                                "Алушта,Артем,Архангельск,Астрахань,Барнаул,Белгород,Бийск,Брянск,Великий Новгород,Владивосток,Владимир,Волгоград,Волжский,Воронеж,Гатчина,Дзержинск,Екатеринбург,Иваново,Ижевск,Иркутск,Казань,Калининград,Калуга,Кемерово,Киров,Кострома,Краснодар,Красноярск,Курск,Липецк,Магнитогорск,Москва,Мурманск,Мытищи,Набережные Челны,Нижний Новгород,Нижний Тагил,Новокузнецк,Новороссийск,Новосибирск,Омск,Орел,Оренбург,Пенза,Пермь,Ростов-на-Дону,Рязань,Самара,Санкт-Петербург,Саранск,Саратов,Севастополь (Крым),Симферополь,Смоленск,Сочи,Ставрополь,Стерлитамак,Сургут,Таганрог,Тамбов,Тверь,Тольятти,Томск,Тула,Тюмень,Улан-Удэ,Ульяновск,Уфа,Хабаровск,Чебоксары,Челябинск,Череповец,Чита,Энгельс,Ярославль");

                                function createCityNameLine($city){
                                    $newLine = "<p>$city</p>";
                                    return $newLine;
                                }

                                function createLetterBlock($letter){
                                    $newBlock = "<div class='city_select_letter_block' data-first-letter=$letter>";
                                    return $newBlock;
                                }

                                function createCityList($citiesList){
                                    $citiesListResult = "";
                                    $currentLetterBlock = "";

                                    for ($i = 0; $i < count($citiesList); $i++) {
                                        $currentFirstLetter = substr($citiesList[$i], 0, 2);
                                        if ($currentFirstLetter !== $currentLetterBlock){
                                            $currentLetterBlock = $currentFirstLetter;
                                            $citiesListResult .= "</div>";
                                            $citiesListResult .= createLetterBlock($currentLetterBlock);
                                            $citiesListResult .= createCityNameLine($citiesList[$i]);
                                        } else {
                                            $citiesListResult .= createCityNameLine($citiesList[$i]);
                                        }
                                    }
                                        
                                    $citiesListResult .= "</div>";
                                    echo substr($citiesListResult, 6);

                                }

                                createCityList($citiesList);
                            ?>

                        </div>

                    </div>

                    

                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'v-podmoskovie')echo '_active';?>" href="/catalog/v-podmoskovie/">За городом</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'v-sharte')echo '_active';?>" href="/catalog/v-sharte/">В шатре</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'y-vody')echo '_active';?>" href="/catalog/y-vody/">У воды</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'na-verande')echo '_active';?>" href="/catalog/na-verande/">На веранде</a>
                </div>

                <div class="header_phone">
                    <p>(846) 205-78-45</p>
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

                <div class="header_form_popup _hide">
                    <div class="header_form_popup_content">
                    
                        <?= $this->render('../components/generic/form_callback.twig') ?>
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
                            </a>
                            <div class="footer_info">
                                <p class="footer_copy">© <?php echo date("Y");?> Новогодний корпоратив</p>
                                <a href="#" class="footer_pc _link">Политика конфиденциальности</a>
                            </div>                        
                        </div>
                        <div class="footer_block _right">
                            <div class="footer_phone">
                                <p>Тел.: (846) 205-78-45</p>
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
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600&display=swap&subset=cyrillic" rel="stylesheet">
</body>
</html>
<?php $this->endPage() ?>
