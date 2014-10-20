<h3>Унос групе збирки</h3>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'zbirka-form',
	'enableAjaxValidation'=>false,
));
    $url = Yii::app()->createUrl('zbirka/ajaxZbirkeIz');
    $csrf = Yii::app()->request->csrfToken;
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/zbirka.js'));
    $cs->registerScript('zbirka_js',"
$(document).ready( function()
{
   zbirka('$url', '$csrf', '#GrupaZbirkiForm_roditelj');
});");
    if($model->roditelj)
        $naziv_zbirke = Zbirka::model()->findByPk($model->roditelj)->naziv_zbirke;
    else
        $naziv_zbirke = '';
        
?>

    
	<p class="note">Поља означена <span class="required">*</span> су обавезна.</p>

	<?php echo $form->errorSummary($model); ?>

        <?php echo $form->hiddenField($model,'roditelj',array('size'=>60,'maxlength'=>100)); ?>
        
        <div style="margin-bottom:20px;" class="row">
            <strong>Родитељ*:</strong> <span style="width:100px;" id="labela_roditelj"><?php echo $naziv_zbirke;?></span>
        </div>

	<div class="row">
		<?php echo $form->labelEx($model,'txtSr'); ?>
		<?php echo $form->textField($model,'txtSr'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'txtEn'); ?>
		<?php echo $form->textField($model,'txtEn'); ?>
	</div>       
        
	<div class="row">
		<?php echo $form->labelEx($model,'json'); ?>
		<?php echo $form->textArea($model,'json', array('style'=>'height:300px;')); ?>
		<?php echo $form->error($model,'json'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton('Направи групу'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->