<?php

class DbCitac implements Citac
{
    public function ucitajZaInvBr($invBr)
    {
        $invBr = intval($invBr);
        return Yii::app()->db->createCommand()->select('stranica')->from('original_html')->where("inv_br=$invBr")->queryScalar();
    }
}