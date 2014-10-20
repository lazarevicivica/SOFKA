<?php
class VrstaGradje extends CI18nActiveRecord
{

    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }

    public function tableName()
    {
            return 'vrsta_gradje';
    }
}