<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';

require_once 'include/kiwi_product_copy.class.php';

$test = new Kiwi_Product_Copy(2);
echo $test->getCopyPID();
?>