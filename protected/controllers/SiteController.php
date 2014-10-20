<?php

class SiteController extends Controller
{
    public $layout='application.views.layouts.column2';
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

        /**
         *
         * @param <int> $sakrij_animaciju  postavlja vrednost cookie promenljivoj sakrij_okvir
         */
	public function actionIndex($sakrij_animaciju=null)
	{
            if($sakrij_animaciju)
            {
                $request = Yii::app()->request;
                $cookie=new CHttpCookie('sakrij_okvir',1, array('expire' => time() + (30 * 24 * 60 * 60)));//mesec dana
                $request->cookies['sakrij_okvir'] = $cookie;
                unset($_GET['sakrij_animaciju']);
            }
            elseif($sakrij_animaciju !== null)
            {
                $request = Yii::app()->request;
                $cookie = new CHttpCookie('sakrij_okvir',0);
                $request->cookies['sakrij_okvir'] = $cookie;
                unset($_GET['sakrij_animaciju']);
            }
            //$this->registrujPortlet('AktuelnoPortlet');
            $this->registrujPortlet('ZastitnikPortlet');
            $this->registrujPortlet('CasopisPortlet');
            $this->registrujPortlet('TagoviPortlet', array('id_odeljak'=>Odeljak::ID_NASLOVNA));            
            $this->registrujPortlet('KataloziPortlet');
            $this->registrujPortlet('DigitalPortlet');
            $this->registrujPortlet('LinkoviPortlet');
            $this->registrujPortlet('FBPortlet');            
            $dataProvider = Objava::listaobjava(Helper::getAppjezikId(), Odeljak::ID_NASLOVNA);
            $this->render('index', array('dataProvider'=>$dataProvider, 'naslovna'=>true));
	}

        public function actionKeep_alive()
        {
            die();
        }
        
        public function actionRegenerisiThSlicice()
        {
            $korisnik = Clan::getLogovani();
            if( ! $korisnik->isSuperAdministrator())
                    throw new CHttpException(400, Yii::t('biblioteka', 'Страница није доступна, немате одговарајуће дозволе!'));
            set_time_limit(0);
            Yii::import('ext.phpThumb.PhpThumbFactory');
            $slike = Slika::model()->findAll();
            echo 'Почињем регенерисање смањених слика';
            foreach($slike as $slika)
            {
                try
                {
                    @$thumb = PhpThumbFactory::create($slika->getAbsPutanja(), array('jpegQuality' => 80));
                    @$thumb->adaptiveResize(Slika::THUMB_SIRINA, Slika::THUMB_VISINA);
                    @$thumb->save($slika->getThAbsPutanja());
                }
                catch(Exception $ex)
                {
                    echo ';<br/>Дошло је до грешке при смањивању слике: '.$slika->getAbsPutanja();
                    continue;
                }
            }
            echo '<br/>Крај регенерисања сличица';
        }

        public function actionUvozStarihVesti()
        {
            //ovu funkciju ne bi trebalo nikad da koristim nakon pokretanja sajta.
            return;
            $korisnik = Clan::getLogovani();
            if( ! $korisnik->isSuperAdministrator())
                    throw new CHttpException(400, Yii::t('biblioteka', 'Страница није доступна, немате одговарајуће дозволе!'));
            
            set_time_limit(0);

            Yii::import('ext.phpThumb.PhpThumbFactory');


            echo "pocetak <br>";
            $redovi = StaraVest::model()->findAll();
            assert($redovi);
            $trans = Yii::app()->db->beginTransaction();
            try
            {
                foreach($redovi as $vest)
                {                
                    $dan = $vest->dan;
                    $mesec = $vest->mesec;
                    $godina = $vest->godina;

                    $datum = strtotime($godina.'-'.$mesec.'-'.$dan);
                    $naslov = $vest->naslov;
                    $uvod = $vest->prvi_pasus;
                    $tekst = $vest->tekst;
                 //dodato bez testiranja
                    if( ! $tekst) //jedno mora da postoji, tekst ili uvod.
                        $tekst = $uvod;
                    if( ! $uvod)
                        $uvod = $tekst;
                 //end
                    //latinica je originalno pismo starih vesti
                    $objava = Objava::model()->napraviNovi(Helper::ID_SRPSKI_LATINICA);
                    $objava->naslov = $naslov;
                    $objava->datum = $datum;
                    if($datum === false)
                        $objava->datum = time();
                    $objava->uvod = $uvod;
                    $objava->tekst = $tekst;
                    $objava->tekst_sirov = $tekst;
                    $sacuvano = $objava->save();
                   if(!$sacuvano)
                   {
                       echo '<br/>naslov '.$objava->naslov;
                       echo '<br/>datum'.$objava->datum;
                       echo '<br/>uvod'.$objava->uvod;
                       echo '<br/>tekst '.$objava->tekst;
                       echo '<br/>tekst_sirov'.$objava->tekst_sirov;
                   }
                    assert($sacuvano);

                    $odeljakobjava = new OdeljakObjava();
                    $odeljakobjava->id_odeljak = Odeljak::ID_NASLOVNA;
                    $odeljakobjava->id_objava = $objava->id;
                    $odeljakobjava->save();
                    //preslovaljavam na cirilicu

                    $objava->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_JEZIK);
                                        
                    $objava->naslov = Helper::lat2cirSacuvajtagove($naslov);
                    $objava->uvod = Helper::lat2cirSacuvajtagove($uvod);
                    $objava->tekst = Helper::lat2cirSacuvajtagove($tekst);
                    $objava->tekst_sirov = $objava->tekst;
                    $objava->save();

                    //upisujem prevod ako postoji
                    $vestEn = StaraVestEn::model()->findByAttributes(array('dan'=>$dan, 'mesec'=>$mesec, 'godina'=>$godina));
                    if($vestEn)
                    {
                        $objava->setAktivanjezikNapraviAkoNePostoji(Helper::ID_ENGLESKI_JEZIK);
                        $objava->naslov = $vestEn->naslov;
                        $objava->uvod = $vestEn->prvi_pasus;
                        $objava->tekst = $vestEn->tekst;
                        $objava->tekst_sirov = $vestEn->tekst;
                        $objava->save();
                    }

                    //stara galerija je niz koji sadrzi podatke o slikama
                    $staragalerija = StaraGalerija::model()->findAllByAttributes(array('dan'=>$dan, 'mesec'=>$mesec, 'godina'=>$godina, 'sifra'=>'VES'));
                    if($staragalerija)
                    {
                        $galerija = new GalerijaModel();
                        $galerija->pozicija = GalerijaModel::DOLE;
                        $galerija->direktorijum = '/images/objave/stare-slike';
                        $galerija->save();
                        $objava->id_galerija = $galerija->id;
                        $sacuvano = $objava->save();
                        assert($sacuvano);

                        $folder = Slika::ROOT_FOLDER . 'objave/stare-slike/';
                        $redosled = 0;
                        foreach($staragalerija as $staraslika)
                        {
                            $slika = Slika::model()->napraviNovi(Helper::ID_SRPSKI_LATINICA);

                            $slika->url = $folder . $staraslika->slika . '.jpg';
                            $slika->tekst = $staraslika->naslov;
                            $slika->alt = $staraslika->naslov;
                            try
                            {
                                @$thumb = PhpThumbFactory::create($slika->getAbsPutanja(), array('jpegQuality' => 80));
                                @$thumb->adaptiveResize(Slika::THUMB_SIRINA, Slika::THUMB_VISINA);
                                @$thumb->save($slika->getThAbsPutanja());
                            }
                            catch(Exception $ex)
                            {
                                continue;
                            }
                            
                            $slika->save();

                            if($redosled === 0)
                            {
                                $objava->url_slika = $slika->getThAbsPutanja();
                            }

                            $slika->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_JEZIK);
                            $slika->tekst = Helper::lat2cirSacuvajtagove($staraslika->naslov);
                            $slika->alt = $slika->tekst;
                            $slika->save();

                            if($staraslika->nasloveng)
                            {
                                $slika->setAktivanjezikNapraviAkoNePostoji(Helper::ID_ENGLESKI_JEZIK);
                                $slika->tekst = $staraslika->nasloveng;
                                $slika->alt = $slika->tekst;
                                $slika->save();
                            }
                            $slika->save();
                            $galerijaslika = new GalerijaSlika();
                            $galerijaslika->redosled = $redosled;
                            $galerijaslika->id_galerija = $galerija->id;
                            
                            assert($galerijaslika->id_galerija);
                            
                            $galerijaslika->id_slika = $slika->id;
                            $galerijaslika->save();
                            $redosled++;
                        }
                    }

                    $sacuvano = $objava->save();
                    assert($sacuvano);

                }
                $trans->commit();
                echo 'podaci su uspesno upisani';
            }
            catch(Exception $e)
            {
                $trans->rollBack();
                echo 'transakcija je ponistena '. $e->getMessage();
            }
        }

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())				
                            $this->redirect(Helper::createI18nUrl ('objava/admin'));
                            //$this->redirect(Yii::app()->user->returnUrl);
                            
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
        
        
        public function actionUpload()
        {

                /**
                 * upload.php
                 *
                 * Copyright 2009, Moxiecode Systems AB
                 * Released under GPL License.
                 *
                 * License: http://www.plupload.com/license
                 * Contributing: http://www.plupload.com/contributing
                 */

                // HTTP headers for no cache etc
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");

                // Settings
                $targetDir = Slika::getUploadDir();

                //$cleanupTargetDir = false; // Remove old files
                //$maxFileAge = 60 * 60; // Temp file age in seconds

                // 5 minutes execution time
                @set_time_limit(5 * 60);

                // Uncomment this one to fake upload time
                // usleep(5000);

                // Get parameters
                $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
                $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
                $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

                // Clean the fileName for security reasons
                $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

                $fileName = strtolower($fileName);

                //proveravam ekstenziju, dozvoljene su samo jpg, jpeg, gif i png
                if(Helper::getEkstenzija($fileName) === false)
                    die();
                
                // Make sure the fileName is unique but only if chunking is disabled
                if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
                        $ext = strrpos($fileName, '.');
                        $fileName_a = substr($fileName, 0, $ext);
                        $fileName_b = substr($fileName, $ext);

                        $count = 1;
                        while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                                $count++;

                        $fileName = $fileName_a . '_' . $count . $fileName_b;
                }

                // Create target dir
                if (!file_exists($targetDir))
                        @mkdir($targetDir);

                // Remove old temp files
                /* this doesn't really work by now

                if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                        while (($file = readdir($dir)) !== false) {
                                $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                                // Remove temp files if they are older than the max age
                                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
                                        @unlink($filePath);
                        }

                        closedir($dir);
                } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
                */

                // Look for the content type header
                if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
                        $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

                if (isset($_SERVER["CONTENT_TYPE"]))
                        $contentType = $_SERVER["CONTENT_TYPE"];

                // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
                if (strpos($contentType, "multipart") !== false) {
                        if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                                // Open temp file
                                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                                if ($out) {
                                        // Read binary input stream and append it to temp file
                                        $in = fopen($_FILES['file']['tmp_name'], "rb");

                                        if ($in) {
                                                while ($buff = fread($in, 4096))
                                                        fwrite($out, $buff);
                                        } else
                                                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                                        fclose($in);
                                        fclose($out);
                                        @unlink($_FILES['file']['tmp_name']);
                                } else
                                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                        } else
                                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
                } else {
                        // Open temp file
                        $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                        if ($out) {
                                // Read binary input stream and append it to temp file
                                $in = fopen("php://input", "rb");

                                if ($in) {
                                        while ($buff = fread($in, 4096))
                                                fwrite($out, $buff);
                                } else
                                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                                fclose($in);
                                fclose($out);
                        } else
                                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }

                // Return JSON-RPC response
                die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');            
        }        
}