<?php

class AutomatskaObradaPortlet extends CWidget implements IPortlet
{
    public $title = 'Аутоматска обрада';
    
    public function vidljivo()
    {
        return true;
    }

    public function run()
    {
        $csrf = Yii::app()->request->csrfToken;
        $ajaxUrl = '/digital/ajaxAutomatskaObrada';
        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile(Helper::baseUrl('js/digital_automatska_obrada.js'));
        $cs->registerScript('auto_obrada_portlet_js', 
        "$(document).ready( function(){automatskaObrada('$ajaxUrl', '$csrf');});");
        $this->render('automatska_obrada_portlet');
    }
}
