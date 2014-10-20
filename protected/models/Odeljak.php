<?php

/**
 * This is the model class for table "odeljak".
 *
 * The followings are the available columns in table 'odeljak':
 * @property integer $id
 * @property string $naziv
 *
 * The followings are the available model relations:
 * @property odeljenje[] $odeljenjes
 */
class Odeljak extends CI18nActiveRecord
{
//koristi se samo pri izobru odeljaka u kojima ce se naci objava    
    public $cekiran = false;
    public $cekEnabled = false;
    public $top = false;
//////////////////////////////////////////////////////////////////////////////////////

    const ID_NASLOVNA=1;
    const ID_DIGITALNA_BIBLIOTEKA=19;
    /**
     * Returns the static model of the specified AR class.
     * @return odeljak the static model class
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
            return 'odeljak';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
            // NOTE: you should only define rules for those attributes that
            // will receive user inputs.
            return array(
                    array('naziv', 'length', 'max'=>50),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, naziv', 'safe', 'on'=>'search'),
                    array('id, naziv, cekiran, cekEnabled, top', 'safe', 'on' => 'izbor')
            );
    }

    public static function getNaziv($id, $id_jezik)
    {
        $db = Yii::app()->db;
        return $db->createCommand("SELECT naziv FROM i18n_odeljak WHERE id_odeljak=$id AND id_jezik=$id_jezik")->queryScalar();
    }
    
    /**
     * @return array relational rules.
     */
    public function relations()
    {
            $relacije = array(
                'rodeljakclan' => array(self::HAS_MANY, 'ClanOdeljak', 'id_odeljak'),
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
                    'naziv' => 'Naziv',
            );
    }

    public static function filtrirajZaIzbor(array & $sviOdeljci, $clan, $objava, $scenario = 'izbor')
    {
        $odeljci = array();
        foreach($sviOdeljci as $odeljak)
        {
            $odeljak->scenario = $scenario;
            $odeljak->initIzbor($clan, $objava); //setuje atribut mozeDaPrikljuci, mozeDaIskljuci i cekirano
            //odeljak se preskace ako nije cekiran i nije enejblovan
            if( ! ( ! $odeljak->cekiran && ! $odeljak->cekEnabled) )
            {
                $odeljakobjava = OdeljakObjava::model()->find('t.id_odeljak=:id_odeljak AND t.id_objava=:id_objava',
                    array(':id_odeljak'=>$odeljak->id, ':id_objava'=>$objava->id));
                if($odeljakobjava)
                    $odeljak->top = $odeljakobjava->top;
                
                $odeljci[] = $odeljak;
            }
        }
        return $odeljci;
    }

    public function getDozvole($clan, $objava)
    {

        if($clan->isSuperAdministrator())
            return Uloga::DOZVOLJENO_SVE;
        
        $clanodeljak = $this->rodeljakclan(array('condition'=>"rodeljakclan.id_clan=$clan->id"));
        $idUloga = $clanodeljak[0]->uloga;

        if( $objava->isNewRecord)
            $vlasnik = true;
        else
            $vlasnik = $objava->id_clan === $clan->id;
        if($vlasnik)
            $dozvole = Uloga::get()->getDozvoleVlasnik($idUloga);
        else
            $dozvole = Uloga::get()->getDozvoleNijeVlasnik($idUloga);
        return $dozvole;
    }

    /**
     * Setuje atribute cekiran i cekEnabled.
     * @param <type> $clan
     * @param <type> $objava
     */
    public function initIzbor($clan, $objava)
    {        
        //cekirano je ako objava pripada odeljku, t.j. ako postoji unos u tabeli odeljak_objava
        $this->cekiran = OdeljakObjava::model()->exists('t.id_odeljak=:id_odeljak AND t.id_objava=:id_objava', array(':id_odeljak'=>$this->id, ':id_objava'=>$objava->id));
//ispitati da lije dozvoljno prikljuciti ili iskljuciti objavu
//ako nije dozvoljeno onda je indikator cekEnabled = false;
        if( ! $this->cekiran)        
            $this->cekEnabled = $this->mozeDaPrikljuci($clan, $objava);
        else //jeste cekiran, ispitujem da li sme da se iskljuci
            $this->cekEnabled = $this->mozeDaIskljuci($clan, $objava);
    }

    public function mozeDaPrikljuci($clan, $objava)
    {
        $dozvole = $this->getDozvole($clan, $objava);
        switch($objava->status)
        {
            case Objava::OBJAVLJENO:
                return $dozvole & Uloga::PRIKLJUCI_OBJAVLJENO;
            case Objava::CEKA_ODOBRENJE:
                return $dozvole & Uloga::PRIKLJUCI_CEKA_ODOBRENJE;
            case Objava::OTPAD:
                return $dozvole & Uloga::PRIKLJUCI_OTPAD;
            case Objava::DRAFT:
                return ($dozvole & Uloga::OBJAVI_NOVI) || ($dozvole & Uloga::STAVI_NA_CEKANJE_NOVI);
            default:
                assert(false);
        }        
    }

    public function mozeDaIskljuci($clan, $objava)
    {
        $dozvole = $this->getDozvole($clan, $objava);
        switch($objava->status)
        {
            case Objava::OBJAVLJENO:
                return $dozvole & Uloga::ISKLJUCI_OBJAVLJENO;
            case Objava::CEKA_ODOBRENJE:
                return $dozvole & Uloga::ISKLJUCI_CEKA_ODOBRENJE;
            case Objava::OTPAD:
                return $dozvole & Uloga::ISKLJUCI_OTPAD;
            case Objava::DRAFT:
                return $dozvole & Uloga::IZMENI_NOVI;//da bi bio u poziciji da iskljuci draft, pre toga mora da ima dozvolu izmeni_novo
            default:
                assert(false);
        }
    }

    public static function getUrl(array & $odeljak)
    {
        $naziv = Helper::getSEOText($odeljak['naziv']);
        $ruta = $odeljak['ruta'];
        if($ruta)
        {
            $parametri = array();
            $id = $odeljak['id_param'];
            if($id)
            {
                $parametri['id'] = $id;
                $parametri['naziv'] = $naziv;
            }
            return Helper::createI18nUrl($ruta, null, $parametri);
        }
        else
        {
            $id = $odeljak['id'];
            return Helper::createI18nUrl('objava/index', null, array('odeljak'=>$id, 'naziv'=>$naziv));
        }
    }

    /*
     * @return Vraca listu tagova kojima su oznacene objave iz ovog odeljka
     */
    public static function gettagovi($id_odeljak, $limit=20, $zbirka = null)
    {
        $jezik = Helper::getAppjezikId();
        $status = Objava::OBJAVLJENO;
        $joinDigital = '';
        $whereDigital = '';
        if($id_odeljak === self::ID_DIGITALNA_BIBLIOTEKA AND ! empty($zbirka))
        {            
            $joinDigital = 
' INNER JOIN knjiga k ON(k.id_objava=o.id) INNER JOIN 
zbirka z ON(k.id_zbirka = z.id)
';
           $whereDigital = 
"AND z.levo BETWEEN $zbirka->levo AND $zbirka->desno";
        }
        $sql =
"SELECT t.id, it.naziv, COUNT(*) AS ucestalost
FROM
tag t INNER JOIN
i18n_tag it ON (t.id = it.id_tag) INNER JOIN
objava_tag ot ON (t.id = ot.id_tag) INNER JOIN 
(SELECT objava.id, objava.status FROM objava ORDER BY objava.id DESC) AS o ON (ot.id_objava = o.id) INNER JOIN
odeljak_objava oo ON(ot.id_objava = oo.id_objava)
$joinDigital
WHERE
oo.id_odeljak=$id_odeljak AND it.id_jezik=$jezik AND o.status=$status $whereDigital
GROUP BY t.id, it.naziv
ORDER BY ucestalost DESC, it.naziv ASC
LIMIT $limit";
        $tagovi = Yii::app()->db->createCommand($sql)->queryAll();
        Tag::upisiTezinetagova($tagovi);
        return $tagovi;
    }

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
            $criteria->compare('naziv',$this->naziv,true);

            return new CActiveDataProvider(get_class($this), array(
                    'criteria'=>$criteria,
            ));
    }
    
}