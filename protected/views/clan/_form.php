<div class="form-wide">

<?php
    $this->layout='application.views.layouts.column1';
    $form=$this->beginWidget('CActiveForm', array(
	'id'=>'clan-form',
	'enableAjaxValidation'=>false,
        'htmlOptions'=>array('enctype'=>'multipart/form-data'),
    ));
    $jezik = Helper::getAppjezikGoogle();
    $cs = Yii::app()->getClientScript();
    $cs->registerCSSFile(Helper::baseUrl('css/nova-objava.css'));
    $cs->registerCSSFile(Helper::baseUrl('css/nova-objava.css'));
    $cs->registerCSSFile(Helper::baseUrl('css/update-clan.css'));
    $cs->registerScriptFile(Helper::baseUrl('js/ckeditor/ckeditor.js'));
    $toolTip = Yii::t('biblioteka', 'Слика профила може бити типа jpg, png или gif. За најбоље резултате пошаљите слику квадратног облика, пошто ће бити исечена на димензије 50х50.');
    $pogresnaEkstenzija = Yii::t('biblioteka', 'Фајл који сте изабрали није одговарајућег типа. Молим Вас изаберите фајл који је типа jpg, png или gif!');
    $cs->registerScriptFile(Helper::baseUrl('js/stylefile/jquery.stylefile.js'));
    $cs->registerScript('_form_izmena_registracija_filestyle_js',"
    $(\"#fajlslika\").filestyle({
         image: \"/images/sajt/izbor.png\",
         imageheight : 26,
         imagewidth : 32,
         width : 200
     },'".$toolTip."','".$pogresnaEkstenzija."');
    ");

$cs->registerScript('clan_azuriranje_js',"
$(document).ready( function()
{
    CKEDITOR.replace('Clan_profil',
    {
        toolbar:
        [
            ['Source'],
            ['Undo','Redo'],
            ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
            ['Link','Unlink','Anchor'], ['Maximize']
        ],
        language:'$jezik',
        height: '640px'
    }
  );
});");
?>

<?php echo $form->errorSummary($model); ?>

<div id="levo">
    	<div class="row">
                <?php echo $form->error($model,'profil'); ?>
            
            <div style="text-align: left;">
                <?php echo $form->labelEx($model,'profil'); ?>
            </div>
		<?php echo $form->textArea($model,'profil'); ?>
	</div>
</div>

<div id="desno">
    <div class="kutija">
        <div class ="zaglavlje-kutije">
            <?php echo Yii::t('biblioteka', 'Основни подаци');?>
        </div>
        <div class="telo-kutije">
            <div class="row">
                    <?php echo $form->labelEx($model,'puno_ime'); ?>
                    <?php echo $form->textField($model,'puno_ime',array('size'=>50,'maxlength'=>50)); ?>
                    <?php echo $form->error($model,'puno_ime'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'email'); ?>
                    <?php echo $form->textField($model,'email',array('size'=>60,'maxlength'=>255)); ?>
                    <?php echo $form->error($model,'email'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'telefon'); ?>
                    <?php echo $form->textField($model,'telefon',array('size'=>60,'maxlength'=>255)); ?>
                    <?php echo $form->error($model,'telefon'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'sajt'); ?>
                    <?php echo $form->textField($model,'sajt',array('size'=>60,'maxlength'=>255)); ?>
                    <?php echo $form->error($model,'sajt'); ?>
            </div>


           <div class="row">
                <?php echo $form->labelEx($model,'fajlslika'); ?>
                <?php echo $form->fileField($model, 'fajlslika', array('title'=>Yii::t('biblioteka', 'Слика профила'), 'id'=>'fajlslika')); ?>
                <?php echo '<br>'.$form->error($model,'fajlslika'); ?>
            </div>

            <div class="row">                
                <?php echo $form->checkBox($model, 'licni_podaci', array('title'=>Yii::t('biblioteka', 'Слика профила'))); ?>
                <?php echo $form->labelEx($model,'licni_podaci'); ?>
                <?php echo '<br>'.$form->error($model,'licni_podaci'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'id_radno_mesto'); ?>
                    <?php
                        $radnaMesta = RadnoMesto::model()->with(array(
                                                          'ri18n'=>array(
                                                              'order'=>'i18n_radno_mesto.naziv',
                                                              'condition'=>'i18n_radno_mesto.id_jezik=' . Helper::ID_SRPSKI_JEZIK
                        )))->findAll();
                        echo $form->dropDownList($model, 'id_radno_mesto',
                                                CHtml::listData($radnaMesta,'id','naziv'),
                                                array('prompt'=>Yii::t('biblioteka','Изаберите радно место')));

                    ?>
                    <?php echo $form->error($model,'id_radno_mesto'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'id_odeljenje'); ?>
                    <?php
                        $odeljenja = Odeljenje::model()->with(
                                array(
                                    'ri18n'=>array(
                                        'order'=>'i18n_odeljenje.naziv',
                                        'condition'=>'t.id_vrsta_odeljenja='.Odeljenje::ORGANIZACIONA_JEDINICA.' AND i18n_odeljenje.id_jezik=' . Helper::ID_SRPSKI_JEZIK
                        )))->findAll();
                        echo $form->dropDownList($model, 'id_odeljenje',
                                                CHtml::listData($odeljenja,'id','naziv'),
                                                array('prompt'=>Yii::t('biblioteka','Изаберите одељење')));
                    ?>
                    <?php echo $form->error($model,'id_odeljenje'); ?>
            </div>        
        </div>
    </div>
    
    
    <div class="kutija">
        <div class ="zaglavlje-kutije plavo">
            <?php echo Yii::t('biblioteka', 'Промена лозинке');?>
        </div>
        <div class="telo-kutije">
            
                <div class="row">
                        <?php echo $form->labelEx($model,'staraLozinka'); ?>
                        <?php echo $form->passwordField($model,'staraLozinka',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'staraLozinka'); ?>
                </div>

                <div class="row">
                        <?php echo $form->labelEx($model,'novaLozinka'); ?>
                        <?php echo $form->passwordField($model,'novaLozinka',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'novaLozinka'); ?>
                </div>

                <div class="row">
                        <?php echo $form->labelEx($model,'ponovljenaLozinka'); ?>
                        <?php echo $form->passwordField($model,'ponovljenaLozinka',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'ponovljenaLozinka'); ?>
                </div>  
        </div>
    </div>
    <div class="row buttons">
            <?php echo CHtml::submitButton(Yii::t('biblioteka', 'Сачувај')); ?>
    </div>
</div>

<?php $this->endWidget(); ?>

</div><!-- form -->