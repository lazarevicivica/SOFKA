<?php

class ObjavaTag extends CActiveRecord
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
		return 'objava_tag';
	}
}
?>
