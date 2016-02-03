<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('group');
$_e_[2] = $_b_->build('flow');
$_e_[3] = $_b_->build('div');
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('datetime');
$_e_[5]->set('eid', 'datum');
$_e_[5]->set('template', '/datetime/teloL-aktuality');
$_e_[6] = $_b_->build('h3');
$_e_[7] = $_b_->build('inline');
$_e_[8] = $_b_->build('a');
$_e_[9] = $_b_->build('text');
$_e_[9]->set('eid', 'nadpis');
$_e_[8]->set('eid', 'detail');
$_e_[8]->set('title', 'weiter lesen');
$_e_[8]->set('shape', 'rect');
$_e_[8]->add($_e_[9]);
$_e_[7]->set('eid', 'ma_detail');
$_e_[7]->add($_e_[8]);
$_e_[10] = $_b_->build('inline');
$_e_[11] = $_b_->build('span');
$_e_[12] = $_b_->build('text');
$_e_[12]->set('eid', 'nadpis');
$_e_[11]->add($_e_[12]);
$_e_[10]->set('eid', 'nema_detail');
$_e_[10]->add($_e_[11]);
$_e_[6]->add($_e_[7]);
$_e_[6]->add($_e_[10]);
$_e_[13] = $_b_->build('br');
$_e_[13]->set('class', 'clear');
$_e_[4]->add($_e_[5]);
$_e_[4]->add($_e_[6]);
$_e_[4]->add($_e_[13]);
$_e_[14] = $_b_->build('div');
$_e_[15] = $_b_->build('inline');
$_e_[15]->set('eid', 'obsah');
$_e_[14]->set('class', 'news-obsah');
$_e_[14]->add($_e_[15]);
$_e_[16] = $_b_->build('br');
$_e_[16]->set('class', 'clear');
$_e_[17] = $_b_->build('div');
$_e_[18] = $_b_->build('span');
$_e_[19] = $_b_->build('text_content');
$_e_[19]->set('text', 'Autor: ');
$_e_[20] = $_b_->build('strong');
$_e_[21] = $_b_->build('text');
$_e_[21]->set('eid', 'autor');
$_e_[20]->add($_e_[21]);
$_e_[18]->set('class', 'autor');
$_e_[18]->add($_e_[19]);
$_e_[18]->add($_e_[20]);
$_e_[22] = $_b_->build('span');
$_e_[23] = $_b_->build('inline');
$_e_[24] = $_b_->build('a');
$_e_[25] = $_b_->build('text_content');
$_e_[25]->set('text', 'Weiter lesen');
$_e_[24]->set('eid', 'detail');
$_e_[24]->set('shape', 'rect');
$_e_[24]->add($_e_[25]);
$_e_[23]->set('eid', 'ma_detail');
$_e_[23]->add($_e_[24]);
$_e_[22]->set('class', 'odkaz');
$_e_[22]->add($_e_[23]);
$_e_[17]->set('class', 'news-lista');
$_e_[17]->add($_e_[18]);
$_e_[17]->add($_e_[22]);
$_e_[3]->set('class', 'news');
$_e_[3]->add($_e_[4]);
$_e_[3]->add($_e_[14]);
$_e_[3]->add($_e_[16]);
$_e_[3]->add($_e_[17]);
$_e_[2]->set('eid', 'novinka_vzor');
$_e_[2]->add($_e_[3]);
$_e_[1]->set('active', '0');
$_e_[1]->add($_e_[2]);
$_e_[26] = $_b_->build('div');
$_e_[27] = $_b_->build('h2');
$_e_[28] = $_b_->build('text_content');
$_e_[28]->set('text', 'Neuheiten');
$_e_[27]->add($_e_[28]);
$_e_[29] = $_b_->build('pagination');
$_e_[29]->set('eid', 'horni_paginace');
$_e_[29]->set('vid', 'paginace');
$_e_[29]->set('case', 'top');
$_e_[29]->set('radius', '2');
$_e_[29]->set('template', 'pagination/teloL-aktuality');
$_e_[30] = $_b_->build('flow');
$_e_[30]->set('eid', 'zadne_novinky');
$_e_[31] = $_b_->build('flow');
$_e_[31]->set('eid', 'prazdna_stranka');
$_e_[32] = $_b_->build('flow');
$_e_[32]->set('eid', 'novinky');
$_e_[33] = $_b_->build('pagination');
$_e_[33]->set('eid', 'dolni_paginace');
$_e_[33]->set('vid', 'paginace');
$_e_[33]->set('case', 'bottom');
$_e_[26]->set('class', 'teloP-news');
$_e_[26]->add($_e_[27]);
$_e_[26]->add($_e_[29]);
$_e_[26]->add($_e_[30]);
$_e_[26]->add($_e_[31]);
$_e_[26]->add($_e_[32]);
$_e_[26]->add($_e_[33]);
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[26]);
?>