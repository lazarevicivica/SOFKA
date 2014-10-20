<?php
class Uloga
{
    const ADMINISTRATOR=1;
    const UREDNIK=2;
    const PISAC=3;
    const SARADNIK=4;
    const PREVODILAC=5;

    const DOZVOLJENO_SVE = 0xFFFFFFFF;
    //najvise 32 pravila jer int ima 32 bita na vecini platforma
/* 1*/const IZMENI_PREVOD_NEOBJAVLJENO = 1;
/* 2*/const IZMENI_PREVOD_OBJAVLJENO = 2;
/*13*/const IZMENI_PREVOD_OTPAD = 4096;
/* 3*/const DODAJ_PREVOD = 4;

/* 4*/const IZMENI_CEKA_ODOBRENJE = 8;
/* 5*/const IZMENI_OTPAD = 16;
/* 6*/const IZMENI_OBJAVLJENO = 32;
/*25*/const IZMENI_NOVI = 16777216;

/* 8*/const ODBACI_OBJAVLJENO = 128; //Odbaci znaci da objavu stavlja u korpu za otpatke, ona postaje nevdljiva u svim odeljcima
/* 9*/const ODBACI_CEKA_ODOBRENJE = 256;

/*10*/const IZBRISI = 512; //fizicko brisanje

/*11*/const OBJAVI_CEKA_ODOBRENJE = 1024; //kada se nesto OBJAVI automatski postaje vidljivo u svim odeljcima kojima je PRIKLJUCENO
/*12*/const OBJAVI_OTPAD = 2048;
/* 7*/const OBJAVI_NOVI = 64;

/*14*/const STAVI_NA_CEKANJE_OBJAVLJENO = 8192;
/*15*/const STAVI_NA_CEKANJE_OTPAD = 16384;
/*22*/const STAVI_NA_CEKANJE_NOVI = 2097152;

/*16*/const PRIKLJUCI_OBJAVLJENO = 32768; //ne menja status objave, samo je prikljucuje odeljku
/*17*/const PRIKLJUCI_CEKA_ODOBRENJE = 65536;
/*18*/const PRIKLJUCI_OTPAD = 131072;
    
/*19*/const ISKLJUCI_OBJAVLJENO = 262144;//ne menja status objave, iskljucuje je tako da se vise ne prikazuje u okviru odeljka
/*20*/const ISKLJUCI_CEKA_ODOBRENJE = 524288;
/*21*/const ISKLJUCI_OTPAD = 1048576;

/*23*/const OTKLJUCAJ_KOMENTARE = 4194304;
/*24*/const ZAKLJUCAJ_KOMENTARE = 8388608;

/*26*/const PROMENA_AUTORA = 33554432;

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
            self::$uloga = new Uloga();
        return self::$uloga;
    }

    protected function  __construct()
    {        
//administrator kontrolise sve
        $this->svoje[self::ADMINISTRATOR] = self::DOZVOLJENO_SVE;
        $this->tudje[self::ADMINISTRATOR] = self::DOZVOLJENO_SVE;

//urednik sve sem fizickog brisanja
        $pravaUrednika = ~self::IZBRISI & self::DOZVOLJENO_SVE & ~self::PROMENA_AUTORA; //setujem bitove IZBRISI i PROMENA_AUTORA na 0
        $this->svoje[self::UREDNIK] = $pravaUrednika; 
        $this->tudje[self::UREDNIK] = $pravaUrednika;

//pisac kontrolise sve sto je sam napisao, osim sto ne moze fizicki da izbrise
        $this->svoje[self::PISAC] = $this->svoje[self::UREDNIK];
        $this->tudje[self::PISAC] = 0;

//saradnik moze da menja samo svoje stvari koje jos uvek nisu objavljene
        $this->svoje[self::SARADNIK] =
                self::IZMENI_CEKA_ODOBRENJE |
                self::IZMENI_OTPAD |
                self::IZMENI_PREVOD_NEOBJAVLJENO |
                self::IZMENI_PREVOD_OTPAD |
                self::IZMENI_NOVI |
                self::DODAJ_PREVOD |
                self::STAVI_NA_CEKANJE_NOVI |
                self::PRIKLJUCI_CEKA_ODOBRENJE |
                self::ISKLJUCI_CEKA_ODOBRENJE;
        $this->tudje[self::SARADNIK] = 0;

//prevodilac moze da prevede bilo sta sto su drugi napisali
        $this->tudje[self::PREVODILAC] =
                self::IZMENI_PREVOD_NEOBJAVLJENO |
                self::IZMENI_PREVOD_OBJAVLJENO |
                self::IZMENI_PREVOD_OTPAD |
                self::DODAJ_PREVOD;
        $this->svoje[self::PREVODILAC] = $this->tudje[self::PREVODILAC];
    }

}
