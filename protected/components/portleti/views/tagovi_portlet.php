<div id="portlet_<?php echo $id_odeljak;?>" class="portlet dropshadow">
<div class="portlet-title">
    <?php echo $this->title?>
</div>
<div class="portlet-content">
        <?php $n = count($tagovi);?>
        <?php for($i=0; $i<$n; $i++):?>
            <?php $tag = $tagovi[$i];?>
            <?php
                if($id_odeljak === Odeljak::ID_DIGITALNA_BIBLIOTEKA)
                    $url = Knjiga::getTagUrl($tag, $osnovniUrl);                
                else
                    $url = Tag::getUrlZaodeljak($tag, $id_odeljak, $nazivOdeljka);
            ?>
            <a style="font-size: <?php echo $tag['tezina'];?>px;margin-right:5px;" href="<?php echo $url?>"><?php echo $tag['naziv']."({$tag['ucestalost']})"; if($i<$n-1) echo ','?></a>
        <?php endfor;?>
</div>

</div>
