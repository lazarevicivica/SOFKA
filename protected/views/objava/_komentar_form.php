<?php if(Yii::app()->user->hasFlash('greska')): ?>

<div class="flash-error">
	<?php echo Yii::app()->user->getFlash('greska'); ?>
</div>
<?php else: ?>
<?php if(Yii::app()->user->hasFlash('komentar')): ?>
<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('komentar'); ?>
</div>

<?php else: ?>

<?php
    $cs = Yii::app()->getClientScript();
//    $cs->registerScriptFile('https://www.google.com/jsapi?key=ABQIAAAAAN5CQ81Kr99QCw2eLiqRuRTHJQtndEWnKPJf1q9aDQbCdsrrKhRl7je0yyDasO2cVJl5ciwOe9UysA');
    //kada ukljucim key onda ne rade na istoj strnici transliteracija i prevod
    $cs->registerScriptFile('https://www.google.com/jsapi');
    $cs->registerScriptFile(Helper::baseUrl('js/google-translit.js'));
?>
<div class="hint">
<?php echo Yii::t('biblioteka','Молимо Вас, напишите шта Ви мислите о "{tema}"?',array('{tema}'=>$naslov));?>
</div>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array('action'=>Yii::app()->request->getUrl().'#pisanje_komentara')); ?>

	<?php //<p class="note"> echo Yii::t('biblioteka', 'Поља означена'). ' <span class="required">*</span> '.
                //                   Yii::t('biblioteka', 'морају бити унета').'</p>';?>

	<?php echo $form->errorSummary($modelForme); ?>
        
<?php //ako je gost onda mora da popuni polja email i sajt. Ako je registrovan onda nema potrebe.
    if(Yii::app()->user->isGuest):?>
	<div class="row">
		<?php echo $form->labelEx($modelForme,'ime'); ?>
		<?php echo $form->textField($modelForme,'ime'); ?>            
	</div>

        <div class="row">
		<?php echo $form->labelEx($modelForme,'email'); ?>
		<?php echo $form->textField($modelForme,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($modelForme,'web'); ?>
		<?php echo $form->textField($modelForme,'web',array('size'=>60,'maxlength'=>128)); ?>
	</div>
<?php endif;?>

	<div class="row">
		<?php echo $form->labelEx($modelForme,'poruka'); ?>
		<?php echo $form->textArea($modelForme,'poruka',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<?php if(CCaptcha::checkRequirements()): ?>
    		<div class="hint">
                <?php echo Yii::t('biblioteka', 'Молимо Вас унесите текст са слике! Иако је текст писан латиницом Ви можете користити и <strong>ћирилицу</strong>! Није важно да ли пишете малим или великим словима.'); ?>
		</div>

	<div class="row">
		<?php echo $form->labelEx($modelForme,'verifyCode'); ?>
		<div class="captcha">
		<?php $this->widget('CCaptcha',array('buttonLabel'=>'<br/>'.Yii::t('biblioteka','Нови код'))); ?>
                <br/>
		<?php echo $form->textField($modelForme,'verifyCode'); ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="buttons">
		<?php echo CHtml::submitButton(Yii::t('biblioteka','Пошаљите коментар')); ?>
	</div>
    <div class="clear"></div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>
<?php endif; ?>