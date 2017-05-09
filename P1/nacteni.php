<?php
//zkontroluje semanticke chyby
function kontrola(&$stavy, &$abeceda, &$pravidla, &$zacstav, &$koncstavy){

	foreach ($stavy as $stav) {
		$delka = mb_strlen($stav);
		if (!strcmp($stav[$delka - 1],"_"))
			exit(40);
	}
	foreach ($koncstavy as $key) {
		if (!in_array($key, $stavy))
			exit(41);
	}

	if (empty($abeceda))
		exit(41);

	if (!in_array($zacstav, $stavy))
			exit(41);

	foreach ($pravidla as $key) {
		if (!in_array($key->zacatek, $stavy))
			exit(41);

		if (!in_array($key->prechod, $abeceda) and strcmp($key->prechod, ""))
			exit(41);
		
		if (!in_array($key->konec, $stavy))
			exit(41);		
	}
}

//odebere 1. znak ze stringu
function dejznak(&$znak, &$string){
	$znak = mb_substr($string, 0, 1);
	$string = mb_substr($string, 1);
}
//odstrani komentare a prebytecne bile znaky
function pryc(&$text){
	$stav = 0;
	$ret = "";
	$sipka = false;


	while (mb_strlen($text)) {
		dejznak($znak, $text);

		switch ($stav) {
			case 0:
				if (!strcmp($znak, "'")) {
					$ret .= $znak;
					dejznak($znak, $text);
					if (!strcmp($znak, "'")) {
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
							dejznak($znak, $text);
							if (!strcmp($znak, "'")) {
								$ret .= $znak;
							}
						}
						else{
							$text = $znak.$text;
						}
					}
					else
					{
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
						}
						else
						{
							$text = $znak.$text;
						}
					}
				}

				elseif (!strcmp($znak, "#")){
					$stav = 1;
					$tmp = 0;						
					
				}
				elseif (ctype_space($znak)) {
					if ($sipka)
						exit(40);
		
				}
				elseif (ctype_alpha($znak)) {
					$ret .= $znak;
					$stav = 2;
				}
				else{
					if (!strcmp($znak, "-"))
						$sipka = true;
					else
						$sipka = false;

					$ret .= $znak;
					break;
				}
				$sipka = false;
				break;

			case 1:
				if (!strcmp($znak, "\n")){
					$stav = $tmp;
					break;
				}
				elseif (!strcmp($znak, "\r")) {
					$stav = $tmp;
					break;
				}
				break;
			
			case 2:
				if (!strcmp($znak, "'")) {
					$ret .= $znak;
					dejznak($znak, $text);
					if (!strcmp($znak, "'")) {
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
							dejznak($znak, $text);
							if (!strcmp($znak, "'")) {
								$ret .= $znak;
							}
						}
						else
							$text = $znak.$text;
					}
					else
					{
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
						}
						else
						{
							$text = $znak.$text;
						}
					}
				}

				elseif (!strcmp($znak, "#")) {
					$stav = 1;
					$tmp = 2;
				}
				elseif (ctype_alpha($znak)){
					$ret .= $znak;
				}
				elseif (ctype_space($znak)){
					if ($sipka)
						exit(40);
					$stav = 3;
				}
				else{
					if (!strcmp($znak, "-"))
						$sipka = true;
					else
						$sipka = false;

					$stav = 0;
					$ret .= $znak;
					break;
				}
				$sipka = false;
				break;

			case 3:
				if (!strcmp($znak, "'")) {
					$ret .= $znak;
					dejznak($znak, $text);
					if (!strcmp($znak, "'")) {
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
							dejznak($znak, $text);
							if (!strcmp($znak, "'")) {
								$ret .= $znak;
							}
						}
						else
							$text = $znak.$text;
					}
					else
					{
						$ret .= $znak;
						dejznak($znak, $text);
						if (!strcmp($znak, "'")) {
							$ret .= $znak;
						}
						else
						{
							$text = $znak.$text;
						}
					}
				}

				elseif (!strcmp($znak, "#")) {
					$stav = 1;
					$tmp = 3;
				}
				elseif (ctype_alpha($znak)){
					exit(40);
				}
				elseif (ctype_space($znak)) {
					if ($sipka)
						exit(40);
				}
				else {
					if (!strcmp($znak, "-"))
						$sipka = true;
					else
						$sipka = false;

					$stav = 0;
					$ret .= $znak;
					break;
				}
				$sipka = false;
				break;

			default:
				break;
		}
	}
	return $ret;
}
//rozdeli automat ze vstupu(string) do promennych
function automat(&$string, &$stavy, &$abeceda, &$pravidla, &$pocstav, &$koncstavy){
	$stav = 1;
	$nelze = false;
	$slovo = "";
	$znak = "";

	while (mb_strlen($string)){

		dejznak($znak, $string);
		#echo $znak;
		switch ($stav) {
			case 1:
				
				if (!strcmp($znak, "("))
					$stav = 2;
				else
					exit(40);
				break;
			
			case 2:
				if (!strcmp($znak, "{"))
					$stav = 3;
				else
					exit(40);
				break;

			case 3:
				if (ctype_alpha($znak)){
					$stav = 4;
					$slovo = $znak;
					$nelze = false;
				}
				elseif (!strcmp($znak, "}")) {
					if ($nelze)
						exit(41);
					$stav = 5;
				}
				else
					exit(40);
				break;

			case 4:
				if (!strcmp($znak, "_") or ctype_alnum($znak))
					$slovo .= $znak;

				elseif (!strcmp($znak, ",")) {
					pridej($stavy, $slovo);
					$nelze = true;
					$stav = 3;
				}
				elseif (!strcmp($znak, "}")) {
					pridej($stavy, $slovo);
					$stav = 5;
				}
				else 
					exit(40);
				break;

			case 5:
				if (!strcmp($znak, ","))
					$stav = 6;
				else
					exit(40);
				break;

			case 6:
				if (!strcmp($znak, "{"))
					$stav = 7;
				else
					exit(40);
				break;
				
			case 7:
				if (!strcmp($znak, "'")){
					$stav = 8;
					$nelze = false;
				}
				elseif (!strcmp($znak, "}")) {
					if ($nelze)
						exit(40);
					$stav = 13;
				}
				else
					exit(40);
				break;

			case 8:
				if (!strcmp($znak, "'")){
					//mam '' => ''''
					$stav = 11;
				}
				else {
					pridej($abeceda, $znak);
					$stav = 9;
				}				
				break;

			case 9:
				if (!strcmp($znak, "'"))
					$stav = 10;
				else
				{	
					exit(40);
				}
				break;

			case 10:
				if (!strcmp($znak, ",")){
					$nelze = true;
					$stav = 7;
				}

				elseif (!strcmp($znak, "}")) {
					$stav = 13;
				}
				else
					exit(40);
					
				break;

			case 11:
				if (!strcmp($znak, "'"))
					$stav = 12;
				else
					exit(40);
				break;

			case 12:
				if (!strcmp($znak, "'"))
				{
					$stav = 10;
					$slovo = "''";
					pridej($abeceda, $slovo);					
				}
				else
					exit(40);
				break;

			case 13:
				if (!strcmp($znak, ","))
					$stav = 14;
				elseif (!strcmp($znak, "}")) {
					$stav = 99;
				}
				else
					exit(40);
				break;

			case 99:
				if (!strcmp($znak, ","))
					$stav = 10;
				else
					exit(40);
				break;
			case 14:
				if (!strcmp($znak, "{"))
					$stav = 15;
				else
					exit(40);
				break;

			case 15:
				if (ctype_alpha($znak)){
					$stav = 16;
					$slovo = $znak;
					$nelze = false;
				}
				elseif (!strcmp($znak, "}")) {
					if ($nelze)
						exit(40);
					$stav = 25;
				}
				else
					exit(40);
				break;

			case 16:
				if (!strcmp($znak, "_") or ctype_alnum($znak))
					$slovo .= $znak;

				elseif (!strcmp($znak, "'")) {
					$pravidlo = new Prav;
					$stav = 17;
					$pravidlo->zacatek = $slovo;
				}
				else 
					exit(40);
				break;

			case 17:
				if (!strcmp($znak, "'")){
					$stav = 19;
				}
				else {
					$pravidlo->prechod = $znak;
					$stav = 18;
				}				
				break;

			case 18:
				if (!strcmp($znak, "'"))
					$stav = 21;
				else
					exit(40);
				break;

			case 19:
				if (!strcmp($znak, "'")){
					//mam ''' => ''''
					$stav = 20;
				}
				else {
					$pravidlo->prechod = "";
					$stav = 21;
					$string = $znak.$string;
				}				
				break;

			case 20:
				if (!strcmp($znak, "'")){
					//mam ''''
					$pravidlo->prechod = "''";  
					$stav = 21;
				}
				else 
					exit(40);			
				break;

			case 21:
				if (!strcmp($znak, "-")){
					$stav = 22;
				}
				else 
					exit(40);			
				break;

			case 22:
				if (!strcmp($znak, ">")){
					$stav = 23;
				}
				else 
					exit(40);			
				break;

			case 23:
				if (ctype_alpha($znak)){
					$stav = 24;
					$slovo = $znak;
				}
				else
					exit(40);
				break;		

			case 24:
				if (!strcmp($znak, "_") or ctype_alnum($znak))
					$slovo .= $znak;

				elseif (!strcmp($znak, ",")) {
					$stav = 15;
					$nelze = true;
					$pravidlo->konec = $slovo;
					pridej($pravidla, $pravidlo);
				}
				elseif (!strcmp($znak, "}")) {
					$stav = 25;
					$pravidlo->konec = $slovo;
					pridej($pravidla, $pravidlo);
				}
				else
					exit(40);
				break;

			case 25:
				if (!strcmp($znak, ",")){
					$stav = 26;
				}
				else 
					exit(40);			
				break;

			case 26:
				if (ctype_alpha($znak)){
					$stav = 27;
					$slovo = $znak;
				}
				else
					exit(40);
				break;	

			case 27:			
				if (!strcmp($znak, "_") or ctype_alnum($znak))
					$slovo .= $znak;

				elseif (!strcmp($znak, ",")) {
					$stav = 28;
					$pocstav = $slovo;
				}
				else
					exit(40);
				break;

			case 28:
				if (!strcmp($znak, "{"))
					$stav = 29;
				else
					exit(40);
				break;	

			case 29:
				if (ctype_alpha($znak)){
					$stav = 30;
					$slovo = $znak;
					$nelze = false;
				}
				elseif (!strcmp($znak, "}")) {
					if ($nelze)
						exit(40);
					$stav = 31;
				}
				else
					exit(40);
				break;

			case 30:
				if (!strcmp($znak, "_") or ctype_alnum($znak))
					$slovo .= $znak;

				elseif (!strcmp($znak, ",")) {
					pridej($koncstavy, $slovo);
					$nelze = true;
					$stav = 29;
				}
				elseif (!strcmp($znak, "}")) {
					pridej($koncstavy, $slovo);
					$stav = 31;
				}
				else 
					exit(40);
				break;

			case 31:
				if (!strcmp($znak, ")"))
					$stav = 32;
				else
					exit(40);
				break;

			case 32:
				exit(40);
		}
	}
}
