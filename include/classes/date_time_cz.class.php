<?
class Date_Time_CZ extends Date_Time
{
	protected static $months_cz = array
	(
		1 => array (0 => 'led', 1 => 'leden', 2 => 'ledna'),
		2 => array (0 => 'úno', 1 => 'únor', 2 => 'února'),
		3 => array (0 => 'bře', 1 => 'březen', 2 => 'března'),
		4 => array (0 => 'dub', 1 => 'duben', 2 => 'dubna'),
		5 => array (0 => 'kvě', 1 => 'květen', 2 => 'května'),
		6 => array (0 => 'čer', 1 => 'červen', 2 => 'června'),
		7 => array (0 => 'čvc', 1 => 'červenec', 2 => 'července'),
		8 => array (0 => 'srp', 1 => 'srpen', 2 => 'srpna'),
		9 => array (0 => 'zář', 1 => 'září', 2 => 'září'),
		10 => array (0 => 'říj', 1 => 'říjen', 2 => 'října'),
		11 => array (0 => 'lis', 1 => 'listopad', 2 => 'listopadu'),
		12 => array (0 => 'pro', 1 => 'prosinec', 2 => 'prosince')
	);

	public function __construct($data = null)
	{
		parent::__construct($data);
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'mes':
			case 'mon_cz':
				return self::$months_cz[parent::get('month')][0];
			case 'mesic':
			case 'month_cz':
			case 'month_cz1':
				return self::$months_cz[parent::get('month')][1];
			case 'mesice':
			case 'month_cz2':
				return self::$months_cz[parent::get('month')][2];
			default:
				return parent::get($name);
		}
	}
}