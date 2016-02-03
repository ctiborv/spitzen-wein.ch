<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('body');
$_e_[2] = $_b_->build('newslettersubscriber');
$_e_[2]->set('ns', 'test');
$_e_[1]->add($_e_[2]);
$_e_[0]->add($_e_[1]);
?>