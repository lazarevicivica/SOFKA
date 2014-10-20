<?php

class ObradaProvera implements Obrada
{
    
    public function izvrsi($stranica, $invBr)
    {
        if(empty($stranica))
            echo 'prazna stranica ' . "$invBr<br/>";
        //Izdvajam tabelu koja sadrzi podatke o knjizi.
        $zapisi = @qp($stranica)->find('#dbase')->find('#rcrds')->text();
        
        $broj = str_replace('(Бр. записа: ', '', $zapisi);
        $broj = str_replace(')','',$broj);
        
        if(strpos($broj, '-') !== false)
                echo 'Neispravna stranica ' . "$invBr<br/>"; 
        
//        $broj = intval(str_replace('.','',$broj));
        

        

    }
}
