<?php
Yii::import('zii.widgets.CWidget');

class StabloSlovaPortlet extends CWidget implements IPortlet
{

	public $title;
        public $id_zbirka=1;
        public function  __construct($owner = null)
        {
            parent::__construct($owner);
            $this->title = Yii::t('biblioteka', 'Годишта');
        }

        public function vidljivo()
        {
            return true;
        }

	public function run()
	{
            $otvorenaKategorija = Zbirkaslova::model()->find('id=:id', array(':id'=>$this->id_zbirka));
            if( ! $otvorenaKategorija)
                throw new CHttpException('400', Yii::t('biblioteka', 'Тражена збирка не постоји!'). ' id#'.$this->id_zbirka);
            $this->render('stablo_slova_portlet', array('otvorenaKategorija' => $otvorenaKategorija));
	}
}
