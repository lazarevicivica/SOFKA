<div id="admin-meni">
<?php
    $clan = Clan::getclan(Yii::app()->user->id);
$this->widget('zii.widgets.CMenu', array(
    'items'=>array(
        array('label'=>'Управљање објавама', 'url'=>array('objava/admin')),
        array('label'=>'Нова објава', 'url'=>array('objava/create')),
        array('label'=>'Незавршене објаве', 'url'=>array('objava/nezavrsene')),
        array('label'=>'Коментари', 'url'=> array('komentar/admin')),
        array('label'=>'Мој профил', 'url'=> array('clan/update', 'id'=>Yii::app()->user->id)),
        array('label'=>'Одељења', 'url'=>array('odeljenje/admin'), 'visible'=>$clan->isSuperAdministrator()),
        array('label'=>'Ново одељење', 'url'=>array('odeljenje/create'), 'visible'=>$clan->isSuperAdministrator()),
    ),
));
/*    $odeljci = $clan->getNizIdOdeljkaNaziv();
    echo '<span class="veliko">'. Yii::t('biblioteka', 'Одељци: ').'</span>';
    $strOdeljci = '';
    foreach($odeljci as $odeljak)
        $strOdeljci .= $odeljak . ', ';
    $strOdeljci = rtrim(trim($strOdeljci),',');
    echo $strOdeljci;*/
?>
</div>
