<?php

class DVCIcons
{
	public static function getIcon($value)
	{
		static $dvcIcons = array
		(
			'n/a' => 'dvacheck_na.gif',
			'none' => 'dvacheck_ok.gif',
			'small' => 'dvacheck_debt.gif',
			'relevant' => 'dvacheck_debt.gif',
			'severe' => 'dvacheck_debt.gif'
		);

		if (isset($dvcIcons[$value]))
		{
			$alt = 'dvacheck result: ' . htmlspecialchars($value);
			$dvcIcon = <<<EOT
<img src="/img/{$dvcIcons[$value]}" alt="$alt" />
EOT;
		}
		else
			$dvcIcon = '';

		return $dvcIcon;
	}
}