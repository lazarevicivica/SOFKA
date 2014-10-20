<?php
require_once(Yii::app()->extensionPath.'/querypath-3.0.2/src/qp.php');
\QueryPath\Options::merge(array('ignore_parser_warnings' => true));
class Sesija
{
	public $id;
	
	private $baza;
	private $odeljenje;
	
	public function __construct($baza='70804', $odeljenje='00')
	{
		$this->baza = $baza;
		$this->odeljenje = $odeljenje;
	}
	
	public function osvezi()
	{
		//otvaram sesiju na cobiss serveru 
		$url = "http://www.vbs.rs/scripts/cobiss?command=CONNECT&base={$this->baza}&dept={$this->odeljenje}";
		$html = file_get_contents($url);						
		if( empty($html))
			throw new Exception('Грешка при отварању сесије!');
		//citam id sesije
		$stranicaQp = @qp($html);
		$formUrl = $stranicaQp->find('div.advancesearchform')->find('form')->attr('action');
		$ar = explode('id=', trim($formUrl));
		$this->id = $ar[1];
		
		$url = "http://www.vbs.rs/scripts/cobiss?ukaz=SFRM&mode=3&id={$this->id}";
		file_get_contents($url);
	}
	
}
