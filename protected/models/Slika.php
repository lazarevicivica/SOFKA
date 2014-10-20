<?php

class Slika extends CI18nActiveRecord
{
    const ROOT_FOLDER = '/images/';
    const MAX_SIRINA = 1024;
    const MAX_VISINA = 768;

    const THUMB_SIRINA = 185;//151;
    const THUMB_VISINA = 139;//113;
    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }

    public function tableName()
    {
            return 'slika';
    }

    public static function getUploadDir()
    {
        return dirname(__FILE__) . '/../../images/uploads';
    }
  //proveri da li postoji orgignalni fajl
  //ako ne postoji napravi ga kopiranjem postojece slike za prikaz
    private function originalniFajl()
    {
        $fajlZaPrikaz = $this->getAbsPutanja();        
        $info = pathinfo($fajlZaPrikaz);
        $dir = $info['dirname'];
        $fajl = $info['basename'];
        $originalni = "$dir/original$fajl";
        if( ! file_exists($originalni))
            copy($fajlZaPrikaz, $originalni);
        return $originalni;
    }

    //ova funkcija izracunava za koliko stepeni treba rotirati originalnu sliku
    private function setRotacija($novaRotacija)
    {
        $this->rotacija = ((($this->rotacija + $novaRotacija) / 90) % 4) * 90;
    }
    /**
     *      Menja velicinu slici tako sto je uklapa u pravougaonik max_sirina, max_visina
     *      i kreira thumbnail za sliku. Thumbnail snima u isti folder u kome se nalazi slika
     *      samo mu dodaje th ispred imena.
     */
    public function resizeIThumb($rotacija=0, $prviPrimerak = false)
    {
        $rotate = false;
        if($rotacija)
        {
            $this->setRotacija($rotacija);
            if($prviPrimerak)//za novi fajl se ne prave kopija originala, vec se rotira prvi primerak
                $originalniFajl = $this->getAbsPutanja();
            else
                $originalniFajl = $this->originalniFajl();

            @$rotate = PhpThumbFactory::create($originalniFajl, array('jpegQuality' => 100));
            //rotiraj sliku ako originalna nije vracena na pocetak            
            if($this->rotacija !== 0)
            {
                if($this->rotacija === 180)
                    @$rotate->rotateImage('CCW')->rotateImage('CCW');
                else
                    @$rotate->rotateImageNDegrees($this->rotacija);
            }
            //sacuvaj kao fajl za prikaz
            @$rotate->save($this->getAbsPutanja());
        }

        if($rotate)
        {
            $resize = $rotate;
            $resize->setOptions(array('jpegQuality' => 90));
        }
        else
            $resize = PhpThumbFactory::create($this->getAbsPutanja(), array('jpegQuality' => 90));
        @$resize->resize(Slika::MAX_SIRINA, Slika::MAX_VISINA);
        @$resize->save($this->getAbsPutanja());
        $thumb = $resize;//PhpThumbFactory::create($this->getAbsPutanja(), array('jpegQuality' => 95));      
        $thumb->setOptions(array('jpegQuality' => 95));     
        @$thumb->adaptiveResize(Slika::THUMB_SIRINA, Slika::THUMB_VISINA);     
        @$thumb->save($this->getThAbsPutanja());       //pravi problem todo
    }

    public function napraviKopiju($sirina, $visina)
    {
        $putanja = $this->getAbsPutanja();
        if($sirina && $visina)
        {
            $nazivKopije = "{$sirina}x{$visina}_r{$this->rotacija}_".basename($putanja);
            $putanjaKopije = dirname($putanja) . '/' . $nazivKopije;
            @$kopija = PhpThumbFactory::create($putanja, array('jpegQuality' => 90));
            @$kopija->adaptiveResize($sirina, $visina);
            @$kopija->save($putanjaKopije);
        }
        else//Nisu navedeni i sirina i visina, kopira fajl pod novim imenom da naknadna rotacija ne bi uticala na trenutno prikazani fajl
        {
            $nazivKopije = "r{$this->rotacija}_".basename($putanja);
            $putanjaKopije = dirname($putanja) . '/' . $nazivKopije;
            copy($putanja, $putanjaKopije);
        }
        return dirname($this->getUrl()).'/'.$nazivKopije;
    }

    public static function getThumbPutanja(array & $ar)
    {
        $putanja = $ar['url'];
        if( ! $putanja)
            return '';
        //return Helper::baseUrl(dirname($putanja).'/'.'th'.basename($putanja));
        return dirname($putanja).'/'.'th'.basename($putanja);
    }

    public function getThumbUrl()
    {
        $ar = array('url'=>$this->url);
        return self::getThumbPutanja($ar);
    }

    public static function getPutanja(array & $ar)
    {
        return $ar['url'];
    }

    public function getUrl()
    {
        return $this->url;
    }


    public function getAbsPutanja()
    {
        return Helper::basePath($this->url);
    }

    public function getThAbsPutanja()
    {
       $ar = array('url'=>Helper::basePath($this->url));
       return self::getThumbPutanja($ar);
    }
}

?>
