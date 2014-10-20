<?php

/**
 * This is the model class for table "tag".
 *
 * The followings are the available columns in table 'tag':
 * @property integer $id
 * @property string $odrednica
 *
 * The followings are the available model relations:
 * @property jezik[] $jeziks
 * @property objava[] $objavas
 */
class Tag extends CI18nActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return tag the static model class
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
		return 'tag';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*array('odrednica', 'required'),
			array('odrednica', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, odrednica', 'safe', 'on'=>'search'),*/
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		$relacije = array(
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
			'odrednica' => 'Odrednica',
		);
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
		$criteria->compare('odrednica',$this->odrednica,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

        public static function get($naziv, $id_jezik = Helper::ID_SRPSKI_JEZIK)
        {
            if( ! $naziv)
                return null;
            $criteria = new CDbCriteria();
            $criteria->with = array('ri18n');
            $criteria->addCondition('i18n_tag.id_jezik=:id_jezik');
            $criteria->addCondition('i18n_tag.naziv=:naziv');
            $criteria->params = array(':naziv'=>$naziv, ':id_jezik'=>$id_jezik );
            return Tag::model()->find($criteria);
        }

        public static function getNaziv($id, $id_jezik)
        {
            $db = Yii::app()->db;
            return $db->createCommand("SELECT naziv FROM i18n_tag WHERE id_tag=$id AND id_jezik=$id_jezik")->queryScalar();
        }
        
        public static function predlozitagove($term, $limit=20, $id_jezik=Helper::ID_SRPSKI_JEZIK)        
        {     
               if( ! trim($term))
                   return array();
               $cmd = Yii::app()->db->createCommand()
                       ->select('i.naziv')
                       ->from('tag t')
                       ->join('i18n_tag i', 't.id=i.id_tag')
                       ->where('i.naziv LIKE :term AND i.id_jezik=:id_jezik')
                       ->order('t.ucestalost DESC, i.naziv ASC')
                       ->limit($limit);
               $cmd->params[':term'] = '%'.Helper::escapeZaSqlLike($term).'%';
               $cmd->params[':id_jezik'] = $id_jezik;
               $tagovi = $cmd->queryAll();
               $predlozi = array();
               foreach($tagovi as $tag)
			$predlozi [] = $tag['naziv'];
               return $predlozi;
        }

        /*
         * kreira novi cirilicni tag. Cirilicni tag se odmah transliteruje i kreira se i latinicni.
         * Funkcija poziva dva puta save() za  tag.
         *
         * @return <tag>
         */
        public static function novitag($nazivCirilica)
        {
            $tag = Tag::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);
            $tag->naziv = $nazivCirilica;
            $tag->ucestalost = 1;
            $uspeh = $tag->save();
            $tag->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_LATINICA);
            $tag->naziv = Helper::cir2lat($nazivCirilica);
            $uspeh = $uspeh && $tag->save();
            if( ! $uspeh)
                throw new Exception (Yii::t('biblioteka', 'Грешка приликом уписивања нове кључне речи у базу!'));
            $tag->setAktivanjezik(Helper::ID_SRPSKI_JEZIK);
            return $tag;
        }

        /**
         *
         * @param array $tagovi array( array(id, naziv, ucestalost), ...) elementima niza se dodaje clan tezina.
         * Tezinu koristim za postavljanje velicine slova kojima se ispisuje tag.
         */
        public static function upisiTezinetagova(array & $tagovi)
        {
            $ukupno = 0;
            foreach($tagovi as $tag)
                $ukupno += $tag['ucestalost'];
            if($ukupno > 0)
            {
                foreach($tagovi as &$tag)
                    $tag['tezina'] = 12+(int)(16*$tag['ucestalost']/($ukupno+10));
            }
        }

        public static function getUrlZaodeljak(array & $tag, $id_odeljak, $odeljak)
        {
            return Helper::createI18nUrl('objava/kljucnaRecodeljak', null, array('id_odeljak'=>$id_odeljak, 'idRec'=>$tag['id'], 'odeljak'=>Helper::getSEOText($odeljak), 'rec'=>Helper::getSEOText($tag['naziv'])));
        }

        public static function getUrlZaSve(array & $tag)
        {
            return Helper::createI18nUrl('objava/kljucnaRecSviOdeljci', null, array('rec'=>$tag['id'], 'naziv'=>Helper::getSEOText($tag['naziv'])));
        }

 /*       public function dodajtagove($strtagovi, $objava)
	{
		$criteria=new CDbCriteria;

                $strtagovi = array_unique($strtagovi);

                $criteria->with('ri18n');
                $criteria->addCondition('i18n_tag.id_jezik', Helper::ID_SRPSKI_JEZIK);
		$criteria->addInCondition('i18n_tag.naziv', $strtagovi);
		$this->updateCounters(array('ucestalost'=>1), $criteria);

		foreach($strtagovi as $naziv)
		{
                    $criteria = new CDbCriteria();
                    $criteria->with('ri18n');
                    $criteria->addCondition('i18n_tag.naziv', $naziv);
                    if( ! $this->exists($criteria))
                    {
                            $tag=Tag::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);
                            $tag->naziv = $naziv;
                            $tag->ucestalost=1;
                            $uspeh = $tag->save();
                            $tag->setAktivanjezikNapraviAkoNePostoji(Helper::ID_SRPSKI_LATINICA);
                            $tag->naziv = Helper::cir2lat($naziv);
                            $uspeh = $uspeh && $tag->save();                            
                            if( ! $uspeh)
                                throw new Exception(Yii::t('biblioteka', 'Грешка приликом уписивања кључне речи у базу!'));
                    }
		}
	}*/
}