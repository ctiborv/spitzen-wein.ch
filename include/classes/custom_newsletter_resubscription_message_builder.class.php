<?php
class Custom_Newsletter_Resubscription_Message_Builder extends Custom_Message_Builder
{
	protected function getConfigValue($key)
	{
		$conf = new Project_Config;
		if (isset($conf->newsletters, $conf->newsletters[$key])) return $conf->newsletters[$key];
		return isset($config->$key) ? $config->$key : NULL;
	}

	protected function getHtmlTitle(array $data)
	{
		return $this->getConfigValue('www') . ' - Newsletter service subscription';
	}

	protected function getHeaderTitle(array $data)
	{
		return 'Newsletter service subscription';
	}

	public function getBodyContent(array $data)
	{
		$client = isset($data['client']) ? $data['client'] : NULL;
		$protocol = $this->getConfigValue('protocol');
		$www = $this->getConfigValue('www');
		$urlBase = "$protocol://$www";

		$html = <<<EOT
	<tr style="background:#ffffff; height:auto;">
		<td></td>
		<td style="width:600;">
			<div style="font-size:12px; margin: 0 auto; width:600px; height:90px; text-align:center; float:left;">{$this->getResubscriptionHtml($urlBase, $client)}
			</div>
		</td>
		<td></td>
	</tr>

EOT;

		return $html;

	}

	protected function getResubscriptionHtml($urlBase, $client)
	{
		$nav = new Project_Navigator;
		$code = $client ? $client['Code'] : 'XXXXXXXXXXXXXXXX';
		$resubscriptionLink = $urlBase . $nav->get('newsletters_resubscription') . '?code=' . urlencode($code);

		return <<<EOT
<p>You can confirm your subscription for our newsletters service by clicking on <a href="$resubscriptionLink">this link</a>.</p>
EOT;
	}

}