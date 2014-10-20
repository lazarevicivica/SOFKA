
<h1>Управљање збиркама</h1>



<?php 
    $this->layout = 'application.views.layouts.column1';
    $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'zbirka-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
        'summaryText'=>'Приказ {start}-{end} од укупно {count}',
	'columns'=>array(
		'id',
		array('name'=>'nazivSr', 'type'=>'html',  'value'=>'$data->getNazivSRHtml()'),
                array('name'=>'opisSr', 'type'=>'html',  'value'=>'$data->getOpisSRHtml()'),
		'url_slike',
		array(
			'class'=>'CButtonColumn',
                        'template' => '{update}',
		),            
	),
                'pager' => array('firstPageLabel'=>Yii::t('biblioteka', 'Прва'),
                          'prevPageLabel'=>Yii::t('biblioteka', '&lt; Претходна'),
                          'nextPageLabel'=>Yii::t('biblioteka', 'Следећа &gt;'),
                          'lastPageLabel'=>Yii::t('biblioteka', 'Последња'),
                          'header'       => Yii::t('biblioteka', 'Страна: '),)
)); ?>
