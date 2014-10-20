<?php
$this->breadcrumbs=array(
	'clans'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List clan', 'url'=>array('index')),
	array('label'=>'Create clan', 'url'=>array('create')),
	array('label'=>'Update clan', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete clan', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage clan', 'url'=>array('admin')),
);
?>

<h1>View clan #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'id_jezik_originala',
		'korisnicko_ime',
		'lozinka',
		'puno_ime',
		'tip',
		'id_radno_mesto',
		'id_odeljenje',
		'aktivan',
		'slika',
		'email',
		'telefon',
		'sajt',
		'uloga',
	),
)); ?>
