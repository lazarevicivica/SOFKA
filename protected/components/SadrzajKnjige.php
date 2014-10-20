<?php
class Poglavlje
{
    public function __construct($indeks, $tekst) 
    {
        $this->txt = $tekst;
        $this->i = $indeks;
    }
    public $txt;
    public $i;
    public $sub = array();
}

class SadrzajKnjige 
{
    /**
     *
     * @param type $sadrzaj sadrzaj je u formatu broj minusa koji oznacavaju nivo:indeks:naslov
     * Na primer
     * -:0:Naslov
     * --:1:Ispod naslova
     * --:4:Takodje ispod naslova...
     * -:5:U istoj ravni sa naslovom
     * --:6:Ispod "U istoj ravni sa naslovom" - namerno sam stavio navodnike
     * ---:7:Ispod ispod u istoj ravni sa naslovom...
     */
/*    private $sadrzaj;
    public function __construct($sadrzaj) 
    {
        $this->setSadrzaj($sadrzaj);
    }
    
    public function setSadrzaj($sadrzaj)
    {
        $this->sadrzaj = $sadrzaj;
    }*/
    
    private static function generisiArSadrzaj($txtSadrzaj)
    {
        $root = new Poglavlje(null, null);
        $stek = array($root);
        $top = 0;
        $prethodniNivo = 0;
        $redovi = explode("\n", $txtSadrzaj);
        $brojReda = 0;
        foreach($redovi as $red)
        {
            $red = trim(str_replace("\r", '', $red));
            if( ! $red)
                continue;            
            $brojReda++;
            $elementi = explode(':', $red, 3);   
            if(count($elementi) != 3)
                throw new Exception("Непотпун унос, ред мора садржати три целине одвојене симболом :! Ред бр.:$brojReda");            
            $minusi = $elementi[0];
            if( ! is_numeric($elementi[1]))
                throw new Exception("Погрешан унос, индекс странице нема нумеричку вредност! Ред бр.:$brojReda");
            $indeks = intval($elementi[1]);
            $nivo = strlen($minusi);
            if( ! $nivo)
                throw new Exception("Погрешан унос, ниво не сме бити 0 (морате унети бар један симбол -)! Ред бр.:$brojReda");
            for($i=0; $i<$nivo; $i++)
            {                
                if($minusi[$i] !== '-')
                    throw new Exception("Погрешан унос, недозвољен симбол! Ред бр.:$brojReda");
            } 
            $tekst = $elementi[2];
            $poglavlje = new Poglavlje($indeks, $tekst); 
            
            if($nivo == $prethodniNivo)
            {
                array_pop($stek);
                $top--;
            }
            elseif($nivo > $prethodniNivo)
            {                
                if($nivo - $prethodniNivo != 1)
                    throw new Exception("Погрешан унос, прескочили сте ниво (унели сте више симбола - него што је потребно! Ред бр.:$brojReda");             
            }
            else //$nivo < $prethodniNivo
            {
                $vratiSe = $prethodniNivo - $nivo;
                for($i=0; $i < $vratiSe+1; $i++)
                {
                    array_pop($stek);
                    $top--;
                }
            }
            $stek[$top]->sub[] = $poglavlje;
            $stek[] = $poglavlje;
            $top++;            
            $prethodniNivo = $nivo;         
        }
        return $root;
    }
    
    public static function generisiSadrzaj($txtSadrzaj)
    {
         $str = json_encode(self::generisiArSadrzaj($txtSadrzaj)->sub);            
         $json = array('x'=>$str);   
         $duploEnkodovan = json_encode($json); //{"x":"[...]"}
         $str = explode(':', $duploEnkodovan, 2); //da bih izbacio {"x":         
         return substr($str[1], 1,  strlen($str[1])-3);//uklanja sa leve strane jedan znak, a sa desne 2
    }
}