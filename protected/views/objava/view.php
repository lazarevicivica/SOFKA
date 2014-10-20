<div class="objava-komplet">
<?php
    //ako postoji jezik u bazi onda se sve nalazi u nizu $data, ako ne postoji onda se ucitava
    $naslovIUvod = $data;
    $id_objava = $data['id'];
    if( ! Helper::isPostojijezik($data))
        $naslovIUvod = Objava::ucitajNaslovUvodITekst($id_objava, $data['id_jezik_originala']);
    $naslov = $naslovIUvod['naslov'];
    $this->pageTitle = Yii::t('biblioteka', 'Народна библиотека у Јагодини') . ' - ' . CHtml::encode($naslov);
    $this->renderPartial('//objava/_naslov', array('id_objava'=>$id_objava, 'heding'=>'h1', 'data' => $data, 'naslovIUvod' => $naslovIUvod));
?>
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style ">
<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
<a class="addthis_button_tweet"></a>
<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
<a class="addthis_counter addthis_pill_style"></a>
</div>
<!--script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4f170b8558bf98dd"></script-->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4f170b8558bf98dd"></script>
<!-- AddThis Button END -->    
  
<?php    
//SAMO ZA KNJIGU META PODACI    
    if($data['tip'] === 'Knjiga')
    {
        assert(! empty($knjiga));
        $cs = Yii::app()->getClientScript();        
        $cs->registerScriptFile(Helper::baseUrl('js/digital.js')); 
        $cs->registerCSSFile(Helper::baseUrl('css/digital.css'));        
        $cs->registerCSSFile(Helper::baseUrl('css/knjiga.css'));
        //$knjiga = Knjiga::getKnjigaDeo($id_objava);        
        $this->renderPartial('//digital/_knjiga2', array('data'=>$knjiga, 'upit'=>$frmPretraga->ftsUpit,'deoStranice' => true));
    }        
//KRAJ KNJIGE    

//GLAVNI TEKST
?>
<?php if( ! empty($naslovIUvod['tekst']) || ! empty($naslovIUvod['uvod'])):?>
    <?php
        $element = "objava_tekst_$id_objava";    
        echo "<div id=\"$element\" class=\"objava\">";
        if(!$naslovIUvod['tekst'])
            echo $naslovIUvod['uvod'];
        else
            echo $naslovIUvod['tekst'];
    echo '</div>';
    ?>
<?php endif;?>
</div> <!-- Objava komplet-->

<?php
//PRETRAGA SAMO ZA KNJIGU

    if( ! empty($knjiga) && $knjiga['sadrzi_indeks'])
    {
        $csrf = Yii::app()->request->csrfToken;
        $urlStranice = '/digital/stranice';
        $idOdeljak = Odeljak::ID_DIGITALNA_BIBLIOTEKA;
        $jezik = Yii::app()->language;
        $cs->registerScript('digital_js', "$(document).ready(function(){digital('$csrf', '$urlStranice', 0, '$jezik', true, $idOdeljak);});");
        $frmPretraga->prikazCitanka = ! empty($knjiga['json_desc']);
        echo '<div id="pretraga">';
            $this->renderPartial('//digital/_pretraga_form', array('frmPretraga'=>$frmPretraga, 'animacija'=>false));
            $this->renderPartial('//digital/pretraga_stranica', array('frmPretraga' => $frmPretraga, 'brRezultataPoStrani' => 5));        
        echo '</div>';
    }


//GALERIJA

    $id_galerija = $data['id_galerija'];
    if($id_galerija)
    {
        echo '<div id="objava_galerija">';
        $this->widget('Galerija', array('id_galerija'=>$id_galerija));
        echo '</div>';
    }
?>

<?php if($komentari->itemCount):?>
<div class="lista-komentara">
    <h3>
        <?php echo '<a name="komentar">'.Yii::t('biblioteka', 'Коментари') .'</a>';
        ?>
    </h3>
    <?php 
        
        $this->widget('zii.widgets.CListView', array(
            'dataProvider'=>$komentari,
            'ajaxUpdate'=>false,
            'itemView'=>'//objava/_komentar',
            'summaryText'=>Yii::t('biblioteka', 'Коментари {start}-{end} од укупно {count}'),
            'emptyText' =>'',// Yii::t('biblioteka', ''),
            'pager' => array('class'=>'MojLinkPager', 'aName'=>'#komentar', 'firstPageLabel'=>Yii::t('biblioteka', 'Прва'),
                                                        'prevPageLabel'=>Yii::t('biblioteka', '&lt; Претходна'),
                                                        'nextPageLabel'=>Yii::t('biblioteka', 'Следећа &gt;'),
                                                        'lastPageLabel'=>Yii::t('biblioteka', 'Последња'),
                                                        'header'       => Yii::t('biblioteka', 'Страна: '),

        )));
    ?>
</div>
<?php endif;?>

<?php if( ! $data['zakljucano']):?>
<div class="pisanje-komentara">
    <?php
        echo '<h3><a name="pisanje_komentara">'.Yii::t('biblioteka', 'Оставите коментар').'</a></h3>';
        $this->renderPartial('//objava/_komentar_form', array('modelForme'=>$modelForme, 'naslov'=>$naslov));
    ?>

</div>
<?php endif;?>