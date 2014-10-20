<?php
class LangBox extends CWidget
{
    public function run()
    {
        $currentLang = Yii::app()->language;
        $this->render('lang_box', array('currentLang' => $currentLang));
    }
}
