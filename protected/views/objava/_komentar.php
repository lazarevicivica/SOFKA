<?php
//$data, $prevodilac
$pozadina = ($index%2?' belo':'');
echo "<div class=\"komentar$pozadina\">";
        $url_slika = Clan::getslikaS($data);
        echo "<div class=\"komentar-slika\"><img src=\"$url_slika\"/></div>";
        echo "<div class=\"komentar-txt\">";
        $komentator = Komentar::getKomentatorHtmlS($data);
        echo "<span class=\"komentar-ime\">$komentator</span>";
        echo '<span class="kaze"> '.Yii::t('biblioteka', 'каже').':</span>';
        echo '<br/>';

    $id_komentar = $data['id'];
    $element = "komentar_$index";

    if( ! Helper::isPostojijezik($data))
    {
        //$prevedi = true;
        $izvornijezikId = $data['id_jezik_originala'];
        $izvornijezik = Helper::prevediUGoogleKod($izvornijezikId);
        $branding = "branding_komentar_$id_komentar";
        echo "<span class=\"translation_branding\" id=\"$branding\">".Yii::t('biblioteka','Превод ').'</span>';
        $tekstkomentara = Komentar::getkomentar($id_komentar, $izvornijezikId);
    }
    else
    {
        $tekstkomentara = $data['tekst'];
    }

echo "<div id=\"$element\" class=\"tekst-komentara\">";
    echo CHtml::encode($tekstkomentara);
echo '</div>';
        $vreme = '<span class="datum">'.date(' d.m.Y ', $data['datum']) . '</span>'.
                 '<span class="komentar-meta">'.Yii::t('biblioteka', 'у').'</span>'.
                 '<span class="datum">'.date(' H:i ', $data['datum']) . '</span>'.
                 '<span class="komentar-meta">'.Yii::t('biblioteka','часова').'</span>';        
        echo "<span class=\"komentar-vreme\">$vreme</span></div>";  
echo '</div>';
?>