<?php
$this->breadcrumbs=array(
	'Књиге'=>array('index'),
	'Нова',
);

$this->menu=array(
	array('label'=>'Листа свих књига', 'url'=>array('index')),
	array('label'=>'Управљање књигама', 'url'=>array('admin')),
);
?>

<h1>Нова књига</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>