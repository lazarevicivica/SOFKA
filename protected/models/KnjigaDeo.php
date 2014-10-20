<?php

class KnjigaDeo extends CActiveRecord
{
    public $azuriraj_tekst = false;
    
    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }
    
    public static function cmpBroj($a, $b) 
    {
        if (intval($a['broj']) === intval($b['broj'])) 
            return 0;
        return (intval($a['broj']) < intval($b['broj'])) ? -1 : 1;
    }
    
    public function tableName()
    {
            return 'knjiga';
    }   
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
            // NOTE: you should only define rules for those attributes that
            // will receive user inputs.
            return array(
                    array('id_zbirka, id_vrsta_gradje, url_slike', 'required'),
                    array('id_zbirka, id_objava, id_vrsta_gradje, dan, mesec, godina, indeks_prve_stranice, azuriraj_tekst', 'numerical', 'integerOnly'=>true),
                    array('naslov, autor, inv_br, url_slike, cobiss', 'length', 'max'=>255),
                    array('json_sadrzaj, json_desc, sadrzaj, izdanje, tekst_putanja', 'safe'),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, id_zbirka, json_desc, naslov, json_sadrzaj, autor, inv_br, cobiss, izdanje', 'safe', 'on'=>'search'),
            );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
            return array(
                    'zbirka' => array(self::BELONGS_TO, 'Zbirka', 'id_zbirka'),
                    'vrstaGradje' => array(self::BELONGS_TO, 'VrstaGradje', 'id_vrsta_gradje'),
            );
    }    

    private function getTextIzFajla()
    {
        if(empty($this->tekst_putanja) || ! file_exists($this->tekst_putanja))
        {
            $this->addError('tekst_putanja', 'Морате навести путању до текстуалних фајлова!');
            throw new Exception;
        }            
        $arStranice = array();
        if(is_dir($this->tekst_putanja))
        {
            $this->tekst_putanja = rtrim($this->tekst_putanja, '/\\') . '/';
            $handle = opendir($this->tekst_putanja);
            if (! $handle)
                throw new Exception('Погрешна путања локалног фолдера!');
            //citam sve fajlove sa date putanje                
            while (false !== ($fajl = readdir($handle)))
            {
                //ako nije fajl ili ako nema txt ekstenziju preskace se
                if( ! is_file($this->tekst_putanja . $fajl) || ! Helper::getEkstenzija($fajl, array('txt',)) )
                    continue;                                                                                  
                //procitaj vrednost brojaca iz naziva fajla
                $broj = Helper::prefixBrojacEkstenzija($fajl);
                $broj = $broj['brojac'];
                //ako brojac ne postoji izbaci izuzetak         
                if( ! $broj)
                    throw Exception('Постоји текстуални фајл који није нумерисан!');
                //ucitaj tekst iz fajla
                $tekst = file_get_contents($this->tekst_putanja . $fajl);
                //upisi tekst i brojac u niz
                $arStranice[] = array('tekst'=>$tekst, 'broj'=>$broj);                        
            }
            uasort($arStranice, array('self', 'cmpBroj'));
        }
        else //tekst iz tekstualnog ili pdf fajla
        {
            $putanja = $this->tekst_putanja;
            //ako se radi o pdf falju izvlacim tekst i upisujem u txt fajl
            if(strtolower(substr($putanja, strlen($putanja)-3, 3)) === 'pdf')
            {
                $pdf = escapeshellarg($putanja);
                //$putanja = substr($putanja, 0, strlen($putanja)-3) . 'txt';
                $putanja = $temp_file = tempnam(sys_get_temp_dir(), 'txt');
                exec("pdftotext $pdf $putanja");
            }
            $stranice = trim(file_get_contents($putanja));
            $stranice = rtrim($stranice, "\f");
            $arExp = explode("\f", $stranice);
            $broj = 0;
            foreach($arExp as $tekst)
                $arStranice[] = array('tekst'=>$tekst, 'broj' => ++$broj);
        }                        
        return $arStranice;
    }    
    
    protected function azurirajTekst()
    {
        if( ! $this->id)
        {
            if( ! $this->save(false)) //cuvam knjigu da bih dobio vrednost primarnog kljuca, koji je serial. Validacija je false.
                throw new Exception('Примарни кључ књиге није познат');                                       
        }                   
        //citam sve fajlove sa date putanje
        $arStranice = $this->getTextIzFajla(); 
        $descBroj = 0;
        if( ! empty($this->json_desc))
        {
            $arDesc = CJSON::decode($this->json_desc);
            $descBroj = $arDesc['broj_strana'];            
            $arBroj = count($arStranice);
            //proveri da li se broj txt fajlova slaze sa brojem stranica                    
            if($descBroj && ($arBroj !== $descBroj))
                throw new Exception("Број текстова ($arBroj) не одговара броју страница из метаподатака($descBroj)!");
        }
        //za svaki clan niza upisi tekst u novi objekat
            $broj = 0;
        //brisem postojece stranice, ako ih ima
            Yii::app()->db->createCommand("DELETE FROM stranica_tekst WHERE id_knjiga=$this->id")->execute();                     
            foreach($arStranice as $s)
            {
                $stranica = new StranicaTekst();
                $stranica->id_knjiga = $this->id;                         
                $stranica->broj = ++$broj;
                $stranica->tekst = $s['tekst'];
                if( ! $stranica->save())
                    throw new Exception('Грешка при снимању странице!');
            }                            
    }         
    
    public function sacuvaj()
    {
        if($this->azuriraj_tekst && $this->tekst_putanja)
            $this->azurirajTekst ();
        if( ! $this->save() )
        {
            $greske = $this->getErrors();
            var_dump($greske);
            //foreach($greske as $key=>$greska)
            //    $msg .= $greska . '<br/>';
            throw new Exception ('Неуспех при снимању публикације!<br/>' /*. $msg*/);
        }
        return true;
    }     
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
            return array(
                    'id' => 'Ид',
                    'id_zbirka' => 'Збирка',
                    'id_vrsta_gradje' => 'Врста грађе',
                    'json_desc' => 'Опис приказа',
                    'json_sadrzaj' => 'Садржај',
                    'naslov' => 'Наслов',
                    'autor' => 'Аутор',
                    'inv_br' => 'Инвентарни бр',
                    'izdanje' => 'Издање',
                    'url_slike' => 'УРЛ корице',
                    'dan' => 'Дан',
                    'mesec' => 'Месец',
                    'godina' => 'Година',
            );
    }    
    
    protected function beforeSave()
    {
        $this->postoji_autor = $this->autor ? 1 : 0;
        $this->postoji_sort_datum = $this->godina ? 1 : 0;                   
        if($this->godina)
        {                
            $godina = $this->godina;                
            $mesec = $this->mesec ? $this->mesec : 1;
            $dan = $this->dan ? $this->dan : 1;
            $this->sort_datum = "$godina-$mesec-$dan";
        }
        if($this->sadrzaj)
        {                   
            try
            {
                $this->json_sadrzaj = SadrzajKnjige::generisiSadrzaj($this->sadrzaj);
            }
            catch (Exception $e)
            {
                $this->addError('sadrzaj', $e->getMessage());
                return false;
            }
        }            
        return parent::beforeSave();
    }
    
        public function getDanMesecGodina()
        {
            $danMesecGodina = '';
            if($this->dan != NULL && $this->dan != '')
                $danMesecGodina.=$this->dan.'. ';
            if($this->mesec != NULL && $this->mesec != '')
                 $danMesecGodina.= $this->getMesec().' ';
            elseif($danMesecGodina != '')
                $danMesecGodina .= '?? ';
            if($this->godina != NULL && $this->godina != '')
                $danMesecGodina .= $this->godina.'.';
            elseif($danMesecGodina != '')
                $danMesecGodina .= '????.';
            return $danMesecGodina;
        }

        public function getMesec()
        {
            if($this->mesec == NULL || $this->mesec == '')
                return '';
            $intMesec = intVal($this->mesec);
            switch ($intMesec)
            {
                case 1:
                    return 'јануар';
                case 2:
                    return 'фебруар';
                case 3:
                    return 'март';
                case 4: 
                    return 'април';
                case 5:
                     return 'мај';
                case 6:
                     return 'јун';
                case 7:
                     return 'јул';
                case 8:
                     return 'август';
                case 9:
                     return 'септембар';
                case 10:
                     return 'октобар';
                case 11:
                     return 'новембар';
                case 12:
                     return 'децембар';
                default:
                     return '';
            }
        }    
}
