<?php      
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/digital.js'));
    $csrf = Yii::app()->request->csrfToken;
    $prazanFilter = $frmModel->isPrazno() ? 1 : 0;
    $jezik = Yii::app()->language;
    $sort = '';
    if(isset($_GET['sort']))
        $sort = '?sort='.$_GET['sort'];
    $idOdeljak = Odeljak::ID_DIGITALNA_BIBLIOTEKA;
    $cs->registerScript('digital_js', "$(document).ready(function(){digital('$csrf', '$urlStranice', $prazanFilter, '$jezik', false, $idOdeljak);});" );
    $urlUkloni = Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$id_zbirka, 'naziv'=>$seoNaziv));

    //echo '<div class="skriveno" id="dijalog-kontejner">';
    $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
            'id'=>'pretraga',
            'htmlOptions' => array('class' => 'skriveno'),
            // additional javascript options for the dialog plugin
            'options'=>array(
                    'title'=>'Dialog box 1',
                    'autoOpen'=>false,
                    'modal'=>true,
                    'width' => 915,
                    'height' => 580,
                    'resizable' => false,                    
            ),
    ));    
    //echo '</div>';
    $this->renderPartial('_pretraga_form', array('frmPretraga'=>$frmPretraga));
    $this->renderPartial('pretraga_stranica', array('frmPretraga' => $frmPretraga));
?>
    <div id="rezultat-pretrage"></div>
    <a id="zatvori" href="#">
    <?php echo Yii::t('biblioteka', 'Затвори'); ?>
</a>
<?php
    $this->endWidget('zii.widgets.jui.CJuiDialog');
//}
?>

<h1 style="border-bottom: 1px solid #ccc;padding-bottom: 5px;"><?php echo Yii::t('biblioteka', 'Дигитална библиотека');?>
</h1>
<div style="margin-bottom:5px;">
    <h3 style="margin-bottom: 0;">
        <span class="naziv-zbirke"> <?php echo $naziv_zbirke;?> </span>    
    </h3>
    <?php if(! $zbirka->isRoot())
        $this->widget('zii.widgets.CBreadcrumbs', array('htmlOptions'=>array('class'=>'putanja'),'homeLink'=>false,'links' => $zbirka->getPutanjaZaBreadcrumbs()));    
    ?>
</div>    
<div id="filter-zbirke1">    
<?php $seoNaziv = Helper::getSEOText($naziv_zbirke);
      $appJezik = ($sort ? '&' : '?') . 'jezik='.Yii::app()->language;

      $cs = Yii::app()->getClientScript();
      $cs->registerCSSFile(Helper::baseUrl('js/iontabs/css/ion.tabs.css'));
      $cs->registerCSSFile(Helper::baseUrl('js/iontabs/css/ion.tabs.skinBordered.css'));            
      $cs->registerScriptFile(Helper::baseUrl('js/iontabs/js/ion-tabs/ion.tabs.js'));
      $cs->registerScript('tabovi_digital_js', '$.ionTabs("#tabs_1",{type:"storage"});');        
?>
    <div class="ionTabs" id="tabs_1" data-name="pretraga">
        <div class="ruka" title="<?php echo Yii::t('biblioteka', 'Сакриј/прикажи')?>"></div>
    <ul class="ionTabs__head">
        <li class="ionTabs__tab" data-target="komplet"><?php echo Yii::t('biblioteka','Обједињена претрага');?></li>
        <li class="ionTabs__tab" data-target="detalji"><?php echo Yii::t('biblioteka','Детаљна претрага');?></li>
    </ul>
    <div class="ionTabs__body">
        <div class="ionTabs__item" data-name="komplet">
            <?php $form = $this->beginWidget('CActiveForm', array('id'=>'digital-form', 'action' => "/digital/zbirka/$id_zbirka/$seoNaziv/$sort$appJezik",'enableAjaxValidation'=>false,'method' => 'get',));?>
            <div id="upit-komplet" style="float:none;">
            <table>
                <tr>
                    <td id="labela-pretraga-komplet" colspan="2">
                        <label>Наслов, аутор, кључне речи, опис, текст публикације</label>               
                    </td>
                </tr>
                <tr>
                    <td id="polje-upit-td-komplet">                        
                        <?php echo $form->textField($frmModel, 'ftsKomplet'); ?> 
                    </td>
                    <td id="dugme-trazi-td-komplet">
                        <?php echo CHtml::submitButton(Yii::t('biblioteka', 'Постави филтер'), array('id'=>'dugme-trazi-komplet', 'class'=>'dugmad')); ?>	
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="color:#777;font-size:11px; padding-top:5px;">
                    <?php echo Yii::t('biblioteka', 'Звездица са десне стране речи производи више резултата. Нпр. <strong>књи</strong><span style="color:red;">*</span> се поклапа са <strong>књи</strong>га, <strong>књи</strong>ге, <strong>књи</strong>жевни...');?>
                    
                    <?php //echo Yii::t('biblioteka','Користите ћирилицу или латиницу са дијакритичким знацима (šćčžđ).');?>
                    </td>                
                </tr>
            </table>
            </div>            
            <?php $this->endWidget(); ?>
        </div>
        <div class="ionTabs__item" data-name="detalji">
            <?php $form = $this->beginWidget('CActiveForm', array('id'=>'digital-form1', 'action' => "/digital/zbirka/$id_zbirka/$seoNaziv/$sort$appJezik",'enableAjaxValidation'=>false,'method' => 'get',));?>
            <div style="float:left;" class="kolona"> 

                <div class="grupa">
                    <?php echo $form->labelEx($frmModel,'naslov'); ?>
                    <?php echo $form->textField($frmModel, 'naslov'); ?>               
                    <?php echo $form->error($frmModel,'naslov'); ?>
                </div>

                <div class="grupa">
                    <?php echo $form->labelEx($frmModel,'autor'); ?>
                    <?php echo $form->textField($frmModel, 'autor'); ?>               
                    <?php echo $form->error($frmModel,'autor'); ?>
                </div>                                                   

                <div class="grupa">
                    <?php echo $form->labelEx($frmModel,'kljucneReci'); ?>
                    <?php echo $form->textField($frmModel, 'kljucneReci'); ?>               
                    <?php echo $form->error($frmModel,'kljucneReci'); ?>
                </div>         

                <div class="grupa">
                    <?php echo $form->labelEx($frmModel,'opis'); ?>
                    <?php echo $form->textField($frmModel, 'opis'); ?>               
                    <?php echo $form->error($frmModel,'opis'); ?>
                </div>                    


            </div>
            <div style ="float:right;" class="kolona">
                <div class="grupa">
                    <?php echo $form->labelEx($frmModel,'ftsUpit');?>
                    <?php echo $form->textField($frmModel, 'ftsUpit'); ?>               
                    <?php echo $form->error($frmModel,'ftsUpit'); ?>
                </div> 

                <div class="grupa" style="height:41px; width:92.5%;">
                    <div style="float:left;">
                        <?php echo $form->labelEx($frmModel,'godinaOd'); ?>
                        <?php echo $form->textField($frmModel, 'godinaOd'); ?>               
                        <?php echo $form->error($frmModel,'godinaOd'); ?>
                    </div>

                    <div style="float:right;">
                        <?php echo $form->labelEx($frmModel,'godinaDo'); ?>
                        <?php echo $form->textField($frmModel, 'godinaDo'); ?>               
                        <?php echo $form->error($frmModel,'godinaDo'); ?>
                    </div>
                    <div style="clear:both"></div>
                </div>

                <div class="grupa">
                <?php
                    $id_jezik = Helper::getAppjezikId();
                    $vrsteGradje = VrstaGradje::model()->with(
                            array('ri18n'=>array(
                                    'order'=>'i18n_vrsta_gradje.naziv_vrste',
                                    'condition'=>"i18n_vrsta_gradje.id_jezik=$id_jezik"
                    )))->findAll();                 
                    echo $form->labelEx($frmModel,'vrstaGradje'); 
                    echo $form->dropDownList($frmModel, 'vrstaGradje', CHtml::listData($vrsteGradje,'id','naziv_vrste'), array('prompt'=>Yii::t('biblioteka','Било која врста')));
                ?>
                </div>  
                <div  id="dugmad1" class="grupa">
                    <?php echo CHtml::submitButton(Yii::t('biblioteka', 'Постави филтер'));?>       
                </div>            
            </div>
            <div style="clear:both"></div>
            
            <div style="color:#777;font-size:11px; padding-top:5px;">
                    <?php echo Yii::t('biblioteka', 'Звездица са десне стране речи производи више резултата. Нпр. <strong>књи</strong><span style="color:red;">*</span> се поклапа са <strong>књи</strong>га, <strong>књи</strong>ге, <strong>књи</strong>жевни...');?>
                    
                    <?php //echo Yii::t('biblioteka','Користите ћирилицу или латиницу са дијакритичким знацима (šćčžđ).');?>
            </div>            
            
            <?php $this->endWidget(); ?>    
        </div> <!--detaljna pretraga-->
        <div class="ionTabs__preloader"></div>
        </div><!--body-->
    </div><!--tabs_1--> 
</div>
<?php if(!$prazanFilter):?>  
    <div id="filter-tekst">
        <?php echo $frmModel->getFilterTxt($urlUkloni.$sort);?>    
    </div>
<?php endif;?>    




<?php
$cs = Yii::app()->getClientScript();
$cs->registerCSSFile(Helper::baseUrl('css/digital.css'));
$cs->registerScriptFile(Helper::baseUrl('js/jquery-tools/jquery.tools.min.js'));
$this->pageTitle = Yii::t('biblioteka', 'Народна библиотека у Јагодини - Дигитална библиотека');

$data->pagination = array('pageSize'=>16);
$aktivno4 = 'dropshadow'; // 4 u liniji
$aktivno1 = ''; //1 u liniji
if($pogled == '2')
{
    $aktivno4 = '';
    $aktivno1 = 'dropshadow';
}
$request = Yii::app()->request;
$url1 = $this->createUrl($this->id.'/'.$this->action->id, array_merge($_GET, array('pogled'=>1)));
$url2 = $this->createUrl($this->id.'/'.$this->action->id, array_merge($_GET, array('pogled'=>2)));
?>

<div style="float:right;height:20px;width:50px;padding-top: 4px;margin-left: 10px;">
    <a href="<?php echo $url1;?>"><img class="<?php echo $aktivno4;?>" src="<?php echo Helper::baseUrl('/images/sajt/4stranice.gif')?>"/></a>
    <a href="<?php echo $url2;?>"><img class="<?php echo $aktivno1;?>" src="<?php echo Helper::baseUrl('/images/sajt/1stranica.gif')?>"/></a>
</div>

<?php
    $ftsUpit = $frmModel->getUpitZaStranice();
    $this->widget('zii.widgets.CListView', array(
        'dataProvider'=>$data,
        'ajaxUpdate'=>false,
        'sorterHeader'=>Yii::t('biblioteka', 'Редослед:'),
        'template' => '{sorter} {items} {pager}' ,
        'viewData' => array('ftsUpit' => $ftsUpit, 'prikaziNaslov'=> ! empty($prikaziNaslov)), // ftsUpit ili false
        'sortableAttributes' => array('id', 'naslov', 'autor', 'godina',), //( ! empty($frmModel->ftsUpit)? array('naslov', 'autor', 'godina', 'rang') : array('naslov', 'autor', 'godina',)),
        'itemView'=> '_knjiga'. ($pogled == '1' ? '' : '2'), //_knjiga ili _knjiga2
        'summaryText'=>Yii::t('biblioteka','Приказ {start}-{end} од укупно {count}'),
        'emptyText' => Yii::t('biblioteka', 'Не постоји ни једна књига која задовољава дате услове! Промените збирку или филтер претраге.'),
        'pager' => array('class'=>'CLinkPager', 'firstPageLabel'=>Yii::t('biblioteka', '&lt;&lt;'),
                                                'prevPageLabel'=>Yii::t('biblioteka', '&lt;'),
                                                'nextPageLabel'=>Yii::t('biblioteka', '&gt;'),
                                                'lastPageLabel'=>Yii::t('biblioteka', '&gt;&gt;'),
                                                'header'       => Yii::t('biblioteka', ''),                                                
         ),
));?>  
<div class="clear"></div>