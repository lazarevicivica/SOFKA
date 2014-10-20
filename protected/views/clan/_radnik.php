<div class="radnik">
    
    <?php
        $prikaziSliku = $radnik->licni_podaci;
        $prikaziBiografiju = $radnik->licni_podaci && ! empty($radnik->profil);
    ?>

    <?php if($prikaziSliku):?>
    <div class="komentar-slika left">
        <?php echo CHtml::image($radnik->getslika());?>
    </div>
    <?php endif;?>

    <div class="right">
        <div class="podaci">
            <h3>
                <?php

                    $ime = $radnik->getImeZaPrikaz();
                    if(Helper::getAppjezikId() !== Helper::ID_SRPSKI_JEZIK)
                        $ime = Helper::cir2lat ($ime);
                    echo $ime;
                ?>
            </h3>
            <?php if($radnik->rradno_mesto)
                    echo CHtml::encode($radnik->rradno_mesto->naziv); ?>
            <br/>
            <?php
                if($radnik->telefon)
                    echo CHtml::encode($radnik->telefon);
                if($radnik->email)
                {
                    echo ', ';
                    echo ' <a href="mailto:'.$radnik->email.'">'.CHtml::encode($radnik->email).'</a>';
                }
            ?>
        </div>
<?php if($prikaziBiografiju):?>
        <div id="prikazi-biografiju_<?php echo $radnik->id;?>" class="prikazi-biografiju" title="<?php echo Yii::t('biblioteka', 'Биографија');?>">
            <?php
                 $cs = Yii::app()->getClientScript();
                 $cs->registerScript('biografija_js', '
$(document).ready(function()
{
    $(".prikazi-biografiju").click(function()
    {
        var strelica = $(this);
        var prviDeo = "prikazi-biografiju_";
        var id = strelica[0].id.substring(prviDeo.length);
        var biografija = $("#biografija_"+id);
        if( $(this).hasClass("gore"))
        {
            strelica.removeClass("gore");
            biografija.addClass("skriveno");
        }
        else
        {
            strelica.addClass("gore");
            biografija.removeClass("skriveno");
        }
    });
});
');?>
        </div>
<?php endif;?>
    </div>
    <div class="clear"></div>
<?php if($prikaziBiografiju):?>
    <div id="biografija_<?php echo $radnik->id;?>" class="biografija skriveno">
        <?php echo $radnik->profil;?>
    </div>
<?php endif;?>
</div>
