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
   zbirka('$url', '$csrf', '#Zbirka_roditelj');
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
            <strong>Родитељ:</strong> <span style="width:100px;" id="labela_roditelj"><?php echo $naziv_zbirke;?></span>
        </div>

	<div class="row">
		<?php echo $form->labelEx($model,'naziv_zbirke'); ?>
		<?php echo $form->textField($model,'naziv_zbirke',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'naziv_zbirke'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'naziv_zbirkeEn'); ?>
		<?php echo $form->textField($model,'naziv_zbirkeEn',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'naziv_zbirkeEn'); ?>
	</div>

        <div class="row">
		<?php echo $form->labelEx($model,'opis'); ?>
		<?php echo $form->textArea($model,'opis',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'opis'); ?>
	</div>

        <div class="row">
		<?php echo $form->labelEx($model,'opisEn'); ?>
		<?php echo $form->textArea($model,'opisEn',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'opisEn'); ?>
	</div>
        
        <div class="row">
		<?php echo $form->labelEx($model,'redosled'); ?>
		<?php echo $form->textArea($model,'redosled',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'redosled'); ?>
	</div>        

	<div class="row">
		<?php echo $form->labelEx($model,'url_slike'); ?>
		<?php echo $form->textField($model,'url_slike',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'url_slike'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Направи нови' : 'Сачувај измене'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->