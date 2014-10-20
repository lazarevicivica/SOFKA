<?php

class AutomatskaObradaForm extends CFormModel
{
    public $invBr;
    
    public function rules()
    {
        return array(
          array('invBr', 'safe'),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'invBr'=>Yii::t('biblioteka', 'Инвентарни број'),			
        );
    }        
}