<?php
class KnjigaController extends ObjavaImplController
{
    public $layout='//layouts/column2';

    private function proveraPravaPristupa()
    {
        $clan = Clan::getLogovani();
        if(! $clan->isSuperAdministrator())
            throw new CHttpException(400, Yii::t('biblioteka', 'Само суперадминистратор може да изврши ову акцију!'));
    }    
    
    protected function ucitajModelIzPost($model)
    {
        $model->attributes = $_POST['Knjiga'];
        $model->knjiga->attributes = $_POST['KnjigaDeo'];
    }    
    
    /*override*/
    protected function klasaModela()
    {
        return 'Knjiga';
    }               
    
       /*override*/ 
    protected function updateRuta()
    {
        return '//knjiga/update';
    }

    /*override*/ 
    protected function createRuta()
    {
        return '//knjiga/create';
    }

    /*override*/
    protected function adminRuta()
    {
        return '//knjiga/admin';
    }   

/*    public function actionCreate()
    {
        $this->proveraPravaPristupa();
        $this->registrujPortlet('StabloPortlet');        
        $model = new Knjiga(); //knjiga::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK, 'unos');
        if(isset($_POST['Knjiga']))
        {
                $model->attributes=$_POST['Knjiga'];   
                if($model->save())
                        $this->redirect(array('admin'));
        }

        $this->render('//knjiga/create',array(
                'model'=>$model,
        ));
    }*/

    //TODO funkcija nije zavrsena, koristi se pri unosu svih casopisa koji pripadaju jednom godistu.
    public function actionCreateGodiste()
    {
        $this->proveraPravaPristupa();
        $this->registrujPortlet('StabloPortlet');
        $model = new NovineGodisteForm();
        if(isset($_POST['NovineGodisteForm']))
        {
                $model->attributes=$_POST['NovineGodisteForm'];   
                if($model->sacuvaj())
                        $this->redirect(array('admin'));
        }

        $this->render('//knjiga/create_godiste',array(
                'model'=>$model,
        ));        
    }
    
    public function actionView($id, $upit=null)
    {
        //$knjiga = Knjiga::model()->findByPk($id);       
        $knjiga = Knjiga::getKnjigaDeo($id);//$id == $id_objava
        $idZbirka = null;
        if($knjiga)
        {
            $idZbirka = $knjiga['id_zbirka'];
            $zbirka = Zbirka::model()->findByPk($idZbirka);
        }
        $this->registrujPortlet('StabloPortlet', array('id_zbirka'=>$idZbirka));
        if( ! empty($zbirka))
            $this->registrujPortlet('TagoviPortlet', array('id_odeljak'=>Odeljak::ID_DIGITALNA_BIBLIOTEKA, 'limit'=>70, 'zbirka'=>$zbirka, 'url'=>$zbirka->getUrl()));
        $frmPretraga = new PretragaStranicaForm();
        $frmPretraga->idKnjiga = $knjiga['id'];
        $frmPretraga->ftsUpit = $upit;
        $frmPretraga->indeksPrveStranice = $knjiga['indeks_prve_stranice']  ;
        parent::actionView($id, array('frmPretraga'=>$frmPretraga, 'knjiga'=>$knjiga));
    }    
    
/*    public function actionUpdate($id)
    {
        $this->proveraPravaPristupa();
        $id = intval($id);
        $model=$this->loadModel($id);
        if(isset($_POST['Knjiga']))
        {
                $model->attributes=$_POST['Knjiga'];
                if($model->sacuvaj())
                        $this->redirect(array('admin'));
        }
        $this->registrujPortlet('StabloPortlet', array('id_zbirka' => $model->id_zbirka));//stablo zbirki
        $this->render('update',array(
                'model'=>$model,
        ));
    }

    public function actionDelete()
    {
        $this->proveraPravaPristupa();
    }


    public function actionAdmin()
    {
        $this->proveraPravaPristupa();
        $model=new Knjiga('search');          
        if(isset($_GET['Knjiga']))
                $model->attributes=$_GET['Knjiga'];
        $this->render('admin',array(
                'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
            $model=Knjiga::model()->findByPk($id);
            if($model===null)
                    throw new CHttpException(404,'Тражена страница не постоји.');
            return $model;
    }*/
}