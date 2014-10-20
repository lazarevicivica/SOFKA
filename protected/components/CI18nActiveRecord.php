<?php
//CI18nActiveRecord verzija 1.0
//


//Bitan implementacioni detalj>Klasa ima definisan niz i18n, takodje pored njega
//ima i ri18n koji je definisan u funkciji relations().
//U okviru funkcije getI18n() i isUcitanjezik() proverava se
//da li postoji pevod u nizu i18n, ako postoji ri18n se ne gleda. Ako ne ne postoji
//proverava se da li postoiji u ri18n i ako postoji tamo onda se kopira u i18n.
//Na taj nacin je postignuto da se jednim sql upitom moze ucitati grupa objekata
//u ri18n niz a da se nemogucnost upisa u ri18n prevazidje njegovom zamenom sa i18n.
//Daj boze da razumem ovo posle mesec dana :)


/*
TODO moguca buduca nadogradnja:
dodati atribut $m_jezikJeZamenjenOriginalom (ili $ucitanTrazeni)
svaki put kada poziv setAktivanjezik vrati false
postavatviti ovaj atribut na true (u suprotnom na false).
 */

abstract class CI18nActiveRecord extends CActiveRecord
{
    const YIIT = 'biblioteka';
    private $m_idAktivanjezik;
    private $i18n = array();

     /**
     * Vraca klasu koja odgovara referenciranom i18n objektu
     */
    private function getI18nKlasa()
    {
        return 'I18n'.get_class($this);
        //return 'i18n_' . get_class($this);//promenjeno zbog postgresa
    }

    /**
     * Ako je i18n objekat za trazeni jezik vec ucitan onda vraca taj objekat.
     * Ako nije ucitan pokusava da ga ucita iz baze.
     * Ako objekat ne postoji u bazi vraca null.
     */
    private function getI18n($id_jezik)
    {
        assert(isset($this->i18n));
        assert(isset($this->ri18n));
        if($this->isUcitanjezik($id_jezik))
        {
            if(array_key_exists($id_jezik, $this->i18n)) //ako postoji u nizu koji sam ja definisao
                return $this->i18n[$id_jezik];           //vrati objekat iz niza
            else //if(array_key_exists($id_jezik, $this->ri18n)) //ako postoji u nizu definisanom relacijom
            {
                foreach($this->ri18n as $value)
                {
                    if($value->id_jezik == $id_jezik)
                    $this->i18n[$id_jezik] = $value;
                    return $this->i18n[$id_jezik];
                }

            }
            assert(false); //funkcija isUcitanjezik ne radi ispravno
        }
        $klasa = $this->getI18nKlasa();
        $id = $this->id;
        //assert(isset($id));
        //assert($id);
        if( ! $id) 
            return null; //dodato kasnije 2. septembar 2011.*/
        $fk = $this->getIdFk();
        return $klasa::model()->findByPk(array($fk=>$id,'id_jezik'=>$id_jezik));
    }

//TODO ove funkcije ne bi valjale ako bi bilo vise jezika od planiranih srpski i engleski
    public function getNazivOriginalnogjezika()
    {
        if($this->id_jezik_originala == Helper::ID_SRPSKI_JEZIK)
                return Yii::t(YIIT, 'Ћирилица');
        else if($this->id_jezik_originala == Helper::ID_ENGLESKI_JEZIK)
                return Yii::t(YIIT, 'English');
        else if($this->id_jezik_originala == Helper::ID_SRPSKI_LATINICA)
                return Yii::t(YIIT, 'Latinica');
        return 'nepoznat jezik!';
    }

    public function getNazivjezikaPrevoda()
    {
        if($this->id_jezik_originala == Helper::ID_SRPSKI_JEZIK)
            return Yii::t(YIIT, 'engleski');
        else if($this->id_jezik_originala == Helper::ID_ENGLESKI_JEZIK)
             return Yii::t(YIIT, 'srpski');
        return 'nepoznat jezik!';
    }


    /**
    * Ako je postavljen aktivan jezik onda vraca id tog jezika.
    * Ako nije postavljen onda trazi u bazi kod za jezik iz Yii aplikacije(tacnije sesije) i vraca njegov id
    * Ako nista nije nasao na kraju vraca id jezika originala
    */
    public function getAktivanjezikId()
    {
        assert($this->id_jezik_originala != null);
        if($this->m_idAktivanjezik == null)
        {
            if(isset($_SESSION['id_jezik']))
            {
                $id_jezik = $_SESSION['id_jezik'];
                if($this->setAktivanjezik($id_jezik))
                    $this->m_idAktivanjezik = $id_jezik;
                else
                    $this->m_idAktivanjezik = $this->id_jezik_originala;
            }
            else
                $this->m_idAktivanjezik = $this->id_jezik_originala;
        }
        return $this->m_idAktivanjezik;
    }


    public static function model($className=__CLASS__)
    {
		return parent::model($className);
    }

    public function napraviNovi($id_jezik_originala, $scenario = null)
    {
        $klasa = get_class($this);
        if($scenario == null)
            $objekat = new $klasa();
        else
            $objekat = new $klasa($scenario);
        $objekat->initNovi($id_jezik_originala);
        return $objekat;
    }

 //   public function on
    public function brUcitanihI18n()
    {
        $brI18n = count($this->i18n);
        $brRi18n = count($this->ri18n);

        //saberi broj elemenata u mom nizu sa brojem elemenata u nizu definisanom relacijom
        $ukupno = $brI18n + $brRi18n;

        //ako se id_jezika iz relacije poklapa sa kljucem
        //iz niza koji sam ja definisao (i18n) ukupno se umanjuje za jedan
        //jer ne zelim da brojim dva puta jedan isti element
        foreach($this->ri18n as $rk => $rv)
        {
            foreach($this->i18n as $kljuc => $v)
            {
                if($rv->id_jezik == $kljuc)
                        $ukupno--;
            }
        }
        return $ukupno;
    }

    protected function initNovi($id_jezik_originala)
    {
 //assert($this->isNewRecord);
        if($id_jezik_originala != null)
        {
            $this->id_jezik_originala = $id_jezik_originala;
            $this->m_idAktivanjezik = $id_jezik_originala;
           // assert(property_exists(get_class($this), 'i18n'));
            $this->i18n[$id_jezik_originala] = $this->noviI18n($id_jezik_originala);
            assert(isset($this->i18n[$id_jezik_originala]));
            $m_idAktivanjezik = $id_jezik_originala;
        }
    }
//promenjeno u private bez testiranja
    private function noviI18n($id_jezik)
    {
        $klasa = $this->getI18nKlasa();
        $i18n = new $klasa();
        $i18n->id_jezik = $id_jezik;
        return $i18n;
    }

    /**
     * Kreira novi I18n za $id. Ukoliko prevod vec postoji on biva zamenjen
     *
     * metod obavezno mora biti public!
     */
    public function dodajNoviI18n($id_jezik)
    {
        $i18n = $this->noviI18n($id_jezik);
        $this->dodajI18n($i18n);
    }

    /**
     * Dodaje prevod $i18n u listu, ukoliko vec postoji prevod za $id_jezik
     * taj prevod biva zamenjen.
     *
     */
    private function dodajI18n($i18n)
    {
        assert(isset($i18n->id_jezik));
        assert($i18n->id_jezik != null);
        assert(isset($this->i18n));
        assert(get_class($i18n) == $this->getI18nKlasa());
        //ako vec postoji i18n za jezik id_jezik onda se radi Update a ne Insert
        //pa zato postavljam isNewRecord na false.
        if($this->isUcitanjezik($i18n->id_jezik))//ako je vec ucitan
            $i18n->isNewRecord = $this->getI18n($i18n->id_jezik)->isNewRecord;
        elseif($this->isPostojiUBazi($i18n->id_jezik))
            $i18n->isNewRecord = false;
        $this->i18n[$i18n->id_jezik] = $i18n;
    }

//Funkcja dodata uz minimalno testiranja! Funkcije koje se pozivaju su detaljno testiranje.
    public function setAktivanjezikNapraviAkoNePostoji($id_jezik)
    {
        $this->setAktivanjezik($id_jezik);
        if($this->getAktivanjezikId() != $id_jezik)
        {
                $this->dodajNoviI18n($id_jezik);
                $this->setAktivanjezik($id_jezik);
        }
    }

    public function isPostojiUBazi($id_jezik)
    {
        $klasa = $this->getI18nKlasa();
        $fk = $this->getIdFk();
        $condition = $fk.'=:id AND id_jezik=:id_jezik';
        $prebrojani = $klasa::model()->count($condition, array(':id'=>$this->id, ':id_jezik'=>$id_jezik));
        assert($prebrojani <= 1);
        return $prebrojani > 0;
    }

    /**
     * Vraca true ako je ucitan trazeni jezik, inace false.
     */
    public function isUcitanjezik($id_jezik)
    {
       assert(isset($this->i18n));
       if(array_key_exists($id_jezik, $this->i18n))
               return true;
       foreach($this->ri18n as $value)
       {
           if($value->id_jezik == $id_jezik)
           {
               $this->i18n[$id_jezik] = $value;
               return true;
           }
       }
       return false;
    }

     /**
     * Vraca true ako je ucitan orignalni jezik, inace false;
     */
    public function isUcitanOriginalnijezik()
    {
        return isUcitanjezik($this->id_jezik_originala);
    }

    function getIdFk()
    {
        //return 'id'.get_class($this);        
        return 'id_' . $this->tableName();
    }

    /*
     * Vraca ukupan broj jezika, upisanih u bazu, za dati objekat.
     * Taj broj ne mora da odgovara broju ucitanih jezika.
     */
    public function ukupanBrojjezikaUBazi()
    {
        $klasa = $this->getI18nKlasa();
        $fk = $this->getIdFk();
        assert($this->id != null);
        $condition = $fk.'=:id';
        return $klasa::model()->count($condition, array(':id'=>$this->id));
    }

    public function relations()
    {
        $klasa = $this->getI18nKlasa();
        $alias = $klasa::model()->tableName();
        $fk = $this->getIdFk();
        return array(
            //'ri18n' =>array(self::HAS_MANY, $klasa , $fk), //TODO izmenjeno mnogo kasnije, dodat alijas
            'ri18n' =>array(self::HAS_MANY, $klasa , $fk, 'alias' => $alias),
        );
    }

    public function izbrisiSvePrevode()
    {
        $this->setAktivanOriginalnijezik();
        foreach($this->ri18n as $prevod)
        {
            if($prevod->id_jezik != $this->id_jezik_originala)
            {
                if(!$prevod->delete())
                    return false;
            }
        }
        return true;
    }

    public function isAktivanjezik($id_jezik)
    {
        return $this->getAktivanjezikId() == $id_jezik;
    }

    public function isAktivanOriginalnijezik()
    {
        return $this->isAktivanjezik($this->id_jezik_originala);
    }

    /**
     * Ako je vec ucitan taj jezik ne radi nista i vraca true;
     * Ako nije ucitan pokusava da ucita trazeni jezik iz baze.
     * Ako postoji UPISUJE GA u i18n listu.
     * Ako ne postoji trazeni jezik onda vraca false
     */
    protected function ucitajI18n($id_jezik)
    {
        assert(isset($this->i18n));
        if(!$this->isUcitanjezik($id_jezik)) // ako jezik NIJE vec ucitan
        {
            $i18n = $this->getI18n($id_jezik);
            if($i18n != null)
            {
                $this->i18n[$id_jezik] = $i18n;
                return true;
            }
            else
                return false;
        }
        else
            return true;
    }

    //TODO
    //Delete
    protected function ucitajOriginalniI18n()
    {
        $idOriginala = $this->id_jezik_originala;
        $postoji = $this->ucitajI18n($idOriginala);
    }

    /**
     * Ako je ucitan postavlja ga kao aktivan.
     * Ako nije vec ucitan onda ucitava jezik.
     * Ako ne moze da se ucita vraca false.
     * Ako je uspesno izvrsena vraca true.
     *
     */
    public function setAktivanjezik($id_jezik)
    {
        if(! $this->isUcitanjezik($id_jezik))//ako nije ucitan
        {
            if(! $this->ucitajI18n($id_jezik)) // ako ne moze da se ucita
                    return false;             // vracam false
            else  //posto je ucitan postavljam aktivan na trazeni
            {
                $this->m_idAktivanjezik = $id_jezik;
                return true;  
            }
        }
        else //jeste ucitan
        {
            $this->m_idAktivanjezik = $id_jezik;
            return true;
        }
    }

    public function setAktivanOriginalnijezik()
    {
        assert(isset($this->i18n));
        $id_jezik = $this->id_jezik_originala;
        $postoji = $this->setAktivanjezik($id_jezik);
        assert($postoji);
    }

    public function __get($atribut)
    {
       $i18nKlasa = $this->getI18nKlasa();
        //ako atribut ne postoji u okviru i18nKlase onda se poziva __get iz bazne
        if( ! array_key_exists($atribut, $i18nKlasa::model()->getAttributes()))
                return parent::__get($atribut);
        $id = $this->getAktivanjezikId();
        if( ! $this->isUcitanjezik($id))
            $this->ucitajI18n($id);
        return $this->getI18n($id)->$atribut;
    }

    public function __set($atribut, $vrednost)
    {
        $i18nKlasa = $this->getI18nKlasa();
        if( ! array_key_exists($atribut, $i18nKlasa::model()->getAttributes()))
        {
                parent::__set($atribut, $vrednost);
        }
        else
        {
            $id = $this->getAktivanjezikId();
            if(! $this->isUcitanjezik($id))
                $this->ucitajI18n($id);
            assert(isset($this->i18n));
            assert(is_array($this->i18n));
            $this->getI18n($id)->$atribut = $vrednost;
            assert(isset($this->i18n[$id]));
        }
    }

    public function __isset($atribut)
    {
        $i18nKlasa = $this->getI18nKlasa();
        //ako je atribut ne postoji u okviru i18nKlase onda se poziva __isset iz bazne
        if(!array_key_exists($atribut, $i18nKlasa::model()->getAttributes()))
            return parent::__isset($atribut);
        $id = $this->getAktivanjezikId();
        if(! $this->isUcitanjezik($id))
                $this->ucitajI18n($id);
        return isset($this->getI18n($id)->$atribut);
    }

    //Ne ocekujem da cu uopste da koristim unset za clanove klase, ali  bolje je da
    //definisem funkciju, da ne bih dobio neko nedefinisano ponasanje.
    public function __unset($atribut)
    {
       //Ova funkcija ne sme da se poziva!!!!

       assert(false);

       //TODO ukoliko core poziva ovu funkciju i moram da uklonim assert(false)
       //onda moram i da prilagodim kod ispod koji je neispravan!!!
 /*      $i18nKlasa = $this->getI18nKlasa();
        //ako je atribut ne postoji u okviru i18nKlase onda se poziva __isset iz bazne
        if(!array_key_exists($atribut, $i18nKlasa::model()->getAttributes()))
             parent::__unset($atribut);
        else
        {
            $id = $this->getAktivanjezikId();
            if(! $id) //ako nije definisan aktivni jezik
            {
                $this->setAktivanOriginalnijezik(); // postavi originalni jezik kao aktivan
                $id = $this->getAktivanjezikId();
            }
             unset($this->i18n[$id]->$atribut);
        }
  */
    }

    public function save($runValidation=true,$attributes=null)
    {
        $validacija = $runValidation;
        if( ! parent::save($validacija))
            return false;           
        foreach($this->i18n as $i18n)
        {
            $atribut = $this->getIdFk();
            $i18n->$atribut = $this->id;
            if(!$i18n->save($validacija))
                return false;
        }
        return true;
    }
    
  
    public function saveUOkviruTransakcije($validacija = true)
    {
        $trans = Yii::app()->db->beginTransaction();
        try
        {
            if( ! $this->save($validacija))
                throw new Exception();
            $trans->commit();
        }
        catch(Exception $e)
        {
            $trans->rollBack();
            return false;
        }
        return true;
    }
}