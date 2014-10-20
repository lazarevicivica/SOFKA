<?php
Yii::import('zii.widgets.CWidget');

class IzdvojeneKnjigePortlet extends CWidget implements IPortlet
{

	public $title;

        public function vidljivo()
        {
            return true;
        }

        public function run()
	{
           if( ! $this->title)
               $this->title = Yii::t('biblioteka', 'Издвојено на захтев корисника');
           $this->render('izdvojene_knjige_portlet');
	}
}