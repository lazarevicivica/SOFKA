<?php
class CirCaptchaAction extends CCaptchaAction
{


/*	public function run()
	{
		if(isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request for regenerating code
		{
			$code=$this->getVerifyCode(true);
			// we add a random 'v' parameter so that FireFox can refresh the image
			// when src attribute of image tag is changed
			echo $this->getController()->createUrl($this->getId(),array('v'=>rand(0,100000)));
		}
		else
		{
			$this->renderImage($this->getVerifyCode());
			Yii::app()->end();
		}
	}*/

	public function generateVerifyCode()
	{
		if($this->minLength<3)
			$this->minLength=3;
		if($this->maxLength>10)
			$this->maxLength=10;
		if($this->minLength>$this->maxLength)
			$this->maxLength=$this->minLength;
		$length=rand($this->minLength,$this->maxLength);
		$code='';
		$letters='BbCcDdFfGgHhJjKkLlMmNnPpRrSsTtVvZzTt';
                $duzina = mb_strlen($letters, 'utf8');
//		$letters='бцдфгхјклмнпљрствџхуз';
		$vowels='aeiou';
//		$vowels='аеиоу';
		for($i=0;$i<$length;++$i)
		{
			if($i%2 && rand(0,10)>2 || !($i%2) && rand(0,10)>9)
			{

//				$code.=$vowels[rand(0,4)];
				$code.=mb_substr($vowels,rand(0,4), 1,'utf8');
			}
			else
			{
//				$code.=$letters[rand(0,20)];
				$code.= mb_substr($letters,rand(0,$duzina-1), 1,'utf8');
			}
		}

		return $code;
	}
	/**
	 * Gets the verification code.
	 * @param string whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false)
	{
		$session=Yii::app()->session;
		$session->open();
		$name=$this->getSessionKey();
		if($session[$name]===null || $regenerate)
		{
			$session[$name]=$this->generateVerifyCode();
			$session[$name.'count']=1;
		}
		return $session[$name];
	}
	public function renderImage($code)
	{
		$this->fontFile = dirname(__FILE__).'/acoustic.ttf';
		//$box=
		imagettfbbox(30,0,$this->fontFile,$code);
		$w = 330;//$box[2] - $box[0] + 10;
		$h = 50;
		$im = imagecreatetruecolor($w,$h)
			  or die('Cannot Initialize new GD image stream');
		$pozadina = imagecolorallocate($im, 255, 255, 255);
                imagecolortransparent ( $im ,  $pozadina);
		imagefilledrectangle($im,0,0,$w,$h,$pozadina);
		//$text_color = imagecolorallocate($im, 108, 150, 6);
		$offset=2;
		$x=5;
                $svetlost = 150;
		for($i=0; $i<mb_strlen($code,'utf8');$i++)
		{
                        $text_color = imagecolorallocate($im, rand(0,$svetlost), rand(0,$svetlost), rand(0,$svetlost));
			$fontSize=(int)(rand(20,30));
			$angle=rand(0,10);
			$letter= mb_substr($code,$i, 1,'utf8');
			//$box=
			imagettftext($im,$fontSize,$angle,$x,rand(30,40),$text_color, $this->fontFile,$letter);
			$x+=22;//$box[2];
                        imagecolordeallocate($im, $text_color);
		}

//		imagettftext($im,30,0,10,30,$text_color,$this->fontFile,$code);
//		imagestring($im, 5, 5, 5,  $code, $text_color);
		//imagecolordeallocate($image,$text_color);
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
	}

	/*
	 * Validates the input to see if it matches the generated code.
	 * @param string user input
	 * @param boolean whether the comparison should be case-sensitive
	 * @return whether the input is valid
	 */
	public function validate($input,$caseSensitive)
	{                
		$code=$this->getVerifyCode();
                $inputCir = Helper::cir2lat($input);
                $validLat = $caseSensitive?($input===$code):!strcasecmp($input,$code);
                $validCir = $caseSensitive?($inputCir===$code):!strcasecmp($inputCir,$code);                
		$session=Yii::app()->session;
		$session->open();
		$name=$this->getSessionKey().'count';
		$session[$name]=$session[$name]+1;
		if($session[$name]>$this->testLimit && $this->testLimit>0)
			$this->getVerifyCode(true);
		return $validLat || $validCir;
	}
}

?>