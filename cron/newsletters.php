<?php
define ('MAX_MESSAGES_PER_CRONJOB', 50);
define ('PASSWORD', 'zukm5pcl');

if (!isset($_GET['pwd']) || $_GET['pwd'] !== PASSWORD) die();

$lockFile = 'locks/' . basename(__FILE__) . '.lock';
$lock = fopen($lockFile, 'c');
chmod($lockFile, 0666);

if (!flock($lock, LOCK_EX | LOCK_NB)) {
	fclose($lock);
	die('Already running...');
}

require_once '../include/essentials.inc.php';

function message($text)
{
	$html = str_replace("\n", "<br />\n", $text);
	echo $html . "<br />\n";
}

$messageBuilder = new Custom_Newsletter_Message_Builder;
$cronJob = new Newsletters_CronJob($messageBuilder);

echo <<<EOT
<html>
<body>

EOT;

$started = $cronJob->startPendingNewsletters();
message('Newsletters started: ' . $started);

$processed = $cronJob->send(MAX_MESSAGES_PER_CRONJOB);
$failures = $cronJob->getFailures();
$sent = $cronJob->getSent();
message('Newsletter e-mails sent: ' . $sent);
if ($failures > 0) {
	message('Failures encountered: ' . $failures);
}

$finished = $cronJob->getFinished();
message('Newsletters finished: ' . $finished);


echo <<<EOT
</body>
</html>
EOT;

flock($lock, LOCK_UN);
fclose($lock);
