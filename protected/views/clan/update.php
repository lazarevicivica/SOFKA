<?php $this->renderPartial('//objava/_admin_zaglavlje');?>

<div id="zaglavlje_profila">
    <?php echo CHtml::image($model->getslika().'?'.time());?>
    <h1 class="naslov_profila">
        <?php
            echo $model->getImeZaPrikaz();
        ?>        
    </h1>
</div>
<div class="clear"></div>
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>