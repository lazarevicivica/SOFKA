<?php
Yii::import('zii.widgets.CWidget');

class FBPortlet extends CWidget implements IPortlet
{

	public $title;

        public function vidljivo()
        {
            return true;
        }

        public function run()
	{
            $this->title = Yii::t('biblioteka', 'Фејсбук пријатељи');
            $this->render('fb_portlet');
	}
}