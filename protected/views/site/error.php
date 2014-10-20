<?php $this->pageTitle=Yii::app()->name . ' - '. Yii::t('biblioteka', 'Грешка ');?>

<h2><?php echo Yii::t('biblioteka', 'Грешка '); echo $code; ?></h2>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>