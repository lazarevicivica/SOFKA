<?php
class Podesavanja extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }
    
    public function rules()
    {
        return array(
                array('digital_direktorijum', 'safe'),
        );
    }
    
    public function tableName()
    {
            return 'podesavanja';
    }     
}
