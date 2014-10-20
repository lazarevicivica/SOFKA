<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class ContactForm extends CFormModel
{
	public $ime;
	public $email;
	public $web;
	public $poruka;
	public $verifyCode;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		$pravila = array(
                        array('poruka, ime', 'safe'),//ovo bi moglo da pravi problem!
			array('email', 'email'),
                        array('web', 'url'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
		);
                
                //neregistrovani korisnik mora da unese sve podatke
                if(Yii::app()->user->isGuest)
                    $pravila[] = array('ime, email, poruka', 'required');
                else                
                    $pravila[] = array('poruka', 'required');
                return $pravila;                
	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
                    'verifyCode'=>Yii::t('biblioteka', 'Код'),			
                    'poruka'=>Yii::t('biblioteka', 'Порука'),
                    'email'=>Yii::t('biblioteka', 'Мејл'),
                    'web'=>Yii::t('biblioteka', 'Сајт'),
                    'ime'=>Yii::t('biblioteka', 'Име'),                                        
		);
	}
}