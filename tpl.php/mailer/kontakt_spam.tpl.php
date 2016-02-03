<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('div');
$_e_[2] = $_b_->build('h2');
$_e_[3] = $_b_->build('text_content');
$_e_[3]->set('text', 'KONTAKTIEREN SIE UNS');
$_e_[2]->add($_e_[3]);
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('p');
$_e_[6] = $_b_->build('text_content');
$_e_[6]->set('text', 'Ihre Nachricht konnte nicht gesendet werden, weil das System sie als Spam gewertet wird.');
$_e_[5]->add($_e_[6]);
$_e_[4]->set('class', 'kontakt-msg');
$_e_[4]->add($_e_[5]);
$_e_[1]->set('class', 'kontakt-formular');
$_e_[1]->add($_e_[2]);
$_e_[1]->add($_e_[4]);
$_e_[0]->add($_e_[1]);
?>