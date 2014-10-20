<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of radno_mesto
 *
 * @author user
 */
class RadnoMesto extends CI18nActiveRecord
{
	public static function model($className=__CLASS__)
	{
            return parent::model($className);
	}

        public function tableName()
	{
            return 'radno_mesto';
	}

        /*public function relations()
	{
            $relacije = array();
            return array_merge(parent::relations(), $relacije);
	}*/
}
?>
