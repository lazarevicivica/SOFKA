<?php


class MojLinkPager extends CLinkPager
{
/*    function __construct() 
    {
        ;
    }*/
    public $aName;
    
    protected function createPageUrl($page)
    {
        return parent::createPageUrl($page) . $this->aName;
    }
}

?>
