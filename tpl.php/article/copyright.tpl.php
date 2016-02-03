<?php
$_e_[0] = $_b_->build('group');
$_e_[1] = $_b_->build('div');
$_e_[2] = $_b_->build('div');
$_e_[3] = $_b_->build('div');
$_e_[4] = $_b_->build('a');
$_e_[5] = $_b_->build('text_content');
$_e_[5]->set('text', '© Spitzen-Wein');
$_e_[4]->set('href', 'mailto:info@spitzen-wein.ch');
$_e_[4]->set('title', 'E-Mail senden');
$_e_[4]->set('shape', 'rect');
$_e_[4]->add($_e_[5]);
$_e_[3]->set('class', 'copyrightL span6');
$_e_[3]->add($_e_[4]);
$_e_[6] = $_b_->build('div');
$_e_[7] = $_b_->build('text_content');
$_e_[7]->set('text', '
 ');
$_e_[8] = $_b_->build('a');
$_e_[9] = $_b_->build('text_content');
$_e_[9]->set('text', ' ');
$_e_[8]->set('href', ' ');
$_e_[8]->set('title', 'Webdesign, SEO, e-shop, e-commerce');
$_e_[8]->set('shape', 'rect');
$_e_[8]->add($_e_[9]);
$_e_[6]->set('class', 'copyrightP span6');
$_e_[6]->add($_e_[7]);
$_e_[6]->add($_e_[8]);
$_e_[2]->set('class', 'copyright');
$_e_[2]->add($_e_[3]);
$_e_[2]->add($_e_[6]);
$_e_[1]->set('class', 'copyrightV');
$_e_[1]->add($_e_[2]);
$_e_[10] = $_b_->build('script');
$_e_[11] = $_b_->build('text_content');
$_e_[11]->set('text', '
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':

new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],

j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=

\'//www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);

})(window,document,\'script\',\'dataLayer\',\'GTM-N78X5G\');

');
$_e_[10]->set('type', 'text/javascript');
$_e_[10]->set('space', 'preserve');
$_e_[10]->add($_e_[11]);

$_e_[12] = $_b_->build('noscript');
$_e_[13] = $_b_->build('div');
$_e_[14] = $_b_->build('text_content');
$_e_[14]->set('text', '
<iframe src="//www.googletagmanager.com/ns.html?id=GTM-N78X5G"

height="0" width="0" style="display:none;visibility:hidden"></iframe>
');
$_e_[14]->set('raw', TRUE);
$_e_[13]->add($_e_[14],FALSE);
$_e_[12]->add($_e_[13]);


$_e_[0]->add($_e_[1]);
$_e_[0]->add($_e_[12]);
$_e_[0]->add($_e_[10]);
?>