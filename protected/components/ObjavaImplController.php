<?php

class ObjavaImplController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}
        public function actions()
        {
            return array(
                    'captcha'=>array(
                        'class'=>'CirCaptchaAction',
                        'backColor'=>0xFFFFFF,
                        'minLength'=>4, 'maxLength'=>6,
                    ),
                );
        }
                
        
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
/*	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}*/

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id, $parametri=null)
	{           
            $id = intval($id);
            $id_jezik = Helper::getAppjezikId();            
            $objava = null;
            $modelForme = new ContactForm;
            if(isset($_POST['ContactForm']))
            {
                $modelForme->attributes = $_POST['ContactForm'];
                $objava = Objava::model()->findByPk($id);
                if($objava->zakljucano)
                    throw new Exception(Yii::t('biblioteka','Објава је закључана и није дозвољено писање коментара.'));
                if($modelForme->validate())
                {
                    $transakcija = Yii::app()->db->beginTransaction();
                    try 
                    {   
                        $kontaktPodaci = null;
                        $clan = null;
                        if(Yii::app()->user->isGuest)
                        {                            
                            $kontaktPodaci = new KontaktPodaci();
                            $kontaktPodaci->initIzForme($modelForme);
                            if( ! $kontaktPodaci->save())
                                throw new Exception('Грешка при упису контакт података у базу.');
                        }
                        else
                        {
                            $clan = Clan::model()->findByPk(Yii::app()->user->id);
                        }                        
                        
                        $id_jezik_originala = Yii::app()->getPodrazumevaniJezikId();
                        if($id_jezik === Helper::ID_ENGLESKI_JEZIK)
                            $id_jezik_originala = Helper::ID_ENGLESKI_JEZIK;
                        
                        $komentar = Komentar::model()->napraviNovi($id_jezik_originala);
                        if( ! $komentar->inicijalizujISacuvaj($modelForme, $clan, $kontaktPodaci, $objava))                         
                            throw new Exception(Yii::t('biblioteka', 'Грешка при упису коментара у базу.'));
                        if($komentar->status === Komentar::OBJAVLJENO)
                        {
                            Yii::app()->user->setFlash('komentar', Yii::t('biblioteka','Ваш коментар је објављен. Хвала Вам што активно учествујете у развоју сајта.'));

                            //ako povecam broj komentara++ moze se javiti greska kod konkurentnog upisa
                            //verovatnoca je mala ali postoji, zato svaki put izracunavam broj komentara
                            //i to radim u okviru transakcije
                            $objava->azurirajBrojkomentara();
                        }
                        else
                        {
                            Yii::app()->user->setFlash('komentar', Yii::t('biblioteka','Ваш коментар чека одобрење администратора. Хвала Вам што активно учествујете у развоју сајта.'));
                        }                        
                        $transakcija->commit();
                    }
                    catch(Exception $e)
                    {
                        Yii::app()->user->setFlash('komentar', null);
                        Yii::app()->user->setFlash('greska',
                                Yii::t('biblioteka','{msg}! Уколико се проблем понови молимо Вас контактирајте администратора сајта <strong>{mejl}</strong>',
                                array('{msg}'=>$e->getMessage(),'{mejl}'=>Yii::app()->params['adminEmail'])));
                        $transakcija->rollBack();
                    }                    
                }
                
            }
            $data = Objava::getobjava($id);
            $this->metaDescription = $data['uvod'];
            if( ! $data)
                throw new CHttpException(400, Yii::t('biblioteka', 'Тражена објава не постоји!'));
            $komentari = Komentar::getListakomentara($id, $id_jezik);    
            $prosledi = array('data'=>$data, 'komentari'=>$komentari, 'modelForme'=>$modelForme);        
            if( ! empty($parametri)) //$parametre moze da preda izvedeni kontroler
                $prosledi = array_merge($parametri, $prosledi);
            $this->render('//objava/view', $prosledi);
	}

	/**
	 * Suggests tags based on the current user input.
	 * This is called via AJAX when the user is entering the tags input.
	 */
	public function actionPredlozitagove($term)
	{

            $tagovi = Tag::predlozitagove($term);
            if($tagovi!==array())
		 echo CJSON::encode($tagovi);
	}
               
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
            $this->adminMeni = true;
            $this->pageTitle = Yii::app()->name . ' - ' . Yii::t('biblioteka', 'Измена објаве');
            $objava = $this->loadModel($id);
/*            if($objava->status == Objava::DRAFT)
                $objava->draft = true;*/
            $this->azuriranje($objava);
	}


        public function actionCreate() 
        {      
            $this->adminMeni = true;
            $this->setJezik(Yii::app()->getPodrazumevaniJezikId());
            $this->azuriranje(null, true);
        }

        /*override*/
        protected  function klasaModela()
        {
            return 'Objava';
        }

        /*override*/ 
        protected function updateRuta()
        {
            return '//objava/update';
        }
        
        /*override*/ 
        protected function createRuta()
        {
            return '//objava/create';
        }
        
        /*override*/
        protected function adminRuta()
        {
            return '//objava/admin';
        }        
        
        /*override*/
        protected function ucitajModelIzPost($model)
        {
            $klasaModela = $this->klasaModela();
            $model->attributes = $_POST[$klasaModela];
        }
        
        /*override*/
        protected function azuriranje($objava, $novo = false)
        {
            $clan = $this->getclan();//izbacuje CHttpException ako clan nije ulogovan
            $klasaModela = $this->klasaModela();
            $superAdmin = $clan->isSuperAdministrator();
            $jezik = Yii::app()->getPodrazumevaniJezikId();
            if( ! $objava)
            {
                $objava = $klasaModela::model()->napraviNovi($jezik);
                $objava->status = Objava::DRAFT; //Objava je bazna klasa za sve klase modela
                $objava->id_clan = $clan->id;
            }
            if( ! $objava->mozeDaMenja($clan) )
                    throw new CHttpException(400,Yii::t('biblioteka', 'Страница није доступна, немате одговарајуће дозволе!'));
            $objava->setAktivanjezik($jezik);
            $objava->popuniPoljetagovi();
            $odeljci = array();
            if( ! $superAdmin)
                $sviOdeljci = $clan->rCRUDOdeljci(array('with'=>array('ri18n'), 'condition'=>"i18n_odeljak.id_jezik=$jezik", 'order'=>'i18n_odeljak.naziv'));
            else
                $sviOdeljci = Odeljak::model()->with(array('ri18n'=>array('order'=>'i18n_odeljak.naziv','condition'=>"i18n_odeljak.id_jezik=$jezik")))->findAll();
            //clan mora da ima definisana prava pristupa bar za jedan odeljak. $sviOdeljci su svi odeljci za datog clana!
            if( ! $sviOdeljci && ! $superAdmin)
                throw new CHttpException(400,Yii::t('biblioteka', 'Страница није доступна, немате дефинисана права приступа ни за један одељак!'));
            $odeljci = Odeljak::filtrirajZaIzbor($sviOdeljci, $clan, $objava);
            $galerija = $objava->rgalerija;
//objava mora da ima bar jedan cekiran odeljak
            if(isset($_POST[$klasaModela], $_POST['Odeljak']))
            {
                $valid=true;
                foreach($odeljci as $i=>$odeljak)
                {
                    $odeljak->scenario = 'izbor';
                    if(isset($_POST['Odeljak'][$i]))
                        $odeljak->attributes=$_POST['Odeljak'][$i];
                    $valid=$odeljak->validate() && $valid;
                }
                //$objava->attributes = $_POST[$klasaModela];               
                $this->ucitajModelIzPost($objava);
                $galerija = $objava->azurirajGaleriju();
                if( ! $galerija && $objava->id_galerija)
                    $galerija = GalerijaModel::model()->findByPk($objava->id_galerija);
                if($galerija)
                    $objava->id_galerija = $galerija->id;
                $valid = $valid && $objava->validate();
                if($valid && $objava->azuriraj($odeljci, $clan, $galerija) && $novo)
                {
                    if($objava->mozeDaMenja($clan))
                        $this->redirect(Helper::createI18nUrl($this->updateRuta(), null, array('id'=>$objava->id)));
                    else
                        $this->redirect(Helper::createI18nUrl($this->adminRuta()));
                }
            }
            $pogled = $novo ? 'create' : 'update';
            $this->render('//objava/'.$pogled, array(
                    'objava'=>$objava, 'odeljci'=>$odeljci, 'galerija' => $galerija
            ));
        }
        
        private function postaviStatus($objava, $status, $funkcijaProvere)
        {
            try
            {
                $objava->postaviStatus($status, $funkcijaProvere);
            }
            catch(Exception $e)
            {                
                Yii::app()->user->setFlash('greska-ispod-zaglavlja', $e->getMessage());
            }
        }
        
        public function actionCekaOdobrenje($id)
        {
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Акција се не може извршити јер тражена објава не постоји!'));
                $this->postaviStatus($objava, Objava::CEKA_ODOBRENJE, 'mozeDaStaviNaCekanje');
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

        public function actionObjavi($id)
        {
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Акција се не може извршити јер тражена објава не постоји!'));
                $this->postaviStatus($objava, Objava::OBJAVLJENO, 'mozeDaObjavi');
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

        public function actionOtpad($id)
        {
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Акција се не може извршити јер тражена објава не постоји!'));
                $this->postaviStatus($objava, Objava::OTPAD, 'mozeDaPosaljeUOtpad');
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

        private function zakljucaj($objava, $zakljucano)
        {
            try
            {
                $objava->zakljucaj($zakljucano);
            }
            catch(Exception $e)
            {                
                Yii::app()->user->setFlash('greska-ispod-zaglavlja', $e->getMessage());
            }
        }
        
        public function actionZakljucaj($id)
        {
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Акција се не може извршити јер тражена објава не постоји!'));
                $this->zakljucaj($objava, true);
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

        public function actionOtkljucaj($id)
        {
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Akcija se ne može izvršti jer tražena objava ne postoji!'));
                $this->zakljucaj($objava, false);
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
        
        private function izbrisi($objava)
        {
            try
            {
                $objava->izbrisi();
            }
            catch(Exception $e)
            {
                Yii::app()->user->setFlash('greska-ispod-zaglavlja', $e->getMessage());
            }
        }
        
	public function actionDelete($id)
	{
            if(Yii::app()->request->isPostRequest)
            {
                $objava = $this->loadModel($id);
                if( ! $objava)
                    CHttpException(400, Yii::t('biblioteka', 'Акција се не може извршити јер тражена објава не постоји!'));
                $this->izbrisi($objava);
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
	}

	public function actionIndex($odeljak, $naziv)
	{
            $id_odeljak = intval($odeljak);
            $dataProvider = Objava::listaobjava(Helper::getAppjezikId(), $id_odeljak); 
            $naziv = Odeljak::getNaziv($id_odeljak, Helper::getAppjezikId()); 
            $naslov = '<p class="iznad-h">'.Yii::t('biblioteka', 'Објаве из одељка:')."</p><h1><strong>$naziv</strong></h1>";
            $this->render('//objava/index',array('dataProvider'=>$dataProvider, 'naslov' => $naslov));
	}

        public function actionKljucnaRecodeljak($id_odeljak, $idRec, $odeljak, $rec)
        {
            $idRec = intval($idRec);
            $id_odeljak = intval($id_odeljak);
            $id_jezik = Helper::getAppjezikId();
            $dataProvider = Objava::listaobjavaZatagoveIzOdeljka(Helper::getAppjezikId(), $idRec, $id_odeljak);
            $naziv = Tag::getNaziv($idRec, $id_jezik);
            $odeljak = Odeljak::getNaziv($id_odeljak, $id_jezik);
            $naslov = '<p class="iznad-h">'. Yii::t('biblioteka', 'Објаве из одељка <em><strong>{odeljak}</strong></em> за кључну реч: ', array('{odeljak}'=>$odeljak)). "</p><h1><strong>$naziv</strong></h1><hr/>";
            $pageTitle = Yii::app()->name . '-' . Yii::t('biblioteka', 'Објаве за кључну реч: ') . $naziv;
            $this->registrujPortlet('StabloPortlet');
            $this->registrujPortlet('TagoviPortlet', array('id_odeljak'=>$id_odeljak));
            $this->render('//objava/index',array('dataProvider'=>$dataProvider, 'naslov' => $naslov, 'pageTitle'=>$pageTitle));
        }

        public function actionKljucnaRecSviOdeljci($rec, $naziv)
        {
            $this->registrujPortlet('StabloPortlet');
            $idRec = intval($rec);
            $dataProvider = Objava::listaobjavaZatag(Helper::getAppjezikId(), $idRec);
            $naziv = Tag::getNaziv($idRec, Helper::getAppjezikId());                        
            $naslov = '<p class="iznad-h">'. Yii::t('biblioteka', 'Објаве из <strong>свих одељака</strong> за кључну реч: '). "</p><h1><strong>".CHtml::encode($naziv)."</strong></h1><hr/>";
             $pageTitle = Yii::app()->name . '-' . Yii::t('biblioteka', 'Све објаве за кључну реч ') . $naziv;
            $this->render('//objava/index',array('dataProvider'=>$dataProvider, 'naslov' => $naslov, 'pageTitle'=>$pageTitle));
        }

        private function getclan()
        {
            return Helper::getLogovaniClan();
        }
       
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
            $this->adminMeni = true;
            $this->setJezik(Yii::app()->getPodrazumevaniJezikId());
            $this->pageTitle = Yii::app()->name . ' - ' . Yii::t('biblioteka', 'Управљање објавама');
            $klasaModela = $this->klasaModela();
            $model = new $klasaModela('search');
            $model->unsetAttributes();  // clear any default values
            if(isset($_GET[$klasaModela]))
                    $model->attributes=$_GET[$klasaModela];
            $clan = $this->getclan();
            if( ! $clan->getNizIdodeljak())
                throw new CHttpException(400, Yii::t('biblioteka', 'Страница није доступна, немате одговарајуће дозволе!'));
            $dataProvider = $model->search($clan);
            $this->render('//objava/admin',array(
                    'model'=>$model,
                    'clan' => $clan,
                    'dataProvider' => $dataProvider,
                    'adminNaslov' => Yii::t('biblioteka', 'Управљање објавама'),
                    'nezavrsene' => false,
            ));
	}

        public function actionNezavrsene()
        {
                $this->adminMeni = true;
                $model = new Objava('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['objava']))
			$model->attributes=$_GET['objava'];

                $clan = $this->getclan();
                $dataProvider = $model->searchNezavrsene($clan);
		$this->render('//objava/admin',array(
			'model'=>$model,
                        'clan' => $clan,
                        'dataProvider' => $dataProvider,
                        'adminNaslov' => Yii::t('biblioteka', 'Незавршене објаве'),
                        'nezavrsene' => true,
		));
        }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model = Objava::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('biblioteka', 'Тражена страница не постоји!'));
		return $model;
	}
        
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='objava-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}