<?php
class NewsletterResubscriber_View extends Template_Based_View
{
	protected $_code;
	protected $_resubscription_result;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->template = 'newsletterresubscriber/default'; // default template
	}


	protected function _updateTemplate()
	{
		$eids = array(
			'prihlasen' => FALSE,
			'neplatny_kod' => FALSE,
			'chybejici_kod' => FALSE,
			'zablokovan' => FALSE,
			'jiz_prihlasen' => FALSE,
			'chyba' => FALSE,
		);

		switch ($this->_resubscription_result) {
			case Newsletter_Subscription_Manager::SUBSCRIBED:
				$eids['prihlasen'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::NOT_FOUND:
				$eids['neplatny_kod'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::DATA_INSUFFICIENT:
				$eids['chybejici_kod'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::BLOCKED:
				$eids['zablokovan'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::ALREADY_SUBSCRIBED:
				$eids['jiz_prihlasen'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::UNKNOWN_ERROR:
			default:
				$eids['chyba'] = TRUE;
		}

		foreach ($eids as $eid => $active) {
			$elems = $this->_index->$eid;
			foreach ($elems as $elem) {
				$elem->active = $active;
			}
		}
	}

	protected function _handleInput()
	{
		$this->_code = isset($_GET['code']) ? $_GET['code'] : NULL;
		$nsm = new Newsletter_Subscription_Manager;
		$this->_resubscription_result = $nsm->resubscribe(NULL, NULL, $this->_code);
	}
}
