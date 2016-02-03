<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'project.inc.php';

class Kiwi_EShopMail_Form extends Page_Item
{
	protected $sendto;
	protected $subject;
	protected $message;
	protected $read_only;
	protected $redirect_to;

	const REDIR_SELF = 1;
	const REDIR_CLIENTS = 2;
	const REDIR_ORDERS = 3;

	public function __construct(&$rights)
	{
		parent::__construct();

		$erights = $rights->EShop;
		if (is_array($erights))
			$this->read_only = !$erights['Write'];
		else $this->read_only = !$erights;

		$this->sendto = '';
		$this->subject = '';
		$this->message = '';
		$this->redirect_to = self::REDIR_SELF;
	}

	public function _getHTML()
	{
		$self = basename($_SERVER['PHP_SELF']);

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$readonly_str = '';
			$disabled_str = '';
			$D_str = '';
		}

		$sendto = htmlspecialchars($this->sendto);
		$subject = htmlspecialchars($this->subject);
		$message = str_replace("\r\n", "\r", htmlspecialchars($this->message));
		$message = str_replace("\n", "\r", $message);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>Katalog - Senden von E-Mail</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset>
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">E-Mail-Adresse :</span></td>
							<td><input type="text" id="kemf_sendto" name="sendto" value="$sendto" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Gegenstand :</span></td>
							<td><input type="text" id="kemf_subject" name="subject" value="$subject" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Nachrichtentext :</span></td>
							<td><textarea id="kemf_message" name="message" class="texarOUT" onfocus="this.className='texarON'" onblur="this.className='texarOFF'"$readonly_str />$message</textarea></td>
						</tr>
					</table>
				</div>
				<input type="hidden" id="kemf_redir" name="redir" value="$this->redirect_to" />
				<input type="submit" id="kemf_cmd1" name="cmd" value="absenden" class="but3$D_str" onclick="return Kiwi_EShopMail_Form.onSubmit()"$disabled_str />
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (array_key_exists('c', $get))
			{
				$this->loadClient($get);
				$this->redirect_to = self::REDIR_CLIENTS;
			}

			if (array_key_exists('mt', $get))
			{
				// přípustné před-definované vzory mailů
				$mts = array
				(
					'odeslano' => true
				);

				if (array_key_exists($get['mt'], $mts))
				{
					$this->loadMessageTemplate($get);
					$this->redirect_to = self::REDIR_ORDERS;
				}
				else
					throw new Exception("Neplatná hodnota parametru \"mt\": {$get['mt']}");
			}
		}

		if (!empty($post))
		{
			if (array_key_exists('sendto', $post))
				$this->sendto = $post['sendto'];

			if (array_key_exists('subject', $post))
				$this->subject = $post['subject'];

			if (array_key_exists('message', $post))
				$this->message = $post['message'];

			if (array_key_exists('redir', $post))
				$this->redirect_to = $post['redir'];

			switch ($post['cmd'] && !$this->read_only)
			{
				case 'absenden':
					$this->sendMail();
					$this->redirect();
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function redirect()
	{
		$redirections = array
		(
			'default' => KIWI_ESHOPMAIL_FORM,
			self::REDIR_SELF => KIWI_ESHOPMAIL_FORM,
			self::REDIR_CLIENTS => KIWI_CLIENTS,
			self::REDIR_ORDERS => KIWI_ORDERS
		);

		$this->redirection = $redirections[array_key_exists($this->redirect_to, $redirections) ? $this->redirect_to : 'default'];
	}

	protected function loadClient($get)
	{
		$cid = (int)$get['c'];
		$sql = "SELECT BusinessName, Email, FirmEmail FROM eshopclients WHERE ID=$cid";
		$result = mysql_query($sql);
		if ($row = mysql_fetch_array($result))
		{
			$client = new Kiwi_DataRow($row);
			$this->sendto = $client->BusinessName ? $client->FirmEmail : $client->Email;
		}
		else
			throw new Exception("Neplatný identifikátor klienta: $cid");
	}

	protected function loadSentOrder($get)
	{
		$oid = (int)(array_key_exists('o', $get) ? $get['o'] : 0);
		$sql = "SELECT C.BusinessName, C.Email, C.FirmEmail, O.YID, YEAR(O.Created) AS Year, O.Delivery FROM eshoporders AS O JOIN eshopclients as C ON O.CID=C.ID WHERE O.ID=$oid";
		$result = mysql_query($sql);
		if ($row = mysql_fetch_array($result))
		{
			$record = new Kiwi_DataRow($row);
			$this->sendto = $record->BusinessName ? $record->FirmEmail : $record->Email;
			$deliverers_7p = array
			(
				'CHP' => 'Post',
				'DPD' => 'DPD',
				'PSA' => 'Persönliche Sammlung'
			);
			$deliverer = $record->Delivery ? (' ' . $deliverers_7p[$record->Delivery]) : '';
			$o_id = sprintf("%03d", $record->YID) . "–$record->Year";
			$this->subject = 'Der Versand der bestellten Ware';
			$this->message = <<<EOT
Grüezi, besten Dank für Ihre Online Bestellung. Ihre bestellte Ware (Bestell-Nr. $o_id) wurde versendet bei $deliverer. Sie können die Sendung über Ihr Benutzerkonto in der Liste der Aufträge verfolgen.

EOT;
		}
		else
			throw new Exception("Neplatný identifikátor objednávky: $oid");
	}

	protected function loadMessageTemplate($get)
	{
		switch ($get['mt'])
		{
			case 'odeslano':
				$this->loadSentOrder($get);
				break;
			default:
				throw new Exception("Neplatný identifikátor vzoru emailové zprávy");
		}
	}

	protected function sendMail()
	{
		global $kiwi_config;
		$from_email = $kiwi_config['eshop']['contact_email'];
		$protocol = $kiwi_config['eshop']['protocol'];
		$shop_www = $kiwi_config['eshop']['www'];

		if ($this->sendto && $this->subject && $this->message)
		{
			$headers =
				"From: <$from_email>\n" .
				"Return-Path: <$from_email>\n" .
				"MIME-Version: 1.0\n" .
				"Content-Type: text/html; charset=utf-8\n";

			$message = str_replace("\n", "</p><p>", $this->message);

			require 'kiwi_eshop_email_message_begin.inc.php';

			$subject_html = htmlspecialchars($this->subject);

			$html .= <<<EOT
            <td>
              <h1 style="display:block; color:#9A5A41; font-size:12pt; margin:10px 0 15px 0; padding:0px; vertical-align: middle; font-family:Arial, Arial CE, Sans-serif;"><a style="color:#9A5A41;" href="$protocol://$shop_www">$shop_www</a> - Bestätigung der Bestellung</h1>
            </td>
          </tr>
        </table>
      </td>
      <td style="background:#fff; margin:0; padding:0; width:30px; height:80px;"></td>
    </tr>
    <tr style="background:#9A5A41; height:4px;">
      <td colspan="3"></td>
     </tr>
    <tr style="background:#D4AE64; height:15px;">
      <td colspan="3"></td>
    </tr>
<!-- hlavicka konec -->
    <tr style="background:#EEECDE;">
      <td height="100%"></td>
      <td height="100%" width="600" style="margin:0px auto;">
<!-- stred tabulka data -->
        <table style="background:#fff; padding:9px; font-size:9pt; font-family:Arial, Arial CE, Sans-serif; width=:100%; height:100%;">
          <tr>
            <td>
              <p>$message</p>
              <hr />

EOT;
			require 'kiwi_eshop_email_message_end.inc.php';

			$subject = "$shop_www - $this->subject";
			$encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
			mail($this->sendto, $encoded_subject, $html, $headers);
		}
	}
}
?>
