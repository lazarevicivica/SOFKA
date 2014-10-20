<?php

/**
 * This is the model class for table "objava".
 *
 * The followings are the available columns in table 'objava':
 * @property integer $id
 * @property integer $datum
 * @property integer $id_clan
 * @property integer $id_jezik_originala
 * @property integer $status
 * @property integer $zakljucano
 *
 * The followings are the available model relations:
 * @property jezik[] $jeziks
 * @property komentar[] $komentars
 * @property clan $id_clan0
 * @property jezik $id_jezik_originala0
 * @property tag[] $tags
 */

//require_once('../extensions/querypath/QueryPath.php');
class Objava extends I18nOptimisticLockingActiveRecord
{

    const OBJAVLJENO=1;
    const CEKA_ODOBRENJE=2;
    const OTPAD=3;
    const DRAFT=4;
    //const ARHIVA=4; //TODO razmisliti o arhiviranju. Mozda postane potrebno?

    //svojstva koja se koriste samo za pretragu
    public $naslovEn;
    public $naslovSr;
    public $odeljci;
    public $tagovi; //atribut tagovi se koristi i pri azuriranju i dodavanju nove objave
    public $autor;
    public $jsongalerija;
    
    public $draft;

    private $registarDozvola = array();
    private static $selectobjava = 'o.id, o.tip, o.datum, o.id_jezik_originala, o.br_komentara, o.url_slika, c.korisnicko_ime, c.puno_ime, c.slika,i18n_o.id_jezik, i18n_o.naslov, i18n_o.uvod, odo.top';
    /**
	 * Returns the static model of the specified AR class.
	 * @return objava the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'objava';
	}
        
        /**
         *
         * Iz nekog razloga ovo ne radi kada stavim u Init.
         * 
         */
        protected function instantiate($attributes)
        {
            $class = $attributes['tip'];            
            $objekat = new $class(null);            
            if($class === 'Knjiga')
                $objekat->knjiga = KnjigaDeo::model()->findByAttributes(array('id_objava'=>$attributes['id']));
            return $objekat;
        }         
                
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
            
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(	
                        array('naslov, tekst_sirov', 'required'),
                        array('url_slika, tip, tekst_sirov, uvod, tagovi, jsongalerija, draft', 'safe'),
			array('br_komentara, datum, id_clan, id_jezik_originala, id_galerija, status, zakljucano', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, autor, naslovSr, naslovEn, odeljci, tagovi, datum, id_clan, id_jezik_originala, status, zakljucano', 'safe', 'on'=>'search'),
		);
	}

/*            id INT  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    datum INT  NOT NULL,
    id_clan INT  NULL, -- trebalo bi da bude NOT NULL ali za stare vesti ne znam autora!!!
    id_jezik_originala SMALLINT NOT NULL DEFAULT 1,
    id_galerija INT NULL,
    br_komentara INT DEFAULT 0, -- vrednost se izracunava, ovde je zbog ubrzanja
-- statusi: 1 Objavljen  2 Ceka odobrenje  3 Arhiva(ne prikazuje se ali mu se moze pristupiti direktno preko urla), 4 Neaktivno (kao da je izbrisano ali i dalje je u bazi)
    status SMALLINT NOT NULL DEFAULT 1, INDEX(status),
    zakljucano SMALLINT NOT NULL DEFAULT 0, INDEX(zakljucano), -- ako je 1 onda se ne mogu dodavati novi komentari
    url_slika varchar(255) NULL, -- slicica koja se pojavljuje u uvodu.

*/
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		$relacije = array(			
			'rkomentari' => array(self::HAS_MANY, 'Komentar', 'id_objava'),
			'rclan' => array(self::BELONGS_TO, 'Clan', 'id_clan'),
			//'rjezik' => array(self::BELONGS_TO, 'jezik', 'id_jezik_originala'),
			'rtagovi' => array(self::MANY_MANY, 'Tag', 'objava_tag(id_objava, id_tag)'),
                        'rodeljci' => array(self::MANY_MANY, 'Odeljak', 'odeljak_objava(id_objava, id_odeljak)'),
                        'rgalerija' => array(self::BELONGS_TO, 'GalerijaModel', 'id_galerija'),
                        //'rodeljak_objava' => array(self::HAS_MANY, 'odeljak_objava', 'id_objava')
		);
                return array_merge(parent::relations(), $relacije);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'datum' => Yii::t('biblioteka', 'Датум'),
			'id_clan' => 'Id clan',
			'id_jezik_originala' => 'Id jezik Originala',
			'status' => Yii::t('biblioteka', 'Статус'),
			'zakljucano' => Yii::t('biblioteka', 'Коментари'),
                        'autor' => Yii::t('biblioteka', 'Аутор'),
                        'naslovSr' => Yii::t('biblioteka', 'Наслов српски'),
                        'naslovEn' => Yii::t('biblioteka', 'Наслов енглески'),
                        'odeljci' => Yii::t('biblioteka', 'Одељци'),
                        'tagovi' => Yii::t('biblioteka', 'Кључне речи'),
                        'naslov' => Yii::t('biblioteka', 'Наслов'),
                        'tekst_sirov' => Yii::t('biblioteka', 'Главни текст'),
                        'tekst' => Yii::t('biblioteka', 'Главни текст'),
                        'uvod'  => Yii::t('biblioteka', 'Увод'),
                        'status' => Yii::t('biblioteka', 'Статус'),
                        'draft' => Yii::t('biblioteka', 'Сачувај као незавршено')
		);
	}

        public function getLinkNaslov($id_jezik = null)
        {
            if($id_jezik)
                $this->setAktivanjezik ($id_jezik);
            if($this->status == Objava::OBJAVLJENO)
                return CHtml::link($this->naslov, $this->getUrl($id_jezik));
            else
                return CHtml::encode($this->naslov);
        }

        public function getUrl($id_jezik = null)
        {
            $klasa = strtolower(get_class($this));
            return Helper::createI18nUrl("$klasa/view", $id_jezik, array('id'=>$this->id, 'rep'=>Helper::getSEOText($this->naslov)));
        }

        public static function getUrlS($data, $rep, $id_jezik = null)
        {
            $tip = get_called_class();
            $kontroler = strtolower($tip);
            return Helper::createI18nUrl("$kontroler/view", $id_jezik, array('id'=>$data['id'], 'rep'=>$rep));
        }
        
        private function criteriaAutor($criteria)
        {
            if($this->autor)
            {
               $autor = pg_escape_string($this->autor);
               $criteria->with['rclan'] = array(
                    'select'=>false,
                    'joinType'=>'INNER JOIN',
                    'condition'=> "rclan.korisnicko_ime LIKE '%$autor%'",
                );
            }
            else
            {
              $criteria->with = array('rclan',);
            }
        }
        private function criteriaNaslovSr($criteria)
        {
            if( !empty($this->naslovSr))
            {                
                $naslovSr = pg_escape_string($this->naslovSr);                
                $uslovSr = "naslov LIKE '%$naslovSr%'";
                $criteria->with['ri18n'] = array(
                    'select' => false,
                    'joinType' => 'INNER JOIN',
                    'condition' => $uslovSr. 'AND id_jezik='.Helper::ID_SRPSKI_JEZIK,
                );
            }
        }

        private function criteriaOdeljci($criteria, $clan)
        {
            if( ! $clan->isSuperAdministrator())
            {
               //ako nije superadministrator onda prikazuje samo objave iz odeljaka za koje clan ima prava pristupa
               $uslov = '';
               if( ! empty($this->odeljci)) //uneto iz padajuce liste iz zaglavlja grida
               {
                   $criteria->params[':id_odeljak'] = $this->odeljci;
                   $uslov = 'rodeljci.id=:id_odeljak AND ';
               }
               $criteria->with['rodeljci'] = array(
                    // ne zelim da selektujem jer pravi problem
                    'select'=>false,
                    'joinType'=>'INNER JOIN',
                    'condition'=> $uslov.'rodeljci.id IN ('.implode(',',$clan->getNizIdodeljak()).')',
                );
            }
            elseif( ! empty($this->odeljci))//vrednost odeljci je uneta iz padajuce liste iz zaglavlja grida
            {
                $criteria->params[':id_odeljak'] = $this->odeljci;
                $criteria->with['rodeljci'] = array(
                    'select'=>false,
                    'joinType'=>'INNER JOIN',
                    'condition'=> 'rodeljci.id=:id_odeljak',
                );
            }
        }
        
        private function criteriatagovi($criteria)
        {
            if( ! empty($this->tagovi))
            {
                if(mb_strlen($this->tagovi,'UTF-8') < 3)
                {
                    $this->tagovi = '';
                    return;
                }
                $id_jezik = Helper::ID_SRPSKI_JEZIK;
                $cmd = Yii::app()->db->createCommand("SELECT t.id_tag FROM i18n_tag t WHERE t.naziv LIKE :naziv AND t.id_jezik=:id_jezik ORDER BY t.naziv LIMIT 20");
                $cmd->params = array(':naziv'=>"%{$this->tagovi}%", ':id_jezik' => Helper::ID_SRPSKI_JEZIK);
                $rows = $cmd->queryAll();                
                $strIn = '';
                foreach($rows as $row)                
                    $strIn .= $row['id_tag']. ',';
                if($strIn)
                    $strIn = substr($strIn, 0, strlen($strIn) - 1);
                else
                    return;                
                $criteria->with['rtagovi'] = array(
                    'select'=>false,
                    'joinType'=>'INNER JOIN',
                    'condition'=> "rtagovi.id IN ($strIn)",
                );
                
            }
        }
        
	/**
         * clan mora biti ulogovan, inace ova funkcija izbacuje izuzetak!
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
        public function search($clan)
        {
            $criteria = new CDbCriteria;
            $criteria->together = true;

    //brze bi bilo kada bi ucitao sve podatke u jednom upitu ali javlja se probelem
    //ako je veza jedan prema vise ili vise prema vise; Zato, ucitavam samo rclan.

            $this->criteriaAutor($criteria);
            $this->criteriaNaslovSr($criteria);
            $this->criteriaOdeljci($criteria, $clan);
            Helper::criteriaDatum($criteria, $this);
            $this->criteriatagovi($criteria);
            $criteria->compare('status',$this->status);
            $criteria->compare('zakljucano',$this->zakljucano);
$criteria->compare('t.tip', get_class($this)); //prikazuje samo klase tipa Objava
            //Ne prikazujem nedovrsenje, one su dostupne samo autoru u listi nedovrsene
            $criteria->addCondition('NOT (t.status = '.Objava::DRAFT.')');

            $criteria->distinct = true;

            return  new CActiveDataProvider(get_class($this), array(
                    'criteria'=>$criteria,
                    'sort'=>array(
                            'defaultOrder'=>'t.id DESC',
                    )
            ));
        }

        public  function searchNezavrsene($clan)
        {
           $criteria = new CDbCriteria;
           $criteria->together = true;

    //brze bi bilo kada bi ucitao sve podatke u jednom upitu ali javlja se probelem
    //ako je veza jedan prema vise ili vise prema vise; Zato, ucitavam samo rclan.

            $this->criteriaAutor($criteria);
            $this->criteriaNaslovSr($criteria);
//            $this->criteriaOdeljci($criteria, $clan);
            Helper::criteriaDatum($criteria, $this);
            $criteria->compare('t.id',$this->id);
            $criteria->compare('status',$this->status);
            $criteria->compare('zakljucano',$this->zakljucano);

            $criteria->addCondition("t.id_clan=$clan->id");
            $criteria->addCondition('t.status = '.Objava::DRAFT);

            $criteria->distinct = true;

            return  new CActiveDataProvider(get_class($this), array(
                    'criteria'=>$criteria,
                    'sort'=>array(
                            'defaultOrder'=>'t.id DESC',
                    )
            ));            
        }

        /**
         * @param <type> $clan  clan za koga se trazi dozvola
         * @param <type> $klasaDozvola  Uloga, UlogaKomentar
         * @param <type> $objekat
         *      Dozvola moze da se odnosi i na neki drugi objekat, na primer komentar.
         *      Taj objekat mora da bude u vezi sa Objavom i da ima svoju odgovarajucu KlasuObjave.
         *      Objekat mora da ima atribut id_clan i status.
         * @return <int> Celobrojna vrednost koja sadrzi bit flegove dozvola. Dobija se superpozicijom
         * dozvola za sve odeljke, tako da rezultujuci skup cini minimum svih zajednickih dozvola.
         * Na primer, ako objava u jednom odeljku ima dozvolu IZMENI_<X>, a u drugom nema, rezultujuca
         * dozvola nece imati IZMENI_<X>.
         */
        public function getDozvole($clan, $klasaDozvola = 'Uloga', $objekat = null, $regenerisiKes = false)
        {            
            if( ! $clan)
                return 0;
            if($clan->isSuperAdministrator())
            {
                return Uloga::DOZVOLJENO_SVE;
            }
            if($objekat === null)
                $objekat = $this;
            //ako je autor objave superadministrator onda $clan,
            //koji nije superadministrator, nema pravo da menja objavu
            if($objekat->id_clan)
            {
                $autor = Clan::model()->findByPk($objekat->id_clan);
                if($autor && $autor->isSuperAdministrator())
                    return 0;
            }
            if( ! $regenerisiKes)
            {
                if(empty($this->registarDozvola[$clan->id][$klasaDozvola][$objekat->status]))
                   $dozvole = 0;
                else
                   $dozvole = $this->registarDozvola[$clan->id][$klasaDozvola][$objekat->status];
                if($dozvole) 
                    return $dozvole;
            }
            //objava je objavljena u sledecim odeljcima
            $objavljenoU = $this->rodeljci;
            //korisnik ima definisana pravila za sledece odeljke:
            $mozeDaObjavljujeU = $clan->rCRUDOdeljci;
            //ako je objava objavljena u okviru nekog odeljka za koji clan nema
            //definisana prava pristupa vraca se 0, sto znaci da je sve zabranjeno!
            $uloge = $clan->rclan_odeljak;
            $dozvoleVlasnik = Uloga::DOZVOLJENO_SVE;
            $dozvoleNijeVlasnik = Uloga::DOZVOLJENO_SVE;
            $registarUloga = $klasaDozvola::get();
            foreach($objavljenoU as $odeljak)
            {
                $postoji = false;
                foreach($uloge as $uloga)
                {
                    if($uloga->id_odeljak === $odeljak->id)
                    {
                        $dozvoleVlasnik &= $registarUloga->getDozvoleVlasnik($uloga->uloga);
                        $dozvoleNijeVlasnik &= $registarUloga->getDozvoleNijeVlasnik($uloga->uloga);
                        $postoji = true;
                        break;
                    }
                }
                //ako je objava objavljena u okviru nekog odeljka za koji clan nema
                //definisana prava pristupa vraca se 0 koja oznacava da je sve zabranjeno!
                //posto vec prolazim kroz petlju nema potrebe da radim array_diff($objavljenoU, $mozeDaObjavljujeU)
                if( ! $postoji)
                    return 0;
            }
            if(($objekat->id_clan === $clan->id) || $objekat->isNewRecord )
                $dozvole = $dozvoleVlasnik;
            else
                $dozvole = $dozvoleNijeVlasnik;
            $this->registarDozvola[$clan->id][$klasaDozvola][$objekat->status] = $dozvole;            
            return $dozvole;
        }

        public function getDozvoleRegenerisiKes($clan, $klasaDozvola = 'Uloga', $objekat = null)
        {
            return $this->getDozvole($clan, $klasaDozvola, $objekat, true);
        }
        
        /**
         *
         * @param <clan> $clan
         * @return bool Ako clan ima pravo da izbrise tekucu objavu vraca true, u suprotnom false. 
         */
        public function mozeDaBrise($clan)
        {
            return $this->getDozvole($clan) & Uloga::IZBRISI;
        }
        
        public function mozeDaMenja($clan)
        {
            $dozvole = $this->getDozvole($clan);
            
            switch($this->status)
            {
                case self::OBJAVLJENO:
                    return $dozvole & Uloga::IZMENI_OBJAVLJENO;
                case self::CEKA_ODOBRENJE:
                    return $dozvole & Uloga::IZMENI_CEKA_ODOBRENJE;
                case self::OTPAD:
                    return $dozvole & Uloga::IZMENI_OTPAD;
                case self::DRAFT:
                    return $dozvole & Uloga::IZMENI_NOVI;
                default:
                    return false;
            }                        
        }

        public function mozeDaOtkljuca($clan)
        {
            $dozvole = $this->getDozvole($clan);
            if( ! $this->zakljucano)
                    return false;
            return $dozvole & Uloga::OTKLJUCAJ_KOMENTARE;
        }

        public function mozeDaZakljuca($clan)
        {
            $dozvole = $this->getDozvole($clan);
            if($this->zakljucano)
                    return false;
            return $dozvole & Uloga::ZAKLJUCAJ_KOMENTARE;
        }

        public function mozeDaPrevodi($clan)
        {
            $dozvole = $this->getDozvole($clan);
                                    
            $id_jezik_originala = $this->id_jezik_originala;
                        
            //postavljam aktivan prvo srpski pa onda engleski
            $idVratiPrethodnijezik = $this->getAktivanjezikId();
            $this->setAktivanjezik(Helper::ID_SRPSKI_JEZIK);
            $this->setAktivanjezik(Helper::ID_ENGLESKI_JEZIK);
            $this->setAktivanjezik($idVratiPrethodnijezik);
            //proveravam da li su ucitana oba jezika i ako nisu  
            if( ! ($this->isUcitanjezik(Helper::ID_SRPSKI_JEZIK) && $this->isUcitanjezik(Helper::ID_ENGLESKI_JEZIK)))
                    return $dozvole & Uloga::DODAJ_PREVOD;
            
            switch($this->status)
            {
                case self::OBJAVLJENO:
                    return $dozvole & Uloga::IZMENI_PREVOD_OBJAVLJENO;
                case self::CEKA_ODOBRENJE:
                    return $dozvole & Uloga::IZMENI_PREVOD_NEOBJAVLJENO;
                case self::OTPAD:
                    return $dozvole & Uloga::IZMENI_PREVOD_OTPAD;
                case self::DRAFT:
                    return false; //ne vidim razlog zatsto bi se prevodio draft tekst. 
                default:
                    return false;
            }              
        }

        public function mozeDaObjavi($clan)
        {
            $dozvole = $this->getDozvole($clan);            
            switch($this->status)
            {
                case self::OBJAVLJENO:
                    return false;
                case self::CEKA_ODOBRENJE:
                    return $dozvole & Uloga::OBJAVI_CEKA_ODOBRENJE;
                case self::OTPAD:
                    return $dozvole & Uloga::OBJAVI_OTPAD;
                case self::DRAFT:
                    return $dozvole & Uloga::OBJAVI_NOVI;
                default:
                    return false;
            }
        }
        
        public function mozeDaStaviNaCekanje($clan)
        {
            $dozvole = $this->getDozvole($clan);
            switch($this->status)
            {
                case self::OBJAVLJENO:
                    return $dozvole & Uloga::STAVI_NA_CEKANJE_OBJAVLJENO;
                case self::CEKA_ODOBRENJE:
                    return false;
                case self::OTPAD:
                    return $dozvole & Uloga::STAVI_NA_CEKANJE_OTPAD;
                case self::DRAFT:
                    return $dozvole & Uloga::STAVI_NA_CEKANJE_NOVI;
                default:
                    return false;
            }            
        }

        public function mozeDaPosaljeUOtpad($clan)
        {
            $dozvole = $this->getDozvole($clan);
            switch($this->status)
            {
                case self::OBJAVLJENO:
                    return $dozvole & Uloga::ODBACI_OBJAVLJENO;
                case self::CEKA_ODOBRENJE:
                    return $dozvole & Uloga::ODBACI_CEKA_ODOBRENJE;
                case self::OTPAD:
                    return false;
                case self::DRAFT:
                    return false;
                default:
                    return false;
            } 
        }

        public function mozeDaPromeniAutora($clan)
        {
            $dozvole = $this->getDozvole($clan);
            return $dozvole & Uloga::PROMENA_AUTORA;
        }
        
        private static function cmdListaobjava($select=null)
        {
            if($select === null)
                $select = self::$selectobjava;
            $db = Yii::app()->db;
            
            return $db->createCommand()
                    ->selectDistinct($select)
                    ->from('objava o')
                    ->join('odeljak_objava odo', 'o.id = odo.id_objava')
                    ->join('odeljak od', 'od.id = odo.id_odeljak' )
                    ->join('i18n_odeljak i18n_od', 'od.id = i18n_od.id_odeljak AND i18n_od.id_jezik=:id_jezik') //mora da postoji unos za sve jezike!                         
                    ->leftJoin('i18n_objava i18n_o', 'o.id = i18n_o.id_objava AND i18n_o.id_jezik=:id_jezik')
                    ->leftJoin('clan c', 'o.id_clan = c.id');
        }

        public static function getobjava($id_objava, $status = self::OBJAVLJENO, $id_jezik=null)
        {
            $id_objava = intval($id_objava);
            if($id_jezik === null)
                $id_jezik = Helper::getAppjezikId();
            $select = 'o.id, o.tip, o.datum, o.id_jezik_originala,o.id_galerija, o.br_komentara, o.zakljucano, c.korisnicko_ime, c.puno_ime, c.slika, i18n_o.id_jezik, i18n_o.naslov,i18n_o.uvod, i18n_o.tekst';
            $where = 'o.status = '. $status .' AND o.id='.$id_objava;
            $cmd = self::cmdListaobjava($select);
            $cmd->where($where);
            $cmd->params = array(':id_jezik'=>$id_jezik);
            return $cmd->queryRow();
        }

        public static function ucitajNaslovIUvod($id_objava, $id_jezik)
        {
           return Yii::app()->db->createCommand()
                    ->select('i18n_o.naslov, i18n_o.uvod')
                    ->from('i18n_objava i18n_o')
                    ->where("i18n_o.id_objava=$id_objava AND i18n_o.id_jezik=$id_jezik")
                    ->queryRow();

        }

        public static function ucitajNaslovUvodITekst($id_objava, $id_jezik)
        {
            return Yii::app()->db->createCommand()
                    ->select('i18n_o.naslov, i18n_o.uvod, i18n_o.tekst')
                    ->from('i18n_objava i18n_o')
                    ->where("i18n_o.id_objava=$id_objava AND i18n_o.id_jezik=$id_jezik")
                    ->queryRow();
        }

        public static function getNaslov(array & $ar)
        {
            return $ar['naslov'];
        }

        public static function getAutor(array & $ar)
        {
            //return $ar['korisnicko_ime'];
            return Clan::getImeZaPrikazS($ar);
        }
        
        public function getAutor_()
        {
            if( ! $this->rclan)
                    return '-';

            return $this->rclan->korisnicko_ime;
        }

        public function getZakljucanoImg()
        {

            if( $this->zakljucano)
               return Helper::baseUrl ('images/sajt/zakljucano.png');
            else
                return Helper::baseUrl ('images/sajt/otkljucano.png');
        }

        public function getZakljucanoTxt()
        {
            if( $this->zakljucano)
               return Yii::t('biblioteka', 'Закључано! Не могу се писати коментари за ову објаву.');
            else
                return Yii::t('biblioteka', 'Откључано! Могу се писати коментари за ову објаву!');
        }

        public function getStatusImg()
        {
            switch($this->status)
            {
                case Objava::OBJAVLJENO:
                    return Helper::baseUrl('images/sajt/objavljeno.png');
                case Objava::CEKA_ODOBRENJE:
                    return Helper::baseUrl('images/sajt/ceka.png');
                case Objava::OTPAD:
                    return Helper::baseUrl('images/sajt/otpad.png');
                case Objava::DRAFT:
                    return Helper::baseUrl('images/sajt/draft.png');
                default:
                    return Helper::baseUrl('images/sajt/ceka.png');
            }
        }

        public function getStatusTxt()
        {
          switch($this->status)
            {
                case Objava::OBJAVLJENO:
                    return Yii::t('biblioteka', 'Садржај је објављен.');
                case Objava::CEKA_ODOBRENJE:
                    return Yii::t('biblioteka', 'Чека на одобрење.');
                case Objava::OTPAD:
                    return Yii::t('biblioteka', 'Садржај се налази у корпи за ђубре.');
                case Objava::DRAFT:
                    return Yii::t('biblioteka', 'Садржај је у изради.');
                default:
                    return Yii::t('biblioteka', 'Непознат статус!!! Обратите се администратору сајта, молим Вас.');
            }            
        }

        public function getOdeljciTxt()
        {            
            $ret = '';            
            foreach($this->rodeljci as $odeljak)
            {
                $odeljak->setAktivanjezik(Helper::ID_SRPSKI_JEZIK);
                $ret .= $odeljak->naziv . ', ';
            }
            return mb_substr($ret, 0, mb_strlen($ret,'utf8')-2, 'utf8');
        }

        public function getBrojkomentara()
        {
            return $this->br_komentara;
        }

        public static function getUvod(array & $ar)
        {
            //TODO ako je uvod null onda
            return $ar['uvod'];
        }


        public static function getDatum(array & $ar)
        {
            return  date('d.m.Y', $ar['datum']);
        }

        public static function getslikaKorisnika(array & $ar)
        {
            return $ar['slika'];
        }


        public static function getslikaUvod(array & $ar)
        {
            return $ar['url_slika'];
        }


        //cita iz kesa niz koji sadrzi dva elementa koji su i sami nizovi: niz odeljci i niz tagovi
        public static function getOdeljciItagovi( array & $data, $id_jezik)
        {                                  
            $id_objava = $data['id'];
            $key = 'get_odeljci_i_tagovi_'.'objava_'.$id_objava.'jezik_'.$id_jezik;
            $lista = Yii::app()->cache->get($key);
            if( $lista === false)
            {
                $lista = array();
                $odeljci = self::izracunajListuodeljaka($id_objava, $id_jezik);
                $tagovi = self::izracunajListutagova($id_objava, $id_jezik);
                $lista['odeljci'] = $odeljci;
                $lista['tagovi'] = $tagovi;
                $vremeKesiranja = Yii::app()->params['vremeKesiranja'];
                Yii::app()->cache->set($key, $lista, $vremeKesiranja );
            }
            return $lista;
        }
        
        public function getStrtagovi($id_jezik = Helper::ID_SRPSKI_JEZIK)
        {
            $tagovi = $this->izracunajListutagova($this->id, $id_jezik);
            $ret = '';
            foreach($tagovi as $tag)
                $ret .= $tag['naziv']. ', ';
            $ret = rtrim(trim($ret), ',');
            if( ! $ret)
                $ret = '-';
            return $ret;
        }

        //spaja odgovarajuce tabele iz baze i vraca listu odeljaka u okviru kojih je vest objavljena
        public static function izracunajListuodeljaka($id_objava, $id_jezik)
        {            
            return Yii::app()->db->createCommand()
                ->select('odeljak.id, odeljak.ruta, odeljak.id_param, i18n_odeljak.naziv')
                ->from('objava')
                ->join('odeljak_objava', 'objava.id = odeljak_objava.id_objava')
                ->join('odeljak', 'odeljak_objava.id_odeljak = odeljak.id')
                ->join('i18n_odeljak', 'i18n_odeljak.id_odeljak = odeljak.id')
                ->where("objava.id=$id_objava AND i18n_odeljak.id_jezik=$id_jezik")
                ->queryAll();
        }

        public static function izracunajListutagova($id_objava, $id_jezik)
        {
            return Yii::app()->db->createCommand()
                ->select('tag.id, i18n_tag.naziv')
                ->from('objava')
                ->join('objava_tag', 'objava.id = objava_tag.id_objava')
                ->join('tag', 'objava_tag.id_tag = tag.id')
                ->join('i18n_tag', 'i18n_tag.id_tag = tag.id')
                ->where("objava.id=$id_objava AND i18n_tag.id_jezik=$id_jezik")
                ->queryAll();
        }

        public function popuniPoljetagovi($id_jezik = Helper::ID_SRPSKI_JEZIK)
        {
            if($this->isNewRecord)
            {
                $this->tagovi = '';
                return;
            }
            $tagovi = self::izracunajListutagova($this->id, $id_jezik);
            $ret = '';
            foreach($tagovi as $tag)            
                $ret .= $tag['naziv'] . ', ';
            if($ret)
                $ret = substr($ret, 0, strlen ($ret)-2);
            $this->tagovi = $ret;
        }

        /**
         * //BITNO
         * odeljak mora imati unos za svaki jezik!!!
         *
         *  @return CSqlDataProvider
         */
        public static function listaobjava($id_jezik, $id_odeljak)
        {
//Objave se spajaju sa Odeljkom i selektuju se samo one za trazeni odeljak
            $id_odeljak = intval($id_odeljak);
            $where = 'o.status = '.self::OBJAVLJENO. ' AND odo.id_odeljak='.$id_odeljak;
            $broj = Yii::app()->db->createCommand()
                    ->selectDistinct('COUNT(*)')
                    ->from('objava o')
                    ->join('odeljak_objava odo', 'odo.id_objava=o.id')
                    ->where($where)->queryScalar();
            $cmd = self::cmdListaobjava();//lista je zajednicka za vise funkcija, razlika je samo u where klauzuli
            $sqlNaslovna = $cmd
                ->where($where)
                ->order('odo.top DESC, o.id DESC')
                ->text;
            return new CSqlDataProvider($sqlNaslovna, array(
                'totalItemCount'=>$broj,
                'pagination'=>array(
                    'pageSize'=>6,
                ),
                'params'=>array(':id_jezik'=>$id_jezik),
            ));
        }

        public static function listaobjavaZatag($id_jezik, $id_tag)
        {
            $id_tag = intval($id_tag);
            $where = 'o.status = '.self::OBJAVLJENO. ' AND ot.id_tag='.$id_tag;
            $broj = Yii::app()->db->createCommand()
                    ->selectDistinct('COUNT(*)')
                    ->from('objava o')
                    ->join('objava_tag ot', 'ot.id_objava=o.id')
                    ->where($where)->queryScalar();
            $db = Yii::app()->db;
            $cmd = $db->createCommand()
                    ->selectDistinct('o.id, o.tip, o.datum, o.id_jezik_originala, o.br_komentara, o.url_slika, c.korisnicko_ime, c.puno_ime, c.slika, i18n_o.id_jezik, i18n_o.naslov, i18n_o.uvod')
                    ->from('objava o')
                    ->join('objava_tag ot', 'o.id = ot.id_objava')
                    ->join('tag tag', 'tag.id = ot.id_tag' )
                    ->join('i18n_tag', 'tag.id = i18n_tag.id_tag AND i18n_tag.id_jezik=:id_jezik') //mora da postoji unos za sve jezike!
                    ->leftJoin('i18n_objava i18n_o', 'o.id = i18n_o.id_objava AND i18n_o.id_jezik=:id_jezik')
                    ->leftJoin('clan c', 'o.id_clan = c.id');
            $sql = $cmd
                ->where($where)
                ->order('o.id DESC')
                ->text;
            return new CSqlDataProvider($sql, array(
                'totalItemCount'=>$broj,
                'pagination'=>array(
                    'pageSize'=>10,
                ),
                'params'=>array(':id_jezik'=>$id_jezik),
            ));
        }
        
        public static function listaobjavaZatagoveIzOdeljka($id_jezik, $id_tag, $id_odeljak)
        {
            $id_tag = intval($id_tag);
            $id_odeljak = intval($id_odeljak);
            $where = 'o.status = '.self::OBJAVLJENO. ' AND ot.id_tag='.$id_tag . ' AND odo.id_odeljak='.$id_odeljak;
            
            $broj = Yii::app()->db->createCommand()
                    ->selectDistinct('COUNT(*)')
                    ->from('objava o')
                    ->join('objava_tag ot', 'ot.id_objava=o.id')
                    ->join('odeljak_objava odo', 'o.id=odo.id_objava')
                    ->where($where)->queryScalar();            
            $db = Yii::app()->db;
            $cmd = $db->createCommand()
                    ->selectDistinct(self::$selectobjava)
                    ->from('objava o')
                    ->join('objava_tag ot', 'o.id = ot.id_objava')
                    ->join('odeljak_objava odo', 'o.id=odo.id_objava')
                    ->join('tag tag', 'tag.id = ot.id_tag' )
                    ->join('i18n_tag', 'tag.id = i18n_tag.id_tag AND i18n_tag.id_jezik=:id_jezik') //mora da postoji unos za sve jezike!                         
                    ->leftJoin('i18n_objava i18n_o', 'o.id = i18n_o.id_objava AND i18n_o.id_jezik=:id_jezik')
                    ->leftJoin('clan c', 'o.id_clan = c.id');
            $sql = $cmd
                ->where($where)
                ->order('o.id DESC')
                ->text;
            return new CSqlDataProvider($sql, array(
                'totalItemCount'=>$broj,
                'pagination'=>array('pageSize'=>10,),
                'params'=>array(':id_jezik'=>$id_jezik),
            ));
        }
        
        public function getNaslovSRHtml()
        {
            $url_slika = Helper::baseUrl('images/sajt/dokument.png');
            if($this->setAktivanjezik(Helper::ID_SRPSKI_JEZIK))
                   return '<img style="float:left; padding-right:5px;" src="'.$url_slika.'"/>'.$this->getLinkNaslov(Helper::KOD_SRPSKI_JEZIK);
            elseif($this->setAktivanjezik(Helper::ID_SRPSKI_LATINICA))
                   return '<img style="float:left; padding-right:5px;" src="'.$url_slika.'"/>'.$this->getLinkNaslov(Helper::KOD_SRPSKI_JEZIK);
            else
                   return '-';
        }

        public function getNaslovENHtml()
        {
            $url_slika = Helper::baseUrl('images/sajt/dokument.png');
            if($this->setAktivanjezik(Helper::ID_ENGLESKI_JEZIK))
                    return '<img style="float:left; padding-right:5px;" src="'.$url_slika.'"/>'.$this->naslov;
            else
                return '-';
        }
        /**
         *
         * @return galerijaModel vraca Galeriju ako postoji, null ako ne postoji, false ako je doslo do greske
         */
        public function azurirajGaleriju()
        {
            //galerija je zaseban objekat i snimam je u posebnoj transakciji
            $greskaPriUpisuGalerije = false;
            $galerija = GalerijaModel::model()->findByPk($this->id_galerija);

            //potrebno je azurirati slike iz teksta pre nego sto se izvrsi rotacija slika iz galerije
            $this->obradiTekst($galerija);

            $arSlike = CJSON::decode($this->jsongalerija);
            if($arSlike)
            {
                $galTransakcija = Yii::app()->db->beginTransaction();
                try
                {                    
                    if( ! $galerija)
                    {
                        $galerija = new GalerijaModel();
                        $galerija->direktorijum();
                        $galerija->save();
                    }
                    $galerija->azurirajSlike($arSlike);
                    if( ! $galerija->rSlike) //ako galerija ne sadrzi ni jednu sliku onda se brise
                    {
                        $galerija->delete();
                        $galerija = null;
                    }
                    $galTransakcija->commit();
                }
                catch(Exception $e)
                {
                    $galTransakcija->rollBack();
                    return null;
                }
            }  
            return $galerija;
        }

        private  function azurirajOsnovnePodatke($clan, $galerija)
        {
            if($galerija)
            {
                $id_galerija = $galerija->id;
                $url_slika= $galerija->getUrlslikaZaNaslovnu();
            }
            else
            {
                $id_galerija = null;
                $url_slika = null;
            }
            $this->id_galerija = $id_galerija;
            $this->url_slika = $url_slika;            
            if( ! $this->datum)
                $this->datum = time();
            if( ! $this->id_clan && $this->isNewRecord)
                $this->id_clan = $clan->id;
            $this->tip = get_class($this);
        }
        
        /*override*/
        protected function onAzurirajPreCommit($galerija){return true;}
        
        /*override*/
        protected function onPosleAzuriranja(){}
        
        public function azurirajBezTransakcije($odeljci, $clan, $galerija)
        {
            $uspeh = true;
            if(! $this->datum)
                $this->datum = 0; //ne sme da bude null
            if($this->isNewRecord)
                $uspeh = $this->save();
            $this->azurirajOsnovnePodatke($clan, $galerija);
            $this->azurirajtagove($this->tagovi);
            $this->azurirajVezuOdeljakObjava($odeljci, $clan);

            $uspeh = $uspeh && $this->onAzurirajPreCommit($galerija);

            //$this->obradiTekst();//izvrsava purify nad tekstom...
            $this->latinicnaTransliteracija();
            if($this->status == Objava::DRAFT)
                $this->izmeniStatusNovog($clan);
            $uspeh = $uspeh && $this->save();       
            if($uspeh)
                $this->onPosleAzuriranja();
            return $uspeh;
        }
        
        public function azuriraj($odeljci, $clan, $galerija)
        {
            $transakcija = Yii::app()->db->beginTransaction();
            try
            {
                if( ! $this->azurirajBezTransakcije($odeljci, $clan, $galerija))
                    throw new Exception(Yii::t('biblioteka', 'Објава #{id_objava} није могла бити уписана у базу!', array('{id_objava}'=>$this->id)));
                $transakcija->commit();
                return true;            
            }
            catch(Exception $e)
            {
                $transakcija->rollBack();
                $this->addError('greska', $e->getMessage());
                return false;
            }
        }
     
        private function izmeniStatusNovog($clan)
        {
            
            if( $this->draft)
                $this->status = Objava::DRAFT;
            else
            {   
                $this->getDozvoleRegenerisiKes($clan);
                if($this->mozeDaObjavi($clan))
                    $this->status = Objava::OBJAVLJENO;
                elseif($this->mozeDaStaviNaCekanje($clan))
                    $this->status = Objava::CEKA_ODOBRENJE;
                elseif($this->mozeDaMenja($clan))
                    $this->status = Objava::DRAFT;
                else
                {
                    assert(false);
                }
            }
        }

        /*
         *  Jedna objava se moze nalaziti istovremeno u vise odeljaka. Funkcija brise pokusava da izbrise sve odeljke
         *  koji nisu cekirani i upisuje vezu za sve cekirane ukoliko veza vec ne postoji. 
         */
        private function azurirajVezuOdeljakObjava(array & $odeljci, $clan)
        {
            $brojIzbrisanih = 0;
            $ukupanBroj = count($odeljci);
            //ako mi kasnije padne na pamet da izbrisem sve veze, pa da upisem nove, losa je ideja jer
            //mogu da postoje veze koje nisu dostupne ovom korisniku i ti odeljci se ne nalaze u listi za upis
            $postojiCekiranodeljak = false;
            foreach($odeljci as $odeljak)
            {
                //pronadji ga u odeljak_objava
                $odeljakobjava = OdeljakObjava::model()->find('t.id_odeljak=:id_odeljak AND t.id_objava=:id_objava',
                        array(':id_odeljak'=>$odeljak->id, ':id_objava'=>$this->id));
             //ako nije cekiran
                if( ! $odeljak->cekiran)
                {
                    if($odeljakobjava)//ako postoji
                    { //proverаvam prvo da li korisnik ima pravo da izbrise odeljak
                        if( ! $odeljak->mozeDaIskljuci($clan, $this))
                                throw new Exception(Yii::t('biblioteka', 'Немате одговарајуће дозволе за уклањање објаве из одељка!'));
                        if($odeljakobjava->delete())
                        {
                            if( ++$brojIzbrisanih === $ukupanBroj) //ako je objava ostala bez odeljka
                                throw new Exception(Yii::t('biblioteka', 'Објава се мора наћи бар у једном одељку!'));
                        }
                        else //ako nije uspelo brisanje izbaci izuzetak
                            throw new Exception(Yii::t('biblioteka', 'Веза између одељка #{id_odeljak} и објаве #{id_objava} није могла бити избрисана', array('{id_objava}'=>$this->id, '{id_odeljak}'=>$odeljak->id)));
                    }
                }
             //ako je cekiran
                else
                {
                    $postojiCekiranodeljak = true;
                    if($odeljakobjava)//ako postoji sacuvaj ga da bi se apdejtovao top
                    {
                        $odeljakobjava->top = $odeljak->top;
                        if( ! $odeljakobjava->save())//ako snimanje nije uspelo izbaci izuzetak
                            throw new Exception(Yii::t('biblioteka', 'Веза између одељка #{id_odeljak} и објаве #{id_objava} није могла бити ажурирана', array('{id_objava}'=>$this->id, '{id_odeljak}'=>$odeljak->id)));
                    }
                    else
                    {
                        //ako ne postoji kreiraj
                        //prvo provera da li korisnik ima pravo da stavi objavu u odeljak
                        if( ! $odeljak->mozeDaPrikljuci($clan, $this))
                            throw new Exception(Yii::t('biblioteka', 'Немате одговарајуће дозволе за смештање објаве у одељак!'));
                        $odeljakobjava = new OdeljakObjava();
                        $odeljakobjava->id_odeljak = $odeljak->id;
                        $odeljakobjava->id_objava = $this->id;
                        $odeljakobjava->top = $odeljak->top;
                        if(!$odeljakobjava->save())
                            throw new Exception(Yii::t('biblioteka',
                                'Веза између одељка #{id_odeljak} и објаве #{id_objava} није могла бити уписана у базу!',
                                 array('{id_objava}'=>$this->id, '{id_odeljak}'=>$odeljak->id)));
                    }
                }
            }
            if( ! $postojiCekiranodeljak)
                throw new Exception(Yii::t('biblioteka', 'Објава се мора наћи бар у једном одељку!'));
        }

        /**
         * Brise unos iz tabele objava_tag ako se naziv taga ne nalazi u nizu $listaNaziva.
         * Za svaki tag, cija je referenca izbrisana, smanjuje se vrednost polja ucestalost u tabeli tag.
         * @param array<String> $listaNaziva
         */
        private function uklonitagoveAkoNisuUListi($listaNaziva, $id_jezik=Helper::ID_SRPSKI_JEZIK)
        {
            $iNazivNotInListaNazivaAnd = '';
            if($listaNaziva)
                $iNazivNotInListaNazivaAnd = "i.naziv NOT IN ($listaNaziva) AND ";
            $db = Yii::app()->db;
           //pre nego sto izbrisem veze updejtujem ucestalost za tagove koji ce biti razdruzeni
            $db->createCommand(
                     "UPDATE 
                        tag t
                     SET
                        ucestalost = ucestalost-1
                     FROM
                     (
                        SELECT ot.id_tag
                        FROM
                            objava_tag ot 
                        JOIN
                            i18n_tag i 
                        ON (ot.id_tag = i.id_tag)
                        WHERE
                            ot.id_objava=$this->id AND
                            $iNazivNotInListaNazivaAnd
                            i.id_jezik = $id_jezik
                     )tbl
                     WHERE
                       t.id = tbl.id_tag AND                        
                       t.ucestalost > 0"                        
                    )->execute();            

            //brisanje veza
            $db->createCommand("DELETE
                    FROM
                        objava_tag ot
                    USING
                        tag t, i18n_tag i
                    WHERE
                        ot.id_objava=$this->id AND
                        ot.id_tag = t.id AND
                        i.id_tag = t.id AND
                        $iNazivNotInListaNazivaAnd
                        i.id_jezik = $id_jezik"
                    )->execute();
            //brisem sve tagove koji ne pripadaju ni jednoj objavi
            $db->createCommand('DELETE FROM tag WHERE ucestalost <= 0')->execute();
        }
        /**
         *
         * @param <String> $tagovi su pisani cirilicom i odvojeni zarezima.
         */
        private function azurirajtagove($strNovitagovi)
        {
            $db = Yii::app()->db;
            $arTmp = explode(',', trim($strNovitagovi));
            $arStrNovitagovi = array();
            foreach($arTmp as $strtag)
            {
                $strtag = trim($strtag);
                if($strtag)
                    $arStrNovitagovi [] = $strtag;
            }
            $arStrNovitagovi = array_unique($arStrNovitagovi);
            //uklanjanje nepostojecih veza - oni koji se nalaze u objava_tag, a nema ih u $strNovitagovi
            $listaNaziva = Helper::escapeStrNizZaIn($arStrNovitagovi);
            if( ! $this->isNewRecord)
                $this->uklonitagoveAkoNisuUListi($listaNaziva);//brise veze iz tabele objava_tag i umanjuje ucestalost u tabeli tag
            //nakon uklanjanja u  bazi se nalazi deo tagova kojima treba dodati nove
            $cmdPostojeci = Yii::app()->db->createCommand()
                    ->select('ot.id_tag')->from('objava_tag ot')->where('ot.id_objava=:id_objava');
            $cmdPostojeci->params[':id_objava'] = $this->id;
            $postojecitagoviIdRow = $cmdPostojeci->queryAll();
            //nije elegantno, ali mi je nakon previda bilo najjednostavnije
            $postojecitagoviId = array();
            foreach($postojecitagoviIdRow as $row)
                $postojecitagoviId [] = $row['id_tag'];
            $povecatiUcestalostId = array();
            foreach($arStrNovitagovi as $strtag)
            {
                $strtag = trim($strtag);
                if( ! $strtag) //morao sam da dodam jer explode dodaje prazan string
                    continue;
                $tag = Tag::get($strtag);//ucitaj iz baze za dato ime
                $pridruzen = true;                
                if($tag)//ako postoji
                {                 
                    if(array_search($tag->id, $postojecitagoviId) === false)//proveri da li je pridruzen
                    {
                        $pridruzen = false;
                         //ako postojeci tag nije bio pridruzen treba mu povecati ucestalost
                        $povecatiUcestalostId [] = $tag->id;
                    }
                }
                else//ako ne postoji
                {
                    $pridruzen = false;
                    $tag = Tag::novitag($strtag);//kreiraj
                }
                if( ! $pridruzen)
                {
                    $objavatag = new ObjavaTag();
                    $objavatag->id_tag = $tag->id;
                    $objavatag->id_objava = $this->id;
                    $objavatag->save();
                }
            }
            //povecavam ucestalost svim tagovima koji se nalaze u listi $povecatiUcestalostId
            if($povecatiUcestalostId)
            {
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $povecatiUcestalostId);
                Tag::model()->updateCounters(array('ucestalost'=>1), $criteria);
            }
        }

        /**
         *
         *  Bolji naziv za ovu funkciju je bio ukloniHTMLtagove
         * 
         * @param type $rec
         * @return type 
         */
        private function uklonitagove($rec)
        {
            return strip_tags($rec, '<p><br/><br><b><i><strong><em><u><strike><sub><sup><a>');
        }

        /**
         * filtrira sirov tekst i dodeljuje ga tekstu za prikaz
         */
        private function obradiTekst($galerija, $duzinaUvoda = 280)
        {
            //TODO nije vezano za ovu funkciju, dodaj proveru da li je naslov pisan cirilicom, t.j. da li sadrzi bar jedno cirilicno slovo.
            $this->naslov = strip_tags($this->naslov);
            $clan = Helper::getLogovaniClan();
            if( ! $clan->isSuperAdministrator())
            {
                $this->tekst = Helper::procisti($this->tekst_sirov);
            }
            else
            {
                $this->tekst = $this->tekst_sirov;
            }
            $this->tekst = str_replace("&nbsp;", '', $this->tekst);
            if($galerija)
                $this->tekst = $galerija->obradiUbaceneSlike($this->tekst);

            //$this->tekst = $this->tekst_sirov;

            //TODO dodatna obrada teksta, na primer tekst_sirov sadrzi <!--[youtube=123z02 visina=80px sirina=90px]-->

            $pattern = "/\[youtube\]((\w|-)+)\[\/youtube\]/";
            $replacement = '<object width="630" height="354"><param name="movie" value="//www.youtube.com/v/$1?hl=sr_RS&amp;version=3"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/$1?hl=sr_RS&amp;version=3" type="application/x-shockwave-flash" width="630" height="354" allowscriptaccess="always" allowfullscreen="true"></embed></object>';            
            $this->tekst = preg_replace($pattern, $replacement, $this->tekst);
            
            if(trim(CHtml::decode(strip_tags($this->uvod))))
                $this->uvod = $this->uklonitagove($this->uvod);
            else
            {
                $this->uvod = $this->uklonitagove($this->tekst);
                if(mb_strlen($this->uvod, 'utf8') > $duzinaUvoda)
                    $this->uvod = mb_substr($this->uvod, 0, $duzinaUvoda, 'utf8').'...';
            }
            $this->uvod = Helper::procisti($this->uvod);
            $this->uvod = str_replace("&nbsp;", '', $this->uvod);
                            
        }

        /**
         * Konvertuje naslov, tekst, tekst_sirov iz cirilice u latinicu i dodaje zapis za upis u i18n
         */
        public function latinicnaTransliteracija()
        {
            $jezik = $this->getAktivanjezikId();
            assert($jezik == Helper::ID_SRPSKI_JEZIK);
            $naslovCirilica = $this->naslov;
            $tekstCirilica = $this->tekst;
            $tekst_sirovCirilica = $this->tekst_sirov;
            $uvodCirilica = $this->uvod;
            $this->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_LATINICA);
            $this->naslov = Helper::cir2lat($naslovCirilica);
            $this->tekst = Helper::cir2latSacuvajtagove($tekstCirilica);
            $this->tekst_sirov = Helper::cir2latSacuvajtagove($tekst_sirovCirilica);
            $this->uvod = Helper::cir2lat($uvodCirilica);
            $this->setAktivanjezik($jezik);
        }

        public function getSlikeIzGalerije()
        {
            if( ! $this->id_galerija)
                    return null;
            $galerija = GalerijaModel::model()->findByPk($this->id_galerija);
            return $galerija->rSlike;

        }

       public function postaviStatus($status, $funkcijaProvere)
       {           
           if(Yii::app()->user->isGuest)
                   throw new CHttpException(400, Yii::t('biblioteka', 'Нисте се пријавили на систем!'));
           $id = Yii::app()->user->id;
           $clan = Clan::getclan($id);
           $parametri = array('{id}'=>$this->id, '{naslov}'=>CHtml::encode($this->naslov));
           if($status == Objava::DRAFT)
               throw new CHttpException(400, Yii::t('biblioteka', 'Није дозвољена измена статуса незавршене објаве #{id}:<strong>{naslov}</strong>!'), $parametri);
           if( !$this->$funkcijaProvere($clan))
                   throw new CHttpException(400, Yii::t('biblioteka', 'Немате одговарајуће дозволе!'));
           $stariStatus = $this->status;
           $this->status = $status;
           if( ! $this->saveUOkviruTransakcije())
           {
                if($stariStatus == Objava::DRAFT)
                    $poruka = Yii::t('biblioteka', 'Промена статуса објаве #{id}:<strong>{naslov}</strong> није успела. Oбјава je у <strong>недовршеном стању</strong> предлажемо Вам да урадите исправке пре него што поново покренете ову акцију.', array('{id}'=>$this->id, '{naslov}'=>CHtml::encode($this->naslov)));
                else
                    $poruka = Yii::t('biblioteka', 'Промена статуса објаве #{id}:<strong>{naslov}</strong> није успела!', $parametri);                
                throw new CHttpException(400, $poruka); 
           }
           return true;
       }

       
       public function zakljucaj($zakljucavanje = true)
       {
           if(Yii::app()->user->isGuest)
               throw new CHttpException(400, Yii::t('biblioteka', 'Нисте се пријавили на систем!'));
           $id = Yii::app()->user->id;
           $clan = Clan::getclan($id);
           $dozvoljeno = $zakljucavanje ? $this->mozeDaZakljuca($clan) : $this->mozeDaOtkljuca($clan);
           if( ! $dozvoljeno)
               throw new CHttpException(400, Yii::t('biblioteka', 'Немате одговарајуће дозволе!'));
           if( ! $zakljucavanje)
               $zakljucavanje = 0;
           $this->zakljucano = $zakljucavanje;
           if( ! $this->saveUOkviruTransakcije())
                throw new CHttpException(400, Yii::t('biblioteka', 'Промена статуса није успела, дошло је до грешке при упису у базу!'));
           return true;
       }

       public function izbrisi()
       {
           if(Yii::app()->user->isGuest)
               throw new CHttpException(400, Yii::t('biblioteka', 'Нисте се пријавили на систем!'));
           $id = Yii::app()->user->id;
           $clan = Clan::getclan($id);
           if( ! $this->mozeDaBrise($clan))
                throw new CHttpException(400, Yii::t('biblioteka', 'Немате одговарајуће дозволе!'));
           $trans = Yii::app()->db->beginTransaction();
           try
           {
                $this->azurirajtagove(''); //uklanja tagove i umanjuje im frekfenciju posto se objava brise
                if( ! $this->delete())
                    throw new CHttpException(400, Yii::t('biblioteka', 'Брисање није успело, дошло је до грешке при упису у базу!'));
                $trans->commit();
           }
           catch(Exception $e)
           {
               $trans->rollBack();
               throw $e;
           }
           return true;
       }

       public function azurirajBrojkomentara()
       {
            $objavljeno = Komentar::OBJAVLJENO;
            $sql = "UPDATE objava SET br_komentara = (SELECT COUNT(*) FROM komentar k WHERE k.id_objava=$this->id AND k.status=$objavljeno) WHERE id=$this->id";
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->execute();
       }
}