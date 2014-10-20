<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_jezik_originala'); ?>
		<?php echo $form->textField($model,'id_jezik_originala'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'korisnicko_ime'); ?>
		<?php echo $form->textField($model,'korisnicko_ime',array('size'=>50,'maxlength'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'lozinka'); ?>
		<?php echo $form->textField($model,'lozinka',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'puno_ime'); ?>
		<?php echo $form->textField($model,'puno_ime',array('size'=>50,'maxlength'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'tip'); ?>
		<?php echo $form->textField($model,'tip'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_radno_mesto'); ?>
		<?php echo $form->textField($model,'id_radno_mesto'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_odeljenje'); ?>
		<?php echo $form->textField($model,'id_odeljenje'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'aktivan'); ?>
		<?php echo $form->textField($model,'aktivan'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'slika'); ?>
		<?php echo $form->textField($model,'slika',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'telefon'); ?>
		<?php echo $form->textField($model,'telefon',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'sajt'); ?>
		<?php echo $form->textField($model,'sajt',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'uloga'); ?>
		<?php echo $form->textField($model,'uloga',array('size'=>50,'maxlength'=>50)); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->