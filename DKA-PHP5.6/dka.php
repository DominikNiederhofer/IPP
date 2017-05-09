
<?php
/**************************************/
include('nacteni.php');
include('funkce.php');
include('eps.php');
mb_internal_encoding("UTF-8");

$vstups = "php://stdin";
$vystups = "php://stdout";
$odstranepsilon = false;
$determinizace = false;
$malapismena = false;
$help = false;

//////////////////
$Stavy = [];
$Abeceda = [];
$Pravidla = [];
$Pocstav = "";
$Koncstavy = [];


//zpracuju parametry
Params($argc, $vstups, $vystups, $odstranepsilon, $determinizace, $malapismena, $help);
// vytiskne napovedu
if ($help){
    echo "napoveda:\n".
         "mozne parametry:\n".
            "--input=filename filename je soubor ze ktereho bude nacten automat\n".
            "--output=filename filename je soubor do ktereho bude ulozen vysledek\n".
            "-e, --no-epsilon-rules pro pouhé odstranění ε-pravidel vstupního konečného automatu\n".
            "-d, --determinization provede determinizaci bez generování nedostupných stavů \n".
            "-i, --case-insensitive nebude brán ohled na velikost znaků\n";

    exit(0);
}

//otevru soubor pro cteni a nactu ho do stringu
$fp = fopen($vstups, 'r');
if (!$fp) {
    echo "Could not open file $vstups";
    exit(2);
}
$file = "";
while (false !== ($char = fgetc($fp))) {
    $file .= $char;
}

//odstranim komentare a prebytecne bile znaky
$file = pryc($file);
//pokud parametr (-i --case-sensitive) prevedu vstupni string na mala pismena
if ($malapismena)
		$file = mb_strtolower($file);
//nactu automat ze vstupu do globalnich promennych 
automat($file, $Stavy, $Abeceda, $Pravidla, $Pocstav, $Koncstavy);
//zkontoluju semanticke chyby
kontrola($Stavy, $Abeceda, $Pravidla, $Pocstav, $Koncstavy);

//pokud parametr (-e --no-epsilon-rules) odstrani epsilon prechody
if ($odstranepsilon)
	noepsilon($Stavy, $Pravidla, $Koncstavy);
//pokud parametr (-d --determinization) provede determinizaci automatu
if ($determinizace)
	determin($Stavy, $Abeceda, $Pravidla, $Pocstav, $Koncstavy);
// vytisknu automat do vystupniho souboru
tisk($vystups, $Stavy, $Abeceda, $Pravidla, $Pocstav, $Koncstavy);
?>