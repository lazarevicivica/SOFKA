<?php
$this->breadcrumbs=array(
	'clans'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List clan', 'url'=>array('index')),
	array('label'=>'Manage clan', 'url'=>array('admin')),
);
?>

<h1>Create clan</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>