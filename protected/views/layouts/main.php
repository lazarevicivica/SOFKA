<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if lt IE 7]>      <html class="ie6"> <![endif]-->
<!--[if IE 7]>         <html class="ie7"> <![endif]-->
<!--[if IE 8]>         <html class="ie8"> <![endif]-->
<!--[if IE 9]>         <html class="ie9"> <![endif]-->
<!--[if gt IE 9]><!--> <html xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->    
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



<link href="/meni/css/dropdown/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
<link href="/meni/css/dropdown/themes/flickr.com/default.css" media="screen" rel="stylesheet" type="text/css" />
<!--[if lt IE 7]>
<script type="text/javascript" src="meni/js/jquery/jquery.js"></script>
<script type="text/javascript" src="meni/js/jquery/jquery.dropdown.js"></script>
<![endif]-->

<!-- / END -->


<title><?php echo CHtml::encode($this->pageTitle); ?></title>

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
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/sr_RS/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div id="omotac">
<div  id="page">    
    	<div id="header">
        <div>
            <div class="login-traka">
                    <label for="login-email" class="login-label"><?php echo Yii::t('biblioteka','И-мејл:');?></label> <input id="login-email" class="login-polje" type="text"/>
                    <label for="login-lozinka" class="login-label"><?php echo Yii::t('biblioteka','Лозинка:');?></label> <input id="login-lozinka" class="login-polje" type="text"/>
            </div>
            <div id="jezici">
                    <?php $this->widget('application.components.LangBox'); ?>
            </div>                       
        </div>
	</div><!-- header -->      
            <div id="mainmenu1">
                <div style="<?php echo $this->adminMeni?'width:960px;':'width:730px;';?>float:left;">
                <?php $this->widget('zii.widgets.CMenu',array(
                        'id' => 'nav',
                        'htmlOptions' => array('class'=>'dropdown dropdown-horizontal'),
                        'items'=>array(
                                array('label'=>Yii::t('biblioteka', 'Почетак'), 'url'=>Helper::createI18nUrl('site/index')),
                                array('label'=>Yii::t('biblioteka', 'Библиотека'), 'itemOptions'=>array('class'=>'dir'),
                                            'items'=>array(
                                                    array('label'=>Yii::t('biblioteka', 'Историја'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'istorija-biblioteke'))),
                                                    array('label'=>Yii::t('biblioteka', 'Библиотека данас'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-danas'))),
                                                    array('label'=>Yii::t('biblioteka', 'Одељења'), 'itemOptions'=>array('class'=>'dir'),
                                                            'items' =>
                                                        array(
                                                            array('label'=>Yii::t('biblioteka', 'Управа'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>8, 'naziv'=>'uprava'))),
                                                            array('label'=>Yii::t('biblioteka', 'Општи и помоћни послови'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>10, 'naziv'=>'odeljenje-opstih-i-pomocnih-poslova'))),
                                                            array('label'=>Yii::t('biblioteka', 'Развој и унапређење'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>7, 'naziv'=>'odeljenje-za-razvoj-i-unapredjenje-bibliotecko-informacione-delatonosti'))),
                                                            array('label'=>Yii::t('biblioteka', 'Набавка и обрада'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>3, 'naziv'=>'odeljenje-za-nabavku-i-obradu-biblioteckog-materijala'))),
                                                            array('label'=>Yii::t('biblioteka', 'Завичајно одељење'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>4, 'naziv'=>'zavicajno-odeljenje-sa-fondom-stare-i-retke-knjige'))),
                                                            array('label'=>Yii::t('biblioteka', 'Популаризација књиге'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>9, 'naziv'=>'odeljenje-za-popularizaciju-knjige-i-citanja'))),                                                            
                                                            array('label'=>Yii::t('biblioteka', 'Дечје одељење'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>1, 'naziv'=>'odeljenje-za-rad-sa-decom'))),
                                                            array('label'=>Yii::t('biblioteka', 'Одељење одраслих'), 'itemOptions'=>array('class'=>'dir'),
                                                               'items' => 
                                                                array(
                                                                    array('label'=>Yii::t('biblioteka', 'Позајмно одељење за одрасле'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>2, 'naziv'=>'pozajmno-odeljenje-za-odrasle'))),
                                                                    array('label'=>Yii::t('biblioteka', 'Стручна књига'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>15, 'naziv'=>'strucna-knjiga'))),
                                                                    array('label'=>Yii::t('biblioteka', 'Легат'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>6, 'naziv'=>'legat-prof-dr-miroslava-radovanovica'))),
                                                                )
                                                            ),
                                                            array('label'=>Yii::t('biblioteka', 'Интернет центар са стараном књигом'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>5, 'naziv'=>'informaciona-sluzba-sa-koriscenjem-interneta-i-fondom-strane-knjige'))),
                                                            array('label'=>Yii::t('biblioteka', 'Сеоске библиотеке'), 'url'=>Helper::createI18nUrl('odeljenje/view', null, array('id'=>11, 'naziv'=>'seoske-biblioteke'))),
                                                        )
                                                    ),
                                                    array('label'=>Yii::t('biblioteka', 'Контакти'), 'url'=>Helper::createI18nUrl('clan/zaposleni_po_odeljenjima')),
                                                    array('label'=>Yii::t('biblioteka', 'Локација'), 'url'=>Helper::createI18nUrl('site/page',null, array('view'=>'lokacija'))),                                                
                                             )
                                ),
                                array('label'=>Yii::t('biblioteka', 'Услуге'), 'itemOptions'=>array('class'=>'dir'),
                                    'items' => array(
                                        array('label'=>Yii::t('biblioteka', 'Позајмица'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'pozajmica'))),
                                        array('label'=>Yii::t('biblioteka', 'Међубиблиотечка позајмица'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'medjubibliotecka-pozajmica'))),
                                        array('label'=>Yii::t('biblioteka', 'Читаоничке услуге'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'citaonicke-usluge'))),
                                        array('label'=>Yii::t('biblioteka', 'Интернет услуге'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'internet-usluge'))),
                                        array('label'=>Yii::t('biblioteka', 'Остале услуге'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'ostale-usluge'))),
                                        array('label'=>Yii::t('biblioteka', 'Ценовник услуга'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'cenovnik-usluga'))),
                                   )
                                ),
                                array('label'=>Yii::t('biblioteka', 'Корисници'), 'itemOptions'=>array('class'=>'dir'),
                                    'items' => array(
                                        array('label'=>Yii::t('biblioteka', 'Чланство'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'clanstvo'))),
                                        //array('label'=>Yii::t('biblioteka', 'Електронско учлањење'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                        //array('label'=>Yii::t('biblioteka', 'Питајте библиотекара'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                    )
                                ),
                                array('label'=>Yii::t('biblioteka', 'Пројекти'), 'itemOptions'=>array('class'=>'dir'),
                                    'items' => array(
                                        array('label'=>Yii::t('biblioteka', 'АгроЛиб'), 'url'=>Helper::createI18nUrl('projekat/view', null, array('id'=>13, 'naziv'=>'agrolib'))),
                                        array('label'=>Yii::t('biblioteka', 'Програми за младе'), 'url'=>Helper::createI18nUrl('projekat/view', null, array('id'=>12, 'naziv'=>'programi-za-mlade'))),
                                        array('label'=>Yii::t('biblioteka', 'Слова на струју'), 'url'=>Helper::createI18nUrl('projekat/view', null, array('id'=>16, 'naziv'=>'slova-na-struju'))),
                                        //array('label'=>Yii::t('biblioteka', 'Адаптација Завичајног одељења'), 'url'=>Helper::createI18nUrl('projekat/view', null, array('id'=>14, 'naziv'=>'adaptacija-zavicajnog-odeljenja'))),
                                    )
                                ),
                                array('label'=>Yii::t('biblioteka', 'Мрежа'), 'itemOptions'=>array('class'=>'dir'),
                       //             'items' => array(
                       //                 array('label'=>Yii::t('biblioteka', 'Јавне библиотеке'), 'itemOptions'=>array('class'=>'dir'),
                                            'items' => array(
                                                array('label'=>Yii::t('biblioteka', '"Др Вићентије Ракић" Параћин'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-paracin'))),
                                                array('label'=>Yii::t('biblioteka', '"Ресавска школа" Деспотовац'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-despotovac'))),
                                                array('label'=>Yii::t('biblioteka', '"Др Милован Спасић" Рековац'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-rekovac'))),
                                                array('label'=>Yii::t('biblioteka', '"Ресавска библиотека" Свилајнац'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-svilajnac'))),
                                                array('label'=>Yii::t('biblioteka', '"Душан Матић" Ћуприја'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>'biblioteka-cuprija'))),
                                            )
                                        //),
                                        /*array('label'=>Yii::t('biblioteka', 'Сеоске библиотеке'), 'itemOptions'=>array('class'=>'dir'),
                                            'items' => array(
                                                array('label'=>Yii::t('biblioteka', 'Багрдан'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                                array('label'=>Yii::t('biblioteka', 'Бунар'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                                array('label'=>Yii::t('biblioteka', 'Глоговац'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                                array('label'=>Yii::t('biblioteka', 'Главинци'), 'url'=>Helper::createI18nUrl('site/page', null, array('view'=>''))),
                                            )
                                        ),*/
                                    //)
                                ),
                                
                                array('label'=>Yii::t('biblioteka', 'Дигитал'), 'itemOptions'=>array('class'=>'dir'),
                                    'items' => array(
                                        array('label'=>Yii::t('biblioteka', 'Дигитална библиотека'), 'url'=>Helper::createI18nUrl('digital/index')),  
                                        array('label'=>Yii::t('biblioteka', 'Јагодина у објективу'), 'url'=>Helper::createI18nUrl('objektiv/index')),  
                                        array('label'=>Yii::t('biblioteka', 'Слова на струју'), 'url'=>Helper::createI18nUrl('/slovanastruju')),
                                    ),
                                ),                                
                                /*array('label'=>Yii::t('biblioteka', 'Часопис'),
                                    'items' => array(
                                    )
                                ),*/                               
                                array('label'=>Yii::t('biblioteka', 'Пријава'), 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
                                array('label'=>Yii::t('biblioteka', 'Одјава').' ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
                        ),
                )); ?>
                </div>
                <?php if(!$this->adminMeni):?>
                <div style="float:left;width:210px;height:30px;">
                    <?php
                        $cs = Yii::app()->getClientScript();
                        $poruka = Yii::t('biblioteka', 'унесите речи за претрагу');
                        $cs->registerScript('pretraga_js', '$(document).ready(function(){
                            $("#upit-g-meni").blur(function(event){if(this.value=="")this.value="'.$poruka.'";});
                            $("#upit-g-meni").focus(function(event){this.value="";});                            
                        });');
                        $model = new PretragaForm;                        
                        $model->ftsUpit = Yii::t('biblioteka', 'унесите речи за претрагу');
                        $actionUrl = Helper::createI18nUrl('pretraga/index');
                        $form = $this->beginWidget('CActiveForm', array('id'=>'pretraga-form', 'method'=>'get', 'enableAjaxValidation'=>false, 'action'=>$actionUrl));
                        echo $form->textField($model,'ftsUpit', array('name'=>'upit', 'id'=>'upit-g-meni'));
                        echo '<input id="pretraga-dugme" type="submit" value=""/>';
                        $this->endWidget();
                    ?>
                </div>                
                <?php endif;?>
            </div><!-- mainmenu -->    
	<?php         
         echo $content;
        ?>
        <div class="clear"></div>
	<div id="futer">
		&copy; <?php echo date('Y'); ?> Народна библиотека "Радислав Никчевић" у Јагодини<br/>
                Програмирање и дизајн: Лазаревић Ивица
                <br/>
	</div><!-- footer -->       
<!-- page -->
</div>
</div> <!-- omotac -->

<!--script src="//pmetrics.performancing.com/js" type="text/javascript"></script-->
<!--script type="text/javascript">try{ clicky.init(22647); }catch(e){}</script-->
<!--noscript><p><img alt="Performancing Metrics" width="1" height="1" src="//pmetrics.performancing.com/22647ns.gif" /></p></noscript-->

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

</body>
</html>