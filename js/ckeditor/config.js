/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/*CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
};*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#e8cba9';
        //config.plugins = 'basicstyles,button,htmldataprocessor,toolbar,wysiwygarea';
        //config.extraPlugins = 'htmldataprocessor';
	config.disableNativeSpellChecker = true;
        config.entities = false; //ovo je bitno,

	config.removePlugins = 'scayt';
	config.resize_maxWidth = '665';
        config.resize_minWidth = '665'
        config.height = '400';
        config.width = '665';

};