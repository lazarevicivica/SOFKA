<?php //$id_objava, $data, $naslovIUvod, $heding, $prevedi, $prevod, $odredisnijezik,  ?>
<div class="oblast-naslova">        
    <?php
        $id_objava = $data['id'];
        $tip = $data['tip'];
        $kontroler = strtolower($tip);
        $element = 'naslov_objava_'.$id_objava;
        $naslov = Objava::getNaslov($naslovIUvod);
        $rep = Helper::getSEOText($naslov);
        echo "<$heding>";
        $urlobjava = Helper::createI18nUrl("$kontroler/view", null, array('id'=>$id_objava,'rep'=>$rep));
        echo "<a id=\"$element\" href=\"$urlobjava\">";
            echo CHtml::encode($naslov);
        echo '</a>';
        echo "</$heding>";

    ?>

    <div class="ispod-naslova">
    <?php
//autor teksta
        $autor = Objava::getAutor($data);
        if($autor)
        {
           echo '<strong>'. Yii::t('biblioteka', 'Аутор').': </strong>';
           echo '<span class="autor">'.CHtml::encode($autor).'</span><strong> | </strong> ';
        }

//datum objavljivanja
        echo '<span class="datum-objave">'. Objava::getDatum($data) . '</span><strong> | </strong>';

//Lista odeljaka u okviru kojih se nalazi objava
        echo '<span class="objavljeno-u">';

        $id_jezik = Helper::getAppjezikId();
        $odeljciItagovi = Objava::getOdeljciItagovi($data, $id_jezik);

            $odeljci = $odeljciItagovi['odeljci'];
            $brojodeljaka = count($odeljci);
            echo '<strong>';
            echo Yii::t('biblioteka', 'Одељци').': ';
            echo '</strong>';
            foreach($odeljci as $odeljak)
            {
                $naziv = Helper::skratiTekst($odeljak['naziv'], 50);
                $urlodeljak = Odeljak::getUrl($odeljak);
                echo '<a href="'.$urlodeljak.'">'.CHtml::encode($naziv).'</a>';
                if(--$brojodeljaka) //zarez se dodaje ako nije poslednji odeljak u nizu
                    echo '<strong>, </strong>';
            }
        echo '</span>';
     ?>
<?php
//tagovi (kljucne reci)
$tagovi = $odeljciItagovi['tagovi'];//Objava::getListatagova($data);
if($tagovi)
{
    echo ' <strong>|</strong> <span class="kljucne-reci">';
    $brojtagova = count($tagovi);
    echo '<strong>'.Yii::t('biblioteka', 'Кључне речи'). ': </strong>';
    foreach($tagovi as $tag)
    {
        $naziv = Helper::skratiTekst($tag['naziv'], 50);
        $urltag = Tag::getUrlZaSve($tag);
        $nazivZaPrikaz = CHtml::encode($naziv);
        $title = Yii::t('biblioteka','Објаве из свих одељака за кључну реч '.$nazivZaPrikaz);
        if($tip === 'Knjiga')
        {            
            $urltag = Knjiga::getTagUrl($tag);
            $title = Yii::t('biblioteka', 'Све књиге за кључну реч ').$nazivZaPrikaz;
        }
        
        echo '<a href="'.$urltag.'" title="'.$title.'">'.$nazivZaPrikaz.'</a>';
        if(--$brojtagova) //zarez se dodaje ako nije poslednji odeljak u nizu
            echo '<b>, </b>';
    }
    echo '</span>';
}
?>

    </div>
    <div class="broj-komentara">
    <?php
        $br_komentara = $data['br_komentara'];
        $urlOlovka = Helper::baseUrl('images/sajt/olovka.gif');
        if(!$br_komentara)
            $txtBrkomentara = '<img title="'.Yii::t('biblioteka','Писање коментара').'" src="'.$urlOlovka.'"/>';
        else
            $txtBrkomentara = $br_komentara;
        
        if($br_komentara == 0)
            $sidro = '#pisanje_komentara';
        else 
            $sidro = '#komentar';
        
        echo '<a href="' . $urlobjava . $sidro .'" title="'.Yii::t('biblioteka','Коментари').'">' . $txtBrkomentara . '</a>';
    ?>
    </div>
</div>