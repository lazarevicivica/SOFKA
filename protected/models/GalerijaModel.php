<?php
Yii::import('ext.phpThumb.PhpThumbFactory');
//Yii::import('ext.querypath.QueryPath');
$path = dirname(__FILE__).'/../extensions/querypath/QueryPath.php';
require_once($path);
class GalerijaModel extends OptimisticLockingActiveRecord
{        
        const DOLE = 1;
        const LEVO = 2;               
        private $direktorijum;
        
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'galerija';
	}

        public function relations()
	{
                $relacije = array(
                    'rSlike' => array(self::MANY_MANY,
                                            'Slika', 'galerija_slika(id_slika, id_galerija)',                                             ),
                );
                return array_merge(parent::relations(), $relacije);
	}
        
        public function getUrlslikaZaNaslovnu()
        {
            $sql = "
            SELECT url FROM
                slika s JOIN
                galerija_slika gs ON (s.id=gs.id_slika)
            WHERE gs.id_galerija=$this->id
            ORDER BY gs.redosled
            LIMIT 1
            ";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if($row)
                return Slika::getThumbPutanja($row);
            return '';
        }

        private function setDir()
        {
            $vreme = time();
            $godina = date('Y');
            $mesec = date('m');
            $korisnikId = Yii::app()->user->id;
            $this->direktorijum = "/images/objave/$godina/$mesec/$korisnikId";
        }

        /**
         * 
         *  @return string  Vrednost koju vraca je string oblika 
         * <div class="slika-na-serveru" id="fajl_{$slika->id}>
         *      <input type="hidden" id="  .../>
         *      .... svi atributi slike
         * </div>
         * 
         */
        public function getSlikeHTMLInputtagovi($jezik = Helper::ID_SRPSKI_JEZIK)
        {
            
            $cmd = Yii::app()->db->createCommand()
                    ->select('s.id, s.naziv, s.url, si.alt, si.tekst, si.title, gs.prikaz')
                    ->from('slika s')
                    ->join('i18n_slika si', 'si.id_slika=s.id')
                    ->join('galerija_slika gs', 'gs.id_slika=s.id')
                    ->where("gs.id_galerija=$this->id AND si.id_jezik=$jezik")
                    ->order('gs.redosled ASC');
            $slike = $cmd->queryAll();
            $ret = '';            
            $ikonica_status = CHtml::image(Helper::baseUrl('/images/sajt/stiklirano.png'), 'deo galerije', array('title'=>'Слика је део галерије.'));            
            foreach($slike as $slika)
            {
                $id = $slika['id'];
                $nazivZaPrikaz = CHtml::encode($slika['title'] ? $slika['title'] : $slika['naziv']);
                $klasaIskljuceno = $slika['prikaz'] ? '' : ' iskljuceno-iz-galerije';
                $ret .= "<tr class=\"slika-na-serveru\" id=\"fajl_$id\">";                
                    $ret .= "<td id=\"naziv_$id\" class=\"slika-naziv{$klasaIskljuceno}\" title=\"{$slika['naziv']}\"><div class=\"slika-naziv-div\">{$nazivZaPrikaz}</div></td>";
                    $ret .= "<td id=\"status_$id\" class=\"status status-staro\">$ikonica_status</td>";
                    $ret .= "<td id=\"strelice_$id\" class=\"strelice\"><div id=\"strelica-gore_$id\" class=\"strelica-gore\"></div><div id=\"strelica-dole_$id\" class=\"strelica-dole\"></div></td>";                    
                    $ret .= "<td id=\"komanda_$id\" class=\"slika-komanda\" title=\"Уклања слику из галерије\"></td>";
                    $ret .= '<td style="display:none;">';
                        $ret .= '<input type="hidden" id="alt_'. $id .'" name="alt" value="'.CHtml::encode($slika['alt']).'"/>';
                        $ret .= '<input type="hidden" id="title_'. $id .'" name="title" value="'.CHtml::encode($slika['title']).'"/>';
                        $ret .= '<div id="tekst_'. $id .'">'.$slika['tekst'].'</div>';
                        $ret .= '<input type="hidden" id="prikaz_'. $id .'" name="tekst" value="'.$slika['prikaz'].'"/>';
                        $ret .= '<input type="hidden" id="urlth_'. $id .'" name="urlth" value="'.Slika::getThumbPutanja($slika).'"/>';
                        $ret .= '<input type="hidden" id="url_'. $id .'" name="url" value="'.Slika::getPutanja($slika).'"/>';
                        $ret .= '<input type="hidden" id="rotacija_'. $id .'" name="rotacija" value="0"/>';
                    $ret .= '</td>';                    
                $ret .= '</tr>';                
            }
            return $ret;
        }

        public function direktorijum()
        {
            if( ! $this->direktorijum)
                $this->setDir();
            $putanja = Helper::basePath($this->direktorijum);
            if( ! file_exists($putanja))
                @mkdir($putanja, 0770, true);
            return rtrim($this->direktorijum, '/');
        }

        private function getIdSlike($element)
        {
            $txtKlase = $element->attr('class');
            $arKlase = explode(' ', $txtKlase);
            $idSlike = false;
            foreach($arKlase as $klasa)
            {
                $par = explode('_', $klasa);
                if(count($par) === 2 && $par[0] === 'slika')
                {
                    $idSlike = intval($par[1]);
                    return $idSlike;
                }
            }            
            return false;
        }

        private function getVisinISirina($element, &$sirina, &$visina)
        {
            $txtStilovi = $element->attr('style');    
            $arStilovi = explode(';', $txtStilovi);
            $sirina = false;
            $visina = false;            
            foreach($arStilovi as $stil)
            {
                $par = explode(':', $stil);
                if(trim($par[0]) === 'width')
                    $sirina = intval(str_replace('px', '', $par[1]));                
                elseif( trim($par[0]) === 'height')                
                    $visina = intval(str_replace('px', '', $par[1]));                
                if($visina && $sirina)
                    return;
            }
        }

        /**
         *
         * @param <type> $html Html objave. galerija izvlaci sve slike klase ".umetnuta-slika",
         * menja im velicinu na onu navedenu u stilu, pravi kopije slika i daje im odgovarajuce ime,
         * uklanja klasu .umetnuta-slika kako slika vise ne bi bila obradjivana
         * i na kraju upisuje putanje do kopija nazad u &$html.
         *
         */
        public function obradiUbaceneSlike($html)
        {
            $qp = qp(QueryPath::HTML_STUB);
            $qp->find('body')->append($html);
            $elementSlike = $qp->top()->find('.umetnuta-slika');
            foreach($elementSlike as $element)
            {
              //nadji sirinu i visinu.
                $sirina = false;
                $visina = false;
                $this->getVisinISirina($element, $sirina, $visina);
              //nadji idSlike. Id se nalazi u okviru css klase slika_<idSlike>.
                $idSlike = $this->getIdSlike($element);
                //ako nije nadjena slika onda trazim sledecu
                if( ! $idSlike)
                    continue;
                $slika = Slika::model()->findByPk($idSlike);
                if( ! $slika)
                    continue;
                $putanja = $slika->napraviKopiju($sirina, $visina);

                $element->removeClass('umetnuta-slika');
                if($putanja)
                    $element->attr('src', $putanja);                
            }
            return $qp->top()->find('body')->innerHtml();
        }

       /*
         *  $arSlike se dobija parsiranjem JSON stringa. Taj string generise javaskript koji je u sklopu interfejsa za plupload
         *
         */
        public function azurirajSlike( & $arSlike)
        {
            if( ! $arSlike)
                return null;
            foreach($arSlike as $arslika)
            {
                $idPlupload = $arslika['id'];
                $naziv = $arslika['naziv'];
                $status = $arslika['status'];
                $komanda = $arslika['komanda'];
                $rotacija = intval($arslika['rotacija']);
                if($komanda === 'brisanje')
                {
                    if($status === 'staro')
                    {
                        //ukloni vezu izmedju galerije i slike i ne brisi sliku jer
                        //moze da bude deo neke druge galerije ili da se pojavljuje na nekoj stranici.
                        //Ovakve slike treba da brise posebna komponenta, GarbageCollector, koji bi
                        //brisao sve slike koje se ne pojavljuju u bazi i to samo kada je sajt oflajn.
                        $id_slika = intval($idPlupload);
                        $vezagalerija_slika = GalerijaSlika::model()->find("id_slika=$id_slika AND id_galerija=$this->id");
                        if($vezagalerija_slika)
                            $vezagalerija_slika->delete();
                    }
                    else
                    {
                        assert($status === 'novo');
                        //slika se moze bezbedno izbrisati jer se ne nalazi u bazi i
                        //sigurno nije deo neke druge stranice ili galerije.
                        $ekstenzija = $this->getEkstenzija($naziv);
                        $putanja = rtrim(Slika::getUploadDir(), '/').'/'.$idPlupload.'.'.$ekstenzija;
                        @unlink($putanja);
                    }
                    continue;
                }
                elseif($komanda === 'dodavanje')
                {
                    assert($status === 'novo');
                    $slika = Slika::model()->napraviNovi(Helper::ID_SRPSKI_JEZIK);
                }
                else
                {
                    assert($status === 'staro' && $komanda === 'azuriranje');
                    $slika = Slika::model()->findByPk($idPlupload);
                }
                //naziv setujem samo prilikom dodavanja nove slike i vise ga ne menjam.
                $alt = empty($arslika['alt']) ? '' : $arslika['alt'];
                $slika->alt = $alt;// strip_tags($alt);
                $title = empty($arslika['title']) ? '' : $arslika['title'];
                $slika->title = $title; // strip_tag($title);
                $tekst = empty($arslika['tekst']) ? '' : $arslika['tekst'];
                $slika->tekst = Helper::procisti($tekst);
                if($komanda === 'dodavanje')
                {
                    $slika->naziv = strip_tags($naziv);//vise se ne menja. To je originalni naziv fajla.
                    $direktorijum = rtrim($this->direktorijum(), '/') . '/';
                    $ekstenzija = $this->getEkstenzija($slika->naziv);
                    $slika->url = $idPlupload; //samo privremeno da bih mogao da snimim;
                    $staroIme = rtrim(Slika::getUploadDir(), '/').'/'.$idPlupload.'.'.$ekstenzija;
                    $slika->save(); //da bi dobila id
                    $slika->url = $this->direktorijum . '/'. $slika->id.'.'.$ekstenzija;
                    $novoIme = Helper::basePath($slika->url);
                    @rename($staroIme, $novoIme);
                    $prviPrimerak = true;
                    $slika->resizeIThumb($rotacija, $prviPrimerak);
                    $slika->save();
                    $vezagalerija_slika = new GalerijaSlika();
                    $vezagalerija_slika->id_galerija = $this->id;
                    $vezagalerija_slika->id_slika = $slika->id;
                }
                else
                {
                     assert($komanda === 'azuriranje');
                     if($rotacija)
                        $slika->resizeIThumb($rotacija);
                     $slika->save();
                     $vezagalerija_slika = GalerijaSlika::model()->find("id_galerija=$this->id AND id_slika=$slika->id");
                 }
                 $vezagalerija_slika->prikaz = empty($arslika['prikaz']) ? 0 : $arslika['prikaz'];
                 $vezagalerija_slika->redosled = empty($arslika['redosled']) ? 0 : $arslika['redosled'];
                 $vezagalerija_slika->save();
            }
        }

        private function getEkstenzija($naziv)
        {
            $ekstenzija = Helper::getEkstenzija($naziv);
            if( ! $ekstenzija)
                throw new Exception('Послат је фајл неодговарајућег типа!');
            return $ekstenzija;
        }
}
?>