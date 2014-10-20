<?php $this->renderPartial('//objava/_admin_zaglavlje');?>
<h1>
    <?php echo Yii::t('biblioteka', 'Измена објаве'); ?>
    <span class="right ispod-naslova">
        <?php
            if($objava->rclan)
                echo Yii::t('biblioteka', 'Аутор: '). $objava->rclan->getKorisnickoImeHtml(). ' ';
            $objavljeno = $objava->getStatusTxt();
            echo CHtml::image($objava->getStatusImg(), $objavljeno, array('title'=>$objavljeno)).' ';
            $zakljucano = $objava->getZakljucanoTxt();
            echo CHtml::image($objava->getZakljucanoImg(), $objavljeno, array('title'=>$zakljucano))
        ?>
    </span>
</h1>

<?php echo $this->renderPartial('//objava/_form', array('objava'=>$objava, 'odeljci'=>$odeljci, 'galerija'=>$galerija, 'novo'=>false));?>