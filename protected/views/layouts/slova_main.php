<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if lt IE 7]>      <html class="ie6"> <![endif]-->
<!--[if IE 7]>         <html class="ie7"> <![endif]-->
<!--[if IE 8]>         <html class="ie8"> <![endif]-->
<!--[if gt IE 8]><!--> <html xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->    
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="sr" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie6.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/slova/main.css" />



<link href="/meni/css/dropdown/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
<link href="/meni/css/dropdown/themes/flickr.com/default.css" media="screen" rel="stylesheet" type="text/css" />
<!--[if lt IE 7]>
<script type="text/javascript" src="meni/js/jquery/jquery.js"></script>
<script type="text/javascript" src="meni/js/jquery/jquery.dropdown.js"></script>
<![endif]-->

<!-- / END -->


<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-27844300-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<?php
//ako se korisnik uloguje i onda bude neaktivan odredjeno vreme, sesija na serveru biva unistena.
//Da se to ne bi desilo, periodicno se salje ajax zahtev serveru.
    if( ! Yii::app()->user->isGuest)
    {
        $osveziNa = 1200000; //20 minuta
        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile(Helper::baseUrl('js/keep-alive.js'));
        $cs->registerScript('keep_alive_js', "$(document).ready( function(){setInterval(keep_alive, $osveziNa);});");
    }
?>

</head>
<body>
<div id="omotac">
<div  id="page">    
    	<div id="header">
            <?php $arRedakcija = array('visible'=>false);?>
            <?php if(Yii::app()->controller->getAction()->getId() == 'broj' && isset($_GET['br'])):?>
                <div id="broj"><?php echo intval($_GET['br']);?></div>
            <?php endif;?>                               
	</div><!-- header -->      
            <div id="mainmenu1">
                <?php   $this->widget('zii.widgets.CMenu',array(
                        'id' => 'nav',
                        'htmlOptions' => array('class'=>'dropdown dropdown-horizontal'),
                        'items'=>array(
                                array('label'=>Yii::t('biblioteka', 'Библиотека'), 'url'=>Helper::createI18nUrl('site/index')),
                                array('label'=>Yii::t('biblioteka', 'Редакција'), 'url'=>Helper::createI18nUrl('slovanastruju/redakcija')),//$arRedakcija,
                                array('label'=>Yii::t('biblioteka', 'Контакт'), 'url'=>Helper::createI18nUrl('slovanastruju/kontakt')),
                                array('label'=>Yii::t('biblioteka', 'Архива'), 'url'=>Helper::createI18nUrl('slovanastruju/arhiva')),
                ))); ?>
            </div><!-- mainmenu -->    
	<?php 
            echo $content;
        
        ?>
        <div class="clear"></div>
	<div id="futer">
		&copy; <?php echo date('Y'); ?> Народна библиотека "Радислав Никчевић" у Јагодини<br/>
                Дизајн: Пеђа Трајковић<br/>
                Програмирање: Лазаревић Ивица
                <br/>
	</div><!-- footer -->       
<!-- page -->
</div>
</div> <!-- omotac -->
</body>
</html>