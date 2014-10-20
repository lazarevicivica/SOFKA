<?php

class Zbirka extends CI18nActiveRecord
{
	/**
	 * The followings are the available columns in table 'zbirka':
	 * @var integer $id
	 * @var string $naziv_zbirke
	 * @var string $url_slike
	 * @var string $opis
	 */

	/**
	 * Returns the static model of the specified AR class.
	 * @return zbirka the static model class
	 */

        public $naziv_zbirkeEn;
        public $opisEn;
        
        public $nazivSr; //koristi se samo za pretragu
        public $opisSr; //koristi se samo za pretragu
        
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'zbirka';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('id_jezik_originala, naziv_zbirke, opis', 'required'),
                        array('roditelj, naziv_zbirkeEn, opisEn', 'required', 'on'=>'unos'),
			array('roditelj, redosled, id_jezik_originala', 'numerical', 'integerOnly'=>true),
			//izbaciti atribute koji nisu potrebni pri pretrazivanju!
			array('id, roditelj, levo, desno, id_jezik_originala, nazivSr, opisSr', 'safe', 'on'=>'search'),
		);
	}


        public function getId()
        {
            return $this->id;
        }

        public function setNaziv($naziv)
        {
            $this->naziv_zbirke = $naziv;
        }

        public function setOpis($opis)
        {
            $this->opis = $opis;
        }

        public function getNaziv()
        {
            return $this->naziv_zbirke;
        }

        public function getUrl()
        {            
            return Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$this->id, 'naziv'=>Helper::getSEOText($this->getNaziv())));
        }

        /**
         * Kreira novu zbirku u okviru postojece
         *
         * slicno kao agrolib::Oblast::sacuvajNovuPodOblast
         *
         * @param <String> $nazivNoveZbirke
         * @param <int> $id_jezik jezik podzbirke, ako je null onda je jezik isti kao i za this zbirku
         * @return <mixed> Ako je snimanje uspesno vraca novokreirani objekat, u suprotnom vraca false
         */
        public function napraviNovuPodkategoriju($nazivNoveZbirke, $id_jezik=null, $save=true)
        {
            if( ! $id_jezik)
                $id_jezik = $this->id_jezik_originala;
            $podzbirka = Zbirka::model()->napraviNovi($id_jezik);
            $podkategorija->roditelj = $this->id;
//            $podkategorija->idVrsta = $this->idVrsta;
            $podzbirka->setNaziv($nazivNoveZbirke);
            if($save)
            {
                if( ! $podzbirka->save())
                    return false;
            }
            return $podzbirka;
        }

        public static function root()
        {
            return Zbirka::model()->findByPk(1);
        }

        public function isRoot()
        {
            return $this->id === 1;
        }
        
        public function sacuvajPrevod($naziv, $opis, $id_jezik)
        {
            $this->setAktivanjezikNapraviAkoNePostoji($id_jezik);
            $this->setNaziv($naziv);
            $this->setOpis($opis);
            return $this->save();
        }

        /**
         *  Prilikom azuriranja ne sme se postaviti za roditelja zbirka koja je dete.
         *  Funkcija proverava da li putanja do novog roditelja sadrzi zbirku koja se azurira. 
         *  Ako sadrzi onda je putanja neispravna. 
         * 
         */
        private function isIspravnaPutanja($idRoditelj)
        {
            if( ! $idRoditelj)
                return false;
            $root = self::root();
            $stablo = new Stablo('Zbirka', 'naziv_zbirke');
            $putanja = $stablo->getPutanja($idRoditelj);
            if( ! $putanja)
                return false;
            foreach($putanja as $zbirka)
            {
                if($this->id === $zbirka->id) 
                    return false;
            }
            return true;
        }
        
        public function sacuvaj()
        {
            $trans = Yii::app()->db->beginTransaction();
            try
            {
                $this->sacuvajBezCommit();
                $scenario = $this->scenario;
                $this->scenario = null;
                $this->rekonstruisiStablo();
                $this->scenario = $scenario;
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

        //upisuje objekat u bazu, ali ne komituje transakciju        
        public function sacuvajBezCommit()
        {
                $this->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_JEZIK);
                if( ! $this->save())
                    throw new Exception('Неуспех при снимању ћириличне верзије.');
                if( ! $this->isIspravnaPutanja($this->roditelj))
                {
                    $this->addError('roditelj', 'Морате изабрати другог родитеља!');
                    throw new Exception('Погрешан родитељ!');
                }
                $nazivLat = Helper::cir2lat($this->naziv_zbirke);
                $opisLat = Helper::cir2lat($this->opis);
                if( ! $this->sacuvajPrevod($nazivLat, $opisLat, Helper::ID_SRPSKI_LATINICA))
                        throw new Exception('Неуспех при снимању латиничне верзије.');
                if( ! $this->sacuvajPrevod($this->naziv_zbirkeEn, $this->opisEn, Helper::ID_ENGLESKI_JEZIK))
                        throw new Exception('Неуспех при снимању енглеске верзије.');          
        }
        
	public function relations()
	{
            $rel =  array(
                'rRoditelj' => array(self::BELONGS_TO, 'Zbirka', 'roditelj'),
		'rDeca' => array(self::HAS_MANY, 'Zbirka', 'roditelj'),
                'rKnjige' => array(self::HAS_MANY, 'Knjiga', 'idknjiga'), //TODO proveri ovo idknjiga ne postoji!
            );

            return array_merge(parent::relations(), $rel);
	}

        public function getStrId()
        {
            return 'id_'.$this->id;
        }

    public function transliterovaniLike($kolona, $vrednost, array & $parametri, $paramIndex = '')
    {   
        $vrednost = trim($vrednost);
        if( ! $vrednost)
            return '';
        $param = str_replace('.', '_', $kolona).$paramIndex;
        $vrednostCir = Helper::lat2cir($vrednost);
        $vrednostLat = Helper::cir2lat($vrednost);  
        $paramOriginal = ":$param";
        $paramCirilica = ":{$param}_cir";
        $paramLatinica = ":{$param}_lat";        
        $uslov = "($kolona LIKE $paramOriginal OR $kolona LIKE $paramCirilica OR $kolona LIKE $paramLatinica)";
        $parametri[$paramOriginal] = '%'.Helper::escapeZaSqlLike($vrednost).'%';
        $parametri[$paramCirilica] = '%'.Helper::escapeZaSqlLike($vrednostCir).'%';
        $parametri[$paramLatinica] = '%'.Helper::escapeZaSqlLike($vrednostLat).'%';
        return $uslov;        
    }        
    
    public function explodeTransliterovaniLike($kolona, $vrednost, array & $parametri)
    {
        $reci = explode(' ', $vrednost);
        $paramIndex = 0;
        $where = '';
        $and = ' AND ';
        foreach($reci as $rec)
        {
            $paramIndex++;//potreban je jer za unos Andric, Ivo pretraga se vrsi i po imenu i prezimenu
            $uslov = $this->transliterovaniLike($kolona, $rec, $parametri, $paramIndex);
            if($uslov)
                $where .= $uslov . $and;
        }        
        return substr($where, 0, strlen($where) - strlen($and));
    }
    
/* $frm sadrzi
    public $naslov;
    public $autor;
    public $poglavlje;
    public $godinaOd;
    public $godinaDo;    
    public $vrstaGradje;   
 */
    
        private function generisiWhere($frm, array & $parametri)
        {
            if( empty($frm))
                return '';
            $where = '';
           //OBJEDINJENO (kada je navedeno onda bi trebalo da bude jedini parametar pretrage, mada nije problem ni ako stoji sa ostalim parametrima : naslov, knjiga itd.)
            if(! empty($frm->ftsKomplet))
            {
                $parametri[':ftsKomplet'] = Helper::plain2tsquery($frm->ftsKomplet, '&');
                $where .= " AND knjiga.komplet_tsvector @@ to_tsquery('sr_lat', :ftsKomplet)"; 
            }  
            
           //OD - DO
            if( ! empty($frm->godinaOd) && ! empty($frm->godinaDo))
            {
                $od = intval($frm->godinaOd);
                $do = intval($frm->godinaDo);
                $where .= " AND (knjiga.godina BETWEEN $od AND $do) ";
            }
            elseif( ! empty($frm->godinaOd))
            {
                $od = intval($frm->godinaOd);
                $where .= " AND knjiga.godina >= $od ";
            }
            elseif( ! empty($frm->godinaDo))
            {
                $do = intval($frm->godinaDo);
                $where .= " AND knjiga.godina <= $do ";
            }
            
            //VRSTA GRADJE
            if( ! empty($frm->vrstaGradje))
            {
                $id_vrsta_gradje = intval($frm->vrstaGradje);
                $where .= " AND knjiga.id_vrsta_gradje=$id_vrsta_gradje ";
            }
            
            //PUNI TEKST 
            if(! empty($frm->ftsUpit))
            {
                $parametri[':ftsUpit'] = Helper::plain2tsquery($frm->ftsUpit, '&');
                $where .= " AND knjiga.knjiga_tsvector @@ to_tsquery('sr_lat', :ftsUpit)"; 
            }
            //NASLOV
            if( ! empty($frm->naslov))
            {
                $parametri[':ftsNaslov'] = Helper::plain2tsquery($frm->naslov, '&');                
                $where .= " AND knjiga.naslov_tsvector @@ to_tsquery('sr_lat', :ftsNaslov)";            
            }
            //AUTOR
            if( ! empty($frm->autor))
            {
                $parametri[':ftsAutor'] = Helper::plain2tsquery($frm->autor, '&');                                
                $where .= " AND knjiga.autor_tsvector @@ to_tsquery('sr_lat', :ftsAutor)";             
            }

            //KLJUCNE RECI
            if( ! empty($frm->kljucneReci))
            {
                $parametri[':ftsKljucneReci'] = Helper::plain2tsquery($frm->kljucneReci, '&');                
                $where .= " AND knjiga.kljucne_reci_tsvector @@ to_tsquery('sr_lat', :ftsKljucneReci)";            
            }
            //OPIS
            if( ! empty($frm->opis))
            {
                $parametri[':ftsOpis'] = Helper::plain2tsquery($frm->opis, '&');                                
                $where .= " AND knjiga.opis_tsvector @@ to_tsquery('sr_lat', :ftsOpis)";             
            }    
            
            //IZDANJE
            /*if( ! empty($frm->izdanje))
            {
                $parametri[':ftsIzdanje'] = Helper::plain2tsquery($frm->izdanje, '&');                                
                $where .= " AND knjiga.izdanje_tsvector @@ to_tsquery('sr_lat', :ftsIzdanje)";             
            } */             
            return $where;
        }
    
               
        public function sqlKnjigeIspodZbirke($frm=null, $countLimit=1000)
        {        
            $id_jezik = Helper::getAppjezikId();
            $cmd = Yii::app()->db->createCommand();
            
            $cmd->select('knjiga.id, knjiga.url_slike, knjiga.br_pregleda, knjiga.id_objava,
                          knjiga.autor, knjiga.inv_br, knjiga.izdanje, (knjiga.json_desc IS NOT NULL AND knjiga.json_desc <> \'\') AS json_desc,
                          i18n_objava.naslov, i18n_objava.uvod, knjiga.id_zbirka, (knjiga.knjiga_tsvector IS NOT NULL) AS sadrzi_indeks,
                          knjiga.dan, knjiga.mesec, knjiga.godina, 
                          i18n_zbirka.naziv_zbirke, i18n_vrsta_gradje.naziv_vrste');
            /*if($frm && $frm->ftsUpit)
                $cmd->select($cmd->getSelect() . ", ts_rank(knjiga.knjiga_tsvector, plainto_tsquery('sr_lat', :ftsUpit)) AS rang");*/
            
            $cmd->from('knjiga');
            $cmd->join('zbirka', 'knjiga.id_zbirka=zbirka.id');
            $cmd->join('i18n_zbirka', 'zbirka.id=i18n_zbirka.id_zbirka');
            $cmd->join('vrsta_gradje', 'knjiga.id_vrsta_gradje=vrsta_gradje.id');
            $cmd->join('i18n_vrsta_gradje', 'i18n_vrsta_gradje.id_vrsta_gradje=vrsta_gradje.id');
            $cmd->join('i18n_objava', 'knjiga.id_objava=i18n_objava.id_objava');
            
            $cmd->join('objava', 'knjiga.id_objava=objava.id');
            
            $where = 'objava.status= :objavljeno AND i18n_objava.id_jezik=:jezik AND i18n_zbirka.id_jezik=:jezik AND i18n_vrsta_gradje.id_jezik=:jezik AND zbirka.levo BETWEEN :levo AND :desno';
            $parametri = array(':levo'=>$this->levo, ':desno'=>$this->desno, ':jezik'=> $id_jezik, ':objavljeno'=>Objava::OBJAVLJENO);                
            $where .= $this->generisiWhere($frm, /*referenca &*/$parametri);                                    
            $cmd->where($where);
            $dp = new CSqlDataProvider($cmd->getText());
            
            $dp->params = $parametri;
            
            $sort = new CSort();
            $sort->defaultOrder = array('id' => 'id DESC');
            $sort->attributes = array('id'     => array('asc'=>'id ASC',
                                                        'desc' => 'id DESC',
                                                        'label' => Yii::t('biblioteka', 'Редни бр.')),
                                      'naslov' => array('asc'=>'naslov ASC, id DESC',
                                                       'desc' => 'naslov DESC, id DESC',
                                                       'label' => Yii::t('biblioteka', 'Наслов')),
                                      'autor' => array('asc'=>'postoji_autor DESC, autor ASC, id DESC',
                                                       'desc' => 'postoji_autor DESC, autor DESC, id DESC',
                                                       'label' => Yii::t('biblioteka', 'Аутор')),
                                      'godina' => array('asc'=>'postoji_sort_datum DESC, sort_datum ASC, id DESC',
                                                        'desc' => 'postoji_sort_datum DESC, sort_datum DESC, id DESC',
                                                        'label' => Yii::t('biblioteka', 'Година')),
                
            );
            /*if($frm && $frm->ftsUpit)
                $sort->attributes ['rang']= array(
                    'asc' => 'rang ASC, id DESC',
                    'desc' => 'rang DESC, id DESC',
                    'label' => Yii::t('biblioteka', 'Релевантност'));*/
            $dp->setSort($sort);

            $cmd->setText('');
            $cmd->select('COUNT(*)');
            $cmd->bindValues($dp->params);
            if($countLimit)
                $cmd->limit = $countLimit;
            $dp->setTotalItemCount($cmd->queryScalar());
            return $dp;
        }

        public static function rekonstruisiStablo()
        {
            $root = self::root();
            $stablo = new Stablo('Zbirka', 'naziv_zbirke');
            $stablo->rekonstruisiStablo($root);
        }

        public static function getULStablo()
        {
            $root = self::root();
            $stablo = new Stablo('Zbirka', 'naziv_zbirke');
            return $stablo->getULStablo($root);
        }

        /**
         *
         * @param <Kategorija> $otvoren
         * @param <Visitor> @visitor - Objekat cija funkcija visit se poziva za svaki cvor stabla koji treba prikazati
         * @return <String> Lista kategorija koje su vidljive kada je otvorena kategorija $otvoren
         */
        public static function getListaOtvorenih($otvoren, $visitor)
        {
            $stablo = new Stablo('Zbirka', 'naziv_zbirke');
            $stablo->getListaOtvorenihAdjency($otvoren, $visitor);
//            $url = Helper::createI18nUrl('');
            $tekst = Yii::t('biblioteka', 'Све збирке');                        
            $id = 1;
            $url = Helper::createI18nUrl('digital/index', null, array('id_zbirka'=>$id, 'naziv'=>Helper::getSEOText($tekst)));
            if(isset($_GET['sort']))
                $url .= '?sort=' . urlencode($_GET['sort']);
            $klasa = $otvoren->id == 1 ? ' selektovana-zbirka' : '';            
            $sveZbirke = "<li class=\"sve-zbirke\"><a id=\"zbirka_$id\" class=\"zbirka$klasa\" href=\"$url\">$tekst</a></li>";
            return $sveZbirke . $visitor->getRezultat();
        }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Ид',
			'naziv_zbirke' => 'Збирка',
			'url_slike' => 'Урл слике',
			'opis' => 'Опис',
                        'nazivSr' => 'Збирка',
                        'opisSr' => 'Опис',
                        'naziv_zbirkeEn' => 'Збирка ЕН',
                        'opisEn' => 'Опис ЕН', 
		);
	}

        public function getNazivSRHtml()
        {
            return CHtml::encode($this->naziv_zbirke);
        }
        
        public function getOpisSRHtml()
        {
            return CHtml::encode($this->opis);
        }
        
        public function getPutanja()
        {
            $stablo = new Stablo('Zbirka', 'naziv');
            return $stablo->getPutanja($this->id);
        }
        
        public function getPutanjaZaBreadcrumbs()
        {
            $putanja = $this->getPutanja();
            $ret = array();
            foreach($putanja as $deo)
                $ret[$deo->naziv] = $deo->getUrl();
            return $ret;
        }        
        
       /* public static function delete($id)
        {
            Zbirka::model()->findByPk($id)->delete();
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
                
                $criteria->together = true;
                
                if( ! empty($this->nazivSr) || ! empty($this->opisSr))
                {
                    $uslovSr = '';
                    if( ! empty($this->nazivSr))
                    {
                        $naziv = pg_escape_string($this->nazivSr);                
                        $uslovSr = "naziv_zbirke LIKE '%$naziv%'";
                        if(! empty($this->opisSr))
                                $uslovSr .= ' AND ';
                    }
                    if(! empty($this->opisSr))
                    {
                        $opis = pg_escape_string($this->opisSr);                
                        $uslovSr .= "opis LIKE '%$opis%'";
                    }
                    
                    $criteria->with['ri18n'] = array(
                        'select' => false,
                        'joinType' => 'INNER JOIN',
                        'condition' => $uslovSr. ' AND id_jezik='.Helper::ID_SRPSKI_JEZIK,
                    );
                }
                                
		$criteria->compare('id',$this->id);
                
                
		$criteria->compare('url_slike',$this->url_slike,true);

		//$criteria->compare('opis',$this->opis,true);

		return new CActiveDataProvider('Zbirka', array(
			'criteria'=>$criteria,
		));
	}
}