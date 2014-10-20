<?php
class Cobiss
{
	public $pocetniInvBr;
	public $krajnjiInvBr;

	
        public $sesija = null;
        
        public $citac;
        public $obrada;
        public $stop;
        
        /**
         *
         * @param type $pocetniInvBr
         * @param type $krajnjiInvBr
         * @param type $citac
         * @param type $obrada
         * @param type $stop 
         */
	public function __construct($pocetniInvBr, $krajnjiInvBr, $citac='HttpCitac', $obrada='ObradaOriginalHtml', $stop=null)
	{
            $this->pocetniInvBr = intval($pocetniInvBr);
            $this->krajnjiInvBr = intval($krajnjiInvBr);
            
            $this->citac = $citac;
            $this->obrada = $obrada;
            
            $this->stop = $stop;
	}              
        	
	public function izvrsi()
	{
            set_time_limit(0);
            $stop = null;
            if($this->stop)
            {
                if(is_string($this->stop))
                {
                    $klasaStop = $this->stop;
                    $stop = new $klasaStop($this);
                }
                else
                    $stop = $this->stop;
            }
            if(is_string($this->citac))
            {
                $klasaCitaca = $this->citac;
                $citac = new $klasaCitaca($this);
            }
            else
                $citac = $this->citac;
            if( is_string($this->obrada))
            {
                $klasaObrade = $this->obrada;
                $obrada = new $klasaObrade($this);
            }
            else
                $obrada = $this->obrada;
            for($i = $this->pocetniInvBr; $i <= $this->krajnjiInvBr; $i++)
            {
                $cobissInvBr = str_pad($i, 9, '0', STR_PAD_LEFT);
                $stranica = $citac->ucitajZaInvBr($cobissInvBr);
                if(!empty($stop) && $stop->izvrsi($stranica, $i))
                        return;                    
                $obrada->izvrsi($stranica, $i);
            }
	}	
}
