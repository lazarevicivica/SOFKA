<?php

function cmp($a, $b)
{
    return strcmp($a['sort'], $b['sort']);
}

class NovineGodisteForm extends CFormModel
{
    public $id_zbirka;
    public $id_vrsta_gradje;
    public $folderKvalitet;
    public $thuFolder;
    public $lokalniFolder;
    public $webPutanja;
    public $brojac;
    public $ekstenzija;
    public $preskociBezDatuma;
    public $kljucneReci;
    
    public function __construct()
    {
        $this->id_vrsta_gradje = 2;
        $this->thuFolder = 'thu';
        $this->folderKvalitet = 'min:мање, mid:веће';
        $this->brojac = '001';
        $this->ekstenzija = 'jpg';
        $this->preskociBezDatuma = false;
    }
    
    public function rules()
    {
        return  
            array(
                array('id_zbirka, id_vrsta_gradje, folderKvalitet, thuFolder, lokalniFolder, webPutanja, preskociBezDatuma', 'required'),
                array('webPutanja', 'url'),
                array('kljucneReci', 'safe'),
            
            );
    }
    
    public function attributeLabels()
    {
        return array(
            'id_vrsta_gradje' => 'Врста грађе',
            'folderKvalitet' => 'Квалитет',
            'thuFolder' => 'Минијатурне',
            'lokalniFolder' => 'Фолдер годишта',
            'webPutanja' => 'Веб путања',
            'preskociBezDatuma' => 'Изостави фолдере без датума',
            'kljucneReci' => 'Кључне речи',
        );
    }    
    
    private function parsirajDatum($str)
    {
        $ar = explode('.', $str);
        if(count($ar) < 3)
            return false;
            //throw new Exception("Погрешан назив <em>$str</em> фолдера. Назив треба да садржи датум!");        
        $dan = intval($ar[0]);
        $mesec = intval($ar[1]);
        $godina = intval($ar[2]);
        
        if( !$dan || !$mesec || !$godina)
            return false;
            //throw new Exception('Погрешан назив фолдера. Назив треба да садржи датум!');        
        $ret = array('dan'=>$dan, 'mesec'=>$mesec, 'godina' => $godina);
        return $ret;
    }
    
    private function parsirajNaslov($str)
    {
        $ar = explode('-', $str);
        if(count($ar) === 1)
            return $str;
        if(count($ar) === 2 && !$ar[1])
            return $str;
        $i = 0;
        $naslov = '';
        foreach($ar as $rec)
        {
            if($i++ === 0)
                continue; //preskacem prvu rec, koja bi trebalo da bude datum.
            $naslov .= $rec . ' ';            
        }
        $naslov = trim($naslov);
        return Helper::lat2cir($naslov);
    }    
    
    private function formatirajDatum($str, $format = 'ymd')
    {

        $ar = $this->parsirajDatum($str);
        if(false === $ar)
            return false;

        $dan = $ar['dan'];
        $mesec = $ar['mesec'];
        $godina = $ar['godina'];
        
        if(strlen($dan) === 1)
            $dan = '0'. $dan;
        if(strlen($mesec) === 1)
            $mesec = '0'. $mesec;
        
        if($format === 'ymd')
            return "$godina.$mesec.$dan";
        return "$dan.$mesec.$godina";
    }

    //Na osnovu slika iz thu foldera odredjuje prefix imena fajlova i broj stranica
    private function thumbPrefixIBroj($dir)
    {
        $putanja = $this->lokalniFolder . $dir . '/'. rtrim($this->thuFolder,'/') . '/';
        $brojFajlova = 0;
        if ($handle = @opendir($putanja))
        {
            while (false !== ($fajl = readdir($handle)))
            {
                $info = pathinfo($putanja.$fajl);
                if($info['extension'] === 'jpg')
                {
                    $prefix_brojac = $info['basename'];
                    $brojFajlova++;
                }
            }
            closedir($handle);
//odredjivanje prefiksa: idem unazad sve dok ne naidjem na karakter koji nije numerik.   
            $prefix_brojac = substr($prefix_brojac, 0, strlen($prefix_brojac)-4); //izbacujem .jpg iz naziva
            $i = strlen($prefix_brojac) - 1;
            for(; $i>=0; $i--)
            {
                if( !is_numeric($prefix_brojac[$i]))
                    break;
            }
            if($i === 0 && is_numeric($prefix_brojac[0]))
                $prefix = '';
            else
                $prefix = substr($prefix_brojac, 0, $i+1);   
            return array('brojStrana'=>$brojFajlova, 'prefix'=>$prefix);            
        }                
    }
    
    private function urlSlike($dir, $prefix)
    {
        return $this->webPutanja . $dir . '/' . rtrim($this->thuFolder,'/').'/' . $prefix . $this->brojac . '.'. $this->ekstenzija;
    }
    
    private function strMesec($mesec)
    {
        switch(intval($mesec))
        {
            case 1:  return 'јануар';              
            case 2:  return 'фебруар';             
            case 3:  return 'март';             
            case 4:  return 'април';             
            case 5:  return 'мај';             
            case 6:  return 'јун';             
            case 7:  return 'јул';             
            case 8:  return 'август';             
            case 9:  return 'септембар';             
            case 10:  return 'октобар';             
            case 11:  return 'новембар';             
            case 12:  return 'децембар';    
            default : return false;
        }
    }
    
    /*
     * zbirka koja se nalazi ispod $this->id_zbirka, t.j. ciji je roditelj $this->id_zbirka, a naziv odgovara
     * broju meseca $mesec. Ako ne postoji ni jedna zbirka ispod, onda funkcija vraca $this->id_zbirka
     */     
    private function idZbirka($mesec)
    {              
        if(false === $mesec)
            return $this->id_zbirka;
        $mesec = $this->strMesec($mesec);
        $roditelj = $this->id_zbirka;
        $srpski = Helper::ID_SRPSKI_JEZIK;
        $sql = 
"SELECT z.id FROM zbirka z
 JOIN i18n_zbirka i18n ON z.id = i18n.id_zbirka
 WHERE z.roditelj=$roditelj AND i18n.id_jezik = $srpski AND i18n.naziv_zbirke ILIKE('%$mesec%')";
        $id = Yii::app()->db->createCommand($sql)->queryScalar(); //TODO nastavi. Proveri sta funkcija vraca ako ni jedan red ne zadovoljava kriterijum!               
        if(false === $id)
            return $this->id_zbirka;
        return $id;
    }
    
    /**
     *
     * @param string $dir Direktorijum koji sadrzi  publikaciju (npr. 12.12.2012-Novi-put-br.-12)
     * @param type $folderKvalitet
     * @return type 
     */
    private function podrazumevanaDimenzija($dir, $folderKvalitet, $prefix)
    {
        //sve slike imaju iste dimenzije kao prva
        $putanja = $this->lokalniFolder . $dir . '/' . $folderKvalitet . '/' . $prefix . $this->brojac . '.'. $this->ekstenzija;
        if( ! file_exists($putanja))
            throw new Exception("Фолдер $dir нема одговарајуће подфолдере са различитим квалитетима слика!");
        $info = getimagesize($putanja); 
        return array('s'=>$info[0], 'v'=>$info[1]);
    }
    
    private function webPutanjaKvalitet($dir, $folderKvalitet)
    {
        return $this->webPutanja . $dir . '/' . $folderKvalitet ;        
    }
    
    private function getKvalitet($dir, $prefix)
    {
        $kvalitet = array();
        $parovi = explode(',', $this->folderKvalitet);
        if( ! $parovi)
            throw new Exception('Парови фолдер:назив квалитета нису у одговарајућем формату!');
        foreach($parovi as $par)
        {
            $arPar = explode(':', $par);
            if(count($arPar) !== 2)
                throw new Exception('Парови фолдер:назив квалитета нису у одговарајућем формату!');
            $folder = trim($arPar[0]);
            $naziv = trim($arPar[1]);
            $dimenzije = $this->podrazumevanaDimenzija($dir, $folder, $prefix);
            $url = $this->webPutanjaKvalitet($dir, $folder);
            $kvalitet[] = array(
                'naziv' => $naziv,
                'url' => $url,
                'podrazumevana_dimenzija' => $dimenzije,
            );
        }
        return $kvalitet;        
    }   
    
    /**
     *
     * @param type $dir Direktorijum sa stranicama knjige.
     * @return string|null Putanja do direktorijuma ako postoji, u suprotnom null
     */
    private function getTxtPutanja($dir)
    {
        /*$txtPutanja = rtrim($this->lokalniFolder, '/') . rtrim($dir, '/') . '/txt';
        if( is_dir($txtPutanja))
            return $txtPutanja;
        return null;       */
        return Knjiga::getTekst(rtrim($this->lokalniFolder, '/'). $dir);
    }
    
    /**
     *  TODO funkcija nije zavrsena!!! 
     *  Folderi koji sadrze publikacije treba da imaju naziv u obliku 13.9.1978-Novi-put-br.-1
     */
    public function sacuvaj()
    {     
        set_time_limit(0);  
        if( ! $this->validate())
            return false;
        $this->lokalniFolder = rtrim($this->lokalniFolder, '/') . '/';
        $this->webPutanja = rtrim($this->webPutanja, '/') . '/';
        $trans = Yii::app()->db->beginTransaction();
        try
        {
            $direktorijumi = array();
            //ulazim u lokalniFolder                
            $handle = opendir ($this->lokalniFolder);
            if (! $handle)
                throw new Exception('Погрешна путања локалног фолдера!');
            while (false !== ($dir = readdir($handle)))
            {
                if( is_dir($this->lokalniFolder . $dir) && $dir !== '.' && $dir !== '..')
                {
                    $ymd = $this->formatirajDatum($dir);// 13.09.1978-Novi-put-br.-1  postaje 1978.09.13
                    if(false === $ymd)
                    {                        
                        if($this->preskociBezDatuma)
                        {
//TODO upisi u log      
                            continue;
                        }
                        else
                            $ymd = $dir; //sortiranje se vrsi po punom nazivu direktorijuma
                    }
                    //upisujem nazive svih podfoldera (publikacija) u niz
                    $direktorijumi[] = array('naziv' => $dir, 'sort'=> $ymd);
                }                                
            }
            closedir($handle);
            //sortiram niz u rastucem redosledu po datumu(iz naziva podfoldera)
            uasort($direktorijumi, 'cmp');                        
            foreach($direktorijumi as $dir)
            {
                $arDatum = $this->parsirajDatum($dir['naziv']);

                $br_prefix = $this->thumbPrefixIBroj($dir['naziv']);               
                $brojStrana = $br_prefix['brojStrana'];
                $prefix = $br_prefix['prefix'];                                            

                $objava = Knjiga::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);   
                $objava->datum = time();
                $objava->knjiga->id_zbirka = $this->idZbirka($arDatum['mesec']);
                if($arDatum)
                {
                    $objava->knjiga->dan = $arDatum['dan'];
                    $objava->knjiga->mesec = $arDatum['mesec'];
                    $objava->knjiga->godina = $arDatum['godina']; 
                }
                $objava->naslov = $this->parsirajNaslov($dir['naziv']);
                $urlSlika = $this->urlSlike($dir['naziv'], $prefix);
                $objava->url_slika = $urlSlika;
                $objava->knjiga->url_slike = $urlSlika;

                $minijaturneDimenzija = $this->podrazumevanaDimenzija($dir['naziv'], $this->thuFolder, $prefix);                
                $arOpis = array(
                    'prefix' => $prefix,
                    'ekstenzija' => $this->ekstenzija,
                    'brojac' => $this->brojac,
                    'broj_strana' => $brojStrana,
                    'podrzan_tekst'=>false, 
                    'podrzane_slike'=>true, 
                    'podrzan_slajd_prikaz'=>true, 
                    'podrzan_sadrzaj'=>false, 
                    'podrzan_neprekidni_prikaz'=>true, 
                    'neprekidne_default'=>true,   
                    'minijaturne' => array(
                        'url'=> $this->webPutanjaKvalitet($dir['naziv'], $this->thuFolder . '/'),
                        'podrazumevana_dimenzija' => array('s'=>$minijaturneDimenzija['s'], 'v'=>$minijaturneDimenzija['v']),
                        ),
                );                
                $arOpis['kvalitet'] = $this->getKvalitet($dir['naziv'], $prefix);
                $objava->knjiga->json_desc = json_encode($arOpis);   
                if( ! empty($this->kljucneReci))
                    $objava->tagovi = $this->kljucneReci;
                $objava->knjiga->tekst_putanja = $this->getTxtPutanja($dir['naziv']);
                if( $objava->knjiga->tekst_putanja)
                    $objava->knjiga->azuriraj_tekst = 1;
                else 
                    $objava->knjiga->azuriraj_tekst = 0;
                $clan = Helper::getLogovaniClan();
                $odeljak = Odeljak::model()->findByPk(Odeljak::ID_DIGITALNA_BIBLIOTEKA);
                $odeljak->cekiran = true;
                $odeljci = [$odeljak];
                if( ! $objava->azurirajBezTransakcije($odeljci, $clan, $galerija=null) )
                    throw new Exception('Грешка приликом уписа публикације у базу!');                
            }     
            $trans->commit();
            return true;
        }
        catch(Exception $e)
        {
            $trans->rollBack();
            $this->addError('greska', $e->getMessage() );
            return false;
        }
    }
}

?>
