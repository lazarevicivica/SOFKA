<?php
/**
 * Klasa se koristi privremeno samo za uvoz vesti iz starog sajta koji je pravio Milos
 */
class StaraVestEn extends AltActiveRecord
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
		return 'vestieng';
	}
}
?>
