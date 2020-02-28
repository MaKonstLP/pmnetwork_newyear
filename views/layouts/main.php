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

                <div class="header_city_select">
                    <span>Москва</span>
                </div>

                <div class="header_menu">
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'v-podmoskovie')echo '_active';?>" href="/catalog/v-podmoskovie/">За городом</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'v-sharte')echo '_active';?>" href="/catalog/v-sharte/">В шатре</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'y-vody')echo '_active';?>" href="/catalog/y-vody/">У воды</a>
                    <a class="header_menu_item <?if(!empty($this->params['menu']) and $this->params['menu'] == 'na-verande')echo '_active';?>" href="/catalog/na-verande/">На веранде</a>
                </div>
                <div class="header_phone">
                    <p>(846) 205-78-45</p>
                    <div class="header_phone_button">
                        <div class="header_phone_button_img"></div>
                        <p class="_link">Подберите мне зал</p>
                    </div>
                </div>
                <div class="header_burger">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </header>

        <div class="content_wrap">
            <?= $content ?>
        </div>

        <footer>
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
        </footer>

    </div> 

<?php $this->endBody() ?>
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600&display=swap&subset=cyrillic" rel="stylesheet">
</body>
</html>
<?php $this->endPage() ?>
