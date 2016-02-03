<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'mpdf/mpdf.php';

class Kiwi_Invoice_Pdf extends Page_Item
{
	protected $id;
	protected $data;

	public function __construct()
	{
		parent::__construct();
		$this->id = 0;
		$this->data = null;
	}

	public function _getHTML()
	{
		return '';
	}

	protected function file2Url($filename)
	{
		return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $filename;
	}

	public function send()
	{
		$this->loadData();

		$mpdf = new mPDF('utf-8', 'A4');
		$mpdf->useOnlyCoreFonts = true;
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->SetAutoFont(0);

		// CSS soubory
		$css[] = './styles/faktura.css';
		// faktura v HTML (PHP, atd.)
		$html[] = KIWI_INVOICE . '?bauth&o=' . $this->id;

		foreach ($css as $filename) {
			@$mpdf->WriteHTML(
				file_get_contents($filename, true),
				1);
		}

		foreach ($html as $filename) {
			@$mpdf->WriteHTML(
				file_get_contents($this->file2Url($filename)),
				2
			);
		}

		$o_id = sprintf("%03d", $this->data->YID) . "-{$this->data->Year}";
		$name = "invoice_$o_id.pdf";
		$mpdf->Output($name,"D"); // download
	}

	public function handleInput($get, $post)
	{
		// todo: přidat práva
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (isset($get['o']))
			{
				if (($o = (int)$get['o']) < 1)
					throw new Exception("Neplatná hodnota parametru \"o\": $o");

				$this->id = $o;
			}
			else $this->redirection = KIWI_ORDERS;
		}
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id != 0)
		{
			$sql = "SELECT YID, Year(Created) AS Year FROM eshoporders WHERE ID=$this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
			}
			else throw new Exception('Neplatný identifikátor objednávky');
		}
	}
}
?>
