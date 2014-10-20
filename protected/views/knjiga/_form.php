<?php

    $url = Yii::app()->createUrl('zbirka/ajaxZbirkeIz');
    $csrf = Yii::app()->request->csrfToken;
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/zbirka.js'));
    $cs->registerCSSFile(Helper::baseUrl('css/knjiga.css'));
    $cs->registerCSSFile(Helper::baseUrl('css/digital.css'));
    $cs->registerScript('knjiga_js',"$(document).ready( function(){zbirka('$url', '$csrf', '#KnjigaDeo_id_zbirka');});");
    
    if($model->id_zbirka)
        $naziv_zbirke = Zbirka::model()->findByPk($model->id_zbirka)->naziv_zbirke;
    else
        $naziv_zbirke = '';
    
?>

	<?php echo $form->errorSummary($model); ?>

        <?php echo $form->hiddenField($model,'id_zbirka',array('size'=>60,'maxlength'=>100)); ?>
        
        <div style="margin-bottom:20px;" class="row">
            <strong>Збирка:</strong> <span style="width:100px;" id="labela_roditelj"><?php echo $naziv_zbirke;?></span>
        </div>
        
        <div class="row">
		<?php echo $form->labelEx($model,'id_vrsta_gradje'); ?>
		<?php echo $form->dropDownList($model, 'id_vrsta_gradje', CHtml::listData(VrstaGradje::model()->findAll(),'id','naziv_vrste')); ?>
		<?php echo $form->error($model,'id_vrsta_gradje'); ?>
	</div>
        
        <div class="row">
		<?php echo $form->labelEx($model,'tekst_putanja'); ?>
		<?php echo $form->textField($model,'tekst_putanja',array('size'=>60)); ?>
		<?php echo $form->error($model,'tekst_putanja'); ?>
	</div>

        <div class="row">
		<?php echo $form->labelEx($model,'azuriraj_tekst'); ?>
		<?php echo $form->checkBox($model,'azuriraj_tekst'); ?>
		<?php echo $form->error($model,'azuriraj_tekst'); ?>
	</div>        
        
        
	<div class="row">
		<?php echo $form->labelEx($model,'url_slike'); ?>
		<?php echo $form->textField($model,'url_slike',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'url_slike'); ?>
	</div>
        

	<div class="row">
		<?php echo $form->labelEx($model,'autor'); ?>
		<?php echo $form->textField($model,'autor',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'autor'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'indeks_prve_stranice'); ?>
		<?php echo $form->textField($model,'indeks_prve_stranice',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'indeks_prve_stranice'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'inv_br'); ?>
		<?php echo $form->textField($model,'inv_br',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'inv_br'); ?>
	</div>

        <div class="row">
		<?php echo $form->labelEx($model,'cobiss'); ?>
		<?php echo $form->textField($model,'cobiss',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'cobiss'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'izdanje'); ?>
		<?php echo $form->textArea($model,'izdanje',array('rows'=>2, 'cols'=>60)); ?>
		<?php echo $form->error($model,'izdanje'); ?>
	</div>

        <div class="row">
		<?php echo $form->labelEx($model,'godina'); ?>
		<?php echo $form->textField($model,'godina',array('size'=>60,'maxlength'=>4)); ?>
		<?php echo $form->error($model,'godina'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'mesec'); ?>
		<?php echo $form->textField($model,'mesec',array('size'=>60,'maxlength'=>2)); ?>
		<?php echo $form->error($model,'mesec'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'dan'); ?>
		<?php echo $form->textField($model,'dan',array('size'=>60,'maxlength'=>2)); ?>
		<?php echo $form->error($model,'dan'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'json_desc'); ?>
		<?php echo $form->textArea($model,'json_desc',array('rows'=>20, 'cols'=>75)); ?>
		<?php echo $form->error($model,'json_desc'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sadrzaj'); ?>
		<?php echo $form->textArea($model,'sadrzaj',array('rows'=>20, 'cols'=>75)); ?>
		<?php echo $form->error($model,'sadrzaj'); ?>
	</div>