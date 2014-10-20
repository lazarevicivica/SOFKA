<?php

    //klik na zbirku iz stabla salje get zahtev sa poslednje registrovanim filterom.
    //Vrednosti poslednje registrovanog filtera se cuvaju u JSON strukturi.
    $filter = json_encode($frmModel->attributes);   
    
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/digital.js'));
    $csrf = Yii::app()->request->csrfToken;
    $prazanFilter = $frmModel->isPrazno() ? 1 : 0;
    $jezik = Yii::app()->language;
    $idOdeljak = Odeljak::ID_DIGITALNA_BIBLIOTEKA;
    $cs->registerScript('digital_js', "$(document).ready(function(){digital('$csrf', $urlStranice, $prazanFilter, '$jezik', false, '$idOdeljak');});");
    $urlUkloni = Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$id_zbirka, 'naziv'=>$seoNaziv));
?>

<h1><?php echo Yii::t('biblioteka', 'Дигитална библиотека');?></h1>
<hr/>

<h3>
    <?php echo Yii::t('biblioteka', 'Збирка: '); ?> 
    <span class="naziv-zbirke"> <?php echo $naziv_zbirke;?> </span>   
    <a id="filter-link" class="zatvoreno" href="#"><?php echo Yii::t('biblioteka', 'Филтер');?></a>
    <span id="filter-tekst" <?php echo  !$prazanFilter ? 'class="postavljen-filter"' : '';?>>
        <?php echo $frmModel->getFilterTxt();?>
    </span>
    <?php if(!$prazanFilter):?>
    <span>
        <a id="ukloni-filter-slicica" href="<?php echo $urlUkloni;?>" title="<?php echo Yii::t('biblioteka', 'Уклони филтер');?>"></a>
    </span>
    <?php endif;?>
</h3>

<div id="filter-zbirke" class="skriveno">
<?php $seoNaziv = Helper::getSEOText($naziv_zbirke);?>
<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'digital-form',
        'action' => "/digital/zbirka/$id_zbirka/$seoNaziv",
	'enableAjaxValidation'=>false,
        'method' => 'get',
)); 
?>
    <h4><?php echo Yii::t('biblioteka', 'Филтер');?></h4>    
    <div style="float:left;">        
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
		<?php echo $form->labelEx($frmModel,'godinaOd'); ?>
                <?php echo $form->textField($frmModel, 'godinaOd'); ?>               
		<?php echo $form->error($frmModel,'godinaOd'); ?>
            </div>
            
            <div class="grupa">
		<?php echo $form->labelEx($frmModel,'godinaDo'); ?>
                <?php echo $form->textField($frmModel, 'godinaDo'); ?>               
		<?php echo $form->error($frmModel,'godinaDo'); ?>
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
    </div>
    
    <div  style="float:right;" id="dugmad">
        <?php echo CHtml::submitButton(Yii::t('biblioteka', 'Постави филтер'));?>       
        <a id="ukloni-filter" href="<?php echo $urlUkloni;?>"><?php echo Yii::t('biblioteka', 'Уклони филтер');?></a>
    </div>
<?php $this->endWidget(); ?>     
    <hr/>
</div>
<?php
$cs = Yii::app()->getClientScript();
$cs->registerCSSFile(Helper::baseUrl('css/digital.css'));
$cs->registerScriptFile(Helper::baseUrl('js/jquery-tools/jquery.tools.min.js'));
$this->pageTitle = Yii::t('biblioteka', 'Народна библиотека у Јагодини - Дигитална библиотека');

$data->pagination = array('pageSize'=>16);

$w = $this->widget('zii.widgets.CListView', array(
        'dataProvider'=>$data,
        'ajaxUpdate'=>false,
        'sorterHeader'=>Yii::t('biblioteka', 'Редослед:'),
        //'template' => '{summary} {sorter} {pager} {items} {pager}' ,
        'template' => '{sorter} {items} {pager}' ,
        'sortableAttributes' => array('naslov', 'autor', 'godina'),
        'itemView'=>'_knjiga',
        'summaryText'=>Yii::t('biblioteka','Приказ {start}-{end} од укупно {count}'),
        'emptyText' => Yii::t('biblioteka', 'Не постоји ни једна књига која задовољава дате услове!'),
        'pager' => array('class'=>'CLinkPager', 'firstPageLabel'=>Yii::t('biblioteka', '&lt;&lt;'),
                                                'prevPageLabel'=>Yii::t('biblioteka', '&lt;'),
                                                'nextPageLabel'=>Yii::t('biblioteka', '&gt;'),
                                                'lastPageLabel'=>Yii::t('biblioteka', '&gt;&gt;'),
                                                'header'       => Yii::t('biblioteka', ''),                                                
         ),
));
?>
    
<div class="clear"></div>