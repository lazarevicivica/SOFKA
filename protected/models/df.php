<?php
class df extends CFormModel //DigitalForm, skratio sam na df
{
    public $naslov; // za naslov i autor se takodje koristi FTS
    public $autor;
    //public $poglavlje; // ne koristi se
    public $godinaOd;
    public $godinaDo;    
    public $vrstaGradje;    
    public $kljucneReci;
    public $opis;
    
    public $ftsKomplet; //spojeni svi fts vektori
    
    public $ftsUpit;
    
    public function rules()
    {
        return  array(
            array('naslov, autor, vrstaGradje, godinaOd, godinaDo, kljucneReci, opis, ftsUpit, ftsKomplet', 'safe'),
            array('ftsUpit', 'length', 'max'=>250)
        );
    }
    
    public function setAttributes(array $values, $safeOnly=true)
    {
        parent::setAttributes($values, $safeOnly);
        $godinaOd = intval($this->godinaOd);
        if($godinaOd <= 0)
            $this->godinaOd = '';
        $godinaDo = intval($this->godinaDo);
        if($godinaDo <= 0)
            $this->godinaDo = '';
    }
    
    public function isPrazno()
    {
        return ! ($this->ftsKomplet || $this->ftsUpit || $this->naslov || $this->autor || $this->opis || $this->kljucneReci || $this->godinaOd || $this->godinaDo || $this->vrstaGradje);
    }
    
    private function filterHtml(&$labele, $kljuc, $vrednost, $osnovniUrl)
    {
        $title = Yii::t('biblioteka', 'Уклони овај филтер');
        return '<li id="filter_'.$kljuc.'"><strong>'.CHtml::encode($labele[$kljuc]).'</strong>: ' . CHtml::encode($vrednost). '<a title="'.$title.'" id="ukoni-filter_'.$kljuc.'" class="ukloni-filter-tag" href="'.$osnovniUrl.'"></a></li>';
    }
    
    /*
     * Ako je navedena pretraga teksta knjige onda vraca ftsUpit
     * Ako se radi o objedinjenoj pretrazi onda vraca ftsKomplet
     * inace vraca false.
     */
    public function getUpitZaStranice()
    {        
        if( ! empty($this->ftsUpit))
            return $this->ftsUpit;
        elseif( ! empty($this->ftsKomplet))
            return $this->ftsKomplet;
        return false;
    }
    
    public function getFilterTxt($urlUkloni)
    {
        $txt = '';
        $labele = $this->attributeLabels();
        foreach($this->attributes as $kljuc=>$vrednost)
        {
            if($vrednost)
            {
                if($kljuc === 'vrstaGradje')
                {
                    $id = intval($vrednost);
                    $id_jezik = Helper::getAppjezikId();
                    $naziv_vrste = Yii::app()->db->createCommand("SELECT naziv_vrste FROM vrsta_gradje v JOIN i18n_vrsta_gradje i18n ON v.id=i18n.id_vrsta_gradje WHERE v.id=$id AND i18n.id_jezik=$id_jezik")->queryScalar();
                    $vrednost = $naziv_vrste;
                }
                $txt .= $this->filterHtml($labele, $kljuc, $vrednost, $urlUkloni);
            }
        }
        if($txt)
            $txt = rtrim($txt, ', ');
        else
            return '';
        $title = Yii::t('biblioteka', 'Уклони све филтере');
        return "<ul>$txt<li title=\"$title\" id=\"ukloni-sve-filter-tagove\"><a href=\"$urlUkloni\">X</a></li></ul>";
    }
    
    public function attributeLabels()
    {
        return array(
            'naslov'=>Yii::t('biblioteka', 'Наслов'),		
            'autor'=>Yii::t('biblioteka', 'Аутор'),
            'poglavlje'=>Yii::t('biblioteka', 'Поглавље'),
            'godinaOd'=>Yii::t('biblioteka', 'Од године'),
            'godinaDo'=>Yii::t('biblioteka', 'до године'),            
            'vrstaGradje' => Yii::t('biblioteka', 'Врста грађе'),   
            'ftsUpit' => Yii::t('biblioteka', 'Текст'),
            'ftsKomplet' => Yii::t('biblioteka', 'Обједињено'),
            'kljucneReci' => Yii::t('biblioteka', 'Кључне речи'),
            'opis' => Yii::t('biblioteka', 'Опис'),            
        );
    }
}