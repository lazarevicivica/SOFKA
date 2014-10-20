<?php

class DigitalController extends Controller
{
    public $layout='//layouts/column2';

    public function actionIndex($id_zbirka=1, $naziv='', $pogled='')
    {
       
        $request = Yii::app()->request;
        if($pogled)
        {
            $cookie = new CHttpCookie('pogled', $pogled, array('expire' => time() + (365 * 24 * 60 * 60))); //expire za 1 godinu
            $request->cookies['pogled'] = $cookie;
            unset($_GET['pogled']);
        }
        elseif( ! empty($request->cookies['pogled']))
            $pogled = $request->cookies['pogled'];
        else
            $pogled = '1';
        $id_zbirka = intval($id_zbirka);
        $zbirka = Zbirka::model()->findByPk($id_zbirka);
        
        $this->registrujPortlet('DigitalPortlet', array('prikaziNaslov'=>false));
        $this->registrujPortlet('StabloPortlet', array('id_zbirka' => $id_zbirka));//stablo zbirki
        $this->registrujPortlet('TagoviPortlet', array('id_odeljak'=>Odeljak::ID_DIGITALNA_BIBLIOTEKA, 'limit'=>70, 'zbirka'=>$zbirka));
        $this->registrujPortlet('IzdvojeneKnjigePortlet');
        $this->registrujPortlet('DigitalneBibliotekePortlet');
        $seoNaziv = Helper::getSEOText($zbirka->naziv);
        if( ! $zbirka)
            throw new CHttpException('400', Yii::t('biblioteka', 'Тражена збирка не постоји!'));
        $frmModel = new df(); //Forma za pretragu svih knjiga (Filter)
        $frmPretraga = new PretragaStranicaForm(); //Forma za pretragu stranica u okviru jedne knjige.
        if( isset($_GET['df']))
            $frmModel->attributes = $_GET['df'];
        $frmPretraga->ftsUpit = $frmModel->getUpitZaStranice(); //tekst upita je isti i za pretragu svih knjiga i za pretragu stranica jedne knjige.
        $data = $zbirka->sqlKnjigeIspodZbirke($frmModel);
        $urlStranice = $request->hostInfo . '/digital/stranice/';
        $this->render('index2', array('data'=>$data, 'zbirka'=>$zbirka, 'prikaziNaslov'=>true, 'urlStranice'=> $urlStranice, 'pogled'=>$pogled, 'frmModel'=>$frmModel, 'frmPretraga' => $frmPretraga, 'naziv_zbirke' => $zbirka->naziv_zbirke, 'id_zbirka'=>$zbirka->id, 'seoNaziv'=>$seoNaziv));
    }
    
    public function actionStranice()//($idKnjiga, $ftsUpit = '', $operator = 'ili', $ajax = false)
    {
        $frmPretraga = new PretragaStranicaForm();
        $offset = 0;
        if(isset($_GET['PretragaStranicaForm']))
        {            
            $frmPretraga->attributes = $_GET['PretragaStranicaForm'];
            $idKnjiga = $frmPretraga->idKnjiga;
            $knjigaDeo = KnjigaDeo::model()->findByPk($idKnjiga);
            $frmPretraga->indeksPrveStranice = $knjigaDeo->indeks_prve_stranice;
            $frmPretraga->prikazCitanka = ! empty($knjigaDeo->json_desc);
        }
        $this->renderPartial('pretraga_stranica', array('frmPretraga'=>$frmPretraga));
        die();
    }
    
    public function actionBrojStranica()
    {
        $sql = 'SELECT json_desc FROM knjiga';
        $opisi = Yii::app()->db->createCommand($sql)->query();
        $broj = 0;
        foreach($opisi as $opis)
        {
            
            $ar = CJSON::decode($opis['json_desc']);
            $broj += intval($ar['broj_strana']);
        }
        echo 'Укупно страница у дигиталној библиотеци: '.$broj;
    }
    
    public function actionTest()    
    {
        /*$invBr = '000074686';
        $dir = Knjiga::getUlazniDir();
        $meta = Knjiga::obradiKnjigu($invBr, $dir);
        echo json_encode($meta,  JSON_UNESCAPED_UNICODE);
        die();*/
        
     //echo phpinfo();
    }
    
    public function actionAjaxAutomatskaObrada($invBr)
    {
        if(Yii::app()->user->isGuest)
            die('Корисник није пријављен');
        $clan = Helper::getLogovaniClan();                
        if( ! Knjiga::dozvoljenaAutomatskaObrada($clan))
            die('Корисник нема одговарајућа овлашћења.');
        $dir = Knjiga::getUlazniDir();
        $meta = Knjiga::obradiKnjigu($invBr, $dir);
        echo json_encode($meta,  JSON_UNESCAPED_UNICODE);
        die();
    }
}
