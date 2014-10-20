<?php
//$prevodilac, $odredisnijezik
    if(!$index)
        $klasa = 'view top';
    else
        $klasa = 'view'
?>

<div class="<?php echo $klasa;?>">

    <?php
    //ako postoji jezik u bazi onda se sve nalazi u nizu $data,
    //ako ne postoji onda se ucitava
        $naslovIUvod = $data;
        $id_objava = $data['id'];
        $prevedi = false;
        if( ! Helper::isPostojijezik($data))
        {
            $prevedi = true;
            $naslovIUvod = Objava::ucitajNaslovIUvod($id_objava, $data['id_jezik_originala']);
        }    
        $this->renderPartial('//objava/_naslov', array('id_objava'=>$id_objava, 'heding'=>'h3', 'data' => $data, 'naslovIUvod' => $naslovIUvod));

//tekst UVODA    
        $element = 'uvod_objava_'.$id_objava;
        echo "<div id=\"$element\" class=\"uvod\">";                
        $url_slika = Objava::getslikaUvod($data);  
        $naslov = $naslovIUvod['naslov'];
        //$urlobjava = Helper::createI18nUrl('objava/view', null, array('id'=>$id_objava, 'rep'=>Helper::getSEOText($naslov)));
        $tip = $data['tip'];
        $rep = Helper::getSEOText($naslov);
        $urlobjava = $tip::getUrlS($data, $rep);
        
        $klasa = 'tekst-uvoda';
        if($url_slika)        
            echo "<a href=\"$urlobjava\"><img class=\"dropshadow\" src=\"$url_slika\" alt=\"\"/></a>";
        else
            $klasa .= ' siroki-uvod';
        echo "<div class=\"$klasa\">";
        echo Objava::getUvod($naslovIUvod);
        echo "<span class=\"saznajte-vise nobr\"> <a href=\"$urlobjava\"> ".Yii::t('biblioteka', 'Сазнајте више').'</a></span>';
        echo '</div>';
echo '</div>';
?>
    <div class="clear">&nbsp;</div>
</div>