<?php
//Widget koji prikazuje galeriju slika.
//Svaka slika moze da ima tekst.
//Ukoliko tekst koji odgovara slici nije preveden onda se prikazuje samo slika,
//ne koristi se Google Translation, orgininalni tekst ili nesto slicno!

class Galerija extends CWidget
{
    public $id_galerija;
    public $broj_kolona;
    public $pozicija; //pozicija za prikaz galerije u okviru stranice (gore, dole, levo, desno)
    public function run()
    {
        //id galerije mora da bude setovan pre poziva funkcije run()
        $id_galerija = intval($this->id_galerija);
        if(!$id_galerija)
                return;
        //parametri se ucitavaju iz baze ako nisu setovani pre poziva funkcije run()
        $this->ucitajParametre($id_galerija);
        //kada se komanda izvrsi dobija se recordset sa slikama url|visina|sirina|redosled|tekst
        $cmdSlike = $this->cmdSlike($id_galerija);

        $this->render('galerija', array('cmdSlike'=>$cmdSlike, 'broj_kolona'=>$this->broj_kolona ));
        //echo 'broj kolona je '. $this->broj_kolona;
    }

    private function cmdSlike($id_galerija)
    {
        $id_jezik = Helper::getAppjezikId();
        $db = Yii::app()->db;
        $cmd = $db->createCommand()
                ->select('s.url, gs.redosled, i18ns.tekst, i18ns.title, i18ns.alt')
                ->from('slika s')
                ->join('galerija_slika gs', 's.id = gs.id_slika')
                ->leftJoin('i18n_slika i18ns', 's.id = i18ns.id_slika AND i18ns.id_jezik='.$id_jezik)
                ->where("gs.id_galerija=$id_galerija AND gs.prikaz=1")
                ->order('gs.redosled');
        return $cmd;
    }

    private function ucitajParametre($id_galerija)
    {
        //ako nije predata widgetu pozicija i broj_kolona onda se ucitava iz baze
        if(!($this->broj_kolona && $this->pozicija)  )
        {
            $db = Yii::app()->db;
            $gal= $db->createCommand()
                    ->select('g.broj_kolona, g.pozicija')
                    ->from('galerija g')
                    ->where("g.id=$id_galerija")
                    ->queryRow();
            $this->broj_kolona = $gal['broj_kolona'];
            $this->pozicija = $gal['pozicija'];
        }
    }
}
?>
