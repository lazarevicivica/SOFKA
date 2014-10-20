<?php

/**
 * This is the model class for table "clan".
 *
 * The followings are the available columns in table 'clan':
 * @property integer $id
 * @property integer $id_jezik_originala
 * @property string $korisnicko_ime
 * @property string $lozinka
 * @property string $puno_ime
 * @property integer $tip
 * @property integer $id_radno_mesto
 * @property integer $id_odeljenje
 * @property integer $aktivan
 * @property string $slika
 * @property string $email
 * @property string $telefon
 * @property string $sajt
 * @property string $uloga
 *
 * The followings are the available model relations:
 * @property jezik $id_jezik_originala0
 * @property odeljenje $id_odeljenje0
 * @property Radnomesto $id_radno_mesto0
 * @property jezik[] $jeziks
 * @property komentar[] $komentars
 * @property objava[] $objavas
 */
Yii::import('ext.phpThumb.PhpThumbFactory');
class Clan extends CI18nActiveRecord
{
        const RADNIK=1;
        const KORISNIK=2;        

        const PODRAZUMEVANA_SLIKA = '/images/sajt/clan.png';
        const MAX_SIRINA_VELIKE_SLIKE = 250;
        const MAX_VISINA_VELIKE_SLIKE = 250;
        
        const DIMENZIJE_SLIKE = 50;

        public $staraLozinka;
        public $novaLozinka;
        public $ponovljenaLozinka;
        public $fajlslika;
        
        private static $registar = array(); //registar ucitanih clanova

        /**
	 * Returns the static model of the specified AR class.
	 * @return clan the static model class
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
		return 'clan';
	}

        public static function getclan($id)
        {            
            if( ! empty(self::$registar[$id]))
                return self::$registar[$id];
            $clan = Clan::model()->findByPk($id);
            if( ! $clan)
                throw new CHttpException(400, Yii::t('biblioteka', 'Тражени корисник не постоји!'));
            self::$registar[$id] = $clan;
            return $clan;

        }
        
        public static function getLogovani()
        {
            $id = 0; //korisnik sa id=0 ne postoji, tako da za gosta izbacuje izuzetak iz funkcije getclan
            if( ! Yii::app()->user->isGuest)            
                $id = Yii::app()->user->id;            
            return self::getclan($id);
        }

        public function isSuperAdministrator()
        {
            if( false !== array_search($this->korisnicko_ime, Yii::app()->params['superAdministratori']))
                    return true;
            return false;            
        }

        /**
         * @return  array (id_odeljak => naziv)
         * Niz sadrzi sve odeljke za koje clan ima definisana  prava pristupa
         * 
         */
        private $odeljciIdNaziv = array();
        private $odeljciId = array();
        public function getNizIdOdeljkaNaziv($id_jezik = Helper::ID_SRPSKI_JEZIK)
        {
            $dodajUId = empty($this->odeljciId);

            if( empty($this->odeljciIdNaziv[$id_jezik]))
            {
                if( ! $this->isSuperAdministrator())
                    $cmd = Yii::app()->db->createCommand()
                        ->selectDistinct('o.id, i18n.naziv')
                        ->from('clan c')
                        ->join('clan_odeljak co', 'co.id_clan=c.id')
                        ->join('odeljak o', 'co.id_odeljak=o.id')
                        ->join('i18n_odeljak i18n', 'i18n.id_odeljak=o.id')
                        ->where('c.id='.$this->id.' AND i18n.id_jezik='.$id_jezik);
                else //selektujem sve odeljke posto je superadministrator
                    $cmd = Yii::app()->db->createCommand()
                        ->selectDistinct('o.id, i18n.naziv')
                        ->from('odeljak o')
                        ->join('i18n_odeljak i18n', 'i18n.id_odeljak=o.id')
                        ->where('i18n.id_jezik='.$id_jezik);
                
                $cmd->order = 'i18n.naziv ASC';

                $redovi = $cmd->queryAll();

                $odeljci = & $this->odeljciIdNaziv[$id_jezik];
                foreach($redovi as $red)
                {
                    $id = $red['id'];
                    if($dodajUId)
                        $this->odeljciId[] = $id;
                    $odeljci[$id] = $red['naziv'];
                }
            }
            return $this->odeljciIdNaziv[$id_jezik];
        }

        public function getNizIdodeljak($id_jezik = Helper::ID_SRPSKI_JEZIK)
        {
            if(empty($this->odeljciId))
                $this->getNizIdOdeljkaNaziv();
            return $this->odeljciId;
        }
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
                        array('novaLozinka, ponovljenaLozinka', 'length','encoding'=>'utf-8','min'=>8,'max'=>16,'allowEmpty'=>false,'on'=>'insert'),
                        array('novaLozinka, ponovljenaLozinka', 'length','encoding'=>'utf-8','min'=>8,'max'=>16,'allowEmpty'=>true, 'on'=>'update'),
                        array('staraLozinka', 'proveraLozinke', 'on'=>'update'),
                        array('novaLozinka', 'jednakaPonovljenoj'),
                        array('novaLozinka', 'ispravnaAkoJeUnetaStara'),
                        array('fajlslika', 'file', 'types'=>'jpg, jpeg, gif, png', 'allowEmpty'=>true),
                        array('profil', 'safe'),
			array('id_jezik_originala, tip, id_radno_mesto, id_odeljenje, aktivan', 'numerical', 'integerOnly'=>true),
			array('korisnicko_ime, puno_ime, uloga', 'length', 'max'=>50),
			array('email, telefon, sajt', 'length', 'max'=>255),
                        array('sajt', 'url'),
                        array('email', 'email'),
                        array('licni_podaci', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_jezik_originala, korisnicko_ime, lozinka, puno_ime, tip, id_radno_mesto, id_odeljenje, aktivan, slika, email, telefon, sajt, uloga', 'safe', 'on'=>'search'),
		);
	}

        public function obradiTekst()
        {            
            $procisceno = Helper::procisti($this->profil);
            $this->profil = $procisceno;
            
            $this->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_LATINICA);
            $this->profil = Helper::cir2latSacuvajtagove($procisceno);
            
            $this->setAktivanjezik(Helper::ID_SRPSKI_JEZIK);
            
        }

	public function relations()
	{
                $relacije = array(

                    'rclan_odeljak' => array(self::HAS_MANY, 'ClanOdeljak', 'id_clan'),
                    'rCRUDOdeljci' => array(self::MANY_MANY,
                                            'Odeljak', 'clan_odeljak(id_clan, id_odeljak)',
                                            'through'=>'rclan_odeljak'),
                    'rradno_mesto' => array(self::BELONGS_TO, 'RadnoMesto', 'id_radno_mesto'),
                );
                return array_merge(parent::relations(), $relacije);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
                	'korisnicko_ime' => Yii::t('biblioteka', 'Корисничко име'),
                        'puno_ime' => Yii::t('biblioteka', 'Пуно име'),
			'staraLozinka' => Yii::t('biblioteka', 'Стара лозинка'),
			'novaLozinka' => Yii::t('biblioteka', 'Нова лозинка'),
                    	'ponovljenaLozinka' => Yii::t('biblioteka', 'Поновљена нова лозинка'),
			'id_radno_mesto' => Yii::t('biblioteka', 'Радно место'),
			'id_odeljenje' => Yii::t('biblioteka', 'Одељење'),
			'slika' => Yii::t('biblioteka', 'Слика'),
                        'fajlslika' => Yii::t('biblioteka', 'Слика'),
			'email' => Yii::t('biblioteka', 'Електронска пошта'),
			'telefon' => Yii::t('biblioteka', 'Телефон'),
			'sajt' => Yii::t('biblioteka', 'Сајт'),
                    	'profil' => Yii::t('biblioteka', 'Биографија'),
                        'licni_podaci' => Yii::t('biblioteka', 'Слика и биографија су јавни'),
			'uloga' => 'Улога',
		);
	}

        public static function getImeZaPrikazS(array & $data)
        {
            if(empty($data['puno_ime']))
                if(! empty($data['korisnicko_ime']))
                    return CHtml::encode($data['korisnicko_ime']);
                else 
                    return '';
            return CHtml::encode($data['puno_ime']);          
        }
        
        private function getDir()
        {
            return "/images/clan/$this->id";
        }

        private function direktorijum()
        {
            $putanja = Helper::basePath( $this->getDir());
            if( ! file_exists($putanja))
                @mkdir($putanja, 0770, true);
            return rtrim($putanja, '/');
        }

        public function sacuvajSliku()
        {
            if( ! empty($this->fajlslika))
            {
                $ekstenzija = trim(strtolower($this->fajlslika->getExtensionName()));
                $dozvoljene = array('jpg', 'jpeg', 'gif', 'png');                
                $rezultatPretrage = array_search($ekstenzija, $dozvoljene);
                if( $rezultatPretrage === false)
                {
                    $this->addError('fajlslika', Yii::t('biblioteka', 'Слика профила је погрешног типа. Дозвољени типови су jpg, png и gif.'));
                    return false;
                }
                $direktorijum = $this->direktorijum();
                $nazivSlike = "$this->id.$ekstenzija";
                $putanja = "$direktorijum/$nazivSlike";
                $putanjaVelike = "$direktorijum/v$nazivSlike";

                $this->fajlslika->saveAs($putanja);

                @$resize = PhpThumbFactory::create($putanja, array('jpegQuality' => 90));
                @$resize->resize(self::MAX_SIRINA_VELIKE_SLIKE, self::MAX_VISINA_VELIKE_SLIKE);
                @$resize->save($putanjaVelike);                

                @$thumb = PhpThumbFactory::create($putanja, array('jpegQuality' => 95));
                @$thumb->adaptiveResize(self::DIMENZIJE_SLIKE, self::DIMENZIJE_SLIKE);
                @$thumb->save($putanja);

                $this->slika = $this->getDir(). '/' . $nazivSlike;
                $this->velika_slika = $this->getDir() . '/v' .$nazivSlike;               
            }
            return true;
        }

        public function proveraLozinke($attribute, $params)
        {
            if( ! empty($this->staraLozinka))
            {              
                if($this->lozinka !== Helper::getHash($this->staraLozinka))
                        $this->addError($attribute, Yii::t('biblioteka','Погрешна лозинка.'));
                 $this->staraLozinka = null;
            }
        }

        public function ispravnaAkoJeUnetaStara($attribute, $paramas)
        {
            $broj = 8;
            if( ! empty($this->staraLozinka))
                    if(mb_strlen($this->$attribute, 'utf8') < $broj)
                        $this->addError($attribute, Yii::t('biblioteka', 'Нова лозинка мора имати најмање {broj} слова', array('{broj}'=>$broj)));
        }

        public function jednakaPonovljenoj($attribute, $paramas)
        {
            //ako su obe prazne sve je u redu, izadji.
            if(empty($this->novaLozinka) && empty($this->ponovljenaLozinka))
                return;
            if($this->novaLozinka !== $this->ponovljenaLozinka)
                $this->addError($attribute, Yii::t('biblioteka', 'Унете су различите лозинке!'));
        }

        public function promeniLozinku()
        {
            if( ! empty($this->novaLozinka))
                $this->lozinka = Helper::getHash($this->novaLozinka);
        }

        public function getImeZaPrikaz()
        {
            $ime = $this->puno_ime;
            if( ! $ime)
                $ime = $this->korisnicko_ime;
            return CHtml::encode($ime);
        }

        public function getKorisnickoImeHtml()
        {
            return CHtml::encode($this->korisnicko_ime);
        }

        public static function getslikaS(array & $data)
        {
            /*$url = $data['slika'];
            if( ! $url)
                return Clan::PODRAZUMEVANA_SLIKA;
            return $url;*/
            if( ! empty($data['mejl_registrovanog']))
                $email = $data['mejl_registrovanog'];
            else if( ! empty($data['mejl_neregistrovanog']))
                $email = $data['mejl_neregistrovanog'];
            else
                return Clan::PODRAZUMEVANA_SLIKA;
            
            $email = strtolower(trim($email));
            $hash = md5($email);
            $url = "http://www.gravatar.com/avatar/$hash?s=50&d=identicon";
            return $url;            
        }

        public function getslika()
        {
            if( ! empty($this->slika))
                return $this->slika;
            else 
                return self::PODRAZUMEVANA_SLIKA;
        }

        public static function getWeb($data)
        {
            return $data['sajt'];
        }

 /*       public static function getZaposleni($id_odeljenje)
        {
            $id_odeljenje = intval($id_odeljenje);
            $tip = self::RADNIK;
            return Clan::model()->findAll("id_odeljenje=$id_odeljenje AND tip=$tip ORDER BY puno_ime");
        }*/

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('id_jezik_originala',$this->id_jezik_originala);
		$criteria->compare('korisnicko_ime',$this->korisnicko_ime,true);
		$criteria->compare('lozinka',$this->lozinka,true);
		$criteria->compare('puno_ime',$this->puno_ime,true);
		$criteria->compare('tip',$this->tip);
		$criteria->compare('id_radno_mesto',$this->id_radno_mesto);
		$criteria->compare('id_odeljenje',$this->id_odeljenje);
		$criteria->compare('aktivan',$this->aktivan);
		$criteria->compare('slika',$this->slika,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('telefon',$this->telefon,true);
		$criteria->compare('sajt',$this->sajt,true);
		$criteria->compare('uloga',$this->uloga,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}