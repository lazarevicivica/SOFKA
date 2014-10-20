<?php

//poslednji prikazan u listi ne treba da ima border-bottom
    $brojPoStrani = $widget->dataProvider->pagination->limit;
    $broj = $index + 1 + $widget->dataProvider->pagination->getCurrentPage() * $brojPoStrani;    
    if( $broj % $brojPoStrani === 0 || $broj >= $widget->dataProvider->getTotalItemCount() )
        $style = ' style="border-bottom:none;"';
    else
        $style='';
?>
<div class="markirano-<?php echo $index % 2 ?>"<?php echo $style;?>>
<?php

$indeks = $data['broj'];
$broj = $indeks - $indeksPrveStranice  + 1;

if($broj <= 0) //korica i ostale stranice ispred prve numerisane.
{
    $ukupnoIspredPrve = $indeksPrveStranice - 1;
    $broj = $ukupnoIspredPrve + $broj;
    $broj = Helper::rimskiBroj($broj);
}

if($frmPretraga->prikazCitanka)
{
    $url = Helper::baseUrl('citanka/knjiga.php').'?id='.$idKnjiga.'&start='. $indeks;
    echo "<a target=\"_blank\" href=\"$url\">#$broj</a>";
}
    else 
        echo "<span style=\"color:brown;font-weight:bold;\">#$broj</span>";
echo '...' . $data['markirani_tekst'] . '...';
?>
</div>
