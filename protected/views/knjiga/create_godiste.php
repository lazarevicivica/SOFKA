<h3>Упис групе публикација</h3>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'knjiga-form',
	'enableAjaxValidation'=>false,
)); 
    $url = Yii::app()->createUrl('zbirka/ajaxZbirkeIz');
    $csrf = Yii::app()->request->csrfToken;
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile(Helper::baseUrl('js/zbirka.js'));
    $cs->registerCSSFile(Helper::baseUrl('css/knjiga.css'));
    $cs->registerCSSFile(Helper::baseUrl('css/digital.css'));
    $cs->registerScript('knjiga_js',"
$(document).ready( function()
{
   zbirka('$url', '$csrf', '#NovineGodisteForm_id_zbirka');
});");
    
    if($model->id_zbirka)
        $naziv_zbirke = Zbirka::model()->findByPk($model->id_zbirka)->naziv_zbirke;
    else
        $naziv_zbirke = '';
    
?>

        <p class="note">Користи се за упис серијских публикација за одређену годину. На основу назива фолдера
        програм покушава да разврста публикације у збирке испод изабраног родитеља. Пример фолдера који садржи публикацију (болдирани део)
        <strong>/var/www/digital.jabooka.org.rs/novi-put/1945/</strong>12.12.1945.-Novi-put-br.-50. Фолдер публикације (у овом примеру 12.12.1945.-Novi-put-br.-50.) треба да 
        садржи подфолдере са раличитим квалитетом слика као нпр. thu, min, mid (минијатурне - thu су обавезне).</p>

	<?php echo $form->errorSummary($model); ?>

        <?php echo $form->hiddenField($model,'id_zbirka',array('size'=>60,'maxlength'=>100)); ?>
        
        <div style="margin-bottom:20px;" class="row">
            <strong>Родитељ:</strong> <span style="width:100px;" id="labela_roditelj"><?php echo $naziv_zbirke;?></span>
        </div>

        <div class="row">
		<?php echo $form->labelEx($model,'lokalniFolder'); ?>
		<?php echo $form->textField($model,'lokalniFolder',array('size'=>60)); ?>
		<?php echo $form->error($model,'lokalniFolder'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'webPutanja'); ?>
		<?php echo $form->textField($model,'webPutanja',array('size'=>60)); ?>
		<?php echo $form->error($model,'webPutanja'); ?>
	</div>
        
        <div class="row">
		<?php echo $form->labelEx($model,'id_vrsta_gradje'); ?>
		<?php echo $form->dropDownList($model, 'id_vrsta_gradje', CHtml::listData(VrstaGradje::model()->findAll(),'id','naziv_vrste')); ?>
		<?php echo $form->error($model,'id_vrsta_gradje'); ?>
	</div>
        
	<div class="row">
		<?php echo $form->labelEx($model,'kljucneReci'); ?>
		<?php echo $form->textField($model,'kljucneReci',array('size'=>60)); ?>
		<?php echo $form->error($model,'kljucneReci'); ?>
	</div>        

	<div class="row">
		<?php echo $form->labelEx($model,'folderKvalitet'); ?>
		<?php echo $form->textField($model,'folderKvalitet',array('size'=>60)); ?>
		<?php echo $form->error($model,'folderKvalitet'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'thuFolder'); ?>
		<?php echo $form->textField($model,'thuFolder',array('size'=>60)); ?>
		<?php echo $form->error($model,'thuFolder'); ?>
	</div>   
        
        <div class="row">
		<?php echo $form->labelEx($model,'preskociBezDatuma'); ?>
		<?php echo $form->checkBox($model,'preskociBezDatuma'); ?>
	</div>
         
        <div style="clear:both;"></div>
	<div class="row buttons">
		<?php echo CHtml::submitButton('Упиши публикације'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->