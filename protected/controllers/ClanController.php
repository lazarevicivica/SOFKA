<?php

class ClanController extends Controller
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

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view', 'zaposleni_po_odeljenjima'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('ivica'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

        private function registrujPortlete()
        {
            $this->registrujPortlet('OdeljenjaPortlet');
            $this->registrujPortlet('KataloziPortlet');
            $this->registrujPortlet('DigitalPortlet');
        }

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
            return;
		$model = new Clan;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Clan']))
		{
			$model->attributes=$_POST['Clan'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
         *
         * @param <int> $id  id clana
         */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
                $logovani = Clan::getLogovani();
                if( ! $logovani->isSuperAdministrator())
                {
                    if($model->id !== $logovani->id)
                        throw new CHttpException('', Yii::t('biblioteka', 'Не можете мењати туђи налог!'));
                }
                $model->scenario = 'update';
                $model->setAktivanjezik(Helper::ID_SRPSKI_JEZIK);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Clan']))
		{
                    $model->attributes=$_POST['Clan'];
                    $model->fajlslika = CUploadedFile::getInstance($model, 'fajlslika');                    
                    if($model->validate())
                    {
                        $trans = Yii::app()->db->beginTransaction();
                        try
                        {
                            $model->obradiTekst();
                            $model->promeniLozinku();
                            if( ! $model->sacuvajSliku())
                            {
                                $model->addError('greska',Yii::t('biblioteka', 'Грешка при снимању слике'));
                                $trans->rollBack();
                            }
                            elseif($model->save())
                            {
                                $trans->commit();
                                //$this->redirect(array('view','id'=>$model->id));
                            }
                        }
                        catch(Exception $e)
                        {
                            $model->addError('greska', $e->getMessage());
                            $trans->rollBack();
                        }
                    }
                }
                $model->novaLozinka = '';
                $model->ponovljenaLozinka ='';
                $model->staraLozinka = '';

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
            return;
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
            return;
		$dataProvider = new CActiveDataProvider('Clan'); 
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
            return;
		$model=new Clan('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Clan']))
			$model->attributes=$_GET['Clan'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

        public function actionZaposleni_Po_Odeljenjima()
        {
            $this->registrujPortlete();
            $vrsta = Odeljenje::ORGANIZACIONA_JEDINICA;
            $odeljenja = Odeljenje::model()->findAll("id_vrsta_odeljenja=$vrsta ORDER BY redosled DESC");
            $this->render('zaposleni', array('odeljenja'=>$odeljenja));
        }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Clan::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='clan-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
