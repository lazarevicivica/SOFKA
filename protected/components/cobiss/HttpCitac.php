<?php
class HttpCitac implements Citac
{
    
    const FORMAT_PUNI_KORISNICKI = 11;
    const FORMAT_ISBD = 12;
    
    public $sesija;

    public $ciklusSesije = 1000;
    public $pauzaIzmedjuZahteva = 0;//sekundi
    public $pauzaIzmedjuCiklusa = 3;//sekundi
    
    public $format;
    
    private $tekuciCiklus = 0;
    
    public function __construct($roditelj) 
    {
        $this->format = self::FORMAT_PUNI_KORISNICKI;
        if( empty($roditelj->sesija))
            $this->sesija = new Sesija();
        else
            $this->sesija = $roditelj->sesija;
    }
    public function ucitajZaInvBr($invBr)
    {
        if($this->tekuciCiklus===0)
            $this->sesija->osvezi();
          
        sleep($this->pauzaIzmedjuZahteva);           
            
        if( ++$this->tekuciCiklus === $this->ciklusSesije)
        {
            sleep($this->pauzaIzmedjuCiklusa);
            $this->sesija->osvezi();
            $this->tekuciCiklus = 0;
        }            
        
        $url = "http://www.vbs.rs/scripts/cobiss?ukaz=SEAR&ID={$this->sesija->id}&ss1=in%3D{$invBr}&mat=51&scpt=&find=%D0%9F%D0%A0%D0%95%D0%A2%D0%A0%D0%90%D0%96%D0%98&fmt={$this->format}";            
        return file_get_contents($url);
    }
}