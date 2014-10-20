<?php

class I18nOdeljenje extends CActiveRecord
{
 	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'i18n_odeljenje';
	}
}