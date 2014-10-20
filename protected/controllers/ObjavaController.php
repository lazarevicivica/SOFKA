<?php

class ObjavaController extends ObjavaImplController
{
    public function actionView($id, $parametri=null)
    {
        $this->registrujPortlet('ZastitnikPortlet');
        $this->registrujPortlet('CasopisPortlet');
//        $this->registrujPortlet('TagoviPortlet', array('id_odeljak'=>Odeljak::ID_NASLOVNA));            
        $this->registrujPortlet('KataloziPortlet');
        $this->registrujPortlet('DigitalPortlet');
        $this->registrujPortlet('LinkoviPortlet');
        $this->registrujPortlet('FBPortlet');                  
        parent::actionView($id, $parametri);
    }
}
