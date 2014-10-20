<?php 
    
    $id = $data['id'];
    $aktivnaCitanka = ! empty($data['json_desc']);
    $aktivnaPretraga = ! empty($data['sadrzi_indeks']);
    $deoStranice = ! empty($deoStranice);
    $urlCitanka = Helper::baseUrl("citanka/knjiga.php?id=$id");
    
    if( ! empty($deoStranice))
    {
        if($aktivnaCitanka)
            $url = $urlCitanka;
        else 
            $url = false;
    }
    else //Ako se prikazuje u okviru liste
    {
        $rep = Helper::getSEOText($data['naslov']);
        if( ! empty($ftsUpit))
            $url = Helper::createI18nUrl("knjiga/view", null, array('id'=>$data['id_objava'],'rep'=>$rep, 'upit'=>$ftsUpit));
        else 
            $url = Helper::createI18nUrl("knjiga/view", null, array('id'=>$data['id_objava'],'rep'=>$rep));
    }
    if(empty($index))
        $index = 0;
?>

<?php 
    $itemCount = $widget->dataProvider->itemCount;    
    $kompletKlasa = (($index+1) % 4 ? 'nije-poslednja' : 'poslednja-u-redu');
?>

<div id="knjiga_<?php echo $id;?>" class="knjiga-komplet <?php echo $kompletKlasa?>">

    <div class="wraptocenter">
        <span></span><a href="<?php echo $url;?>"><img class="slika-knjige" src="<?php echo $data['url_slike'];?>"/></a>
    </div>   
    
    <?php if( $aktivnaPretraga || $aktivnaCitanka):?>
        <?php
            $padding =  0;
            if($aktivnaPretraga && $aktivnaCitanka)
                $padding = 42;
            else 
                $padding = 57;

        ?>
        <div style="padding-left:<?php echo $padding;?>px;height:32px;width:<?php echo 147-$padding?>px;">
        <?php if($aktivnaPretraga):?>            
            <?php
                if( ! $deoStranice)
                {
                    $klasaPretrage = 'pretraga-stranica knjiga-pretraga';
                    $urlPretraga = Helper::createI18nUrl('digital/stranice', null, array('idKnjiga'=>$id, 'ftsUpit'=>$ftsUpit));
                }
                else 
                {
                    $klasaPretrage = 'pretraga-deo-stranice';
                    $urlPretraga = '#';
                }
            ?>            
            <a style="" title="<?php echo Yii::t('biblioteka', 'Претрага');?>" id="pretraga_<?php echo $id;?>" class="<?php echo $klasaPretrage;?>" href="<?php echo $urlPretraga;?>"></a>          
        <?php endif;?>
        <?php if($aktivnaCitanka):?>
            <a title="<?php echo Yii::t('biblioteka', 'Преглед');?>" class="citanka knjiga-citanka" href="<?php echo $urlCitanka;?>"></a>  
        <?php endif;?>
        </div>
    <?php endif;?>   
    
    <div class="naslov_<?php echo $id;?> naslov-knjige">
        <a id="a-naslov_<?php echo $id;?>" href="<?php echo $url;?>">        
            <?php echo CHtml::encode($data['naslov']); ?>
        </a>  

        <?php if($data['autor']):?>
            <div class="autor-knjige autor-knjige-data">        
                <?php echo CHtml::encode($data['autor']); ?>
            </div>
         <?php endif;?>                              
    </div>        
</div>



<div  class="tooltip tooltip-knjiga_<?php echo $id;?> dropshadow">
    <table>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Наслов:');?></strong></td>
            <td><strong><?php echo CHtml::encode($data['naslov']);?></strong></td>
        </tr>
        <?php if($data['autor']):?>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Аутор:');?></strong></td>
            <td><?php echo CHtml::encode($data['autor']);?></td>
        </tr>
        <?php endif;?>    
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Збирка:');?></strong></td>
            <td><a href="<?php echo Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$data['id_zbirka'], 'naziv' => Helper::getSEOText($data['naziv_zbirke'])));?>"><?php echo CHtml::encode($data['naziv_zbirke']);?></a></td>
        </tr>
        <?php if($data['izdanje']):?>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Издање:');?></strong></td>
            <td><?php echo CHtml::encode($data['izdanje']);?></td>
        </tr>
        <?php endif;?>  
        <?php if($data['dan'] || $data['mesec'] || $data['godina']):?>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Датум:');?></strong></td>
            <td><?php 
                    $datum = '';
                    $dan = $data['dan'] ? $data['dan'] : '';
                    $mesec = $data['mesec'] ? $data['mesec'] : '';
                    if($dan)
                        $datum = $dan . '.' . ($mesec ? $mesec : '??') . '.';
                    else
                        $datum = $mesec ? $mesec.'.' : '';
                    $godina = $data['godina'] ? $data['godina'] : '????';
                    $datum .= $godina . '.';                    
                    echo CHtml::encode($datum);
                 ?>
            </td>
        </tr>
        <?php endif;?>  
        <?php if($data['br_pregleda']):?>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Прегледа:');?></strong></td>
            <td><?php echo CHtml::encode($data['br_pregleda']);?></td>
        </tr>
        <?php endif;?>                 
        <?php if($data['inv_br']):?>
        <tr>
            <td><strong><?php echo Yii::t('biblioteka', 'Инв. бр.:');?></strong></td>
            <td><?php echo CHtml::encode($data['inv_br']);?></td>
        </tr>
        <?php endif;?>
        
        <?php if( ! empty($data['uvod'])):?>
        <tr>
            <td colspan="2" style="text-align:justify;"><?php echo $data['uvod'];?></td>
        </tr>
        <?php endif;?> 
        
    </table>
</div>
<?php 
    $cs = Yii::app()->getClientScript();
    $cs->registerScript("knjiga_{$id}_js", "$(document).ready(function(){ 
                                                $('.naslov_$id').tooltip({predelay:650, position:'bottom center', opacity: 1, tipClass:'tooltip-knjiga_$id'}).dynamic({ bottom: { direction: 'down', bounce: true } }); 
                                            });");
?>