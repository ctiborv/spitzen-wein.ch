<?php
function nahrad_desetinnou_carku_teckou($value)
{
	return str_replace(',', '.', $value);
}

function pridej_prefix_OL($value)
{
	return 'OL' . $value;
}

function zkonvertuj_dostupnost($value)
{
	$konverzni_tabulka = array
	(
		'0' => 'Vyprodáno',
		'1' => 'Skladem omezené množství',
		'2' => 'Skladem - expedice od 4 dnů'
	);

	$svalue = (string)$value;
	return array_key_exists($svalue, $konverzni_tabulka) ? $konverzni_tabulka[$svalue] : $svalue;
}
?>