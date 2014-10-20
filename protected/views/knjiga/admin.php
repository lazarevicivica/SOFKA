<?php
$this->layout = 'application.views.layouts.column1';
?>

<h1>Управљање књигама</h1>


<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'knjiga-grid',
	'dataProvider'=>$model->search(),
        'summaryText'=>'Приказ {start}-{end} од укупно {count}',
	'filter'=>$model,
	'columns'=>array(
		'naslov',
		'autor',		
		'inv_br',
		'izdanje',
                'godina',
                'mesec',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
