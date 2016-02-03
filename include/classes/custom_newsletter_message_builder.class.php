<?php
class Custom_Newsletter_Message_Builder extends Custom_Message_Builder
{
	private $_catalog;

	public function __construct($catalog = 'katalog')
	{
		$this->_catalog = $catalog;
		parent::__construct();
	}

	protected function getConfigValue($key)
	{
		$conf = new Project_Config;
		if (isset($conf->newsletters, $conf->newsletters[$key])) return $conf->newsletters[$key];
		return isset($config->$key) ? $config->$key : NULL;
	}

	protected function getHtmlTitle(array $data)
	{
		return $this->getConfigValue('www') . ' - Newsletter';
	}

	protected function getHeaderTitle(array $data)
	{
		return 'Newsletter';
	}

	protected function getBodyContent(array $data)
	{
		$pictures_config = $this->getConfigValue('pictures');
		$nav = new Project_Navigator;

		$client = isset($data['client']) ? $data['client'] : NULL;
		$protocol = $this->getConfigValue('protocol');
		$www = $this->getConfigValue('www');
		$urlBase = "$protocol://$www";

		$newsletter = $data['newsletter'];
		$content = $newsletter['Content'];

		$html = <<<EOT
<!-- stred tabulka data -->
	<tr style="background:#fff;">
		<td style="height:100%;"></td>
		<td style="height:100%; width:600px; margin:0px auto;">
			<table style="width:100%;">
				<tr>
					<td>$content
					</td>
				</tr>
			</table>

EOT;

		if ($this->productsEnabled())
		{
			// část s produkty

			$html .= <<<EOT
			<table class="tab-katalog" cellspacing="0" cellpadding="0">

EOT;

			$products_per_row = 3;
			$products = $newsletter['Products'];
			$count = count($products);
			$rows = $count / $products_per_row;
			$i = 0;
			for ($r = 0; $r < $rows; $r++)
			{
				$html_2ndrow = '';
				$html .= <<<EOT

				<tr>
EOT;
				for ($c = 0; $c < $products_per_row; $c++)
				{
					if ($i < $count)
					{
						$po = $products[$i];
						$product = new Kiwi_Product($po->PID);
						$flags_str = $this->getFlagsHTML($urlBase, $po->Novelty, $po->Action, $po->Discount, $po->Sellout);
						if ($po->Action || $po->Sellout || $po->Discount)
						{
							$pdif_txt = $po->Action ? 'Aktion' : ($po->Sellout ? 'Sale' : 'Rabatt');
							$pdif_str = $this->getPercentDif($urlBase, $po->OriginalCost, $po->NewCost, $pdif_txt);
						}
						else
							$pdif_str = '';
						$title = htmlspecialchars($po->Title);
						$shortdesc = $this->getProductDescriptionHTML($po->ShortDesc);

						try
						{
							$availability = $product->getProperty('Lieferzeit');
							if (empty($availability['Values']))
								throw new Exception();

							$av_vals = array();
							foreach ($availability['Values'] as $arec)
							{
								$av_value = htmlspecialchars($arec['Value']);
								$av_title = htmlspecialchars($arec['Description']);
								if ($av_title)
									$av_vals[] = <<<EOT
<acronym title="$av_title">$av_value</acronym>
EOT;
								else
									$av_vals[] = $av_value;
							}
							$avail_str = $this->getAvailabilityHTML(implode(' | ', $av_vals));
						}
						catch (Exception $e)
						{
							$avail_str = '';
						}

						try
						{
							$colors = $product->getProperty('Farbe');
							if (!empty($colors['Values']))
							{
								$colors_dir = $nav->get($this->_catalog . '_property_colors');
								$prop_vals = array();
								foreach ($colors['Values'] as $value)
								{
									$colors_a = array_map('strtolower', preg_split('/[,;\|]/', $value['ExtraData']));
									$picname = htmlspecialchars(implode('', $colors_a) . '.gif');
									$filename = "{$colors_dir}$picname";
									$ptitle = htmlspecialchars($value['Value']);
									$prop_vals[] = <<<EOT
<img src="$urlBase$filename" alt="$picname" title="$ptitle" width="16" height="16" />
EOT;
								}

								$prop_vals_str = implode("\n\t\t\t\t", $prop_vals);
								$colors_html = <<<EOT

						<p style="text-align:left; margin:15px 0 5px 12px;">
							$prop_vals_str
						</p>
EOT;
							}
							else
								$colors_html = '';
						}
						catch (Exception $e)
						{
							$colors_html = '';
						}

						$cost_str = $this->getCostHTML($po->OriginalCost, $po->NewCost);
						if ($po->URL != '') $detail = $nav->get($this->_catalog . '_urlbase') . $po->URL;
						else $detail = $nav->get($this->_catalog . '_photos_detail') . '?p=' . $po->PID;
						$order = $detail;
						$psmall = $po->Photo != '' ? ($nav->get($this->_catalog . '_photos') . $po->Photo) : '/image/na-catalog.jpg';

						$td_kat_class_suffix = $c > 0 ? 'n' : 'f';
						$td_style_border = $c > 0 ? 'border-left:1px solid #aaa;' : 'border:0px;';

						$html .= <<<EOT

					<td style="width:230px; height:auto; text-align:center; $td_style_border vertical-align:top;">
						<h2 style="font-size:15px; width:210px; height:50px;"><a style="color:#000; text-decoration:none;" href="$urlBase$detail" title="">$title</a></h2>
						<div class="kat-product" style="position:relative;">$flags_str
							<a style="position:relative; text-decoration:none;" href="$urlBase$detail" title=""><img src="$urlBase$psmall" alt="$title" title="$title" width="{$pictures_config['catalog'][0]}" height="{$pictures_config['catalog'][1]}" />$pdif_str</a>$colors_html$avail_str
						</div>
			$shortdesc
					</td>
EOT;
						$html_2ndrow .= <<<EOT

					<td style="width:230px; height:auto; text-align:center; border:0px; vertical-align:top;">
						$cost_str
						<div style="background:url($urlBase/img/objednat-but.gif) no-repeat; width:160px; height:25px; display:block; margin:10px auto 5px auto;"><a href="$urlBase$order" title="In den Warenkorb"><span style="visibility:hidden;">In den Warenkorb</span></a></div>
					</td>
EOT;

						$i++;
					}
					else
					{
						$html .= <<<EOT

					<td class="white">&nbsp;</td>
EOT;
						$html_2ndrow .= <<<EOT

					<td class="white">&nbsp;</td>
EOT;
					}
				}
				$html .= <<<EOT

				</tr>
				<tr>$html_2ndrow
				</tr>
				<tr style="height:30px;">
					<td colspan="3" style="height:auto;"><hr style="height:1px; border:none; border-top:1px solid #aaa;" /></td>
				</tr>

EOT;
			}

			$html .= <<<EOT
			</table>
EOT;
		}

		// spodní část

		$html .= <<<EOT
		</td>
		<td style="height:100%;"></td>
	</tr>
	<tr style="background:#ffffff; height:auto;">
		<td></td>
		<td style="width:600;">
			<div style="font-size:12px; margin: 0 auto; width:600px; height:90px; text-align:center; float:left;">{$this->getUnsubscriptionHtml($urlBase, $client)}
			</div>
		</td>
		<td></td>
	</tr>
<!-- stred tabulka data konec -->
	<tr>
		<td colspan="3" style="height:100%; display:block;">
		</td>
	</tr>

EOT;

		return $html;
	}

	public function getFlagsHTML($urlBase, $novelty, $action, $discount, $sellout)
	{
		$novinka_str = '';
		$akce_str = '';
		$sleva_str = '';
		$vyprodej_str = '';

		if ($novelty) $novinka_str = <<<EOT

						<span style="background:url($urlBase/img/novinka.gif) no-repeat; width: 42px; height: 15px; display:inline-block"><strong style="display:none;">Neu</strong></span>

EOT;

		if ($action) $akce_str = <<<EOT

						<span style="background:url($urlBase/img/akce.gif) no-repeat; width: 42px; height: 15px; display:inline-block"><strong style="display:none;">Aktion</strong></span>
EOT;

		if ($discount) $sleva_str = <<<EOT

						<span style="background:url($urlBase/img/sleva.gif) no-repeat; width: 42px; height: 15px; display:inline-block"><strong style="display:none;">Rabatt</strong></span>
EOT;

		if ($sellout) $vyprodej_str = <<<EOT

						<span style="background:url($urlBase/img/vyprodej.gif) no-repeat; width: 42px; height: 15px; display:inline-block"><strong style="display:none;">Sale</strong></span>
EOT;

		$html = <<<EOT

					<div class="status" style="margin:0 0 5px 0; width:220px;">$novinka_str$akce_str$sleva_str$vyprodej_str
					</div>
EOT;
		return $html;
	}

	public function getAvailabilityHTML($availability)
	{
		$html = <<<EOT

					<div style="font-size:14px; font-family:Trebuchet MS, Arial, Helvetica, sans-serif;">Lieferzeit: $availability</div>
EOT;
		return $html;
	}

	public function getPercentDif($urlBase, $original, $new, $text)
	{
		$html = '';

		if ($original > $new)
		{
			$discount = (1 - $new / $original) * 100;
			$discount_f = floor($discount); //number_format($discount, 2, ',', '.');

			$var = array
			(
				'Rabatt' => array('class' => 'sleva-kat', 'picture' => 'sleva_bg.png'),
				'Aktion' => array('class' => 'akce-kat', 'picture' => 'akce_bg.png'),
				'Sale' => array('class' => 'vyprodej-kat', 'picture' => 'vyprodej_bg.png'),
				'default' => array('class' => 'sleva-kat', 'picture' => 'sleva_bg.png'),
			);

			$key = array_key_exists($text, $var) ? $text : 'default';
			$divclass = $var[$key]['class'];
			$picfile = $var[$key]['picture'];

			$html = <<<EOT

					<div class="$divclass" style="position:absolute; bottom:0; right:0; color:white; background:url($urlBase/img/$picfile) repeat; padding:4px 2px 4px 2px; text-align:center; font-size:12px;">$text<strong style="display:block; font-size:22px;"> -$discount_f %</strong></div>
EOT;
		}

		return $html;
	}

	public function getCostHTML($original, $new)
	{
/*
		if ($original != 0)
		{
			$original_f = number_format($original, 2, '.', ' ');
			$original_s = <<<EOT
<span class="old$class_suffix"><strong>CZK</strong> $original_f</span>
EOT;
		}
		else
			$original_s = '';
*/
		$new_f = number_format($new, 2, ',', ' ');

		$new_s = <<<EOT
<span style="color:#333; font-family:Trebuchet MS, Arial, Helvetica, sans-serif;  font-size:14px; font-weight:bold; margin:0;">$new_f<strong> CHF</strong></span>
EOT;

		$html = <<<EOT
<div style="width:210px; margin:0 auto; padding:0;">{$new_s}</div>
EOT;
		return $html;
	}

	protected function getProductDescriptionHTML($desc)
	{
		$pattern = "#(\r\n)#";
		$replacement = "</p>\n\t\t\t<p>";
		$result1 = preg_replace($pattern, $replacement, htmlspecialchars($desc));
		$result2 = str_replace("\t\t\t<p></p>\n", '', $result1);
		$result3 = str_replace('<p>', '<p style="font-family:Trebuchet MS, Arial, Helvetica, sans-serif; font-size:12px; text-align:justify; width:200px; margin:0 auto; margin:10px 15px;">', $result2);
		return "\t\t\t<p>$result3</p>";
	}

	protected function getUnsubscriptionHtml($urlBase, $client)
	{
		$nav = new Project_Navigator;
		$code = $client ? $client['Code'] : 'XXXXXXXXXXXXXXXX';
		$unsubscriptionLink = $urlBase . $nav->get('newsletters_unsubscription') . '?code=' . urlencode($code);

		return <<<EOT
<p>You can unsubscribe from our newsletters service by clicking on <a href="$unsubscriptionLink">this link</a>.</p>
EOT;
	}

	protected function productsEnabled()
	{
		return (bool) $this->getConfigValue('products');
	}

}
