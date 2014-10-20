<?php
$this->breadcrumbs=array(
	'Књиге'=>array('index'),
	'Ажурирање књиге',
);

$this->menu=array(
	array('label'=>'Листа књига', 'url'=>array('index')),
	array('label'=>'Нова књига', 'url'=>array('create')),
	array('label'=>'Преглед података', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Управљање књигама', 'url'=>array('admin')),
);
?>

<h1>Ажурирање књиге #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>