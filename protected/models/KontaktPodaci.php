<?php
/**
 * 
 */
class KontaktPodaci extends CActiveRecord
{
    
    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }


    public function tableName()
    {
            return 'kontakt_podaci';
    }
    
    public static function getImeZaPrikaz(array & $data)
    {
        return CHtml::encode($data['ime']);
    }
    
    /**
     *
     * @param type $forma 
     */
    public function initIzForme($forma)
    {
        $this->ime = $forma->ime;
        $this->email = $forma->email;
        $this->web = $forma->web;        
    }

    public static function getWeb($data)
    {
        return $data['web'];
    }
}
