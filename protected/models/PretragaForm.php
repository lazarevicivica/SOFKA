<?php

/**
 * Forma se koristi za pretragu OCR teksta knjige. Rezultat pretrage su pojedinacne stranice za dati idKnjiga,
 * a ne samo naslov knjige.  
 */

class PretragaForm extends CFormModel
{
    public $ftsUpit;
    public $operator = 'i';
    
    public function rules()
    {
        return array(
            array('ftsUpit', 'length', 'max' => 250),
       );
    }
    
    public function attributeLabels()
    {
        return array('ftsUpit'=>Yii::t('biblioteka','Речи за претрагу'), 
                     'operator' => Yii::t('biblioteka', 'Објава садржи:')
        );
    }
    
    public function getUpit($uvekZvezda=false)
    {
        $operator = ' | ';
        if($this->operator == 'i')
            $operator = ' & ';
        
        return Helper::plain2tsquery($this->ftsUpit, $operator, $uvekZvezda);
    }
    
    public function trazi()
    {
//Podupit resava problem. Ovo je MNOGO brze, za Naselja i poreklo stanovnistva (oko 650 stranica) za rec jagodina razlika je 20ms vs 800ms!!!        
$jezik = Helper::getAppJezikId();
$objavljeno = Objava::OBJAVLJENO;
$prviSelect = "SELECT 
                    id,
                    br_komentara,
                    id_jezik_originala,
                    id_jezik,
                    rang, 
                    naslov,
                    datum,
                    tip,
                    url_slika,
                    puno_ime,
                    ts_headline('sr_lat', tekst, q, 'MaxFragments=1, MaxWords=45') AS uvod 
                FROM";//(
$sql =              "SELECT 
                        o.id,
                        o.br_komentara,
                        o.datum, 
                        o.tip,
                        o.url_slika,
                        o.id_jezik_originala,
                        c.puno_ime,
                        i18n.id_jezik,
                        i18n.naslov,
                        i18n.uvod,
                        i18n.tekst,
                        q,
                        ts_rank(tekst_vector, q) AS rang
                     FROM
                        to_tsquery('sr_lat', :q) q, 
                        i18n_objava i18n
                     JOIN objava o ON (o.id=i18n.id_objava)  
                     LEFT JOIN clan c ON (o.id_clan=c.id)
                     WHERE
                        o.status=$objavljeno AND
                        i18n.id_jezik=$jezik AND
                        tekst_vector @@ q";//) AS __psdprovider_
        
            $dp = new PodupitSqlDataProvider($sql);         
            $dp->prviSelect = $prviSelect;
            
            $dp->keyField = 'id';
            $uvekZvezda = true;
            $dp->params = array(':q' => $this->getUpit($uvekZvezda));
             
            $sort = new CSort();
            $sort->defaultOrder = array('rang' => 'rang DESC');
            $sort->attributes =   array(
                                        'rang' => array('asc'=>'rang ASC',
                                                        'desc' => 'rang DESC',
                                                        'label' => Yii::t('biblioteka', 'Релевантност')),
                                        'datum' => array(
                                                    'desc' => 'datum DESC',
                                                        'asc'=>'datum ASC',
                                                    'label' => Yii::t('biblioteka', 'Датум')),  
                                        'naslov' => array('asc'=>'naslov ASC',
                                                          'desc' => 'naslov DESC',
                                                          'label' => Yii::t('biblioteka', 'Наслов')),
                                        'autor' => array('asc'=>'puno_ime ASC',
                                                          'desc' => 'puno_ime DESC',
                                                          'label' => Yii::t('biblioteka', 'Аутор')),                
              
            );
            $dp->setSort($sort);            
            $cmd = Yii::app()->db->createCommand(
                "SELECT 
                    COUNT(*)
                 FROM 
                    to_tsquery('sr_lat', :q) q,
                    i18n_objava i18n
                    JOIN objava o ON (o.id=i18n.id_objava)
                 WHERE 
                    o.status=$objavljeno AND
                    i18n.id_jezik=$jezik AND 
                    i18n.tekst_vector @@ q");  
            $cmd->bindValues($dp->params);
            $dp->setTotalItemCount($cmd->queryScalar());
            return $dp;        
    }
    
}

?>