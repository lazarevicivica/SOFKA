<?php
    $app = Yii::app();
    $route = $app->controller->getRoute();    
    $params = array();  
    foreach($_GET as $key => $val)
    {
        if($key != 'jezik'){
            $params[$key] = $val;
        }
    }
?>
<div id="langbox">
    <ul>
    <?php
        $liAktivan = '<li class="aktivan-jezik">';
        $enLi='<li>';
        $srLatLi = '<li>';
        $srLi= $liAktivan;
        if($currentLang === Helper::KOD_ENGLESKI_JEZIK)
        {
            $srLi = '<li>';
            $enLi = $liAktivan;
        }
        elseif($currentLang == Helper::KOD_SRPSKI_LATINICA)
        {
            $srLi = '<li>';
            $srLatLi = $liAktivan;
        }
        echo $srLi.CHtml::link('ћирилица', Helper::createI18nUrl($route,Helper::KOD_SRPSKI_JEZIK,$params)).'</li>';
        echo $srLatLi.CHtml::link('latinica', Helper::createI18nUrl($route,Helper::KOD_SRPSKI_LATINICA,$params)).'</li>';
        //echo $enLi.CHtml::link('english', Helper::createI18nUrl($route,Helper::KOD_ENGLESKI_JEZIK, $params)).'</li>';
    ?>
    </ul>
</div>