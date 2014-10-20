<?php
Yii::import('zii.widgets.CWidget');

class DigitalneBibliotekePortlet extends CWidget implements IPortlet
{

	public $title;

        public function vidljivo()
        {
            return true;
        }

        public function run()
	{
            $this->title = Yii::t('biblioteka', 'Дигиталне библиотеке у Србији');
            $this->render('digitalne_biblioteke_portlet');
	}
}