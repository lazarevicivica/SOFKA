<?php

class PretragaController extends Controller
{
    public function actionIndex($upit='')
    {
        $model = new PretragaForm;        
        $model->ftsUpit = $upit;
        $dp = $model->trazi();        
        $upit = CHtml::encode($model->ftsUpit);
        $naslov = '<h1>'.Yii::t('biblioteka', 'Претрага').'</h1><p class="iznad-h">'.Yii::t('biblioteka', 'Резултат претраге за')." <span><strong>$upit</strong></span>:</p>";        

        $this->render('//objava/pretraga',array('dataProvider'=>$dp, 'naslov' => $naslov));
    }
}