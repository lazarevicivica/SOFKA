<?php

class OdeljakObjava extends CActiveRecord
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
		return 'odeljak_objava';
	}
}
?>
