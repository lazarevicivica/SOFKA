<?php
//$cmdSlike dobija iz galerija.php
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/fancybox/jquery.fancybox-1.3.4.pack.js'));
    $cs->registerScriptFile(Helper::baseUrl('js/fancybox/jquery.easing-1.3.pack.js'));
    $cs->registerScriptFile(Helper::baseUrl('js/fancybox/jquery.mousewheel-3.0.4.pack.js'));
    $cs->registerCSSFile(Helper::baseUrl('js/fancybox/jquery.fancybox-1.3.4.css'));
    $cs->registerCSSFile(Helper::baseUrl('css/galerija.css'));
    $cs->registerScript('galerija_js','    
$("a[rel=galerija]").fancybox({
\'titlePosition\' :   \'inside\',
\'mouseEnabled\':true,
\'speedIn\':600,
\'speedOut\':200
});');
    $slike = $cmdSlike->queryAll();
    if(!$slike)
        return;
    echo '<ul class="galerija">';
    $kolona = 1;
    foreach($slike as $sl)
    {       
        $urlThumb = Slika::getThumbPutanja($sl);
        $url_slika = Slika::getPutanja($sl);
        $klasa = '';
        if($kolona == $broj_kolona)
        {
            $kolona = 0;
            $klasa = ' class="poslednja"';
        }
        echo '<li'.$klasa.'>';
        $titleTh = $sl['title'];
        if(empty($titleTh))       
            $titleTh = Helper::skratiTekst(strip_tags($sl['tekst']), 100);
        echo  '<a rel="galerija" title="'. CHtml::encode($sl['tekst']).'" href="'.$url_slika.'"><img title="'. CHtml::encode($titleTh).'" alt="'.CHtml::encode($sl['alt']).'" src="'.$urlThumb.'"/></a>';
        $kolona++;
    }
    echo '</ul>';
    echo '<div class="clear"></div>';
?>