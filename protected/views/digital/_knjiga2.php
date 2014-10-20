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
//poslednji prikazan u listi ne treba da ima border-bottom
    $style = ' style="border-bottom:none;margin-bottom:0;"';
    if( ! empty($widget))
    {
        $brojPoStrani = $widget->dataProvider->pagination->limit;
        $broj = $index + 1 + $widget->dataProvider->pagination->getCurrentPage() * $brojPoStrani;    
        //ako nije poslednji u listi stil je prazan
        if( ! ($broj % $brojPoStrani === 0 || $broj >= $widget->dataProvider->getTotalItemCount()) ) 
            $style = '';
    }
        
?>

<div id="knjiga_<?php echo $id;?>" class="knjiga"<?php echo $style;?>>

    <div class="slika" style="float:left;width:120px;">
    <?php
        $urlSlike = $data['url_slike'];
        $img = "<img id=\"korice\" class=\"dropshadow\" src=\"$urlSlike\"/>";
        if($url)
        {
            $title = Yii::t('biblioteka', 'Преглед');
            echo "<a title=\"$title\" href=\"$url\">$img</a>";
        }
        elseif($deoStranice)
        {
            Yii::app()->getClientScript()->registerScript('korice_js', "$(document).ready( function(){ $('#korice').click(function(event){event.preventDefault(); skociNa('a-pretraga'); $('#PretragaStranicaForm_ftsUpit').focus();});});");
            $title = Yii::t('biblioteka', 'Претрага');
            echo "<a title=\"$title\" href=\"#\">$img</a>";            
        }
        else
            echo $img;                
    ?>   

    <?php if( $aktivnaPretraga || $aktivnaCitanka):?>
        <div id="ikonice-kontejner">
        <?php if($aktivnaPretraga):?>            
            <?php
                if( ! $deoStranice)
                {
                    $klasaPretrage = 'pretraga-stranica knjiga2-pretraga';
                    $urlPretraga = Helper::createI18nUrl('digital/stranice', null, array('idKnjiga'=>$id, 'ftsUpit'=>$ftsUpit));
                }
                else 
                {
                    $klasaPretrage = 'pretraga-deo-stranice knjiga2-pretraga';
                    $urlPretraga = '#';
                }
            ?>
            <a title="<?php echo Yii::t('biblioteka', 'Претрага');?>" id="pretraga_<?php echo $id;?>" class="<?php echo $klasaPretrage;?>" href="<?php echo $urlPretraga;?>"></a>          
        <?php endif;?>
        <?php if($aktivnaCitanka):?>
            <a title="<?php echo Yii::t('biblioteka', 'Преглед');?>" class="citanka knjiga2-citanka" href="<?php echo $urlCitanka;?>"></a>  
        <?php endif;?>
        </div>
    <?php endif;?>         
        
    </div> <!--kraj slika-->
    <div  class="opis" style="width:510px;float:right;">
        <table>
            <?php if( ! $deoStranice && ! empty($data['naslov'])):?>
                <tr>
                    <td colspan="2" style="padding-bottom:15px;"><a id="a-naslov_<?php echo $id;?>" class="naslov" href="<?php echo $url;?>"><?php echo CHtml::encode($data['naslov']);?></a></td>
                </tr>
            <?php endif;?>
            <?php if($data['autor']):?>
            <tr>
                <td class="opis"><?php echo Yii::t('biblioteka', 'Аутор:');?></td>
                <td class="vrednost autor-knjige-data"><?php echo CHtml::encode($data['autor']);?></td>
            </tr>
            <?php endif;?>  
            <?php if( ! empty($data['id_zbirka']) && ! empty($data['naziv_zbirke'])):?>
                <tr>
                    <td class="opis"><?php echo Yii::t('biblioteka', 'Збирка:');?></td>
                    <td><a href="<?php echo Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$data['id_zbirka'], 'naziv' => Helper::getSEOText($data['naziv_zbirke'])));?>"><?php echo CHtml::encode($data['naziv_zbirke']);?></a></td>
                </tr>
            <?php endif;?>
            <?php if($data['izdanje']):?>
            <tr>
                <td class="opis"><?php echo Yii::t('biblioteka', 'Издање:');?></td>
                <td><?php echo CHtml::encode($data['izdanje']);?></td>
            </tr>
            <?php endif;?>  
            <?php if($data['dan'] || $data['mesec'] || $data['godina']):?>
            <tr>
                <td class="opis"><?php echo Yii::t('biblioteka', 'Датум:');?></td>
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
                <td class="opis"><?php echo Yii::t('biblioteka', 'Прегледа:');?></td>
                <td><?php echo CHtml::encode($data['br_pregleda']);?></td>
            </tr>
            <?php endif;?>                 
            <?php if($data['inv_br']):?>
            <tr>
                <td class="opis"><?php echo Yii::t('biblioteka', 'Инв. бр:');?></td>
                <td><?php echo CHtml::encode($data['inv_br']);?></td>
            </tr>
            <?php endif;?>                                                           
            
            <?php if( ! empty($data['uvod'])):?>
            <tr>
                <td colspan="2"><?php echo $data['uvod'];?></td>
            </tr>
            <?php endif;?>  
            
            <?php if($deoStranice):?>
            <tr><td colspan="2">
                <?php 
                    $zbirka = Zbirka::model()->findByPk($data['id_zbirka']);
                    $this->widget('zii.widgets.CBreadcrumbs', array('homeLink'=>false,'links' => $zbirka->getPutanjaZaBreadcrumbs()));
                ?>
            </td></tr>
            <?php endif;?>
            
        </table>
    </div>
    <div class="clear"></div>
</div>
