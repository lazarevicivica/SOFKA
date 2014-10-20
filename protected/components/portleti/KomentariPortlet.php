<?php
Yii::import('zii.widgets.CWidget');

class KomentariPortlet extends CWidget implements IPortlet
{

	public $title;
        //public $odeljenja;
        
        public function vidljivo()
        {
            return true;
        }

        public function run()
	{
            if( ! $this->title)
                $this->title = Yii::t('biblioteka', 'Коментари');
/*            if( ! $this->odeljenja)
                $this->odeljenja = Odeljenje::getSveOrganizacioneJedinice();
 * */
            //$komentari =
            $this->render('komentari_portlet');
	}
}