<?php

/**
 * Visitor generise html za prikaz stabla. Za svaki cvor stabla poziva se funkcija visit.
 */

class StabloVisitor
{

    public function visit($kategorija, $nivo, $selektovan)
    {
        $klasa='';
        $tekst = CHtml::encode($kategorija->GetNaziv());
        $url = $kategorija->GetUrl();
        
        if(isset($_GET['sort']))
            $url .= '?sort=' . urlencode ($_GET['sort']);
        $id = $kategorija->id;
        //$klasa = ''; $selektovan ? ' selektovana-zbirka' : '';
        if($selektovan)
        {
            $klasa = ' selektovana-zbirka';
            $this->nivoSelektovanog = $nivo;
        }
        if($nivo > $this->nivoSelektovanog)
            $klasa .= ' ispod-selektovane';
        $margina = 10 * $nivo;
        $this->rezultat .= "<li style=\"margin-left:{$margina}px\"><a id=\"zbirka_$id\" class=\"zbirka$klasa\" href=\"$url\">$tekst</a></li>";
    }
    public function getRezultat(){return $this->rezultat;}
    private $rezultat;
    private $nivoSelektovanog = null;
}
?>
