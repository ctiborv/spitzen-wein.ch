<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('flow');
$_e_[2] = $_b_->build('div');
$_e_[3] = $_b_->build('flow');
$_e_[3]->set('eid', 'obsah');
$_e_[2]->set('class', 'obsah');
$_e_[2]->add($_e_[3]);
$_e_[1]->set('eid', 'ma_obsah');
$_e_[1]->add($_e_[2]);
$_e_[4] = $_b_->build('flow');
$_e_[4]->set('eid', 'nema_obsah');
$_e_[5] = $_b_->build('flow');
$_e_[5]->set('eid', 'neni_aktivni');
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[4]);
$_e_[0]->add($_e_[5]);
?>