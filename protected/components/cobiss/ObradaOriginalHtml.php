<?php

class ObradaOriginalHtml implements Obrada
{
    public function __construct($roditelj){}
    
    public function izvrsi($stranica, $invBr)
    {
        $stranica = mb_convert_encoding($stranica, 'utf-8', 'utf-8');  
        if($stranica === false)
            $stranica = '';
        $original = new OriginalHtml();
        $original->inv_br = $invBr;
        $original->stranica = $stranica;
        $original->save();       
    }
}
