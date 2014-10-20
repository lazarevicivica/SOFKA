<?php $this->beginContent('//layouts/slova_main'); ?>

<div class="container">

	 <div class="span-17">         
            <div id="content">
                    <?php echo $content; ?>
            </div><!-- content -->
	</div>
	<div class="span-7 last">         
            <div id="sidebar">
		<?php $this->prikaziPortlete();?>
            </div>
	</div>
        <div class="clear"></div>
</div>
<?php $this->endContent(); ?>