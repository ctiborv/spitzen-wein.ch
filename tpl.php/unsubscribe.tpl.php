<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('body');
$_e_[2] = $_b_->build('div');
$_e_[3] = $_b_->build('div');
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('div');
$_e_[6] = $_b_->build('article');
$_e_[6]->set('template', 'article/logo');
$_e_[5]->set('class', 'horniL span6');
$_e_[5]->add($_e_[6]);
$_e_[7] = $_b_->build('div');
$_e_[7]->set('class', 'horniP span6');
$_e_[8] = $_b_->build('navmenu');
$_e_[8]->set('template', 'navmenu/hlavni-menu');
$_e_[8]->set('autodetect', 'on');
$_e_[4]->set('class', 'horni span12');
$_e_[4]->add($_e_[5]);
$_e_[4]->add($_e_[7]);
$_e_[4]->add($_e_[8]);
$_e_[3]->set('class', 'horniV');
$_e_[3]->add($_e_[4]);
$_e_[2]->set('class', 'horniBG');
$_e_[2]->add($_e_[3]);
$_e_[9] = $_b_->build('div');
$_e_[10] = $_b_->build('div');
$_e_[11] = $_b_->build('div');
$_e_[12] = $_b_->build('div');
$_e_[12]->set('class', 'teloS span12');
$_e_[13] = $_b_->build('div');
$_e_[14] = $_b_->build('article');
$_e_[14]->set('template', 'search/vyhledavani');
$_e_[15] = $_b_->build('news');
$_e_[15]->set('mid', '5');
$_e_[15]->set('template', 'news/uvod-aktuality');
$_e_[15]->set('images', 'lightbox');
$_e_[15]->set('detailpage', 'aktuality detail');
$_e_[15]->set('ns', 'aktuality');
$_e_[15]->set('nsd', '');
$_e_[15]->set('active', '1');
$_e_[16] = $_b_->build('div');
$_e_[17] = $_b_->build('h2');
$_e_[18] = $_b_->build('text_content');
$_e_[18]->set('text', 'Weinsortiment Katalog');
$_e_[17]->add($_e_[18]);
$_e_[19] = $_b_->build('div');
$_e_[20] = $_b_->build('div');
$_e_[21] = $_b_->build('catalog');
$_e_[21]->set('vid', 'es');
$_e_[21]->set('case', 'menu');
$_e_[21]->set('name', 'katalog');
$_e_[21]->set('template', 'catalog/katalog');
$_e_[21]->set('width', '3');
$_e_[21]->set('rows', '3');
$_e_[21]->set('images', 'lightbox');
$_e_[21]->set('active', '1');
$_e_[20]->set('class', 'katalog-list');
$_e_[20]->add($_e_[21]);
$_e_[19]->set('class', 'katalog-listV');
$_e_[19]->add($_e_[20]);
$_e_[16]->set('class', 'katalog-menu');
$_e_[16]->add($_e_[17]);
$_e_[16]->add($_e_[19]);
$_e_[22] = $_b_->build('newslettersubscriber');
$_e_[22]->set('ns', 'test');
$_e_[22]->set('template', 'newslettersubscriber/ch');
$_e_[22]->set('successPage', 'newsletter');
$_e_[23] = $_b_->build('article');
$_e_[23]->set('mid', '6');
$_e_[23]->set('template', 'article/teloL-bannery');
$_e_[13]->set('class', 'teloL span3');
$_e_[13]->add($_e_[14]);
$_e_[13]->add($_e_[15]);
$_e_[13]->add($_e_[16]);
$_e_[13]->add($_e_[22]);
$_e_[13]->add($_e_[23]);
$_e_[24] = $_b_->build('div');
$_e_[25] = $_b_->build('newsletterunsubscriber');
$_e_[25]->set('template', 'newsletterunsubscriber/ch');
$_e_[24]->set('class', 'teloP span9');
$_e_[24]->add($_e_[25]);
$_e_[26] = $_b_->build('br');
$_e_[26]->set('class', 'clear');
$_e_[11]->set('class', 'telo');
$_e_[11]->add($_e_[12]);
$_e_[11]->add($_e_[13]);
$_e_[11]->add($_e_[24]);
$_e_[11]->add($_e_[26]);
$_e_[10]->set('class', 'teloV');
$_e_[10]->add($_e_[11]);
$_e_[9]->set('class', 'teloBG');
$_e_[9]->add($_e_[10]);
$_e_[27] = $_b_->build('article');
$_e_[27]->set('template', 'article/spodni');
$_e_[28] = $_b_->build('article');
$_e_[28]->set('template', 'article/copyright');
$_e_[1]->set('id', 'peak');
$_e_[1]->set('class', 'bv0');
$_e_[1]->add($_e_[2]);
$_e_[9]->set('comment', array('test'));
$_e_[1]->add($_e_[9]);
$_e_[1]->add($_e_[27]);
$_e_[1]->add($_e_[28]);
$_e_[0]->add($_e_[1]);
?>