<?php

class ObradaNeobradjeneKolone implements Obrada
{
    public $preskoceniInvBr = array();
    public $greskeUcitavanja = array();
    public $greskeParsiranja = array();    
    
    public function izvrsi($stranica, $invBr)
    {
        if(empty($stranica))
            return false;
        //Izdvajam tabelu koja sadrzi podatke o knjizi.
        $tabelaQp = @qp($stranica)->find('table#nolist-full');
        
        if($tabelaQp->count() === 0)
            return false;
        
        $redovi = $tabelaQp->children('tbody')->children('tr');
        
        $obj = new NeobradjeneKolone();
        $obj->inv_br = $invBr;
        foreach($redovi as $red)
        {
            $naziv = $red->children('th')->text();
            if(empty($naziv))
                $naziv = 'nedefinisano';
            $naziv = str_replace(array('/', '.'), '_', $naziv);
            $nazivAtributa = str_replace('-', '_', Helper::getSEOText($naziv));                        
            $vrednost = $red->children('td')->innerhtml();           
            $obj->$nazivAtributa = $vrednost;
        }
        if($obj->save())
            return $obj;
        return false;
    }
}
