<?php
class zbirkaController extends Controller
{
    public $layout='//layouts/column2';

    private function proveraPravaPristupa()
    {
        $clan = Clan::getLogovani();
        if(! $clan->isSuperAdministrator())
            throw new CHttpException(400, Yii::t('biblioteka', 'Само суперадминистратор може да изврши ову акцију!'));            
    }

    public function actionCreate()
    {
        $this->proveraPravaPristupa();
        $this->registrujPortlet('StabloPortlet');
        $model = Zbirka::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK, 'unos');
        if(isset($_POST['Zbirka']))
        {
                $model->attributes=$_POST['Zbirka'];
                if($model->sacuvaj())
                        $this->redirect(array('admin'));
        }

        $this->render('//zbirka/create',array(
                'model'=>$model,
        ));
        
    }

    public function actionCreateGrupa()
    {
        $this->proveraPravaPristupa();
        $this->registrujPortlet('StabloPortlet');
        $model = new GrupaZbirkiForm();
        if(isset($_POST['GrupaZbirkiForm']))
        {
                $model->attributes=$_POST['GrupaZbirkiForm'];
                if($model->sacuvaj())
                        $this->redirect(array('admin'));
        }

        $this->render('//zbirka/create_grupa',array(
                'model'=>$model,
        ));        
    }
    
    public function actionUpdate($id)
    {
        $this->proveraPravaPristupa();
        $id = intval($id);        
        $model = $this->loadModel($id);
        $model->scenario = 'unos';
        if(isset($_POST['Zbirka']))
        {
                $model->attributes=$_POST['Zbirka'];
                if($model->sacuvaj())
                        $this->redirect(array('admin'));
        }
        $this->registrujPortlet('StabloPortlet', array('id_zbirka'=>$model->roditelj));
        $this->render('//zbirka/update',array(
                'model'=>$model,
        ));
    }

/*    public function actionDelete($id)
    {
        $id = intval($id);
        $this->proveraPravaPristupa();
        Zbirka::
    }*/

    public function actionAjaxZbirkeIz()//id zbirke dobija iz post requesta
    {
        if(Yii::app()->request->isPostRequest && ! empty($_POST['id_zbirka']))
        {
            $id_zbirka = $_POST['id_zbirka'];
            $otvorenaKategorija = Zbirka::model()->find('id=:id', array(':id'=>$id_zbirka));
            echo '<ul>'.Zbirka::getListaOtvorenih($otvorenaKategorija, new StabloVisitor()).'</ul>';
            die();
        }
        echo '';
        die();
    }

    public function actionAdmin()
    {
        $this->proveraPravaPristupa();        
        //$model=new Zbirka('search');
        $model = Zbirka::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);
        $model->scenario = 'search';
        if(isset($_GET['Zbirka']))
                $model->attributes=$_GET['Zbirka'];

        echo $model->id;
        
        $this->render('admin',array(
                'model'=>$model,
        ));        
    }

    public function loadModel($id, $jezik = Helper::ID_SRPSKI_JEZIK)
    {
            $model=Zbirka::model()->findByPk($id);
            if($model===null)
                    throw new CHttpException(404,'Тражена страница не постоји.');

            $model->setAktivanjezik(Helper::ID_ENGLESKI_JEZIK);
            $model->naziv_zbirkeEn = $model->naziv_zbirke;
            $model->opisEn = $model->opis;
            $model->setAktivanjezik($jezik);
            return $model;
    }
}