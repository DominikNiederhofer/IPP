<?php
// nastavi parametry
function Params(&$argcount, &$vstup, &$vystup, &$odstranepsilon, &$determinizace, &$malapismena, &$help) {
	$bout = false;
	$bin  = false;
	$longopts = array(
		"help",
		"input:",
		"output:",
		"no-epsilon-rules",
		"determinization",
		"case-insensitive"
	);
	$shortopts = implode("",array_merge(range('a', 'z'),range('A', 'Z'),range('0', '9')));
		
	$pocet = 1;
	foreach (getopt($shortopts, $longopts) as $opt => $value) {
		switch ($opt) {
			case "e":
			case "no-epsilon-rules":
				if ($determinizace)
					exit(1);
				
				if ($odstranepsilon == true)
					exit(1);
				else
					$odstranepsilon = true;
				break;

			case "d":
			case "determinization":
				if ($odstranepsilon)
					exit(1);
					
				if ($determinizace == true)
					exit(1);
				else
					$determinizace = true;
				break;

			case "i":
			case "case-insensitive":
				if ($malapismena == true)
					exit(1);
				else
					$malapismena = true;
				
				break;

			case "help":
				if ($argcount == 2)
					$help = true;
				else {
					exit(1);}
				break;

			case "output":
				if ($bout == true)
					exit(1);
				else
					$bout = true;
				
				$vystup = $value;
				break;

			case "input":
				if ($bin == true)
					exit(1);
				else
					$bin = true;

				$vstup = $value;
				break;

			default:
				exit(1);
		}
	}
}
//funkce overi zda se prvek $co nenachazi v poli $kam, pripadne jej prida na konec pole
function pridej(&$kam, &$co){

	if (empty($kam)){
		array_push($kam, $co);
	}
	elseif (!in_array($co, $kam))
		array_push($kam, $co);	
	
	#$vec = "";
}

//objekt pravidla
class Prav {
	var $zacatek;
	var $prechod;
	var $konec;
}
