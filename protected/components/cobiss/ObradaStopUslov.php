<?php

class ObradaStopUslov implements Obrada
{
    public function izvrsi($stranica, $invBr) 
    {
        if(empty($stranica))
            return true;
        $tabelaQp = @qp($stranica)->find('table#nolist-full');        
        if($tabelaQp->count() === 0)
            return true;
        return false;
    }
}
