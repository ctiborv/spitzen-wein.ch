<?php
abstract class Custom_Message_Builder extends Message_Builder
{
	protected function getHtmlTitle(array $data)
	{
		return 'Message';
	}

	protected function getHeaderTitle(array $data)
	{
		return 'Message';
	}

	protected function getBodyHeader(array $data)
	{
		$headerTitle = $this->getHeaderTitle($data);
		$protocol = $this->getConfigValue('protocol');
		$www = $this->getConfigValue('www');
		$urlBase = "$protocol://$www";

		$html = <<<EOT
<body style="background:#fff; color:#04629F; font-family:Arial, Arial CE, Sans-serif; font-size:8pt; margin:0; padding:0; border:0;">
<table  style="background:transparent; font-family:Arial, Arial CE, Sans-serif; font-size:8pt; margin:0; padding:0; width:100%; border:none;" cellspacing="0" cellpadding="0">
<!-- hlavicka -->
	<tr style="background:#ffffff;">
		<td style="background:#ffffff; margin:0; padding:0; width:30px; height:80px; border:none;"></td>
		<td style="background:#ffffff; margin:0; padding:0; width:auto; height:80px; border:none;">
			<table style="border:0px; width:100%; height:80px; padding:0; margin:0;">
				<tr>
					<td style="width:150px; height:80px;">
						<img src="$urlBase/image/email/logo.gif" alt="" style="float:left; padding:0 0px 0 0px; width:auto; height:auto;" />
					</td>
					<td>
						<h1 style="display:block; font-size:12pt; margin:10px 0 15px 0; padding:0px; vertical-align: middle; font-family:Arial, Arial CE, Sans-serif;"><a style="color:#313131;" href="$urlBase">$www</a> - $headerTitle</h1>
					</td>
				</tr>
			</table>
		</td>
		<td style="background:#ffffff; margin:0; padding:0; width:30px; height:80px;"></td>
	</tr>
	<tr style="background:#0486FC; height:4px;">
		<td colspan="3"></td>
	</tr>
	<tr style="background:#04629F; height:15px;">
		<td colspan="3"></td>
	</tr>
<!-- hlavicka konec -->

EOT;

		return $html;
	}

	protected function getBodyFooter(array $data)
	{
		$firmTitle = htmlspecialchars($this->getConfigValue('title'));
		$email = $this->getConfigValue('contact_email');
		$ic = $this->getConfigValue('ic');
		$phone = $this->getConfigValue('phone');
		$fullAddress = htmlspecialchars($this->getConfigValue('fullAddress'));

		$html = <<<EOT
<!-- paticka -->
	<tr>
		<td colspan="3" style="background:#0486FC; height:10px;"></td>
	</tr>
	<tr style="background:#0486FC; height:3px;">
		<td colspan="3">
		</td>
	</tr>
	<tr style="background:#ffffff; height:auto;">
		<td></td>
		<td style="width:600;">
			<div style="font-size:12px; margin: 0 auto; width:600px; height:90px; text-align:center; float:left;">
				<p style="margin:10px 0 10px 0; color:#313131;">

EOT;

		if ($firmTitle !== NULL) {
			$html .= <<<EOT
$firmTitle<br />

EOT;
		}

		if ($ic !== NULL) {
			$html .= <<<EOT
$ic<br />

EOT;
		}

		if ($fullAddress !== NULL) {
			$html .= <<<EOT
$fullAddress<br />
EOT;
		}

		if ($phone !== NULL) {
			$html .= <<<EOT
 Tel.: $phone<br />

EOT;
		}

		$html .= <<<EOT
E-mail: <a style="color:#313131; font-weight:bold;" href="mailto:$email">$email</a>
				</p>
			</div>
		</td>
		<td></td>
	</tr>
<!-- paticka konec -->
</table>
</body>

EOT;

		return $html;
	}
}
