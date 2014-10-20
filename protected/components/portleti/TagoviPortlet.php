<?php
Yii::import('zii.widgets.CWidget');

class TagoviPortlet extends CWidget implements IPortlet
{

	public $title;
        public $id_odeljak;
        public $url = null;
        public $zbirka = null;
        public $minBrtagova = 2;
        public $limit = 20;
        private $tagovi = array();        

        private function inittagovi()
        {
            if( ! $this->tagovi)
                $this->tagovi = Odeljak::gettagovi($this->id_odeljak, $this->limit, $this->zbirka);
        }

        public function vidljivo()
        {
            $this->inittagovi();
            if( count($this->tagovi) < $this->minBrtagova)
                return false;
            return true;
        }

        public function run()
	{
            $zbirka = '';
            $jezik = Helper::getAppjezikId();
            if( ! empty($this->zbirka))
                $zbirka = "z={$this->zbirka->id}";
            $kesId = 'tagovi_portlet' . "_o={$this->id_odeljak}j=$jezik" . $zbirka;//odeljak, zbirka, limit
            if( $this->beginCache($kesId/*, array('duration'=>3600)*/))
            {
                $nazivOdeljka = Odeljak::getNaziv($this->id_odeljak, Helper::getAppjezikId());
                $this->title = Yii::t('biblioteka', 'Кључне речи - {odeljak}', array('{odeljak}' => CHtml::encode($nazivOdeljka)));
                if($this->id_odeljak === Odeljak::ID_DIGITALNA_BIBLIOTEKA)
                    $this->title = Yii::t('biblioteka', 'Кључне речи');
                if( ! empty($this->url))
                    $url = $this->url;
                else 
                    $url = Yii::app()->request->pathInfo;
                $this->render('tagovi_portlet', array('osnovniUrl'=>$url,'tagovi'=>$this->tagovi, 'id_odeljak' => $this->id_odeljak, 'nazivOdeljka'=>$nazivOdeljka));
                $this->endCache();
            }
	}
}