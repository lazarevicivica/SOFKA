<?php

$this->renderPartial('//objava/_admin_zaglavlje');
$this->layout='application.views.layouts.column1';
?>
<h1><?php echo $adminNaslov;?></h1>
<?php
        $csrf = $csrf = Yii::app()->request->csrfToken;
        $funkcija =
"function(e){
e.preventDefault();
var th=this;
$.fn.yiiGridView.update('komentar-grid', {
        type:'POST',
        url:$(this).attr('href'),
        data:{ 'YII_CSRF_TOKEN':'$csrf' },
        success:function(data) {
                $.fn.yiiGridView.update('komentar-grid');
        }/*,
        error:function(XHR) {
                return afterDelete(th,false,XHR);
        }*/
});
}";

        $id_clan = $clan->id;
        $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'komentar-grid',
        'ajaxUpdate'=>false,
	'dataProvider'=>$dataProvider,
        'summaryText'=>Yii::t('biblioteka', 'Коментари {start}-{end} од укупно {count}'),
        'emptyText' => Yii::t('biblioteka', 'Ни један коментар не задовољава услове.'),
	'filter'=>$model,
        'selectableRows' => false,
	'columns'=>array(
                array('name'=>'naslovSr', 'type'=>'html', 'value'=>'$data->getNaslovSRHtml()'),
                array('name'=>'datum',    'type'=>'html', 'htmlOptions'=>array('width'=>'100'), 'value'=>'"<strong>".date("d.m.Y.", $data->datum)."</strong>"'),
                array('name'=>'autor',    'type'=>'html', 'htmlOptions'=>array('width'=>'50'), 'value'=>'$data->getKomentatorZaGridHtml()."<br/>".$data->getAutorEmail()'),
		array('name'=>'status',
                      'type'=>'html',
                      'htmlOptions'=>array('width'=>'30'),
                      'filter'=> array(Komentar::OBJAVLJENO=>Yii::t('biblioteka','објављено'), Komentar::CEKA_ODOBRENJE=>Yii::t('biblioteka','чека одобрење'), Komentar::OTPAD=>Yii::t('biblioteka','отпад')),
                      'value'=> '"<img src=\"" . $data->getStatusImg() . "\" title=\"" . $data->getStatusTxt() . "\"/>"'
                ),
                array('name'=>'txt', 'type'=>'html', 'value'=>'$data->getTekst()'),
		array(
                    'class'=>'CButtonColumn',
                    'template' => '{objava} {cekanje} {otpad} {delete}',
                    'deleteConfirmation' => Yii::t('biblioteka', 'Да ли сте сигурни да желите физчки да избришете објаву?'),
                    'buttons' => array(                        
                        'delete' => array(
                            'label' => Yii::t('biblioteka', 'Физичко брисање! Команда се не може поништити!'),
                            'visible' => '$data->mozeDaBrise(Clan::getclan('.$id_clan.'))',
                            'imageUrl' => Helper::baseUrl('images/sajt/brisanje.gif'),                            
                        ),  
                        'objava' => array(
                            'label' => Yii::t('biblioteka', 'Објављивање'),
                            'imageUrl' => Helper::baseUrl('images/sajt/objavljeno.gif'),
                            'visible' => '$data->mozeDaObjavi(Clan::getclan('.$id_clan.')) && ($data->status != Objava::DRAFT)',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("komentar/objavi", null, array("id"=>$data->id))',
                        ),
                        'otpad' => array(
                            'label' => Yii::t('biblioteka', 'Слање у отпад'),
                            'imageUrl' => Helper::baseUrl('images/sajt/otpad.gif'),
                            'visible' => '$data->mozeDaPosaljeUOtpad(Clan::getclan('.$id_clan.'))',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("komentar/otpad", null, array("id"=>$data->id))',
                        ),
                        'cekanje' => array(
                            'label' => Yii::t('biblioteka', 'Постављање статуса "чека на одобрење"'),
                            'imageUrl' => Helper::baseUrl('images/sajt/ceka.gif'),
                            'visible' => '$data->mozeDaStaviNaCekanje(Clan::getclan('.$id_clan.'))',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("komentar/cekaOdobrenje", null, array("id"=>$data->id))',
                        ),
                    ),
                            
		),
	),
        'pager' => array('firstPageLabel'=>Yii::t('biblioteka', 'Прва'),
                          'prevPageLabel'=>Yii::t('biblioteka', '&lt; Претходна'),
                          'nextPageLabel'=>Yii::t('biblioteka', 'Следећа &gt;'),
                          'lastPageLabel'=>Yii::t('biblioteka', 'Последња'),
                          'header'       => Yii::t('biblioteka', 'Страна: '),
        ),


));
?>
