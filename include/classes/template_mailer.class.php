<?php
class Template_Mailer
{
	protected $_mvo;
	protected $_mailer;
	protected $_fields;
	protected $_from;
	protected $_to;
	protected $_subject;

	public function __construct(Mailing_View_Object $mvo)
	{
		$this->_mvo = $mvo;
		$this->_mailer = new PHPMailer();
		$this->_mailer->CharSet = 'utf-8';
		$this->_fields = $this->_mvo->getMailBodyFields();
		$this->_from = $this->_mvo->getMailSender();
		$this->_to = $this->_mvo->getMailRecipient();
		$this->_subject = $this->_mvo->getMailSubject();
	}

	public function get($name)
	{
		if ($name == 'mailer')
			return $this->_mailer;
		else
			return $this->_mailer->$name;
	}

	public function set($name, $value)
	{
		if ($name == 'mailer')
			throw new Readonly_Variable_Exception($name, get_class($this));
		else
			$this->_mailer->$name = $value;
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function send()
	{
		$contacts = Project_Config::get('contacts');

		if (!is_array($this->_from))
		{
			if (!array_key_exists($this->_from, $contacts))
				throw new Template_Invalid_Argument_Exception('contact', $this->_from);

			$from = $contacts[$this->_from];
			if (is_array($from))
			{
				$this->_mailer->From = is_array($from['email']) ? $from['email'][0] : $from['email'];

				if (array_key_exists('name', $from))
					$this->_mailer->FromName = $from['name'];
			}
			else
				$this->_mailer->From = $from;
		}
		else
		{
			$this->_mailer->From = is_array($this->_from['email']) ? $this->_from['email'][0] : $this->_from['email'];
			if (array_key_exists('name', $this->_from))
				$this->_mailer->FromName = $this->_from['name'];
		}

		$this->_mailer->Subject = $this->_subject;
		$this->setMailBody();

		$to_list = is_array($this->_to) ? $this->_to : array($this->_to);

		foreach ($to_list as $to)
		{
			// either of:
			// text -> registered contact
			// array -> { name => name, email => email }

			if (!is_array($to))
			{
				if (!array_key_exists($to, $contacts))
					throw new Template_Invalid_Argument_Exception('contact', $to);

				$contact = $contacts[$to];
				if (is_array($contact))
				{
					$contact_email = $contact['email'];
					$contact_name = array_key_exists('name', $contact) ? $contact['name'] : '';
				}
				else
				{
					$contact_email = $contact;
					$contact_name = '';
				}
			}
			else
			{
				$contact_email = $to['email'];
				$contact_name = array_key_exists('name', $to) ? $to['name'] : '';
			}

			if (is_array($contact_email))
				foreach ($contact_email as $ce)
					$this->_mailer->addAddress($ce, $contact_name);
			else
				$this->_mailer->addAddress($contact_email, $contact_name);
		}

		return $this->_mailer->Send();
	}

	protected function setMailBody()
	{
		try
		{
			$this->_mailer->Body = $this->createMailBodyHTML();
			$this->_mailer->IsHTML(true);
		}
		catch (File_Not_Found_Exception $e)
		{
			$this->_mailer->Body = $this->createMailBodySimple();
			$this->_mailer->IsHTML(false);
		}
	}

	protected function createMailBodyHTML()
	{
		$mail_template = $this->_mvo->getMailTemplateSource();
		$vm = new View_Manager;
		$builder = new HTML_Custom_Builder($vm);
		$mail_root = Template_Loader::load($mail_template, $builder);
		$mail_index = new HTML_Indexer($mail_root, 'eid', false);

		$this->_mvo->updateMailBodyTemplate($mail_root, $mail_index, $this->_fields);

		$renderer = new Text_Renderer;
		$mail_root->render($renderer);

		return $renderer->text;
	}

	protected function createMailBodySimple()
	{
		$constants = Project_Config::get('constants');
		$key = 'nevyplneno';
		try
		{
			$lang = $this->_mvo->lang;
		}
		catch (Exception $e)
		{
			$lang = Project_Config::get('default_language');
		}
		$ckey = $lang . '_' . $key;
		$nevyplneno_str = array_key_exists($ckey, $constants) ? $constants[$ckey] : mb_str_replace('_', ' ', $key, 'UTF-8');
		
		$body = '';
		$table = $this->_mvo->getMailBodySimpleTable();
		foreach ($table as $key => $text)
			if (isset($this->_fields[$key]))
				$body .= sprintf($text, $this->_fields[$key] !== '' ? $this->_fields[$key] : $nevyplneno_str);
			else
				$body .= $text;

		return $body;
	}
}
?>
