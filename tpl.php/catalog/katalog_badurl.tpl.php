<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('div');
$_e_[2] = $_b_->build('text_content');
$_e_[2]->set('text', 'Artikel ');
$_e_[3] = $_b_->build('b');
$_e_[4] = $_b_->build('text');
$_e_[4]->set('eid', 'url');
$_e_[3]->add($_e_[4]);
$_e_[5] = $_b_->build('text_content');
$_e_[5]->set('text', ' ist nicht mehr vorhanden.');
$_e_[1]->set('class', 'info-text2');
$_e_[1]->add($_e_[2]);
$_e_[1]->add($_e_[3]);
$_e_[1]->add($_e_[5]);
$_e_[0]->add($_e_[1]);
?>