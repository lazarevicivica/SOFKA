<?php class ProjekatController extends OdeljenjeImplController
{
        protected function getOdeljenjaZaPortlet()
        {
            $projekat = Odeljenje::PROJEKAT;
            return Odeljenje::model()->findAll("id_vrsta_odeljenja=$projekat ORDER BY redosled DESC");                
        }
        
        protected function getNaslovZaPortlet()
        {
            return Yii::t('biblioteka', 'Пројекти');
        }    
}