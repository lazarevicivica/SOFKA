<?php
class Ulogakomentar
{

    //najvise 32 pravila jer int ima 32 bita na vecini platforma

    const OBJAVI_CEKA_ODOBRENJE = 1;
    const OBJAVI_OTPAD = 2;

    const ODBACI_OBJAVLJENO = 4;
    const ODBACI_CEKA_ODOBRENJE = 8;

    const IZBRISI = 16;

    const STAVI_NA_CEKANJE_OBJAVLJENO = 32;
    const STAVI_NA_CEKANJE_OTPAD = 64;

    const OBJAVI_NOVI = 128;

    const IZMENI_CEKA_ODOBRENJE = 256;
    const IZMENI_OTPAD = 1024;
    const IZMENI_OBJAVLJENO = 2048;

    const IZMENI_PREVOD_NEOBJAVLJENO = 4096;
    const IZMENI_PREVOD_OBJAVLJENO = 8192;
    const IZMENI_PREVOD_OTPAD = 16384;
    const DODAJ_PREVOD = 32768;

//    const STAVI_NA_CEKANJE_NOVI to svi mogu


    private static $uloga = null;

    private $svoje = array();
    private $tudje = array();

    public function getDozvoleVlasnik($uloga)
    {
        return $this->svoje[$uloga];
    }

    public function getDozvoleNijeVlasnik($uloga)
    {
        return $this->tudje[$uloga];
    }

    public static function get()
    {
        if( ! self::$uloga)
            self::$uloga = new Ulogakomentar();
        return self::$uloga;
    }

    protected function  __construct()
    {
//administrator kontrolise sve
        $this->svoje[Uloga::ADMINISTRATOR] = Uloga::DOZVOLJENO_SVE;
        $this->tudje[Uloga::ADMINISTRATOR] = Uloga::DOZVOLJENO_SVE;

//urednik za svoje komentare sve sem fizickog brisanja, za tudje je zabranjena i izmena
        $pravaUrednika = ~self::IZBRISI & Uloga::DOZVOLJENO_SVE; //setujem bitove IZBRISI i PROMENA_AUTORA na 0
        $this->svoje[Uloga::UREDNIK] = $pravaUrednika;
        $this->tudje[Uloga::UREDNIK] = $pravaUrednika & ~self::IZMENI_CEKA_ODOBRENJE & ~self::IZMENI_OBJAVLJENO & self::IZMENI_OTPAD;

//pisac kontrolise sve sto je sam napisao, osim sto ne moze fizicki da izbrise
        $this->svoje[Uloga::PISAC] = $this->svoje[Uloga::UREDNIK];
        $this->tudje[Uloga::PISAC] = 0;

//saradnik moze da menja samo svoje stvari koje jos uvek nisu objavljene
        $this->svoje[Uloga::SARADNIK] =
                self::IZMENI_CEKA_ODOBRENJE |
                self::IZMENI_OTPAD |
                self::IZMENI_PREVOD_NEOBJAVLJENO |
                self::IZMENI_PREVOD_OTPAD |
                self::DODAJ_PREVOD;
        $this->tudje[Uloga::SARADNIK] = 0;

//prevodilac moze da prevede bilo sta sto su drugi napisali
        $this->tudje[Uloga::PREVODILAC] =
                self::IZMENI_PREVOD_NEOBJAVLJENO |
                self::IZMENI_PREVOD_OBJAVLJENO |
                self::IZMENI_PREVOD_OTPAD |
                self::DODAJ_PREVOD;
        $this->svoje[Uloga::PREVODILAC] = $this->tudje[Uloga::PREVODILAC];
    }
}
