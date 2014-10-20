<?php
Yii::import('zii.widgets.CWidget');

class DigitalPortlet extends CWidget implements IPortlet
{

	public $title;
        public $prikaziNaslov = true;
        public function vidljivo()
        {
            return true;
        }
        
	public function run()
	{
            $this->title = Yii::t('biblioteka', 'Дигитална библиотека');
            $this->render('digital_portlet');
	}
}