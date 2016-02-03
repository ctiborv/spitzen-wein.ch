<?php
class NewsletterUnsubscriber_View extends Template_Based_View
{
	protected $_code;
	protected $_unsubscription_result;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->template = 'newsletterunsubscriber/default'; // default template
	}


	protected function _updateTemplate()
	{
		$eids = array(
			'odhlasen' => FALSE,
			'neplatny_kod' => FALSE,
			'chybejici_kod' => FALSE,
			'neprihlasen' => FALSE,
			'jiz_odhlasen' => FALSE,
			'chyba' => FALSE,
		);

		switch ($this->_unsubscription_result) {
			case Newsletter_Subscription_Manager::UNSUBSCRIBED:
				$eids['odhlasen'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::NOT_FOUND:
				$eids['neplatny_kod'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::DATA_INSUFFICIENT:
				$eids['chybejici_kod'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::NOT_FOUND:
				$eids['neprihlasen'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::ALREADY_UNSUBSCRIBED:
				$eids['jiz_odhlasen'] = TRUE;
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
		$this->_unsubscription_result = $nsm->unsubscribe(NULL, NULL, $this->_code);
	}
}
