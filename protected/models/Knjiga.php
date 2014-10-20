<?php
Yii::import('ext.phpThumb.PhpThumbFactory');
class Knjiga extends Objava
{
        public $knjiga; //KnjigaDeo
        
        public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

        public function __construct($scenario = '')
        {
            parent::__construct($scenario);
            if($scenario == 'search')
            {
                if(!empty($this->knjiga))
                    $this->knjiga->id_vrsta_gradje = NULL;
            }
        }
        
        public static function cmpBrojac($a, $b) 
        {
            if (intval($a['brojac']) === intval($b['brojac'])) 
                return 0;
            return (intval($a['brojac']) < intval($b['brojac'])) ? -1 : 1;
        }         

	public function rules()
	{
            
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(	
                        array('naslov', 'required'),
                        array('url_slika, tip, tekst_sirov, uvod, tagovi, jsongalerija, draft', 'safe'),
			array('br_komentara, datum, id_clan, id_jezik_originala, id_galerija, status, zakljucano', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, autor, naslovSr, naslovEn, odeljci, tagovi, datum, id_clan, id_jezik_originala, status, zakljucano', 'safe', 'on'=>'search'),
		);
	}        
               
        public function napraviNovi($id_jezik_originala, $scenario = null)
        {
            $objekat = parent::napraviNovi($id_jezik_originala, $scenario = null);
            $objekat->knjiga = new KnjigaDeo();
            return $objekat;
        }
        
        public static function getTagUrl($tag, $url=null)
        {
            if(empty($url))
                $url = Zbirka::root()->getUrl();
            $url = ltrim($url, '/');
            $jezikGet = '';
            $jezik = Yii::app()->language;
            if($jezik !== Yii::app()->getPodrazumevaniJezik())
                $jezikGet = '&jezik='.$jezik;
            $url = '/'.$url.'?df[kljucneReci]='. urlencode($tag['naziv']) . $jezikGet;   
            return $url;
        }
        
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'objava';
	}
        
        /*override Objava::onAzurirajPreCommit*/
        protected function onAzurirajPreCommit($galerija)
        {
            $this->knjiga->id_objava = $this->id;
            return $this->knjiga->sacuvaj();
        }
        /*override*/
        protected function onPosleAzuriranja()
        {
            $id = $this->knjiga->id;
            //if($this->knjiga->azuriraj_tekst) 
            if( ! Yii::app()->db->createCommand("SELECT indeksiraj_knjigu($id)")->execute())
                throw new Exception('Индекс књиге није креиран!');            
        }
        
        public static function getKnjigaDeo($id_objava)
        {
            return Yii::app()->db->createCommand()
                    ->select('k.id, k.id_objava, k.indeks_prve_stranice, i18n.naslov, i18n.tekst, k.id_zbirka, k.json_desc, k.autor, k.inv_br, k.izdanje, k.url_slike, k.br_pregleda, k.cobiss, k.dan, k.mesec, k.godina, k.id_vrsta_gradje, k.tekst_putanja, (k.knjiga_tsvector IS NOT NULL) AS sadrzi_indeks')
                    ->from('knjiga k')
                    ->join('i18n_objava i18n', 'k.id_objava=i18n.id_objava')
                    ->where('k.id_objava=:id_objava AND i18n.id_jezik=:jezik', array(':id_objava'=>$id_objava, ':jezik'=>Helper::getAppjezikId()))
                    ->queryRow();            
        }
        
        private static function generisiNazivDirektorijuma($brKnjiga)
        {
            return dechex($brKnjiga);
        }
        
        public static function getUlazniDir()
        {
            
            return Helper::kosaCrta(Yii::app()->params['digital']['ulazniTgzDir']);
        }
        
        private static function getOdrediste()
        {
            //return Helper::kosaCrta(Yii::app()->params['digital']['odrediste']);  
            
            //procitaj naziv poslednje odredisnog direktorijuma            
            $podesavanja = Podesavanja::model()->find();
            $novoPodesavanje = false;
            if(! $podesavanja)
            {
                $novoPodesavanje = true;
                $podesavanja = new Podesavanja();
            }
            else
                $odrediste = self::getDigitalRootDir() . $podesavanja->digital_direktorijum;
            //nadji max id broj knjige
            $brKnjiga = Yii::app()->db->createCommand('SELECT max(id) FROM knjiga')->queryScalar();
            //ako je ostatak pri deljenju sa n (npr n=500) generisi naziv direktorijuma
            $n = Yii::app()->params['digital']['maxDir'];
            if( $brKnjiga % $n === 0 || $novoPodesavanje)
            {
                $dir = self::generisiNazivDirektorijuma($brKnjiga);
                //spoji putanju do rut direktorijuma sa generisanim nazivom            
                $odrediste = self::getDigitalRootDir() . $dir;
            }
            //ako ne postoji putanja do takvog direktorijuma onda ga kreiraj
            if( ! is_dir($odrediste))
            {
                if( mkdir($odrediste) === false)
                    throw new Exception ('Неуспех при креирању одредишног директоријума');
                
                //upisi u bazu putanju do odredista
                $podesavanja->digital_direktorijum = $dir;
                $podesavanja->save();
            }
            return $odrediste;
        }
        
        private static function getDigitalRootDir()
        {
            return Helper::kosaCrta(Yii::app()->params['digital']['rootDir']);   
        }
        /**
         * Vraca putanju do tgz fajla cije ime pocinje $invBr_ i nalazi se u direktorijumu $izvor
         * @param type $invBr
         * @param type $izvor 
         */
        public static function getTgz($invBr, $dir)
        {
            $dir = Helper::kosaCrta($dir);
            $handle = opendir($dir);   
            while (false !== ($fajl = readdir($handle)))
            {
                if( ! Helper::getEkstenzija($fajl, array('tgz')))
                        continue;
                $delovi = explode('_', $fajl);
                if($delovi[0] === $invBr)
                {                    
                    closedir($handle);
                    return $dir.$fajl;
                }
            }
            return false;
        }
        
        private static function getDirPublikacije($invBr, $dir)
        {
            $dir = Helper::kosaCrta($dir);
            $handle = opendir($dir);   
            while (false !== ($fajl = readdir($handle)))
            {
                if( ! is_dir($dir.$fajl) )
                        continue;
                $delovi = explode('_', $fajl);
                if($delovi[0] === $invBr)
                {
                    closedir($handle);
                    return $dir.$fajl;
                }
            }
            return false;            
        }
        
        /**
         * Knjiga se nalazi u direktorijumu $dir i naziv je u obliku $invBr_naziv-knjige[.tgz]
         * 
         * @param type $invBr
         * @param type $dir
         * @return mixed false ako knjiga ne postoji, inace putanju do direktorijuma 
         */
        private static function otpakujKnjigu($invBr, $dir)
        {      
            $izvor = Helper::kosaCrta($dir);
            $izvorTgz = self::getTgz($invBr, $izvor);
            if( $izvorTgz === false)
                throw new Exception("Не постоји тгз архива за инвентарни број $invBr");
            $odrediste = self::getOdrediste();                                                         
            //ako postoji folder $invBr_nebitan-deo javi gresku
            if( self::getDirPublikacije($invBr, $odrediste) !== false)
                throw new Exception("Публикација са инвентарним бројем $invBr је већ отпакована. Обратите се администратору за ручну исправку.");
            //otpakuj tgz fajl
            //exec("tar xzf $izvorTgz $odrediste");
            ini_set( "memory_limit","512M");
            $p = new PharData($izvorTgz);
            $p->decompress(); 
            $tarPutanja = substr($izvorTgz, 0, strlen($izvorTgz)-3) . 'tar';
            $phar = new PharData($tarPutanja);
            $phar->extractTo($odrediste);
            //$phar->
            //izbrisi tgz fajl
            //unlink($izvorTgz);
            unlink($tarPutanja);
            //vrati putanju do otpakovanog foldera
            $dirPublikacije = self::getDirPublikacije($invBr, $odrediste);
            if($dirPublikacije === false)
                throw new Exception("Отпакована публикација није у одговарајућем формату. Директоријум са инвентарним бројем $invBr не може бити нађен.");
            return $dirPublikacije;
        }               
        
        /**
         * Ucitava podatke o knjizi iz Cobissa u niz.
         * @param type $invBr 
         */
        public static function getCobiss($invBr)
        {
            $obrada = new ObradaDigital();
            $cobiss = new Cobiss($invBr, $invBr, 'HttpCitac', $obrada);
            $cobiss->izvrsi();
            return $obrada->rezultat[0];
        }       
        
        private static function getKorice($dir, $meta)
        {
            $dir = Helper::kosaCrta($dir);
            $kor = $dir.'kor/';
            if(is_dir($kor))
            {
                $handle = opendir($kor);   
                while (false !== ($fajl = readdir($handle)))
                {
                    if(Helper::getEkstenzija($fajl, array('jpg')) && ! is_dir($kor.$fajl))
                    {
                        $putanja = $kor.$fajl;
                        $size = getimagesize($putanja);
                        $sirina = $size[0];
                        if($sirina > 100)
                        {
                            $slika = PhpThumbFactory::create($putanja, array('jpegQuality' => 90));
                            $slika->resize(100, 170);
                            $slika->save($putanja);
                        }
                        return rtrim(self::getDigitalUrl($putanja), '/');
                    }
                }
            }
            elseif($meta)
            {
                $url = self::getDigitalUrl($dir.'thu/'.$meta['prefix'].$meta['brojac'].'.'.$meta['ekstenzija']);
                $url = rtrim($url, '/');
                return $url;
            }
            return false;
        }
        
        public static function getTekst($putanja)
        {
            $dir = Helper::kosaCrta($putanja);
            $txtDir = $dir . 'txt/';
            $sadrziTxtFajl = false;
            if(is_dir($txtDir))
            {
                $handle = opendir($txtDir);   
                while (false !== ($fajl = readdir($handle)))
                {
                    if(is_dir($txtDir.$fajl))
                            continue;
                    if(Helper::getEkstenzija($fajl, array('pdf')))
                            return $txtDir.$fajl;
                    if(Helper::getEkstenzija($fajl, array('txt')))
                            $sadrziTxtFajl = true;
                }
                if($sadrziTxtFajl)
                    return $txtDir;
                return false;
            }
        }
        
        /**
         * 
         * Prevodi putanju do direktorijuma ili fajla u URL adresu.
         * Rut putanju iz fajl sistema menja rut url adresom.
         * @param type $fajl putanja do direktorijuma ili fajla u okviru fajl sistema koji sadrzi slike stranica  
         * @return String
         */
        private static function getDigitalUrl($putanja)
        {
            $rootUrl = Helper::kosaCrta(Yii::app()->params['digital']['rootUrl']);
            $rootDir = self::getDigitalRootDir();
            $pocetniIndex = strlen($rootDir);
            $duzina = strlen($putanja) - strlen($rootDir);
            $relativnaPutanja = substr($putanja, $pocetniIndex, $duzina);            
            return Helper::kosaCrta($rootUrl . ltrim($relativnaPutanja, '/\\'));
        }
        
        /**
         * Sortira slike po brojacu, pa zatim upisuje nove vrednosti brojaca. 
         * @param string $dir
         * @throws Exception 
         * @return array Vraca niz gde je element sa indeksom nula SIRINA, a element sa indeksom 1 VISINA prve slike (slika sa brojacem 001).
         */
        private static function preimenujSlike($dir, $naziv, array & $kvalitet)
        {
            $dir = rtrim($dir, '/\\') . '/';
            $prviFajl = true;
            $slike = array();
            $prefix = '';
            $handle = opendir($dir);   
            $brojSlika = 0;
            while (false !== ($fajl = readdir($handle)))
            {
                if( ! Helper::getEkstenzija($fajl, array('jpg')))
                        continue;
                $brojSlika++;
                $prefBrExt = Helper::prefixBrojacEkstenzija($fajl);
                if($brojSlika === 1)
                    $prefix = $prefBrExt['prefix'];
                if($prefix !== $prefBrExt['prefix'])
                    throw new Exception('Постоје слике са различитим префиксима у називу');
                $slike[] = $prefBrExt;                
            }
            if($brojSlika === 0)
                throw new Exception("Директоријум $dir не садржни ни једну jpg слику!");
            
            $brojCifara = 3;
            if($brojSlika > 999)
                $brojCifara = 4;
            closedir($handle);
            uasort($slike, array('self', 'cmpBrojac'));
            $brojac = 0;
            $preimenovani = array();
            foreach($slike as $slika)
            {
                $brojac++;
                $staroIme = $dir . $slika['prefix'] . $slika['brojac'] . '.' . $slika['ekstenzija'];
                $broj = str_pad($brojac, $brojCifara, '0', STR_PAD_LEFT);
                $novoIme = $dir . $slika['prefix'] . $broj . '.' . $slika['ekstenzija'];
                $privremenoIme = $dir . '_' . $slika['prefix'] . $broj . '.' . $slika['ekstenzija'];;
                if(false === rename($staroIme, $privremenoIme))
                        throw new Exception('Грешка при преименовању слике!');
                $preimenovani[] = array('novoIme'=>$novoIme, 'privremenoIme'=>$privremenoIme);
            }
            foreach($preimenovani as $p)
            {
                if(false === rename($p['privremenoIme'],$p['novoIme']))
                        throw new Exception('Грешка при преименовању слике!');
            }
            $prvaSlika = $preimenovani[0]['novoIme'];
            $size = getimagesize($prvaSlika);
            $s = $size[0]; 
            $v = $size[1];
            $url = self::getDigitalUrl($dir);
            if( ! empty($naziv))
                $kvalitet[] = array('naziv'=>$naziv, 'url'=>$url, 'podrazumevana_dimenzija' => array('s'=>$s, 'v'=>$v));            
            else
                $kvalitet[] = array('url'=>$url, 'podrazumevana_dimenzija' => array('s'=>$s, 'v'=>$v));            
            return array('broj_strana'=>$brojSlika, 'prefix'=>$prefix);
        }
        
        /**
         * 
         * @param type $mid direktorijum koji sadrzi originalne slike
         * @param type $thu direktorijum u koji se upisuju smanjene slike
         */
        private static function kreirajThu($mid, $thu)
        {
            $mid = Helper::kosaCrta($mid);
            $thu = Helper::kosaCrta($thu);
            $handle = opendir($mid);   
            while (false !== ($fajl = readdir($handle)))
            {
                if( ! Helper::getEkstenzija($fajl, array('jpg')) || is_dir($mid.$fajl))
                    continue;
                $original = $mid.$fajl;
                $slika = PhpThumbFactory::create($original, array('jpegQuality' => 90));
                $slika->resize(100, 170);
                $slika->save($thu.$fajl);
            }                        
        }
        
        private static function obradiSlike($dir)
        {
            $ret = array();
            $putanja = rtrim($dir, '/\\');
            $mid = '';       
            $kvalitet = array();
            if( is_dir($putanja . '/min' ))
            {
                $mid = $putanja . '/min';           
                self::preimenujSlike($mid, 'мање', $kvalitet);      
            }
            if( is_dir($putanja . '/mid' ))
            {
                $mid = $putanja . '/mid';
                self::preimenujSlike($mid, 'веће', $kvalitet);      
            }
            if(is_dir($putanja . '/std'))
            {
                $mid = $putanja . '/std';
                self::preimenujSlike($mid, 'стандардни', $kvalitet);      
            }
            if(empty($mid))
                return false;
                        
            //ako ne sadrzi thu, onda ga treba kreirati
            $thu = $putanja . '/thu';
            if( ! is_dir($putanja . '/thu'))
            {            
                if(mkdir($thu) === false)
                    throw new Exception("Грешка при креирању директоријума $thu"); 
                self::kreirajThu($mid, $thu);
            }
            $minijaturne = array();
            $meta = self::preimenujSlike($thu, null, $minijaturne);             
            $ret['kvalitet'] = $kvalitet;
            $ret['minijaturne'] = $minijaturne[0]; 
            //$ret['minijaturne'] = json_decode(json_encode($minijaturne, JSON_FORCE_OBJECT), false ); //konvertujem niz $minijaturne u objekat.
            $ret['prefix'] = $meta['prefix'];
            $ret['broj_strana'] = $meta['broj_strana'];
            return $ret;
        }
        
        private static function dopuniMetaPodatke(array & $meta)
        {
            $meta['ekstenzija'] = "jpg";
            $meta['brojac']  = "001";
            if( ! empty($meta['broj_stranica']))
                if($meta['broj_stranica'] > 999)
                    $meta['brojac'] = '0001';
            $meta['podrzan_tekst'] = false; 
            $meta['podrzane_slike'] = true;
            $meta['podrzan_slajd_prikaz'] = true;
            $meta['podrzan_sadrzaj'] = false;
            $meta['podrzan_neprekidni_prikaz'] = true; 
            $meta['neprekidne_default'] = true; 
        }
        
        
        public static function dozvoljenaAutomatskaObrada($clan)
        {
            if($clan->isSuperAdministrator())
                return true;
            $idOdeljakDigital = Odeljak::ID_DIGITALNA_BIBLIOTEKA;
            $idClan = $clan->id;
            $prava = ClanOdeljak::model()->find("id_clan=$idClan AND id_odeljak=$idOdeljakDigital");
            if(empty($prava))
                return false;
            switch ($prava->uloga)
            {
                case Uloga::ADMINISTRATOR:
                case Uloga::UREDNIK:
                case Uloga::PISAC:
                    return true;
                default: 
                    return false;
                    
            }
        }
  
                
        /**
         * Zipovan folder ivnBr_naziv-knjige.tgz
         * 
         * @param String $invBr 
         * @param String $dir     putanja do direktorijuma koji sadrzi publikaciju
         */
        public static function obradiKnjigu($invBr, $dir)
        {
            set_time_limit(0);
            $ret = array();
            if( ! empty($dir))
            {
                try
                {
                    if( ! is_dir($dir))
                        throw new Exception("Улазни директоријум $dir са тгз фајловима није могао бити отворен!");                
                    $putanja = self::otpakujKnjigu($invBr, $dir);
                    $meta = self::obradiSlike($putanja);
                    if($meta)
                    {
                        self::dopuniMetaPodatke($meta);
                        $ret['meta'] = $meta;
                    }
                    $urlKorice = self::getKorice($putanja, $meta);
                    if($urlKorice)
                        $ret['korice'] = $urlKorice;
                    $tekstPutanja = self::getTekst($putanja);
                    if($tekstPutanja)
                        $ret['tekstPutanja'] = $tekstPutanja;
                }
                catch(Exception $e)
                {
                    $ret['greskaObrade'] = $e->getMessage();
                }
            }
            //podaci iz cobissa
            try
            {
                $ret['cobiss'] = self::getCobiss($invBr);
            }
            catch(Exception $e)
            {
                $ret['greskaCobiss'] = $e->getMessage();
            }                        
            
            return $ret;            
        }
              
}