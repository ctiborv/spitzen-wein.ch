<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('h2');
$_e_[2] = $_b_->build('text_content');
$_e_[2]->set('text', 'PŘIHLÁŠENÍ K ODBĚRU NEWSLETTERŮ');
$_e_[1]->add($_e_[2]);
$_e_[3] = $_b_->build('p');
$_e_[4] = $_b_->build('text_content');
$_e_[4]->set('text', 'Přihlášení proběhlo úspěšně.');
$_e_[3]->set('eid', 'prihlasen');
$_e_[3]->add($_e_[4]);
$_e_[5] = $_b_->build('p');
$_e_[6] = $_b_->build('text_content');
$_e_[6]->set('text', 'Potvrzovací kód již není platný.');
$_e_[5]->set('eid', 'neplatny_kod');
$_e_[5]->add($_e_[6]);
$_e_[7] = $_b_->build('p');
$_e_[8] = $_b_->build('text_content');
$_e_[8]->set('text', 'Je třeba poskytnout potvrzovací kód.');
$_e_[7]->set('eid', 'chybejici_kod');
$_e_[7]->add($_e_[8]);
$_e_[9] = $_b_->build('p');
$_e_[10] = $_b_->build('text_content');
$_e_[10]->set('text', 'Přihlášení bylo zrušeno, neboť zadaná emailová adresa byla zablokována.');
$_e_[9]->set('eid', 'zablokovan');
$_e_[9]->add($_e_[10]);
$_e_[11] = $_b_->build('p');
$_e_[12] = $_b_->build('text_content');
$_e_[12]->set('text', 'Tato adresa již dříve byla přihlášena k odběru newsletterů.');
$_e_[11]->set('eid', 'jiz_prihlasen');
$_e_[11]->add($_e_[12]);
$_e_[13] = $_b_->build('p');
$_e_[14] = $_b_->build('text_content');
$_e_[14]->set('text', 'Omlouváme se, ale došlo k neznámé chybě.');
$_e_[13]->set('eid', 'chyba');
$_e_[13]->add($_e_[14]);
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[3]);
$_e_[0]->add($_e_[5]);
$_e_[0]->add($_e_[7]);
$_e_[0]->add($_e_[9]);
$_e_[0]->add($_e_[11]);
$_e_[0]->add($_e_[13]);
?>