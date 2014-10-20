<?php

class ObradaDigital implements Obrada
{
    //public function __construct(){}
    public $rezultat = array();
    
    private function getAtribut($red)
    {
        $naziv = $red->children('th')->text();
        if(empty($naziv))
            $naziv = 'nedefinisano';
        $naziv = str_replace(array('/', '.'), '_', $naziv);
        $nazivAtributa = str_replace('-', '_', Helper::getSEOText($naziv));                        
        $vrednost = $red->children('td')->innerhtml();           
        return array('naziv'=>$nazivAtributa, 'vrednost'=>$vrednost);
    }
    
    private function filtriraj($par)
    {        
        switch($par['naziv'])
        {
            case 'naslov':
                $delovi = explode('/', $par['vrednost']);
                $par['vrednost'] = trim($delovi[0]);
                break;
            case 'predmetne_odrednice':
                $qp = qp(QueryPath::HTML_STUB);
                $qp->find('body')->append($par['vrednost']);
                $linkovi = $qp->find('a');
                $tagovi = '';
                foreach($linkovi as $link)
                    $tagovi .= $link->text() . ', ';
                $tagovi = rtrim($tagovi, ', ');
                $par['vrednost'] = $tagovi;
                $par['vrednost'] = strip_tags($par['vrednost']);
                break;
            case 'autor':
            case 'izdavanje_i_proizvodnja':
            case 'udk':
            case 'cobiss_sr_id':
            case 'godina':
            case 'jezik':
                $par['vrednost'] = strip_tags($par['vrednost']);       
                break;
            default:
                return false;
        }
        $par['vrednost'] = strip_tags($par['vrednost']);       
        return $par;
    }
    
    public function izvrsi($stranica, $invBr)
    {
        if(empty($stranica))
            throw new Exception('Кобис подаци нису учитани!');;
        //Izdvajam tabelu koja sadrzi podatke o knjizi.
        $tabelaQp = @qp($stranica)->find('table#nolist-full');
        
        if($tabelaQp->count() === 0)
            throw new Exception('Кобис подаци нису учитани!');;
        
        $redovi = $tabelaQp->children('tbody')->children('tr');
        
        if($redovi->count() === 0)
            throw new Exception('Кобис подаци нису учитани!');
        $polja = array();
        $postoji = false;
        foreach($redovi as $red)
        {
            $par = $this->getAtribut($red);
            $par = $this->filtriraj($par);            
            if($par)
            {
                $postoji = true;
                $polja[$par['naziv']] = $par['vrednost'];
            }
        }   
        $this->rezultat[] = $polja;
        if( ! $postoji)
            throw new Exception('Кобис подаци нису учитани!');
    }
}
