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
$.fn.yiiGridView.update('objava-grid', {
        type:'POST',
        url:$(this).attr('href'),
        data:{ 'YII_CSRF_TOKEN':'$csrf' },
        success:function(data) {
                $.fn.yiiGridView.update('objava-grid');
        }/*,
        error:function(XHR) {
                return afterDelete(th,false,XHR);
        }*/
});
}";

        $id_clan = $clan->id;
        $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'objava-grid',
        'ajaxUpdate'=>false,
	'dataProvider'=>$dataProvider,
        'summaryText'=>Yii::t('biblioteka', 'Објаве {start}-{end} од укупно {count}'),
        'emptyText' => Yii::t('biblioteka', 'Ни једна објава не задовољава критеријуме.'),
	'filter'=>$model,
        'selectableRows' => false,
	'columns'=>array(
                //'id',
                array('name'=>'naslovSr', 'type'=>'html',  'value'=>'$data->getNaslovSRHtml()'),
                //array('name'=>'naslovEn', 'filter'=>false, 'type'=>'html', 'value'=>'$data->getNaslovENHtml()'),
                array('type'=>'html','name'=>'datum', 'value'=>'"<strong>".date("d.m.Y.", $data->datum)."</strong>"'),
                array('name'=>'autor','value'=>'CHtml::encode($data->getAutor_())'),
		array('name'=>'status',
                      'htmlOptions'=>array('width'=>'30'),
                      'type'=>'html',
                      'filter'=> $nezavrsene ? false : array(Objava::OBJAVLJENO=>Yii::t('biblioteka','објављено'), Objava::CEKA_ODOBRENJE=>Yii::t('biblioteka','чека одобрење'), Objava::OTPAD=>Yii::t('biblioteka','отпад')),
                      'value'=> '"<img src=\"" . $data->getStatusImg() . "\" title=\"" . $data->getStatusTxt() . "\"/>"'
                ),
		array('name'=>'zakljucano',
                      'htmlOptions'=>array('width'=>'30'),
                      'type'=>'html',
                      'filter'=> array(1=>Yii::t('biblioteka','закључано'), 0=>Yii::t('biblioteka','откључано')),
                      'value'=> '"<span class=\"veliko\">".$data->getBrojkomentara()." </span><img src=\"" . $data->getZakljucanoImg() . "\" title=\"" . $data->getZakljucanoTxt() . "\"/>"'
                ),
                
                array('name'=>'odeljci',
                      'filter' => $clan->getNizIdOdeljkaNaziv(),
                      'value'=>'$data->getOdeljciTxt()'
                ),
                array('name'=>'tagovi', 'value'=>'$data->getStrtagovi()'),
		array(
                    'class'=>'CButtonColumn',
                    'template' => '{view} {update} {prevod} {objava} {cekanje} {otpad} {zakljucaj} {otkljucaj}', //{delete}
                    'deleteConfirmation' => Yii::t('biblioteka', 'Да ли сте сигурни да желите физчки да избришете објаву?'),
                    'buttons' => array(                        
                        'delete' => array(
                            'label' => Yii::t('biblioteka', 'Физичко брисање! Команда се не може поништити!'),
                            'visible' => '$data->mozeDaBrise(Clan::getclan('.$id_clan.'))',
                            'imageUrl' => Helper::baseUrl('images/sajt/brisanje.gif'),                            
                        ),  
                        'update' => array(
                            'label' => Yii::t('biblioteka', 'Измена главног текста'),
                            'visible' => '$data->mozeDaMenja(Clan::getclan('.$id_clan.'))',
                            'imageUrl' => Helper::baseUrl('images/sajt/izmena.gif'),
                        ),
                        'prevod' => array(
                            'label' => Yii::t('biblioteka', 'Уређивање превода'),
                            'visible' => '$data->mozeDaPrevodi(Clan::getclan('.$id_clan.'))',
                            'imageUrl' => Helper::baseUrl('images/sajt/prevod.gif'),
                        ),
                        'objava' => array(
                            'label' => Yii::t('biblioteka', 'Објављивање'),
                            'imageUrl' => Helper::baseUrl('images/sajt/objavljeno.gif'),
                            'visible' => '$data->mozeDaObjavi(Clan::getclan('.$id_clan.')) && ($data->status != Objava::DRAFT)',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("objava/objavi", null, array("id"=>$data->id))',
                        ),
                        'cekanje' => array(
                            'label' => Yii::t('biblioteka', 'Постављање статуса "чека на одобрење"'),
                            'imageUrl' => Helper::baseUrl('images/sajt/ceka.gif'),
                            'visible' => '$data->mozeDaStaviNaCekanje(Clan::getclan('.$id_clan.')) && ($data->status != Objava::DRAFT)',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("objava/cekaOdobrenje", null, array("id"=>$data->id))',
                        ),
                        'otpad' => array(
                            'label' => Yii::t('biblioteka', 'Слање у отпад'),
                            'imageUrl' => Helper::baseUrl('images/sajt/otpad.gif'),
                            'visible' => '$data->mozeDaPosaljeUOtpad(Clan::getclan('.$id_clan.'))',
                            'click' => $funkcija,
                            'url' => 'Helper::createI18nUrl("objava/otpad", null, array("id"=>$data->id))',
                        ),
                        'view' => array(
                             'visible' => '$data->status != Objava::DRAFT', 
                             'label' => Yii::t('biblioteka', 'Преглед'),
                             'imageUrl' => Helper::baseUrl('images/sajt/pregled.gif'),
                        ),
                        'zakljucaj' => array(
                             'label' => Yii::t('biblioteka', 'Закључај'),
                             'imageUrl' => Helper::baseUrl('images/sajt/zakljucaj.png'),
                             'visible' => '$data->mozeDaZakljuca(Clan::getclan('.$id_clan.'))',
                             'click' => $funkcija,
                             'url' => 'Helper::createI18nUrl("objava/zakljucaj", null, array("id"=>$data->id))',
                        ),
                        'otkljucaj' => array(
                             'label' => Yii::t('biblioteka', 'Oткључај'),
                             'imageUrl' => Helper::baseUrl('images/sajt/otkljucaj.png'),
                             'visible' => '$data->mozeDaOtkljuca(Clan::getclan('.$id_clan.'))',
                             'click' => $funkcija,
                             'url' => 'Helper::createI18nUrl("objava/otkljucaj", null, array("id"=>$data->id))',
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
