<?php

class komentarController extends Controller
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

        public function actionAdmin()
	{
            $model=new Komentar('search');
            $model->unsetAttributes();  // clear any default values
            if(isset($_GET['Komentar']))
                    $model->attributes=$_GET['Komentar'];
            $clan = Helper::getLogovaniClan();
            if( ! $clan->getNizIdodeljak())
                throw new CHttpException(400, Yii::t('biblioteka', 'Страница није доступна, немате одговарајуће дозволе!'));
            if($model->status === null)
                $model->status = Komentar::CEKA_ODOBRENJE;
            $dataProvider = $model->search($clan);
            $this->render('admin',array(
                    'model'=>$model,
                    'clan' => $clan,
                    'dataProvider' => $dataProvider,
                    'adminNaslov' => Yii::t('biblioteka', 'Управљање коментарима'),
            ));
	}

        public function loadModel($id)
	{
		$model = Komentar::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('biblioteka', 'Тражена страница не постоји!'));
		return $model;
	}

        private function postaviStatus($komentar, $status, $funkcijaProvere)
        {
            try
            {
                $komentar->postaviStatus($status, $funkcijaProvere);
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
                $komentar = $this->loadModel($id);
                $this->postaviStatus($komentar, Komentar::CEKA_ODOBRENJE, 'mozeDaStaviNaCekanje');
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
                $komentar = $this->loadModel($id);
                $this->postaviStatus($komentar, Komentar::OBJAVLJENO, 'mozeDaObjavi');
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
                $komentar = $this->loadModel($id);
                $this->postaviStatus($komentar, Komentar::OTPAD, 'mozeDaPosaljeUOtpad');
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
        }

        private function izbrisi($komentar)
        {
            try
            {
                $komentar->izbrisi();
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
                $komentar = $this->loadModel($id);
                $this->izbrisi($komentar);
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                if(!isset($_GET['ajax']))
                    $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
            else
                throw new CHttpException(400,Yii::t('biblioteka', 'Погрешан захтев!'));
	}
}
