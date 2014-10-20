<?php

class StranicaTekst extends CActiveRecord
{
    public function tableName() {return 'stranica_tekst';}
    
    public function rules()
    {
        return array(
            array('id_knjiga, broj', 'numerical', 'integerOnly'=>true),
            array('id_knjiga, broj', 'required'),
            array('tekst', 'safe')
        );
    }
    
    public function primaryKey()
    {
        return array('id_knjiga', 'broj');
    }
    
}

?>
