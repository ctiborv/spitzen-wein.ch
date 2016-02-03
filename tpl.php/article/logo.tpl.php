<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('div');
$_e_[2] = $_b_->build('navpoint');
$_e_[3] = $_b_->build('img');
$_e_[3]->set('src', '../image/logo.png');
$_e_[3]->set('alt', 'logo spitzen wein');
$_e_[2]->set('page', 'úvod');
$_e_[2]->set('title', 'Zur Hauptseite spitzen-wein.ch');
$_e_[2]->add($_e_[3]);
$_e_[1]->set('class', 'logo');
$_e_[1]->add($_e_[2]);
$_e_[4] = $_b_->build('div');
$_e_[4]->set('class', 'lahev-vina');
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[4]);
?>