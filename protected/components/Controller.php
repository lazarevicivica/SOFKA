<?php
/**
 * Osnovna klasa za sve kontrolere
 */
class Controller extends CController
{
    public $adminMeni = false;
    public $metaDescription = false;
    
    public function init()
    {
        parent::init();
        $app = Yii::app();
        $jezik = $app->getPodrazumevaniJezik(); //ako nije naveden jezik u url-u onda se koristi podrazumevani
        if(isset($_POST['jezik'])){
            $jezik = $_POST['jezik'];
        }
        elseif(isset($_GET['jezik'])){
            $jezik = $_GET['jezik'];
        }

        if ($jezik !== null)
        {
            if($jezik != 'en' && $jezik != 'sr_yu' && $jezik != 'sr_sr')
                $jezik = Yii::app()->getPodrazumevaniJezik();
            
            $app->language = $jezik;
            $app->session['jezik'] = $app->language;
            $app->session['id_jezik'] = Yii::app()->getPodrazumevaniJezikId();
            
            if( ($idJezik = Helper::jezikKod2Id($jezik)) )
                $app->session['id_jezik'] = $idJezik;            
        }
        else if (isset($app->session['jezik']))
        {
            $app->language = $app->session['jezik'];
        }
    }
    
    public function setJezik($idJezik)
    {     
        $app = Yii::app();
        $kodJezika = Helper::jezikId2Kod($idJezik);  
        $app->language = $kodJezika;
        $app->session['jezik'] = $kodJezika;
        $app->session['id_jezik'] = $idJezik;
    }
    
/*
 * Vraca true ako je postavljen podrazumevani jezik, u suprotnom false
 */
    public function isDefaultjezik()
    {
        $app = Yii::app();
        return $app->language == $app->sourceLanguage;
    }

    private $portleti = array();
    public function registrujPortlet($klasa, $parametri = array(), $naPocetak = false)
    {
        $niz = array('klasa' => $klasa, 'parametri' => $parametri);
        if( ! $naPocetak)
            $this->portleti[] = $niz;
        else
            array_unshift($this->portleti, $niz);
    }
    
    public function prikaziPortlete()
    {
        foreach($this->portleti as $portlet)
        {
            $widget = $this->createWidget($portlet['klasa'], $portlet['parametri']);
            if($widget->vidljivo())
                $widget->run();
        }
    }

    protected function klasaModela()
    {
        return '';
    }
    
    /**
     * @var string the default layout for the controller view. Defaults to 'application.views.layouts.column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout='application.views.layouts.column2';
    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu=array();
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs=array();


    public $noIndex = false; //ako je true znaci da pretrazivaci (npr Google, Bing...) ne treba da indeksiraju stranicu

}