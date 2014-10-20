<?php

    if( ! empty($zaglavlje))
        echo $zaglavlje;
    if(isset($pageTitle))
        $this->pageTitle = $pageTitle;
    $naslovna = isset($naslovna)? $naslovna : false;
    $summaryText = /*isset($summaryText) ? $summaryText : */Yii::t('biblioteka', '{start}-{end} од укупно {count}');
    //$summaryText = '';
    if( empty($naslovna) && !empty($naslov))
            echo $naslov;
    $this->widget('zii.widgets.CListView', array(
        'dataProvider'=>$dataProvider,
        'ajaxUpdate'=>false,
        'sorterHeader'=>Yii::t('biblioteka', 'Редослед:'),
        'template' => '{summary} {sorter} {items} {pager}' ,
        'sortableAttributes' => array('rang', 'datum','naslov', 'autor',),
        'itemView'=>'//objava/_view',
        'summaryText'=>$summaryText, //,
        'emptyText' =>Yii::t('biblioteka', 'Не постоји ни једна објава за задату претрагу.'),
        'pager' => array('firstPageLabel'=>Yii::t('biblioteka', '&lt;&lt;'),
                         'prevPageLabel' =>Yii::t('biblioteka', '&lt;'),
                         'nextPageLabel' =>Yii::t('biblioteka', '&gt;'),
                         'lastPageLabel' =>Yii::t('biblioteka', '&gt;&gt;'),
                         'header'        =>Yii::t('biblioteka', 'Страна: '),
                         'cssFile' => Helper::baseUrl('css/pager.css'),

    )));