<?php

class Helper {
    const ID_SRPSKI_JEZIK = 1;
    const ID_SRPSKI_LATINICA = 2;
    const ID_ENGLESKI_JEZIK = 3;

    const KOD_SRPSKI_JEZIK = 'sr_sr';
    const KOD_SRPSKI_LATINICA = 'sr_yu';
    const KOD_ENGLESKI_JEZIK = 'en';
    const KOD_SRPSKI_JEZIK_GOOGLE = 'sr';
    const KOD_ENGLESKI_JEZIK_GOOGLE = 'en';
    
    /*
     * Razbija tekst $plain na reči, koje zatim spaja operatorom $operator.
     * Ako tekst sadrži zvezdicu, ona se menja sa ':* ' Obratiti pažnju na spejs iza zvezdice,
     * koji za posledicu ima to da se u upitu zvezdica može naći samo sa desne strane reči. 
     */
    public static function plain2tsquery($plain, $operator = '&', $uvekZvezda=false)
    {
        $search = array('|', '&', '^', '!', '~');
        $plain = str_replace($search, '', $plain);

        if( ! $uvekZvezda)
            $plain = str_replace('*', ':* ', $plain);            
        $niz = explode(' ', $plain);
        $upit = '';
        foreach($niz as $rec)
        {
            if( trim($rec) == '' || $rec == ':*')
                continue;
            if($uvekZvezda)
                $rec .= ':* ';
            $upit .= $rec . $operator;
        }
        $upit = trim($upit, $operator);
        return $upit;
    } 
    
    /**
     *
     * @param <String> $route
     * @param <String> $jezik
     * @param array $params
     * @return <String>
     */
    public static function createI18nUrl($route, $jezik = null, array $params=array ( ), $ampersand='&')
    {
        $app = Yii::app();
        if($jezik === null)
            $jezik = $app->language;
        //dodat uslov da ne bi prikazivao jezik u url-u za izvorni jezik
        if($jezik !== $app->getPodrazumevaniJezik())
            $params['jezik'] = $jezik;
        else
            unset($params['jezik']);
        return Yii::app()->getUrlManager()->createUrl($route, $params, $ampersand);
    }
    
    public static function getAppjezikId()
    {
        $jezik = Yii::app()->language;
        
        switch($jezik)
        {
            case self::KOD_SRPSKI_JEZIK:
                return self::ID_SRPSKI_JEZIK;
            case self::KOD_SRPSKI_LATINICA:
                return self::ID_SRPSKI_LATINICA;
            case self::KOD_ENGLESKI_JEZIK:
                return self::ID_ENGLESKI_JEZIK;
            default :
                return ID_SRPSKI_JEZIK;
        }
    }

    public static function getAppjezikGoogle()
    {
        if(Yii::app()->language == Helper::KOD_SRPSKI_JEZIK)
                return Helper::KOD_SRPSKI_JEZIK_GOOGLE;
        return Helper::KOD_ENGLESKI_JEZIK_GOOGLE;
    }
    public static function prevediUGoogleKod($id_jezik)
    {
        if($id_jezik == self::ID_ENGLESKI_JEZIK)
            return self::KOD_ENGLESKI_JEZIK_GOOGLE;
        return self::KOD_SRPSKI_JEZIK_GOOGLE;
    }

    public static function jezikKod2Id($jezikKod)
    {
            switch($jezikKod)
            {
                case self::KOD_SRPSKI_JEZIK:
                    return self::ID_SRPSKI_JEZIK;
                case self::KOD_SRPSKI_LATINICA:
                    return self::ID_SRPSKI_LATINICA;
                case self::KOD_ENGLESKI_JEZIK:
                    return self::ID_ENGLESKI_JEZIK;               
            }        
            return false;
    }
    
    public static function jezikId2Kod($idJezik)
    {
            switch($idJezik)
            {
                case self::ID_SRPSKI_JEZIK:
                    return self::KOD_SRPSKI_JEZIK;
                case self::ID_SRPSKI_LATINICA:
                    return self::KOD_SRPSKI_LATINICA;
                case self::ID_ENGLESKI_JEZIK:
                    return self::KOD_ENGLESKI_JEZIK;               
            }        
            return false;        
    }
    
    /**
     *
     * @param <String> $path putanja do direktorijuma
     * @return <int> Velicina direktorijuma u KiB
     */
    public static function getVelicinaDirektorijuma($path)
    {
        $path = $_SERVER["DOCUMENT_ROOT"].$path;
        $total = self::getDirectorySize($path);
        return intval(round($total['size']/1024,1));
    }

    private static function getDirectorySize($path)
    {   
      $totalsize = 0;
      $totalcount = 0;
      $dircount = 0;
      if ($handle = opendir ($path))
      {
        while (false !== ($file = readdir($handle)))
        {
          $nextpath = $path . '/' . $file;
          if ($file != '.' && $file != '..' && !is_link ($nextpath))
          {
            if (is_dir ($nextpath))
            {
              $dircount++;
              $result = Helper::getDirectorySize($nextpath);
              $totalsize += $result['size'];
              $totalcount += $result['count'];
              $dircount += $result['dircount'];
            }
            elseif (is_file ($nextpath))
            {
              $totalsize += filesize ($nextpath);
              $totalcount++;
            }
          }
        }
      }
      closedir ($handle);
      $total['size'] = $totalsize;
      $total['count'] = $totalcount;
      $total['dircount'] = $dircount;
      //return intval(round($totalsize/1024,1)); //Pretvaram bajtove u KiB
      return $total;
    }

    public static function deleteDir($dir)
    {
       if (substr($dir, strlen($dir)-1, 1) != '/')
           $dir .= '/';
       if ($handle = opendir($dir))
       {
           while ($obj = readdir($handle))
           {
               if ($obj != '.' && $obj != '..')
               {
                   if (is_dir($dir.$obj))
                   {
                       if (!self::deleteDir($dir.$obj))
                           return false;
                   }
                   elseif (is_file($dir.$obj))
                   {
                       if (!unlink($dir.$obj))
                          return false;
                    }

                }
             }
            closedir($handle);
            if (!@rmdir($dir))
                return false;
            return true;
        }
        return false;
    }
    
    public static function kosaCrta($putanja)
    {
        return rtrim($putanja, '/\\') . '/';        
    }

    public static function cmp($str1, $str2)
    {
		if(Helper::getAppjezikId() == Helper::ID_SRPSKI_JEZIK)
                {
                    if ($str1 == $str2) 
                    return 0;                    

                    $cmp = "аaбbвvгgдdђđеeжžзzиiјjкkлlљмmнnњоoпpрrсsтtћćуuфfхhцcчčџшš"; // itd - string koji sadrzi poredjana cir. i lat. slova.

                    $len1 = mb_strlen($str1, 'utf8');
                    $len2 = mb_strlen($str2, 'utf8');
                    $len = ($len1 <= $len2) ? $len1 : $len2; // duzina petlje jednaka duzini kraceg stringa

                    for ($i = 0; $i < $len; $i++)
                    {                       
                            $chr1 = mb_strtolower(mb_substr($str1,$i,1, 'utf8'), 'utf8'); // uzimamo i-ti karakter stringa
                            $chr2 = mb_strtolower(mb_substr($str2,$i,1, 'utf8'), 'utf8');
                            $pos1 = mb_strpos($cmp, $chr1, 0, 'utf8');
                            $pos2 = mb_strpos($cmp, $chr2, 0, 'utf8');
                            if($pos1 > $pos2)
                                    return 1;
                            else if($pos1 < $pos2)
                                    return -1;
                    }
                    // ako su stringovi identicni do duzine kraceg, onda smo se nasli ovde
                    if ($len1 > $len2) 
                        return 1;
                    else if ($len2 > $len1)
                        return -1;
                    
                    // ako smo se nasli ovde, onda su identicni i jednakih duzina
                    // pa posto smo to proverili na pocetku, to znaci da nikako ne bi trebalo ovde da se nadjemo :)

                    return 0;
                }
                else
                {
                    $al = strtolower($str1);
                    $bl = strtolower($str2);
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? +1 : -1;
                }
    }

/*    private  static $cirilica = null;
    private static $latinica = null;
    private static function getCirilica()
    {
        if(!self::$cirilica)
            self::$cirilica =
        return self::$cirilica
    }*/
    public static function lat2cir($lat)
    {
        $latinica = array("DŽ", "Dž", "dŽ", "dž", "Lj", "lJ", "LJ", "lj",  "Nj", "nJ", "NJ", "nj", "e", "r", "t", "z", "u", "i", "o", "p", "š", "đ", "a", "s", "d", "f", "g", "h", "j", "k", "l", "č", "ć", "ž", "c", "v", "b", "n", "m", "E", "R", "T", "Z", "U", "I", "O", "P", "Š", "Ć", "A", "S", "D", "F", "G", "H", "J", "K", "L", "Č", "Ć", "Ž", "C", "V", "B", "N", "M");        
        $cirilica = array("Џ",  "Џ",  "Џ",  "џ",  "Љ",  "Љ",   "Љ", "љ",   "Њ",   "Њ", "Њ",  "њ",  "е", "р", "т", "з", "у", "и", "о", "п", "ш", "ђ", "а", "с", "д", "ф", "г", "х", "ј", "к", "л", "ч", "ћ", "ж", "ц", "в", "б", "н", "м", "Е", "Р", "Т", "З", "У", "И", "О", "П", "Ш", "Ђ", "А", "С", "Д", "Ф", "Г", "Х", "Ј", "К", "Л", "Ч", "Ћ", "Ж", "Ц", "В", "Б", "Н", "М");
        return str_replace($latinica, $cirilica, $lat);
    }

    private static function konvertuj($tekst, array & $slova)
    {
        if( ! $tekst)
            return '';
	$izlaz = '';
	$n = strlen($tekst);
	$otvorentag = false;
	$tag = '';
	$rec = '';
	for($i=0; $i<$n; $i++)
	{
		if($tekst[$i] == '<')
		{
			$otvorentag = true;
			$tag = '<';
			if($rec)
				$izlaz .= strtr($rec, $slova);
			$rec = '';
		}
		elseif($tekst[$i] == '>')
		{
			$otvorentag = false;
			$tag .= '>';
			$izlaz .= $tag;
		}
		elseif($otvorentag) 
		{
			$tag .= $tekst[$i];
		}
		else 
		{
			$rec .= $tekst[$i];
		}
	}
	if($tekst[$i-1] != '>') //ako se tekst ne zavrsava zatvarajucim tagom znaci da postoji rec koja nije dodata u izlaz
	{
		$izlaz .= strtr($rec, $slova);
	}	
	return $izlaz;        
    }
    
    /*
     * Preslovaljava cirilicu u latinicu i pritom sve sto se nalazi izmedju < i > ostavlja netaknuto
     */
    public static function lat2cirSacuvajtagove($tekst)
    {
        $slova = array( 'Lj'=>'Љ', 'LJ'=>'Љ', 'Nj'=>'Њ', 'NJ'=>'Њ', 'Dž'=>'Џ', 'DŽ'=>'Џ', 'lJ'=>'Љ', 'nJ'=>'Њ', 'dŽ'=>'Џ', 
            'A'=>'А', 'B'=>'Б', 'V'=>'В', 'G'=>'Г', 'D'=>'Д', 'Đ'=>'Ђ', 'E'=>'Е', 'Ž'=>'Ж', 'Z'=>'З', 'I'=>'И',
            'J'=>'Ј', 'K'=>'К', 'L'=>'Л', 'M'=>'М', 'N'=>'Н', 'O'=>'О', 'P'=>'П', 'R'=>'Р', 'S'=>'С', 'T'=>'Т',
            'Ć'=>'Ћ', 'U'=>'У', 'F'=>'Ф', 'H'=>'Х', 'C'=>'Ц', 'Č'=>'Ч', 'Š'=>'Ш', 'lj'=>'љ', 'nj'=>'њ', 'dž'=>'џ',
            'a'=>'а', 'b'=>'б', 'v'=>'в', 'g'=>'г', 'd'=>'д', 'đ'=>'ђ', 'e'=>'е', 'ž'=>'ж', 'z'=>'з', 'i'=>'и',
            'j'=>'ј', 'k'=>'к', 'l'=>'л', 'm'=>'м', 'n'=>'н', 'o'=>'о', 'p'=>'п', 'r'=>'р', 's'=>'с', 't'=>'т',
            'ć'=>'ћ', 'u'=>'у', 'f'=>'ф', 'h'=>'х', 'c'=>'ц', 'č'=>'ч', 'š'=>'ш',
        );


        return self::konvertuj($tekst, $slova);

    }


        /*
     * Preslovaljava cirilicu u latinicu i pritom sve sto se nalazi izmedju < i > ostavlja netaknuto
     */
    public static function lat2cirSacuvajtagoveStaro($tekst)
    {
        $slova = array("Lj" => "Љ", "LJ" => "Љ", "Nj" => "Њ","NJ" => "Њ" , "Dž" => "Џ", 'DŽ'=>'Џ', "B" => "Б", "lj" => "љ", "nj" => "њ", "dž" => "џ", "ć" => "ћ", "č" => "ч", "ž" => "ж", "V" => "В", "G" => "Г", "D" => "Д", "Đ" => "Ђ", "Ž" => "Ж", "Z" => "З", "I" => "И", "L" => "Л", "N" => "Н", "P" => "П", "R" => "Р", "S" => "С", "Ć" => "Ћ", "U" => "У", "F" => "Ф", "H" => "Х", "C" => "Ц", "Č" => "Ч", "Š" => "Ш", "b" => "б", "v" => "в", "g" => "г",
            "d" => "д", "đ" => "ђ", "z" => "з", "i" => "и", "k" => "к", "l" => "л", "m" => "м", "n" => "н", "p" => "п", "r" => "р", "s" => "с", "t" => "т", "u" => "у", "f" => "ф", "h" => "х", "c" => "ц", "š" => "ш");
	$izlaz = '';
	$n = strlen($tekst);
	$otvorentag = false;
	$tag = '';
	$rec = '';
	for($i=0; $i<$n; $i++)
	{
		if($tekst[$i] == '<')
		{
			$otvorentag = true;
			$tag = '<';
			if($rec)
				$izlaz .= strtr($rec, $slova);
			$rec = '';
		}
		elseif($tekst[$i] == '>')
		{
			$otvorentag = false;
			$tag .= '>';
			$izlaz .= $tag;
		}
		elseif($otvorentag)
		{
			$tag .= $tekst[$i];
		}
		else
		{
			$rec .= $tekst[$i];
		}
	}
	if($tekst[$i-1] != '>') //ako se tekst ne zavrsava zatvarajucim tagom znaci da postoji rec koja nije dodata u izlaz
	{
		$izlaz .= strtr($rec, $slova);
	}
	return $izlaz;
    }

    public static function cir2latSacuvajtagove($tekst)
    {
        $slova = array( 'Љ'=>'Lj', 'Њ'=>'Nj', 'Џ'=>'Dž',  
            'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Ђ'=>'Đ', 'Е'=>'E', 'Ж'=>'Ž', 'З'=>'Z', 'И'=>'I',
            'Ј'=>'J', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R', 'С'=>'S', 'Т'=>'T',
            'Ћ'=>'Ć', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц'=>'C', 'Ч'=>'Č', 'Ш'=>'Š', 'љ'=>'lj', 'њ'=>'nj', 'џ'=>'dž',
            'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'ђ'=>'đ', 'е'=>'e', 'ж'=>'ž', 'з'=>'z', 'и'=>'i',
            'ј'=>'j', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t',
            'ћ'=>'ć', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'c', 'ч'=>'č', 'ш'=>'š',
        );
        return self::konvertuj($tekst, $slova);        
    }

    public static function cir2lat($cir)
    {
        $cirilica = array("џ", "љ", "њ",  "е", "р", "т", "з", "у", "и", "о", "п", "ш", "ђ", "а", "с", "д", "ф", "г", "х", "ј", "к", "л", "ч", "ћ", "ж", "ц", "в", "б", "н", "м", "Џ", "Љ", "Њ",  "Е", "Р", "Т", "З", "У", "И", "О", "П", "Ш", "Ђ", "А", "С", "Д", "Ф", "Г", "Х", "Ј", "К", "Л", "Ч", "Ћ", "Ж", "Ц", "В", "Б", "Н", "М");
        $latinica = array("dž", "lj", "nj", "e", "r", "t", "z", "u", "i", "o", "p", "š", "đ", "a", "s", "d", "f", "g", "h", "j", "k", "l", "č", "ć", "ž", "c", "v", "b", "n", "m", "Dž", "Lj", "Nj", "E", "R", "T", "Z", "U", "I", "O", "P", "Š", "Đ", "A", "S", "D", "F", "G", "H", "J", "K", "L", "Č", "Ć", "Ž", "C", "V", "B", "N", "M");
        return str_replace($cirilica,$latinica, $cir);
    }
	
    public static function mailUtf8($to, $subject = '(No subject)', $message = '', $header = '')
    {
        $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
        return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
    }

    public static function mailHtml($to, $subject = '(No subject)', $message = '', $header = '')
    {
        $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";
        return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
    }
    
    public static function generisiKod($min, $max)
    {
        $kod = '';
        $n = rand($min, $max);
        $znakovi = '0123456789abvgdezijklmnoprstufhcqwxy';
        $brZnakova = strlen($znakovi)-1;
        for($i = 0; $i<$n; $i++)
            $kod .= $znakovi[rand(0, $brZnakova)];
        return $kod;
    }

    
    public static function getHash($lozinka)
    {
        return hash('sha256', $lozinka);
    }

    public static function procisti($html)
    {
            if(trim($html == ''))
            {
                
                return '';
            }
            $p = new CHtmlPurifier();
			$p->options = array('URI.AllowedSchemes'=>array(
				'http' => true,
				'https' => true,
				'EnableAttrID' => true,
			));
           return $p->purify($html);

//TODO : VAZNO - pre nego sto skinem komentare, moram da izmenim kod i da uzmem u obzir
//da funkcija vraca string, a ne dobija vise string po referenci!!!


            //na kraju html mora da se nadje div sa stilom clear:both
            //da bi bio pravilno isctan border kontejnera
            //Proveravam da li je poslednji div ima stil clear:both
 /*           $clearDiv = '<div style="clear:both;">';
            $pos = strrpos($html, $clearDiv);
            if($pos===false) //ako ne postoji takav div onda se dodaje
                $html .= '<div style="clear:both;"></div>';
            else //div je nadjen, proveriti da li je poslednji
            {
                //proveravam da li postoje html elementi izmedju
                //diva sa clear:both stilom i poslednjeg zatvarajuceg taga </div>
                $start = $pos+strlen($clearDiv)-1;
                $end = strrpos($html, '</div>');
                $sadrzaj = substr($html, $start+1, $end-$start);
                //element postoji ako postoji bar jedan zatvarajuci tag
                if(strpos($sadrzaj, '>') > 0)
                    $html .= '<div style="clear:both;"></div>'; //posto ima elemenata, dodajem novi clear both element
            }			
*/
    }

    public static function getSEOText($txt, $maxDuzina=100)
    {
        if(mb_strlen($txt, 'utf8') > $maxDuzina)
                $txt = mb_substr($txt, 0, $maxDuzina, 'utf8');
        $txt = self::cir2lat($txt);       
        $txt = mb_strtolower($txt, 'utf8');
        $trazi  = array('ć', 'č', 'š', 'ž', 'đ',  '_');
        $zameni = array('c', 'c', 's', 'z', 'dj', '-');
        $txt = str_replace($trazi, $zameni, $txt);
        $txt = preg_replace("/[^a-zA-Z0-9\-\+\s]/", '', $txt);
        $reci = explode(' ', $txt);
        $txt = '';
        foreach($reci as $rec)
        {
            if(trim($rec))
                $txt .= trim($rec).'-';
        }
        $txt = substr($txt, 0, strlen($txt)-1);
        return $txt;        
    }

    /**
     * @return <Int> Funkcija vraca celobrojnu vrednost identifikatora
     * StrId moze biti numerik ili string u obliku <tekst>_<numerik>
     * na primer id_9
     */
    public static function getIntId($strId)
    {
        if(is_numeric($strId))
            return  intval($strId);
          $tipar = explode('_', $strId);
          return intval($tipar[1]);
    }

    public static function brojStrane($ukupniBr, $brPoStrani)
    {
        $brStrane = intval($ukupniBr / $brPoStrani);
        if($ukupniBr % $brPoStrani == 0)
            $brStrane--;
        if($brStrane < 0)
            $brStrane = 0;
        return ++$brStrane; //Indeks strane pocinje od jedinice
    }

    public static function skratiTekst($str, $length=17, $breakWords = true, $append = '…') {
      $strLength = mb_strlen($str, 'utf8');

      if ($strLength <= $length)
         return $str;

      //ako je razlika manja od broja karaktera koje treba dodati onda se vraca originalni tekst
      if($strLength - $length < mb_strlen($append, 'utf8'))
              return $str;

      if ( ! $breakWords) {
           while ($length < $strLength AND preg_match('/^\pL$/', mb_substr($str, $length, 1, 'utf8'))) {
               $length++;
           }
      }

      return trim(mb_substr($str, 0, $length, 'utf8')) . $append;
    }

    public static function isPostojijezik(array & $ar)
    {
        if( ! $ar)
            return false;
        if($ar['id_jezik'])
            return true;
        return false;
    }

/*    public static function baseUrl($url=null) 
    {
        static $baseUrl;
        if (empty($baseUrl))
            $baseUrl=Yii::app()->request->hostInfo; //getBaseUrl();
        return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
    }*/
    
    public static function baseUrl($url=null) 
    {
        static $baseUrl;
        if ($baseUrl===null)
            $baseUrl=Yii::app()->getRequest()->getBaseUrl();
        return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
    }    

    public static function themeUrl($url)
    {
        return empty(Yii::app()->theme) ? self::baseUrl($url) : rtrim(Yii::app()->theme->baseUrl, '/') . '/'. ltrim($url, '/');
    }
    
    public static function basePath($path=null)
    {
        static $basePath;
        if( $basePath === null)
            $basePath = Yii::getPathOfAlias('webroot');
        return $path === null ? $basePath : $basePath. '/' .ltrim($path, '/');
    }

    public static function escapeStrNizZaIn($niz)
    {
        $ret = '';
        foreach($niz as $str)
        {
            $str = trim($str);
            if($str)
                $ret .= "'". pg_escape_string($str)."',";
        }
        if($ret)
            $ret = substr ($ret, 0, strlen ($ret)-1);
        return $ret;
    }

    public static function  getEkstenzija($naziv, array $dozvoljeno = null)
    {
        if( $dozvoljeno === null)
            $dozvoljeno = array('jpg', 'jpeg', 'png', 'gif');
        $info = pathinfo('/'.$naziv);
        if(empty($info['extension']))
            return false;            
        $ekstenzija = strtolower($info['extension']);
        if( array_search($ekstenzija, $dozvoljeno) === false)
                return false;
        return $ekstenzija;
    }

    /**
     * @param type $imeFajla
     * @return mixed
     * 
     * Za parametre imefajla001.jpg vraca array('brojac' => '001', 'prefix'=>'imefajla', 'ekstenzija'=>'jpg')
     */
    public static function prefixBrojacEkstenzija($imeFajla)
    {
            $index = strrpos($imeFajla, '.');
            $len = strlen($imeFajla);
            if($len === $index+1)
                $ekstenzija = '';
            else
                $ekstenzija = substr($imeFajla, $index+1, $len - ($index+1));           
            
            if( ! Helper::getEkstenzija($imeFajla, array($ekstenzija,)))
                    return false;
            $prefix_brojac = substr($imeFajla, 0, $len - strlen($ekstenzija) - 1); //izbacujem .extenzija iz naziva
            $i = strlen($prefix_brojac) - 1;
            for(; $i>=0; $i--)
            {
                if( !is_numeric($prefix_brojac[$i]))
                    break;
            }
            if($i === 0 && is_numeric($prefix_brojac[0]))
                $prefix = '';
            else
                $prefix = substr($prefix_brojac, 0, $i+1);   
            
            $brojac = str_replace($prefix, '', $prefix_brojac);
            return array('brojac'=>$brojac, 'prefix'=>$prefix, 'ekstenzija'=>$ekstenzija);
    }
    
    public static function getLogovaniClan()
    {
        $user = Yii::app()->user;
        if($user->isGuest)
            throw new CHttpException(400, Yii::t('biblioteka', 'Нисте пријављени на систем!'));
        $id_clan = $user->id;
        $clan = Clan::getclan($id_clan);
        if( ! $clan)
            throw new CHttpException(400, Yii::t('biblioteka', 'Члан не постоји!'));
        return $clan;
    }

    public static function criteriaDatum($criteria, $objekat, $atribut = 'datum')
    {
        if($objekat->$atribut)
        {
            $MDY = explode('.', trim(rtrim($objekat->$atribut, '.')));
            $datum = false;
            switch(count($MDY))
            {
                case 0:
                    break;
                case 1: //trebalo bi da se radi o godini
                    if(strlen($MDY[0]) != 4)
                        break;
                    $datum = strtotime("$MDY[0]-01-01");
                    $duzinaIntervala = '+1 years';
                    break;
                case 2:
                    $datum = strtotime("$MDY[1]-$MDY[0]-01");
                    $duzinaIntervala = '+1 month';
                    break;
                case 3:
                    $duzinaIntervala = '+1 day';
                    $datum = strtotime("$MDY[2]-$MDY[1]-$MDY[0]");
                    break;
            }
            if($datum !== false)
            {
                $kraj = strtotime($duzinaIntervala, $datum);
                $criteria->addBetweenCondition('t.'.$atribut, $datum, $kraj-1);//-1 da ne bi ukljucio 1. sledeceg meseca                
            }
            else
            {
                $objekat->$atribut = '##.##.####';
            }
        }
    }
    
    public static function escapeZaSqlLike($term)
    {
        return strtr($term, array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\'));
    }
    
    public static function rimskiBroj($integer, $upcase = true) 
    { 
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); 
        $return = ''; 
        while($integer > 0) 
        { 
            foreach($table as $rom=>$arb) 
            { 
                if($integer >= $arb) 
                { 
                    $integer -= $arb; 
                    $return .= $rom; 
                    break; 
                } 
            } 
        } 
        return $return; 
    }            
}