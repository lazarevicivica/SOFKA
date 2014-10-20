<?php

class Aplikacija extends CWebApplication
{

    protected $podrazumevaniJezik;
    
    public function __construct($config) 
    {
        return parent::__construct($config);
    }
    
    /**
     *
     * Vraca podrazumevani jezik aplikacije. Parametar bi u buducnosti mogao da se ucitava iz baze.
     * 
     * @return type String
     */
    public function getPodrazumevaniJezik()
    {
        if( ! empty($this->podrazumevaniJezik))
                return $this->podrazumevaniJezik;
        return 'sr_sr';
    }        
    
    public function getPodrazumevaniJezikId()
    {
        return Helper::jezikKod2Id($this->getPodrazumevaniJezik());
    }
    
}
