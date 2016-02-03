<?php
// todo: doresit prava
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';

class Kiwi_Import_Export_Form extends Page_Item
{
	protected $_import_filename;
	protected $_picture_import_dir;
	protected $_export_filename;

	const IMPORT_DIR = '../import/';
	const EXPORT_DIR = '../export/';
	const DEFAULT_PICTURE_DIR = 'pictures/';

	public function __construct()
	{
		parent::__construct();
		$this->initialize();
	}

	protected function initialize()
	{
		$this->_import_filename = $this->locateImportFile();
		$this->_picture_import_dir = self::DEFAULT_PICTURE_DIR;
		$this->_export_filename = 'export.csv'; // docasne
	}

	protected function locateImportFile()
	{
		return 'import.csv'; // docasne
	}

	public function _getHTML()
	{
		if ($this->_import_filename !== null)
		{
			$filename = urlencode(self::IMPORT_DIR . $this->_import_filename);
			$onclick1 = "window.open('kiwi-import.php?file=$filename','_blank','height=260px,width=460px,top=150,left=200px,menubar=no,toolbar=no,resizable=yes,scrollbars=yes')";
			$text1 = 'Einfuhr von Waren in den Laden aus einer Datenbank-Datei: ' . $this->_import_filename;
		}
		else
		{
			$onclick1 = 'alert(\'FTP-Upload-Datei und importieren Sie diese Seite neu laden (STRG-R).\nDann klicken Sie wieder auf die Produkte in die Datenbank zu importieren.\')';
			$text1 = 'Konnte keine Datei für import finden!';
		}

		if ($this->_picture_import_dir !== null)
		{
			$picture_import_dir = urlencode(self::IMPORT_DIR . $this->_picture_import_dir);
			$onclick2 = "window.open('kiwi-import-obrazku.php?dir=$picture_import_dir','_blank','height=260px,width=460px,top=150,left=200px,menubar=no,toolbar=no,resizable=yes,scrollbars=yes')";
			$text2 = 'Importieren Sie Bilder in einer Datenbank-Verzeichnis Shop: ' . $this->_picture_import_dir;
		}
		else
		{
			$onclick2 = 'alert(\'Importieren von Bildern wurde geblockt, kontaktieren Sie den Administrator für die Inbetriebnahme.\')';
			$text2 = 'Import von Dateien in Datenbanken Shop ist nicht möglich.';
		}

		if ($this->_export_filename !== null)
		{
			$filename = urlencode(self::EXPORT_DIR . $this->_export_filename);
			$onclick3 = "window.open('kiwi-export.php?file=$filename','_blank','height=260px,width=460px,top=150,left=200px,menubar=no,toolbar=no,resizable=yes,scrollbars=yes')";
			$text3 = 'Export-Produkte aus dem Shop-Datenbank in eine Datei: ' . $this->_export_filename;
		}
		else
		{
			$onclick3 = 'alert(\'Dieser Dienst ist derzeit nicht verfügbar.\')';
			$text3 = 'Export-Produkte aus der Datenbank ist derzeit nicht verfügbar.';
		}

		$html = <<<EOT
<h2>KATALOG Artikel - [Import / Export]</h2>
<div class="levyV">
	<div class="form3">
		<div id="frame2">
			<table class="tab-import" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<input type="button" id="kesfc_cmd1" name="cmd" value="Import von Waren in die Datenbank" class="but1" onclick="$onclick1" />
					</td>
					<td id="vysledek1">$text1</td>
				</tr>
				<tr>
					<td>
						<input type="button" id="kesfc_cmd2" name="cmd" value="Import von Dateien in Datenbanken" class="but1" onclick="$onclick2" />
					</td>
					<td id="vysledek2">$text2</td>
				</tr>
				<tr>
					<td>
						<input type="button" id="kesfc_cmd3" name="cmd" value="Export-Produkte aus der Datenbank" class="but1" onclick="$onclick3" />
					</td>
					<td id="vysledek3">$text3</td>
				</tr>
			</table>
		</div>
	</div>
</div>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
	}
}
?>
