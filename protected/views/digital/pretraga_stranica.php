<?php
//ulaz: $data, $ajax
    $provajder = $frmPretraga->trazi();
    /*if( ! empty($brRezultataPoStrani))
        $provajder->pagination->pageSize = $brRezultataPoStrani;*/
    $this->widget('zii.widgets.CListView', array(
        'id' => 'lista-rezultat',
        'dataProvider'=>$provajder,
        'ajaxUpdate'=> 'lista-rezultat',
        
        'beforeAjaxUpdate' => 'function(id,data){$("#polje-upit-td").addClass("animacija");}',
        'afterAjaxUpdate' => 'function(id,data){$("#polje-upit-td").removeClass("animacija");}',
        
        'sorterHeader'=>Yii::t('biblioteka', 'Редослед:'),
        'template' => '{sorter} {items} {pager}' ,
        //'viewData' => array('ftsUpit' => ( ! empty($frmModel->ftsUpit)? $frmModel->ftsUpit : false )), // ftsUpit ili false
        'viewData' => array('frmPretraga'=>$frmPretraga,'idKnjiga' => $frmPretraga->idKnjiga, 'indeksPrveStranice'=>$frmPretraga->indeksPrveStranice),
        'sortableAttributes' => array('broj', 'rang', ),
        'itemView'=> '//digital/_stranica',
        'summaryText'=>Yii::t('biblioteka','Приказ {start}-{end} од укупно {count}'),
        'emptyText' => Yii::t('biblioteka', 'Не постоји ни једна страница која задовољава дате услове!'),
        'pager' => array('class'=>'CLinkPager', 'firstPageLabel'=>Yii::t('biblioteka', '&lt;&lt;'),
                                                'prevPageLabel'=>Yii::t('biblioteka', '&lt;'),
                                                'nextPageLabel'=>Yii::t('biblioteka', '&gt;'),
                                                'lastPageLabel'=>Yii::t('biblioteka', '&gt;&gt;'),
                                                'header'       => Yii::t('biblioteka', ''), 
            ),
        )
    );
?>
