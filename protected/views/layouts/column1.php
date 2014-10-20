<?php $this->beginContent('//layouts/main'); ?>
<div class="container">
        <?php if(Yii::app()->user->hasFlash('greska-ispod-zaglavlja')):?>
            <div class="flash-error">
                <?php echo Yii::app()->user->getFlash('greska-ispod-zaglavlja'); ?>
            </div>
        <?php endif;?>
    
	<div id="content">
		<?php echo $content; ?>
	</div><!-- content -->
</div>
<?php $this->endContent(); ?>