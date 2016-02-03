<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('head');
$_e_[2] = $_b_->build('hgroup');
$_e_[3] = $_b_->build('meta');
$_e_[3]->set('http-equiv', 'Content-Type');
$_e_[3]->set('content', 'text/html; charset=utf-8');
$_e_[4] = $_b_->build('meta');
$_e_[4]->set('name', 'robots');
$_e_[4]->set('content', 'index, follow');
$_e_[5] = $_b_->build('meta');
$_e_[5]->set('name', 'google-site-verification');
$_e_[5]->set('content', '');
$_e_[6] = $_b_->build('meta');
$_e_[6]->set('name', 'description');
$_e_[6]->set('content', '');
$_e_[6]->set('lang', 'de');
$_e_[7] = $_b_->build('meta');
$_e_[7]->set('name', 'keywords');
$_e_[7]->set('content', '');
$_e_[7]->set('lang', 'de');
$_e_[8] = $_b_->build('meta');
$_e_[8]->set('name', 'author');
$_e_[8]->set('content', 'www.artprodesign.com');
$_e_[9] = $_b_->build('meta');
$_e_[9]->set('name', 'copyright');
$_e_[9]->set('content', 'artprodesign.com');
$_e_[2]->set('eid', 'metas');
$_e_[2]->add($_e_[3]);
$_e_[2]->add($_e_[4]);
$_e_[2]->add($_e_[5]);
$_e_[2]->add($_e_[6]);
$_e_[2]->add($_e_[7]);
$_e_[2]->add($_e_[8]);
$_e_[2]->add($_e_[9]);
$_e_[10] = $_b_->build('title');
$_e_[11] = $_b_->build('text_content');
$_e_[11]->set('text', 'Spitzen wein | spitzenwein.ch');
$_e_[10]->add($_e_[11]);
$_e_[12] = $_b_->build('links');
$_e_[13] = $_b_->build('link');
$_e_[13]->set('rel', 'Shortcut icon');
$_e_[13]->set('href', '/image/favicon.ico');
$_e_[13]->set('type', 'image/x-icon');
$_e_[14] = $_b_->build('link');
$_e_[14]->set('rel', 'stylesheet');
$_e_[14]->set('type', 'text/css');
$_e_[14]->set('href', '/styles/layout.css');
$_e_[15] = $_b_->build('link');
$_e_[15]->set('rel', 'stylesheet');
$_e_[15]->set('type', 'text/css');
$_e_[15]->set('href', '/styles/style.css');
$_e_[12]->set('eid', 'links');
$_e_[12]->add($_e_[13]);
$_e_[12]->add($_e_[14]);
$_e_[12]->add($_e_[15]);
$_e_[16] = $_b_->build('scripts');
$_e_[17] = $_b_->build('script');
$_e_[17]->set('type', 'text/javascript');
$_e_[17]->set('src', '/js/utils.js');
$_e_[17]->set('space', 'preserve');
$_e_[18] = $_b_->build('script');
$_e_[18]->set('type', 'text/javascript');
$_e_[18]->set('src', '/js/jquery.js');
$_e_[18]->set('space', 'preserve');
$_e_[16]->set('eid', 'scripts');
$_e_[16]->add($_e_[17]);
$_e_[16]->add($_e_[18]);
$_e_[1]->add($_e_[2]);
$_e_[1]->add($_e_[10]);
$_e_[1]->add($_e_[12]);
$_e_[1]->add($_e_[16]);
$_e_[0]->add($_e_[1]);
?>