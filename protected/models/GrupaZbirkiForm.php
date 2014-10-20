<?php

class GrupaZbirkiForm extends CFormModel
{

    public $json;
    public $roditelj;
    public $txtSr;
    public $txtEn;
    
    public function __construct()
    {
        $this->txtSr = '2012.';
        $this->txtEn = '2012';
        $this->json = '
[
{
    sr:{
        naziv:"Јануар", 
        opis:"Јануар {txtSr} године"
    },
    en:{
        naziv:"January", 
        opis:"January {txtSr}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Фебруар", 
        opis:"Фебруар {txtSr} године"
    },
    en:{
        naziv:"February", 
        opis:"February {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Март", 
        opis:"Март {txtSr} године"
    },
    en:{
        naziv:"March", 
        opis:"March {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Април", 
        opis:"Април {txtSr} године"
    },
    en:{
        naziv:"April", 
        opis:"April {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Мај", 
        opis:"Мај {txtSr} године"
    },
    en:{
        naziv:"May", 
        opis:"May {txtEn}"
    },
    url_slike:""
},{
    sr:
    {
        naziv:"Јун", 
        opis:"Јун {txtSr} године"
    },
    en:
        {
        naziv:"Juny", 
        opis:"Juny {txtEn}"
    },
    url_slike:""
},

{
    sr:
    {
        naziv:"Јул", 
        opis:"Јул {txtSr} године"
    },
    en:
        {
        naziv:"July", 
        opis:"July {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Август", 
        opis:"Август {txtSr} године"
    },
    en:{
        naziv:"August", 
        opis:"August {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Септембар", 
        opis:"Септембар {txtSr} године"
    },
    en:{
        naziv:"September", 
        opis:"September {txtEn}"
    },
    url_slike:""
},

{
    sr:{
        naziv:"Октобар", 
        opis:"Октобар {txtSr} године"
    },
    en:
        {
        naziv:"October", 
        opis:"October {txtEn}"
    },
    url_slike:""
},

{
    sr:
    {
        naziv:"Новембар", 
        opis:"Новембар {txtSr} године"
    },
    en:
        {
        naziv:"November", 
        opis:"November {txtEn}"
    },
    url_slike:""
},

{
    sr:
    {
        naziv:"Децембар", 
        opis:"Децембар {txtSr} године"
    },
    en:{
        naziv:"December", 
        opis:"December {txtEn}"
    },
    url_slike:""
}]';       
    }
/**
 * Parsira JSON podatke i upusuje grupu zbirki u bazu.
 */
    
    public function sacuvaj()
    {
        $trans = Yii::app()->db->beginTransaction();
        try
        {        
            $arJson = CJSON::decode($this->json);

            $i = 0;
            foreach($arJson as $arZbirka)
            {
               $zbirka = Zbirka::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);
               $zbirka->redosled = ++$i;
               //roditelj i slika je zajednicko za oba jezika
               $zbirka->roditelj = $this->roditelj;
               if($arZbirka['url_slike'])
                $zbirka->url_slike = $arZbirka['url_slike'];
               
               //sr naziv i opis
               $zbirka->naziv_zbirke = $arZbirka['sr']['naziv'];
               if($arZbirka['sr']['opis'])
               {               
                    $opis = $arZbirka['sr']['opis'];
                    $zbirka->opis = str_replace('{txtSr}', $this->txtSr, $opis);               
               }
               
               //en naziv i opis
               $zbirka->naziv_zbirkeEn = $arZbirka['en']['naziv'];
               if($arZbirka['en']['opis'])
               {               
                    $opis = $arZbirka['en']['opis'];
                    $zbirka->opisEn = str_replace('{txtEn}', $this->txtEn, $opis);               
               }

               $zbirka->sacuvajBezCommit();               
            }
            Zbirka::rekonstruisiStablo();
            $trans->commit();
        }
        catch(Exception $e)
        {
            $trans->rollBack();
            $this->addError('greska', $e->getMessage() );
            return false;
        }
        return true;
    }
    
    public function rules()
    {
            return array(
                    array('roditelj', 'numerical', 'integerOnly'=>true),
                    array('json', 'required'),
                    array('txtSr, txtEn', 'safe'),
            );
    }    
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
            return array(
                    'json' => 'Група збирки (шаблон)',
                    'txtSr' => 'txtSr',
                    'txtEn' => 'txtEn',
            );
    }
}

?>
