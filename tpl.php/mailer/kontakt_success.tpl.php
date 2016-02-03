<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('div');
$_e_[2] = $_b_->build('h2');
$_e_[3] = $_b_->build('text_content');
$_e_[3]->set('text', 'Vielen Dank für über unser Kontaktformular.');
$_e_[2]->add($_e_[3]);
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('div');
$_e_[6] = $_b_->build('p');
$_e_[7] = $_b_->build('text_content');
$_e_[7]->set('text', 'Ihre Nachricht, um gesendet wurde, wird Ihre Nachricht versuchen, so schnell wie möglich absetzen und reagieren auf Sie.');
$_e_[6]->add($_e_[7]);
$_e_[5]->set('class', 'kontakt-msg');
$_e_[5]->add($_e_[6]);
$_e_[4]->set('id', 'vzkaz');
$_e_[4]->add($_e_[5]);
$_e_[1]->set('class', 'kontakt-formular');
$_e_[1]->add($_e_[2]);
$_e_[1]->add($_e_[4]);
$_e_[0]->add($_e_[1]);
?>