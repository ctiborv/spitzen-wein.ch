<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('require');
$_e_[2] = $_b_->build('javascript');
$_e_[2]->set('src', '/js/newslettersubscriber_form.js');
$_e_[1]->add($_e_[2]);
$_e_[3] = $_b_->build('form');
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('input');
$_e_[5]->set('type', 'hidden');
$_e_[5]->set('eid', 'identification');
$_e_[5]->set('name', 'newslettersubscriber');
$_e_[5]->set('value', '1');
$_e_[6] = $_b_->build('autoinput');
$_e_[6]->set('formid', 'newslettersubscriber');
$_e_[6]->set('eid', 'hidden');
$_e_[6]->set('id', 'jsv');
$_e_[6]->set('name', 'jsv');
$_e_[6]->set('type', 'hidden');
$_e_[7] = $_b_->build('h2');
$_e_[8] = $_b_->build('text_content');
$_e_[8]->set('text', 'Zum Newsletter anmelden');
$_e_[7]->add($_e_[8]);
$_e_[9] = $_b_->build('span');
$_e_[10] = $_b_->build('text_content');
$_e_[10]->set('text', 'Newsletter abonnieren - neue Angebote und aktuelle Rabatte nicht verpassen!');
$_e_[9]->add($_e_[10]);
$_e_[11] = $_b_->build('div');
$_e_[12] = $_b_->build('autoinput');
$_e_[12]->set('formid', 'newslettersubscriber');
$_e_[12]->set('eid', 'trap');
$_e_[12]->set('name', 'femail');
$_e_[12]->set('type', 'text');
$_e_[12]->set('value', '@');
$_e_[12]->set('class', 'iinp-nwsletter');
$_e_[13] = $_b_->build('autoinput');
$_e_[13]->set('formid', 'newslettersubscriber');
$_e_[13]->set('eid', 'email');
$_e_[13]->set('name', 'email');
$_e_[13]->set('type', 'text');
$_e_[13]->set('value', '@');
$_e_[13]->set('class', 'inp-nwsletter');
$_e_[14] = $_b_->build('inline');
$_e_[15] = $_b_->build('text_content');
$_e_[15]->set('text', '... Email je povinná položka');
$_e_[14]->set('eid', 'validation');
$_e_[14]->set('specification', 'email/missing');
$_e_[14]->add($_e_[15]);
$_e_[16] = $_b_->build('inline');
$_e_[17] = $_b_->build('text_content');
$_e_[17]->set('text', '... Neplatná emailová adresa');
$_e_[16]->set('eid', 'validation');
$_e_[16]->set('specification', 'email/invalid');
$_e_[16]->add($_e_[17]);
$_e_[18] = $_b_->build('inline');
$_e_[19] = $_b_->build('text_content');
$_e_[19]->set('text', '... Emailová adresa vyplněna v pořádku');
$_e_[18]->set('eid', 'validation');
$_e_[18]->set('specification', 'email/ok');
$_e_[18]->add($_e_[19]);
$_e_[20] = $_b_->build('autoinput');
$_e_[20]->set('formid', 'newslettersubscriber');
$_e_[20]->set('eid', 'trap');
$_e_[20]->set('name', 'fsubmit');
$_e_[20]->set('type', 'submit');
$_e_[20]->set('value', 'Senden');
$_e_[20]->set('class', 'ibut5');
$_e_[20]->set('onclick', 'return Newslettersubscriber_Form.onSubmit(\'jsv\')');
$_e_[21] = $_b_->build('autoinput');
$_e_[21]->set('formid', 'newslettersubscriber');
$_e_[21]->set('eid', 'odeslat');
$_e_[21]->set('name', 'submit');
$_e_[21]->set('type', 'submit');
$_e_[21]->set('value', 'Senden');
$_e_[21]->set('class', 'btn-nwsletter');
$_e_[21]->set('onclick', 'return Newslettersubscriber_Form.onSubmit(\'jsv\')');
$_e_[22] = $_b_->build('flow');
$_e_[23] = $_b_->build('javascript');
$_e_[24] = $_b_->build('text_content');
$_e_[24]->set('text', 'location.hash = \'prihlaseni_k_odberu\';');
$_e_[23]->add($_e_[24]);
$_e_[22]->set('eid', 'validation_failed');
$_e_[22]->add($_e_[23]);
$_e_[11]->set('class', 'newsletter-form');
$_e_[11]->add($_e_[12]);
$_e_[11]->add($_e_[13]);
$_e_[11]->add($_e_[14]);
$_e_[11]->add($_e_[16]);
$_e_[11]->add($_e_[18]);
$_e_[11]->add($_e_[20]);
$_e_[11]->add($_e_[21]);
$_e_[11]->add($_e_[22]);
$_e_[4]->set('class', 'newsletter');
$_e_[4]->add($_e_[5]);
$_e_[4]->add($_e_[6]);
$_e_[4]->add($_e_[7]);
$_e_[4]->add($_e_[9]);
$_e_[4]->add($_e_[11]);
$_e_[3]->set('render', 'js');
$_e_[3]->set('method', 'post');
$_e_[3]->set('action', '');
$_e_[3]->set('id', 'prihlaseni_k_odberu');
$_e_[3]->set('enctype', 'application/x-www-form-urlencoded');
$_e_[3]->add($_e_[4]);
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[3]);
?>