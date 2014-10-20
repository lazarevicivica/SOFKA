<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_jezik_originala')); ?>:</b>
	<?php echo CHtml::encode($data->id_jezik_originala); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('korisnicko_ime')); ?>:</b>
	<?php echo CHtml::encode($data->korisnicko_ime); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('lozinka')); ?>:</b>
	<?php echo CHtml::encode($data->lozinka); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('puno_ime')); ?>:</b>
	<?php echo CHtml::encode($data->puno_ime); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('tip')); ?>:</b>
	<?php echo CHtml::encode($data->tip); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_radno_mesto')); ?>:</b>
	<?php echo CHtml::encode($data->id_radno_mesto); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('id_odeljenje')); ?>:</b>
	<?php echo CHtml::encode($data->id_odeljenje); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('aktivan')); ?>:</b>
	<?php echo CHtml::encode($data->aktivan); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('slika')); ?>:</b>
	<?php echo CHtml::encode($data->slika); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('email')); ?>:</b>
	<?php echo CHtml::encode($data->email); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('telefon')); ?>:</b>
	<?php echo CHtml::encode($data->telefon); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('sajt')); ?>:</b>
	<?php echo CHtml::encode($data->sajt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('uloga')); ?>:</b>
	<?php echo CHtml::encode($data->uloga); ?>
	<br />

	*/ ?>

</div>