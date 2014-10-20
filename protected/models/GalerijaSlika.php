<?php

class GalerijaSlika extends CActiveRecord
{
   	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'galerija_slika';
	}
}
?>
