<?php
require_once 'include/essentials.inc.php';

$id = isset($_GET['nl']) ? $_GET['nl'] : NULL;
if (!$id) die();
$builder = new Custom_Newsletter_Message_Builder('katalog');
$preview = new Newsletter_Preview($id, $builder);
echo $preview->render();
