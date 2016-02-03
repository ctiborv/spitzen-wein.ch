<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('flow');
$_e_[2] = $_b_->build('div');
$_e_[3] = $_b_->build('text_content');
$_e_[3]->set('text', 'Seite: ');
$_e_[4] = $_b_->build('inline');
$_e_[4]->set('eid', 'lista');
$_e_[2]->set('class', 'lista');
$_e_[2]->add($_e_[3]);
$_e_[2]->add($_e_[4]);
$_e_[1]->set('eid', 'obsah');
$_e_[1]->add($_e_[2]);
$_e_[5] = $_b_->build('flow');
$_e_[5]->set('eid', 'obsah');
$_e_[5]->set('specification', '1');
$_e_[6] = $_b_->build('flow');
$_e_[6]->set('eid', 'obsah');
$_e_[6]->set('specification', '0');
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[5]);
$_e_[0]->add($_e_[6]);
?>