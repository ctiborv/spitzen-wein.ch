<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('require');
$_e_[2] = $_b_->build('javascript');
$_e_[2]->set('src', '/js/jquery.corner.js');
$_e_[3] = $_b_->build('javascript');
$_e_[3]->set('src', '/js/jquery.corner.ready.js');
$_e_[1]->add($_e_[2]);
$_e_[1]->add($_e_[3]);
$_e_[4] = $_b_->build('div');
$_e_[5] = $_b_->build('ul');
$_e_[6] = $_b_->build('li');
$_e_[7] = $_b_->build('flow');
$_e_[8] = $_b_->build('navpoint');
$_e_[9] = $_b_->build('text_content');
$_e_[9]->set('text', 'Home');
$_e_[8]->set('page', 'úvod');
$_e_[8]->add($_e_[9]);
$_e_[10] = $_b_->build('span');
$_e_[11] = $_b_->build('text_content');
$_e_[11]->set('text', 'Home');
$_e_[10]->add($_e_[11]);
$_e_[7]->set('eid', 'menuitem');
$_e_[7]->set('specification', 'úvod');
$_e_[7]->add($_e_[8]);
$_e_[7]->add($_e_[10]);
$_e_[6]->set('class', 'libtn');
$_e_[6]->add($_e_[7]);
$_e_[12] = $_b_->build('li');
$_e_[13] = $_b_->build('flow');
$_e_[14] = $_b_->build('navpoint');
$_e_[15] = $_b_->build('text_content');
$_e_[15]->set('text', 'Weinsortiment');
$_e_[14]->set('page', 'katalog');
$_e_[14]->add($_e_[15]);
$_e_[16] = $_b_->build('span');
$_e_[17] = $_b_->build('text_content');
$_e_[17]->set('text', 'Weinsortiment');
$_e_[16]->add($_e_[17]);
$_e_[13]->set('eid', 'menuitem');
$_e_[13]->set('specification', 'katalog');
$_e_[13]->add($_e_[14]);
$_e_[13]->add($_e_[16]);
$_e_[12]->set('class', 'libtn');
$_e_[12]->add($_e_[13]);
$_e_[18] = $_b_->build('li');
$_e_[19] = $_b_->build('flow');
$_e_[20] = $_b_->build('navpoint');
$_e_[21] = $_b_->build('text_content');
$_e_[21]->set('text', 'Über uns');
$_e_[20]->set('page', 'o nás');
$_e_[20]->add($_e_[21]);
$_e_[22] = $_b_->build('span');
$_e_[23] = $_b_->build('text_content');
$_e_[23]->set('text', 'Über uns');
$_e_[22]->add($_e_[23]);
$_e_[19]->set('eid', 'menuitem');
$_e_[19]->set('specification', 'o nás');
$_e_[19]->add($_e_[20]);
$_e_[19]->add($_e_[22]);
$_e_[18]->set('class', 'libtn');
$_e_[18]->add($_e_[19]);
$_e_[24] = $_b_->build('li');
$_e_[25] = $_b_->build('flow');
$_e_[26] = $_b_->build('navpoint');
$_e_[27] = $_b_->build('text_content');
$_e_[27]->set('text', 'Unsere Filialen');
$_e_[26]->set('page', 'prodejny');
$_e_[26]->add($_e_[27]);
$_e_[28] = $_b_->build('span');
$_e_[29] = $_b_->build('text_content');
$_e_[29]->set('text', 'Unsere Filialen');
$_e_[28]->add($_e_[29]);
$_e_[25]->set('eid', 'menuitem');
$_e_[25]->set('specification', 'prodejny');
$_e_[25]->add($_e_[26]);
$_e_[25]->add($_e_[28]);
$_e_[24]->set('class', 'libtn');
$_e_[24]->add($_e_[25]);
$_e_[30] = $_b_->build('li');
$_e_[31] = $_b_->build('flow');
$_e_[32] = $_b_->build('navpoint');
$_e_[33] = $_b_->build('text_content');
$_e_[33]->set('text', 'Kontaktformular');
$_e_[32]->set('page', 'kontakty');
$_e_[32]->add($_e_[33]);
$_e_[34] = $_b_->build('span');
$_e_[35] = $_b_->build('text_content');
$_e_[35]->set('text', 'Kontaktformular');
$_e_[34]->add($_e_[35]);
$_e_[31]->set('eid', 'menuitem');
$_e_[31]->set('specification', 'kontakty');
$_e_[31]->add($_e_[32]);
$_e_[31]->add($_e_[34]);
$_e_[30]->set('class', 'libtn');
$_e_[30]->add($_e_[31]);
$_e_[5]->add($_e_[6]);
$_e_[5]->add($_e_[12]);
$_e_[5]->add($_e_[18]);
$_e_[5]->add($_e_[24]);
$_e_[5]->add($_e_[30]);
$_e_[36] = $_b_->build('br');
$_e_[36]->set('class', 'clear');
$_e_[4]->set('class', 'hlavni-menu');
$_e_[4]->add($_e_[5]);
$_e_[4]->add($_e_[36]);
$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[4]);
?>