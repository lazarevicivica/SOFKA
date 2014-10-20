<div class="form-wide">

<?php
 $this->layout='application.views.layouts.column1';
 $form=$this->beginWidget('CActiveForm', array(
	'id'=>'objava-form',
	'enableAjaxValidation'=>false,
));

$klasaModela = get_class($objava);
$csrf = Yii::app()->request->csrfToken;
 
//$jezik = Helper::getAppjezikGoogle();
$cs = Yii::app()->getClientScript();
$cs->registerCSSFile(Helper::baseUrl('css/nova-objava.css'));
$cs->registerScriptFile(Helper::baseUrl('js/ckeditor/ckeditor.js'));
$cs->registerScriptFile(Helper::baseUrl('js/oznaci_autocomplete.js'));
$cs->registerScriptFile(Helper::baseUrl('js/plupload/plupload.js'));
$cs->registerScriptFile(Helper::baseUrl('js/plupload/plupload.html5.js'));
$cs->registerScriptFile(Helper::baseUrl('js/plupload/plupload.html4.js'));
$cs->registerScriptFile(Helper::baseUrl('js/stringify/json2.js'));
$cs->registerScriptFile(Helper::baseUrl('js/encode.js'));
$cs->registerScriptFile(Helper::baseUrl('js/azuriranje_nova_objava.js'));
$cs->registerScript('objava_azuriranje_js',"
$(document).ready( function()
{
    monkeyPatchAutocomplete();        
});");
?>
<div id="stari-browser">Ваш програм за преглед интернет страница не подржава напредне ХТМЛ5 опције! Предлажемо Вам нову верзију <a href="http://www.mozilla.org/en-US/firefox/fx/">Мозила Фајерфокса</a> или <a href ="http://www.google.com/chrome/">Гугл Хрома</a>.</div>

<div id="levo">
    <div class="kutija">
        <div id="objava-heder" class="heder zaglavlje-kutije plavo">Објава</div>
        <div id="objava-panel" class="panel">
            <?php echo $form->errorSummary($objava); ?>
            <?php echo CHtml::activeHiddenField($objava, 'id');?>
            <?php echo CHtml::activeHiddenField($objava, 'id_galerija');?>
            <div class="row">
                <?php echo $form->error($objava,'naslov'); ?>
                <?php echo $form->labelEx($objava,'naslov'); ?>  
                <div></div>
                <?php echo $form->textField($objava,'naslov'); ?>
            </div>

            <?php echo CHtml::activeHiddenField($objava, 'jsongalerija');?>
            <div class="row">
                    <?php echo $form->error($objava,'tekst_sirov'); ?>
                    <?php echo $form->labelEx($objava,'tekst_sirov'); ?>
                    <?php echo $form->textArea($objava,'tekst_sirov'); ?>
            </div>
            <div class="row">
                    <?php echo $form->error($objava,'uvod'); ?>
                    <?php echo $form->labelEx($objava,'uvod'); ?>
                    <?php echo $form->textArea($objava,'uvod'); ?>
            </div>
            
            <?php 
                if(get_class($objava) == 'Knjiga')
                    $this->renderPartial('//knjiga/_form', array('model'=>$objava->knjiga, 'cs'=>$cs, 'csrf'=>$csrf, 'form'=>$form, 'galerija' => $galerija));
            ?>            
        </div>
    </div>
    <!--<p></p>
    <div class="kutija">
        <div id="kviz-heder" class="heder zaglavlje-kutije">Квиз</div>
        <div id="kviz-panel" class="panel">
            <div class="row">
            <label>Назив</label>
                <input id="naziv-kviza"></input>                
            </div>
        </div>
    </div> -->
</div>

<div id="desno">

<?php 
    if($klasaModela === 'Knjiga')
    {
        if(Yii::app()->controller->action->id == 'create')
            $this->registrujPortlet('AutomatskaObradaPortlet', array(), $naPocetak=true);
        $parametri = array();
        if( ! empty($knjiga->id_zbirka))
            $parametri['id_zbirka'] = $knjiga->id_zbirka;
        $this->registrujPortlet('StabloPortlet', $parametri);
        
        /*$ajaxUrl = Yii::app()->createUrl('zbirka/ajaxZbirkeIz');
        $cs->registerScriptFile(Helper::baseUrl('js/zbirka.js'));            
        $cs->registerCSSFile(Helper::baseUrl('css/digital.css'));
        $cs->registerScript('knjiga_zbirka_js', "$(document).ready( function(){zbirka('$ajaxUrl', '$csrf', '#KnjigaDeo_id_zbirka');});"); */   
        $this->prikaziPortlete();
    }
?>    
    
<div class="kutija">
    <div class ="zaglavlje-kutije">
        <?php echo Yii::t('biblioteka', 'Одељци') ?>
    </div>
    <div class="telo-kutije odeljci ogranici-visinu">
        <?php foreach($odeljci as $i=>$odeljak): ?>
            <div>
                <label>
                 <?php
                    $opcije = array();
                    if(! $odeljak->cekEnabled)
                        $opcije = array('disabled'=>'disabled');
                    echo CHtml::activeCheckBox($odeljak,"[$i]cekiran", $opcije);
                 ?>
                 <?php echo $odeljak->naziv ?>
                </label>
                 <?php echo CHtml::activeHiddenField($odeljak,"[$i]id"); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div><!-- kutija odeljci -->

        <div id="kljucne-reci" class="kutija">
            <div class ="zaglavlje-kutije plavo">
                <?php echo Yii::t('biblioteka', 'Кључне речи') ?>
            </div>
            <div class="telo-kutije">
                <?php
$this->widget('TextAreaJuiAutoComplete', array(
    'model'=>$objava,
    'attribute'=>'tagovi',
    'id'=>'tagovi_',
    'name'=>'tagovi',
    'source'=>"js:function(request, response) {
        $.getJSON('".Helper::createI18nUrl('objava/predlozitagove', Helper::KOD_SRPSKI_JEZIK)."', {
            term: extractLast(request.term)
        }, response);
    }",
    'options'=>array(
        'delay'=>300,
        'minLength'=>2,
        'showAnim'=>'fold',
        'select'=>"js:function(event, ui) {
            var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            // add placeholder to get the comma-and-space at the end
            terms.push('');
            this.value = terms.join(', ');
            return false;
        }",
        'focus' => 'js:function() {
                // prevent value inserted on focus
                return false;
        }',
    ),
    'htmlOptions'=>array(),
));
?>
        </div>
    </div><!-- kljucne reci -->

<div id="galerija" class="kutija">
    <div class ="zaglavlje-kutije">
        <?php echo Yii::t('biblioteka', 'Галерија') ?>
        <div id="progres-info">
            <div id="progressbar"></div>
            <div id="progres-labela">0</div>
        </div>
    </div>
    <div class="telo-kutije">
        <div id="uploader">
            <div id="filelist-kontejner">
                <table id="filelist">
                    <thead>
                    <th>Слика</th><th></th><th></th><th></th>
                    </thead>
                    <tbody>
                    <?php
                        if($galerija)
                             echo $galerija->getSlikeHTMLInputtagovi();
                    ?>
                    </tbody>
                </table>
            </div>
           <a id="izbor" href="javascript:;">Избор</a>
           <a id="otpremanje" href="javascript:;">Слање</a>
        </div>

<?php
//Java script koji generise pluploader i ckeditore
$visina = Slika::MAX_VISINA;
$sirina = Slika::MAX_SIRINA;
$kvalitet = 90;
$otpremljeno = CHtml::image(Helper::baseUrl('images/sajt/prihvaceno.png'), '100%', array('title'=>'Слика је отпремљена, да би постала део галерије потребно је сачувати измене.'));
$cekanje = CHtml::image(Helper::baseUrl('images/sajt/sat.png'), '100%', array('title'=>'Слика чека слање на сервер.'));
$brisanjeCB = CHtml::image(Helper::baseUrl('images/sajt/brisanje-slike-cb.png'), '', array('title'=>' Брисање слике.'));
$brisanje = CHtml::image(Helper::baseUrl('images/sajt/brisanje-slike.png'), '', array('title'=>' Брисање слике.'));
$staro = CHtml::image(Helper::baseUrl('images/sajt/stiklirano.png'), '', array('title'=>'Слика је део галерије.'));
$staroBrisanje = CHtml::image(Helper::baseUrl('images/sajt/staro-brisanje.png'), '', array('title'=>'Слика ће бити избисана када буду сачуване измене.'));
$poslatoBrisanje = CHtml::image(Helper::baseUrl('images/sajt/poslato-brisanje.png'), '', array('title'=>'Слика ће бити избисана када промене буду сачуване.'));
$ponistiBrisanje = CHtml::image(Helper::baseUrl('images/sajt/ponisti-brisanje.png'), '', array('title'=>'Поништавање брисања.'));
$ponistiBrisanjeCB = CHtml::image(Helper::baseUrl('images/sajt/ponisti-brisanje-cb.png'), '', array('title'=>'Поништавање брисања.'));
$nedefinisano = CHtml::image(Helper::baseUrl('images/sajt/nedefinisano.png'), '', array('title'=>'Грешка, фајл не може бити отпремљен!'));
$urlSkripte = $this->createUrl('site/upload');

$cs->registerScript('pluploader_js',
"   initAzuriranjeObjaveJS('$klasaModela', $sirina, $visina, $kvalitet, '$urlSkripte', '$csrf', '$otpremljeno', '$cekanje', '$staro', '$staroBrisanje', '$poslatoBrisanje', '$nedefinisano' );

    $('#Objava_draft').click(function()
    {
        var tekst = 'Објави';
        if( this.checked)
            tekst = 'Сачувај';
        $('#dugme-sacuvaj')[0].value = tekst;

    });

    $('#dugme-sacuvaj').click(function(e)
    {
        var sacuvaj = true;
        if($('.nova-slika').size() > 0)
        {            
            if(confirm('Постоје слике које нису отпремљене. Да ли ипак желите да наставите?'))
                sacuvaj = true;
            else
                sacuvaj = false;        
        }
        if(sacuvaj)
        {          
            $('#{$klasaModela}_jsongalerija')[0].value = otpremac_fajlova.serijalizuj();
        }
        else
            e.preventDefault();
    });");
?>     
     </div>
</div>
<a id="otkazi" href="javascript:;">Откажи</a>
<!-- galerija -->  

<?php
        if($novo || $objava->status == Objava::DRAFT)
        {
            echo $form->checkBox($objava, 'draft');
            $labele = $objava->attributeLabels();
            echo $form->labelEx($objava,'draft');
        }
?>
        <div class="row buttons">                       
<?php
                $sacuvaj = Yii::t('biblioteka', 'Сачувај');
                $objavi = Yii::t('biblioteka', 'Објави');
                if($novo)
                {
                    if($objava->draft)
                        $poruka = $sacuvaj;
                    else
                        $poruka = $objavi;
                }
                else//nije nova, vec se nalazi u bazi
                {
                    if($objava->status == Objava::DRAFT)
                    {
                        if($objava->draft)
                            $poruka = $sacuvaj;
                        else
                            $poruka = $objavi;
                    }
                    else
                        $poruka = $sacuvaj;
                }
                echo CHtml::submitButton($poruka, array('id'=>'dugme-sacuvaj') );
?>
        </div>
   </div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id'=>'podesavanje-slike',
    'options'=>array(
        'title'=>'Подешавање слике',
        'autoOpen'=>false,
        'width' => 600,
//        'height' => 450,
        'modal' => true,
        'resizable' =>false
    ),
)); ?>

<div id="dijalog-iznad-opisa">
    <div id="dijalog-slika" class="left">
        <img src="" alt=""/>
    </div>
    <div id="dijalog-polja" class="right">
        <div>
            <label class="lbl">Назив:</label>
            <label id="dijalog-naziv">mojaslika.jpg</label>
        </div>
        <div>
            <label  class="lbl">Статус:</label>
            <div style="width:290px;margin-bottom: 5px;float:right;">
                <label  id="dijalog-status">Отпремљена, у саставу галерије</label>
            </div>
            <div class="clear"></div>
        </div>
        <div>
            <label  class="lbl" for="dijalog-naslov">
                <?php echo Yii::t('biblioteka', 'Наслов:');?>
            </label>
            <input id="dijalog-title"/>
        </div>
        <div>
            <label  class="lbl" for="dijalog-alt">
                <?php echo Yii::t('biblioteka', 'Алт. тхт:');?>
            </label>
            <input id="dijalog-alt"/>
        </div>
        <div>
            <label  class="lbl" for="dijalog-rotacija">
                <?php echo Yii::t('biblioteka', 'Ротација:');?>
            </label>
            <select id="dijalog-rotacija">
                <option>0&deg;</option>
                <option>лево</option>
                <option>десно</option>
                <option>180&deg;</option>
            </select>

            <label class="lbl" for="dijalog-prikaz">
                <?php echo Yii::t('biblioteka', 'Приказ:');?>
            </label>
            <input id="dijalog-prikaz" type="checkbox"/>
        </div>
    </div>
    <div class="clear"></div>
</div>    
    <div>
        <label for="dijalog-tekst">Опис слике</label>
        <textarea id="dijalog-tekst" style="width:100%;height: 200px;"></textarea>
    </div>
    <div id="dijalog-dugmad">
        <div class="left">
            <a id="prethodna-slika" class="prethodna" href="#"></a>
            <a id="sledeca-slika" class="sledeca" href="#"></a>
        </div>
        <div class="right">
            <a id="umetanje-glavni" href="#">Копирај у главни текст</a>
            <a id="gotovo" href="#">Прихвати</a>
        </div>
    </div>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<div class="clear"></div>