<?php

/**
 * This is the model class for table "komentar".
 *
 * The followings are the available columns in table 'komentar':
 * @property integer $id
 * @property integer $id_objava
 * @property integer $id_clan
 * @property integer $id_jezik_originala
 * @property integer $datum
 *
 * The followings are the available model relations:
 * @property jezik[] $jeziks
 * @property clan $id_clan0
 * @property jezik $id_jezik_originala0
 * @property objava $id_objava0
 */
class Komentar extends CI18nActiveRecord
{
    const OBJAVLJENO=1;
    const CEKA_ODOBRENJE=2;
    const OTPAD=3;
    const NOVO=4;

    public $naslovSr;
    public $autor;
    public $txt;
    /**
     * Returns the static model of the specified AR class.
     * @return komentar the static model class
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
            return 'komentar';
    }

    /**
     *
     * @param ContactForm $forma
     */
    public function inicijalizujISacuvaj($forma, $clan, $kontaktPodaci, $objava)
    {
        if($objava->zakljucano)
            return false;
        $this->tekst = strip_tags($forma->poruka);
        $this->status = Komentar::CEKA_ODOBRENJE;
        $this->datum = time();
        $this->id_objava = $objava->id;
        if($clan)
        {
            $this->id_clan = $clan->id;
            $this->setStatus($clan, $objava);
        }
        else
            $this->id_kontakt_podaci = $kontaktPodaci->id;

        if( ! $this->save())
            return false;

        if($this->id_jezik_originala === Helper::ID_SRPSKI_JEZIK)
        {
            $this->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_LATINICA);
            $this->tekst = Helper::cir2lat($forma->poruka);
            if( !$this->save())
                return false;
        }
        return true;

    }

    private function setStatus($clan, $objava)
    {
        $dozvole  = $this->getDozvole($clan, $objava);
        $this->status = self::NOVO;
        if($this->mozeDaObjavi($clan))
            $this->status = self::OBJAVLJENO;
        else
            $this->status = self::CEKA_ODOBRENJE;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
            // NOTE: you should only define rules for those attributes that
            // will receive user inputs.
            return array(
                    array('id_objava, datum', 'required'),
                    array('id_objava, id_clan, id_jezik_originala, datum', 'numerical', 'integerOnly'=>true),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('naslovSr, autor, txt, datum, status', 'safe', 'on'=>'search'),
            );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relacije = array(
            'robjava' => array(self::BELONGS_TO, 'Objava', 'id_objava'),
            'rclan' => array(self::BELONGS_TO, 'Clan', 'id_clan'),
            'rkontakt_podaci' => array(self::BELONGS_TO, 'KontaktPodaci', 'id_kontakt_podaci'),
        );
        return array_merge(parent::relations(), $relacije);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'naslovSr' => 'Наслов објаве',
            'autor' => 'Аутор',
            'txt' => 'Коментар',
            'datum' => 'Датум',
            'status' => 'Статус',
        );
    }

    public static function getListaPoslednjih($id_odeljak, $id_jezik, $limit)
    {
        
    }
    
    /**
     *
     * @param <type> $id_objava
     * @param <type> $id_jezik
     * @return CSqlDataProvider
     */
    public static function getListakomentara($id_objava, $id_jezik)
    {
        $broj = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('komentar k')
                ->where("k.id_objava=$id_objava". ' AND k.status='.Komentar::OBJAVLJENO)
                ->queryScalar();

        $cmd = Yii::app()->db->createCommand()
                ->select('k.id, k.datum, ik.tekst,k.id_jezik_originala, ik.id_jezik,
                    c.korisnicko_ime, c.puno_ime, c.email as mejl_registrovanog, c.slika, c.sajt,
                    kp.email as mejl_neregistrovanog, kp.ime, kp.web')
                ->from('komentar k')
                ->leftJoin('i18n_komentar ik', 'ik.id_komentar = k.id AND ik.id_jezik=:id_jezik')
                ->leftJoin('clan c', 'k.id_clan = c.id')
                ->leftJoin('kontakt_podaci kp', 'k.id_kontakt_podaci = kp.id')
                ->where('k.id_objava=:id_objava AND k.status=:status')
                ->order('k.id ASC');

        return new CSqlDataProvider($cmd->text, array(
            'totalItemCount'=>$broj,
            'pagination'=>array(
                'pageSize'=>10,

            ),
            'params'=>array(':id_jezik'=>$id_jezik, ':id_objava'=>$id_objava, ':status'=>Komentar::OBJAVLJENO),
        ));
    }

    public static function getkomentar($id_komentar, $id_jezik)
    {
        return Yii::app()->db->createCommand()
                ->select('ik.tekst')
                ->from('i18n_komentar ik')
                ->where("ik.id_komentar=:id_komentar AND ik.id_jezik=:id_jezik")
                ->bindValues(array(':id_komentar'=>$id_komentar, ':id_jezik'=>$id_jezik))
                ->queryScalar();
    }

    public static function getImeAutoraHtmlS($data)
    {
        $ime = Clan::getImeZaPrikazS($data);
        if( ! $ime)
            $ime = KontaktPodaci::getImeZaPrikaz($data);
        return $ime;
    }

    public static function getWebAutoraS(array & $data)
    {
        $web = $data['sajt'];
        if( ! $web)
            $web = $data['web'];
        return $web;
    }

    public static function getKomentatorHtmlS(array & $data)
    {
        $ime = self::getImeAutoraHtmlS($data);
        $webclan = self::getWebAutoraS($data);
        if($webclan)
            $komentator = "<a href=\"$webclan\">$ime</a>";
        else
            $komentator = $ime;
        return $komentator;
    }

    public function getKomentatorZaGridHtml()
    {
        $korisnicko_ime = '';
        $ime = '';
        $web = '';
        $puno_ime = '';
        $sajt = '';
        $clan = $this->rclan;
        $kontakt = $this->rkontakt_podaci;
        if($clan)
        {
            $korisnicko_ime = Yii::t('biblioteka', 'Члан: ').$clan->korisnicko_ime;
            //$puno_ime = $clan->puno_ime;
            $sajt = $clan->sajt;
        }
        elseif($kontakt)
        {
            $ime = Yii::t('biblioteka', 'Гост: ').$kontakt->ime;
            $web = $kontakt->web;
        }
        $data = array('korisnicko_ime'=>$korisnicko_ime, 'ime'=>$ime, 'puno_ime'=>$puno_ime, 'sajt'=>$sajt, 'web'=>$web,);
        return self::getKomentatorHtmlS($data);
    }

    public function getNaslovSRHtml()
    {
        return $this->robjava->getNaslovSRHtml(Helper::ID_SRPSKI_JEZIK);
    }


/*      public function getAutorHtml()
    {
        if($this->rclan)
            return CHtml::encode($this->rclan->korisnicko_ime);
        else
            return CHtml::encode($this->rkontakt_podaci->ime);
    }*/

    public function getAutorEmail()
    {
        if($this->rclan)
            return CHtml::encode($this->rclan->email);
        else
            return CHtml::encode($this->rkontakt_podaci->email);
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
        }
    }

    public function getDozvole($clan, $objava = null)
    {
        if($objava === null)
            $objava = $this->robjava;
        return $objava->getDozvole($clan, 'UlogaKomentar', $this);
    }

    public function mozeDaBrise($clan)
    {
        return $this->getDozvole($clan) & UlogaKomentar::IZBRISI;
    }

    public function mozeDaMenja($clan)
    {
        $dozvole = $this->getDozvole($clan);

        switch($this->status)
        {
            case self::OBJAVLJENO:
                return $dozvole & UlogaKomentar::IZMENI_OBJAVLJENO;
            case self::CEKA_ODOBRENJE:
                return $dozvole & UlogaKomentar::IZMENI_CEKA_ODOBRENJE;
            case self::OTPAD:
                return $dozvole & UlogaKomentar::IZMENI_OTPAD;
            default:
                return false;
        }
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
                return $dozvole & UlogaKomentar::DODAJ_PREVOD;

        switch($this->status)
        {
            case self::OBJAVLJENO:
                return $dozvole & UlogaKomentar::IZMENI_PREVOD_OBJAVLJENO;
            case self::CEKA_ODOBRENJE:
                return $dozvole & UlogaKomentar::IZMENI_PREVOD_NEOBJAVLJENO;
            case self::OTPAD:
                return $dozvole & UlogaKomentar::IZMENI_PREVOD_OTPAD;
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
                return $dozvole & UlogaKomentar::OBJAVI_CEKA_ODOBRENJE;
            case self::OTPAD:
                return $dozvole & UlogaKomentar::OBJAVI_OTPAD;
            case self::NOVO:
                return $dozvole & UlogaKomentar::OBJAVI_NOVI;
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
                return $dozvole & UlogaKomentar::STAVI_NA_CEKANJE_OBJAVLJENO;
            case self::CEKA_ODOBRENJE:
                return false;
            case self::OTPAD:
                return $dozvole & UlogaKomentar::STAVI_NA_CEKANJE_OTPAD;
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
                return $dozvole & UlogaKomentar::ODBACI_OBJAVLJENO;
            case self::CEKA_ODOBRENJE:
                return $dozvole & UlogaKomentar::ODBACI_CEKA_ODOBRENJE;
            case self::OTPAD:
                return false;
            default:
                return false;
        }
    }

    public function getTekst()
    {
        return $this->tekst;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($clan)
    {
        $criteria=new CDbCriteria;
        $criteria->together = true;

        Helper::criteriaDatum($criteria, $this);
        if($this->naslovSr)
        {
            $naslovSr = pg_escape_string($this->naslovSr);
            $uslovSr = "i18n_objava.naslov LIKE '%$naslovSr%'";
            $criteria->with['robjava.ri18n'] = array(
                'select' => false,
                'joinType' => 'INNER JOIN',
                'condition' => $uslovSr . ' AND i18n_objava.id_jezik=' . Helper::ID_SRPSKI_JEZIK
            );
        }

        if($this->txt)
        {
            $txt = pg_escape_string($this->txt);
            $criteria->with['ri18n'] = array(
                'select' => false,
                'joinType' => 'INNER JOIN',
                'condition' => "i18n_komentar.tekst LIKE '%$txt%' AND i18n_komentar.id_jezik=" . Helper::ID_SRPSKI_JEZIK
            );
        }

        if($this->autor)
        {
           $autor = pg_escape_string($this->autor);
           $criteria->with['rclan'] = array(
                'select'=>false,
                'joinType'=>'LEFT JOIN',
            );
            $criteria->with['rkontakt_podaci'] = array(
                'select'=>false,
                'joinType'=>'LEFT JOIN',
            );
            $criteria->addCondition("(rclan.korisnicko_ime LIKE '%$autor%' OR rkontakt_podaci.ime LIKE '%$autor%')");
        }

       $criteria->compare('t.status', $this->status);
        if(! $clan->isSuperAdministrator())
        {
          // $criteria->with['robjava'] = array('robjava');
           $criteria->with['robjava.rodeljci'] = array(
                // ne zelim da selektujem jer pravi problem
                'select'=>false,
                'joinType'=>'INNER JOIN',
                'condition'=> 'rodeljci.id IN ('.implode(',',$clan->getNizIdodeljak()).')',
            );
        }
        $criteria->distinct = true;
        $criteria->order = 't.id DESC';
        return new CActiveDataProvider(get_class($this), array(
                'criteria'=>$criteria,
        ));
    }

    public function postaviStatus($status, $funkcijaProvere)
    {
       if(Yii::app()->user->isGuest)
               throw new CHttpException(400, Yii::t('biblioteka', 'Нисте се пријавили на систем!'));
       $id = Yii::app()->user->id;
       $clan = Clan::getclan($id);
       $parametri = array('{id}'=>$this->id);
       if( ! $this->$funkcijaProvere($clan))
               throw new CHttpException(400, Yii::t('biblioteka', 'Немате одговарајуће дозволе!'));
       $this->status = $status;
       $trans = Yii::app()->db->beginTransaction();
       try
       {
           if( ! $this->save())
                throw new CHttpException(400, Yii::t('biblioteka', 'Промена статуса коментара #{id} није успела!', $parametri));
           $this->robjava->azurirajBrojkomentara();
           $trans->commit();
       }
       catch(Exception $e)
       {
           $trans->rollBack();
           throw $e;
       }
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
            if( ! $this->delete())
                throw new CHttpException(400, Yii::t('biblioteka', 'Брисање није успело, дошло је до грешке при упису у базу!'));
            $this->robjava->azurirajBrojkomentara();
            $trans->commit();
       }
       catch(Exception $e)
       {
           $trans->rollBack();
           throw $e;
       }
       return true;
     }   
}