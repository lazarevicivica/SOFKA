<?php 

//if($this->beginCache('objava/index')) { 
//
//TODO ukljuciti i informaciju o broju stranice i svim ostalim parametrima
//verovatno bi bilo dobro da se ukljuci i dependancy, kako bi se kes osvezio svaki put kad se doda nova stranica
    if(isset($pageTitle))
        $this->pageTitle = $pageTitle;
    $naslovna = isset($naslovna)? $naslovna : false;
    $summaryText = isset($summaryText) ? $summaryText : Yii::t('biblioteka', '{start}-{end} од укупно {count}');
    $summaryText = '';
    if( empty($naslovna) && !empty($naslov))
            echo $naslov;
    if( ! empty($naslovna))
    {
        echo '<h1>'.Yii::t('biblioteka', 'Дешавања у Библиотеци').'</h1>';
        echo '<hr/>';
    }
    $this->widget('zii.widgets.CListView', array(
        'dataProvider'=>$dataProvider,
        'ajaxUpdate'=>false,
        'itemView'=>'//objava/_view',
        'template' => '{summary}{sorter}{items}{pager}',
        'summaryText'=>$summaryText, //,
        'emptyText' =>'',// Yii::t('biblioteka', ''),
        'pager' => array('firstPageLabel'=>Yii::t('biblioteka', '&lt;&lt;'),
                         'prevPageLabel' =>Yii::t('biblioteka', '&lt;'),
                         'nextPageLabel' =>Yii::t('biblioteka', '&gt;'),
                         'lastPageLabel' =>Yii::t('biblioteka', '&gt;&gt;'),
                         'header'        =>Yii::t('biblioteka', 'Страна: '),
                         'cssFile' => Helper::baseUrl('css/pager.css'),

    )));
   
//$this->endCache(); } 


?>