<?php

/**
 * Forma se koristi za pretragu OCR teksta knjige. Rezultat pretrage su pojedinacne stranice za dati idKnjiga,
 * a ne samo naslov knjige.  
 */

class PretragaStranicaForm extends CFormModel
{
    public $idKnjiga;
    public $ftsUpit;
    public $operator = 'ili';
    public $ajax = true;
    public $indeksPrveStranice;
    public $prikazCitanka;
    
    public function rules()
    {
        return array(
            array('idKnjiga','numerical', 'integerOnly'=>true),
            array('ftsUpit', 'length', 'max' => 250),
            array('operator', 'length', 'max' => 3),
            array('ajax','numerical', 'integerOnly'=>true)
       );
    }
    
    public function attributeLabels()
    {
        return array('ftsUpit'=>Yii::t('biblioteka','Речи за претрагу'), 
                     'operator' => Yii::t('biblioteka', 'Страница књиге садржи:')
        );
    }
    
    public function getUpit()
    {
        $operator = ' | ';
        if($this->operator == 'i')
            $operator = ' & ';
        
        return Helper::plain2tsquery($this->ftsUpit, $operator);
        
        /*$search = array('|', '&', '^', '!', '~');
        $upit = str_replace($search, '', $this->ftsUpit);
        $arUpit = explode(' ', $upit);
        $upit = '';
        foreach($arUpit as $rec)
            $upit .= $rec . $operator;
        return rtrim($upit, $operator);*/
    }
    
    public function trazi()
    {
//Ovo radi sporo jer se ts_headline poziva za veliki broj redova kada se ukljuci ORDER BY, pa se tek na kraju radi LIMIT i OFFSET!
        /*$sql ="SELECT 
                    broj, 
                    ts_rank(stranica_tsvector, q) AS rang,
                    ts_headline('sr_lat', tekst, q, 'MaxFragments=1, MaxWords=45') AS markirani_tekst
                 FROM 
                    stranica_tekst, to_tsquery('sr_lat', :q) q 
                 WHERE 
                    id_knjiga=:id_knjiga AND 
                    stranica_tsvector @@ q";*/
//Podupit resava problem. Ovo je MNOGO brze, za Naselja i poreklo stanovnistva (oko 650 stranica) za rec jagodina razlika je 20ms vs 800ms!!!        
$prviSelect = "SELECT 
                    broj, 
                    rang, 
                    ts_headline('sr_lat', tekst, q, 'MaxFragments=1, MaxWords=45') AS markirani_tekst 
                FROM";//(
$sql =              "SELECT
                        broj,
                        tekst,
                        q,
                        ts_rank(stranica_tsvector, q) AS rang
                     FROM
                        stranica_tekst, to_tsquery('sr_lat', :q) q
                     WHERE
                        id_knjiga=:id_knjiga AND
                        stranica_tsvector @@ q";//) AS __psdprovider_
        
            $dp = new PodupitSqlDataProvider($sql);
            $dp->prviSelect = $prviSelect;
            
            $dp->keyField = 'broj';
            
            $dp->params = array(':id_knjiga'=>intval($this->idKnjiga),':q' => $this->getUpit());
            
            $sort = new CSort();
            $sort->defaultOrder = array('rang' => 'rang DESC');
            $sort->attributes =   array('broj' => array(
                                                    'desc' => 'broj DESC',
                                                        'asc'=>'broj ASC',
                                                    'label' => Yii::t('biblioteka', 'Бр. стране')),  
                                        'rang' => array('asc'=>'rang ASC, broj ASC',
                                                        'desc' => 'rang DESC, broj ASC',
                                                        'label' => Yii::t('biblioteka', 'Релевантност')),
              
            );
            $dp->setSort($sort);            
            $cmd = Yii::app()->db->createCommand(
                "SELECT 
                    COUNT(*)
                 FROM 
                    stranica_tekst, to_tsquery('sr_lat', :q) q 
                 WHERE 
                    id_knjiga=:id_knjiga AND 
                    stranica_tsvector @@ q");            
            $cmd->bindValues($dp->params);
            $dp->setTotalItemCount($cmd->queryScalar());
            return $dp;        
    }
    
}

?>
