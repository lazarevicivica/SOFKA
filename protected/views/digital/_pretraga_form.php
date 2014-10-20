<?php 

    $form = $this->beginWidget('CActiveForm', array(
            'id'=>'pretraga-form',
            'enableAjaxValidation'=>true,
        )); 

    $cs = Yii::app()->getClientScript();
    $cs->registerScript('_pretraga_form_js',"var global_upit='$frmPretraga->ftsUpit';", CClientScript::POS_HEAD );
    
    if( ! isset($animacija) || $animacija === true)
        $animacija = 'class="animacija"';
    else
        $animacija = '';
?>
<div id="dijalog-naslov"<?php echo $animacija;?>> <a name="a-pretraga"> <?php echo Yii::t('biblioteka', 'Претрага');?></a></div>
<?php
    echo $form->errorSummary($frmPretraga);
    echo $form->hiddenField($frmPretraga, 'idKnjiga'); 
?>
<div id="upit-kontejner">
    <div id="upit">
        <table>
            <tr>
                <td id="labela-pretraga" colspan="2">
                    <?php echo $form->labelEx($frmPretraga,'ftsUpit'); ?>                
                </td>
            </tr>
            <tr>
                    <td id="polje-upit-td">
                        <?php echo $form->textField($frmPretraga, 'ftsUpit'); ?>               
                        <?php echo $form->error($frmPretraga,'ftsUpit'); ?>
                    </td>
                    <td id="dugme-trazi-td">
                        <?php echo CHtml::submitButton(Yii::t('biblioteka', 'Тражи'), array('id'=>'dugme-trazi', 'class'=>'dugmad')); ?>	
                    </td>

            </tr>
               
            <tr>
                <td id="operatori" colspan="2">
                    <?php echo $form->labelEx($frmPretraga,'operator'); ?>
                    <?php echo $form->radioButtonList(
                            $frmPretraga, 
                            'operator',
                            array('i' => Yii::t('biblioteka', 'све речи'), 'ili' => Yii::t('biblioteka', 'било коју реч из упита')), 
                            array('separator'=>' ')
                        ); 
                    ?>               
                    <?php echo $form->error($frmPretraga,'operator'); ?>
                </td>
            </tr>
        
        </table>
    </div>    
</div>

<?php $this->endWidget(); ?>
<div class="clear"></div>