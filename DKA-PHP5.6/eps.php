<?php
//pokud stav obsahuje koncovy stav, prida se do pole 
function pridejkonc(&$stavy, &$koncstavy, &$pole){
    $nasel = false;
    foreach($stavy as $a1){
    	if (in_array($a1, $koncstavy))
                $nasel = true;
    }
    if($nasel){
        pridej($pole, $stavy);
    }
}
//prevede pole do stringu (slouci 2 stavy)
function pole2Dtopole(&$pole){
    $pom = [];
    foreach($pole as $stav){
        sort($stav);
        $tmp = implode("_",$stav);
        array_push($pom, $tmp);
    }
    return $pom;
}
//determinizace
function determin(&$stavy, &$abeceda, &$pravidla, &$zacstav, &$koncstavy){
	
	noepsilon($stavy, $pravidla, $koncstavy);
	$pom = [[$zacstav]];
	$pra = [];
	$stavs = [];
	$cislo = 0;
	$rules = [];
	$final = [];
	do {
		$propom = $pom[$cislo];
		unset($pom[$cislo++]);
		pridej($pra, $propom);
		foreach ($abeceda as $pismeno) {
			$tmp = [];
			foreach ($propom as $pp) {
				foreach ($pravidla as $pravidlo) {
					if (!strcmp($pravidlo->zacatek, $pp) and !strcmp($pravidlo->prechod, $pismeno)){
						pridej($tmp, $pravidlo->konec);
					}
				}
			}
			
			if (!empty($tmp)){
				$pomoc = $propom;
				sort($pomoc);
				sort($tmp);
				$object = new Prav;
			    $object->zacatek = implode("_", $pomoc);
			    $object->prechod = $pismeno;
			    $object->konec = implode("_", $tmp);
			    pridej($rules, $object);
			}

			if (!empty($tmp) and !in_array($tmp, $pra))
				pridej($pom, $tmp);
			
		}
		pridejkonc($propom, $koncstavy, $final);
	} while (!empty($pom));

	$stavy = pole2Dtopole($pra);
	$pravidla = $rules;
	$koncstavy = pole2Dtopole($final);	
}
//odstrani epsilon pravidla
function noepsilon(&$stavy, &$pravidla, &$koncstavy){
	$uzaver = [];
	$pomuzaver = [];
	$pokracuj = true;
	
	foreach ($stavy as $stav) {
		$uzaver = [$stav];
		$pokracuj = true;
		while ($pokracuj) {
			$pomuzaver = $uzaver;
			foreach ($pomuzaver as $k) {			
				foreach ($pravidla as $pravidlo) {
					if (!strcmp($k, $pravidlo->zacatek) and !strcmp("", $pravidlo->prechod)){
						pridej($uzaver, $pravidlo->konec);
					}
				}
			}
			if ($pomuzaver === $uzaver)
				$pokracuj = false;
		}

		foreach ($uzaver as $u) {
			foreach ($pravidla as $prav) {
				if (!strcmp($u, $prav->zacatek) and strcmp("", $prav->prechod)) {
					$objekt = new Prav;
					$objekt->zacatek = $stav;
					$objekt->prechod = $prav->prechod;
					$objekt->konec = $prav->konec;
					pridej($pravidla, $objekt);
				}
			}
		}
		foreach ($koncstavy as $ks) {
			if (in_array($ks, $uzaver))
				pridej($koncstavy, $stav);
		}

	}

	foreach ($pravidla as $key) {
		if (!strcmp($key->prechod, "")){
			$index = array_search($key, $pravidla);
			unset($pravidla[$index]);
		}
	}
}

//vytiskne automat
function tisk(&$file, &$stavy, &$abeceda, &$pravidla, &$pocstav, &$koncstavy){
	$soubor = fopen($file, "w");
	if (!$soubor){
		exit(3);
	}
	fwrite($soubor, "(\n{");
	sort($stavy);
	if (!empty($stavy)){
		$last = array_pop($stavy);
		foreach ($stavy as $stav){
			
			fwrite($soubor, $stav.", ");	
		}
		fwrite($soubor, $last);
	}
	fwrite($soubor, "},\n{");
	sort($abeceda);
	if (!empty($abeceda)){
		$last = array_pop($abeceda);
		foreach ($abeceda as $stav) {
			
			fwrite($soubor, "'".$stav."'".", ");	
		}
		fwrite($soubor, "'".$last."'");
	}
	fwrite($soubor, "},\n{\n");
	sort($pravidla);
	if (!empty($pravidla)){
		$last = array_pop($pravidla);
		foreach ($pravidla as $stav){
			
			fwrite($soubor, $stav->zacatek." '".$stav->prechod."' -> ".$stav->konec.",\n");
		}
		fwrite($soubor, $last->zacatek." '".$last->prechod."' -> ".$last->konec."\n");
	}
	fwrite($soubor, "},\n".$pocstav.",\n{");

	sort($koncstavy);
	$last = array_pop($koncstavy);
	foreach ($koncstavy as $stav){
		
		fwrite($soubor, $stav.", ");	
	}
	fwrite($soubor, $last."}\n)\n");
	fclose($soubor);
}
