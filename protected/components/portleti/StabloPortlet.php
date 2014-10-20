<?php
Yii::import('zii.widgets.CWidget');

class StabloPortlet extends CWidget implements IPortlet
{

	public $title;
        public $id_zbirka=1;
        public function  __construct($owner = null)
        {
            parent::__construct($owner);
            $this->title = Yii::t('biblioteka', 'Збирке');
        }

        public function vidljivo()
        {
            return true;
        }

	public function run()
	{

            /*if( ! empty($_GET['id_zbirka']) )
            {
                $id = $_GET['id_zbirka'];
                $otvorenaKategorija = zbirka::model()->find('id=:id', array(':id'=>$id));
            }
            else
            {                
                $otvorenaKategorija = zbirka::root();
            }*/
            $otvorenaKategorija = Zbirka::model()->find('id=:id', array(':id'=>$this->id_zbirka));
            if( ! $otvorenaKategorija)
                throw new CHttpException('400', Yii::t('biblioteka', 'Тражена збирка не постоји!'). ' id#'.$this->id_zbirka);
            $this->render('stablo_portlet', array('otvorenaKategorija' => $otvorenaKategorija));
	}
}
