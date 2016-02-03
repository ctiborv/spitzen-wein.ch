<?php
// @TODO otestovat
class Newsletters_CronJob
{
	private $_started = 0;
	private $_finished = 0;

	private $_sent = 0;
	private $_failures = 0;

	private $_messageBuilder;

	public function __construct(Message_Builder $builder)
	{
		$this->_messageBuilder = $builder;
	}

	public function getStarted()
	{
		return $this->_started;
	}

	public function getFinished()
	{
		return $this->_finished;
	}

	public function getProcessed()
	{
		return $this->_sent + $this->_failures;
	}

	public function getSent()
	{
		return $this->_sent;
	}

	public function getFailures()
	{
		return $this->_failures;
	}

	public function resetCounter()
	{
		$this->_sent = $this->_failures = 0;
	}

	public function startPendingNewsletters()
	{
		return $this->_started = Newsletters::startPending();
	}

	public function send($max)
	{
		if ($max < 1) return 0;
		$processed = 0;

		$pendingRecords = Newsletters::getPendingRecords();

		$dbh = Project_DB::get();

		foreach ($pendingRecords as $record) {
			$record['Products'] = Newsletters::getProducts($record['ID']);
			$processed += $this->sendNewsletter($record, $max - $processed);
			if ($processed >= $max) return $processed;
		}

		return $processed;
	}

	protected function sendNewsletter($newsletter, $max)
	{
		$dbh = Project_DB::get();

		$sql_params['subscribedStatus'] = Newsletters::SUBSCRIBED;
		$sql_params['lastEmailId'] = $newsletter['LastEmailID'];
		$sql_params['max'] = $max;
		$query = 'SELECT SQL_CALC_FOUND_ROWS ID, ClientID, Email, Code FROM nlemails WHERE Status=:subscribedStatus AND ID>:lastEmailID ORDER BY ID LIMIT :max';

		$stmt = $dbh->prepare($query);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$result = $stmt->execute();
		if (!$result) return 0;

		$leftToProcess = 0;

		$query2 = "SELECT FOUND_ROWS()";
		$stmt2 = $dbh->prepare($query2);
		if ($stmt2->execute())
			if ($row = $stmt2->fetch(PDO::FETCH_NUM))
				$leftToProcess = (int) $row[0];

		$processed = 0;
		$failures = 0;
		$sent = 0;
		while ($emailRecord = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($this->sendNewsletterMailToEmail($newsletter, $emailRecord)) {
				$sent += 1;
			} else {
				$failures += 1;
			}
			$processed += 1;
			$lastEmailId = $emailRecord['ID'];
		}

		$this->_sent += $sent;
		$this->_failures += $failures;

		if ($processed > 0) {

			$assignments = array();
			$assignments[] = 'LastEmailID=:lastEmailId';
			$sql_params = array(
				'lastEmailId' => $newsletter['LastEmailID'],
				'id' => $newsletter['ID']
			);

			if ($sent > 0) {
				$assignments[] = "Sent=Sent+$sent";
			}

			if ($failures > 0) {
				$assignments[] = "Failures=Failures+$failures";
			}

			$assignments_sql = implode(', ', $assignments);

			$query = "UPDATE newsletters SET $assignments_sql WHERE ID=:id";

			$stmt = $dbh->prepare($query);
			foreach ($sql_params as $spk => $spv)
				$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

			$stmt->execute();
		}

		if ($leftToProcess === $processed) {

			$sql_params = array(
				'done' => Newsletters::STATUS_DONE,
				'id' => $newsletter['ID']
			);

			$query = "UPDATE newsletters SET Status=:done, Finished=NOW() WHERE ID=:id";

			$stmt = $dbh->prepare($query);
			foreach ($sql_params as $spk => $spv)
				$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

			$stmt->execute();

			$this->_finished += 1;
		}

		return $processed;
	}

	protected function sendNewsletterMailToEmail($newsletter, $emailRecord)
	{
		global $config;
		$from_email = $config['eshop']['email'];
		$client = $this->resolveClient($emailRecord);
		$message = $this->_messageBuilder->build(array(
			'newsletter' => $newsletter,
			'client' => $client,
		));
		$target_email = $emailRecord['Email'];
		$title = htmlspecialchars($newsletter['Title']);

		$headers =
			"From: $from_email\n" .
			"Return-Path: $from_email\n" .
			"Reply-to: $from_email\n" .
			"MIME-Version: 1.0\n" .
			"Content-Type: text/html; charset=utf-8\n";

		$encoded_subject = '=?UTF-8?B?' . base64_encode($newsletter['Title']) . '?=';

		$success = mail($target_email, $encoded_subject, $message, $headers);

		//echo "Sending message with title '$title' was just sent to: " . $target_email . ' ... ' . ($success ? 'success' : 'failure') . "<br />\n"; // DEBUG

		return $success;
	}

	protected function resolveClient($emailRecord)
	{
		// @TODO will need update if new client scheme (one supported by Kiwi_Client class) is used
		if ($emailRecord['ClientID']) {

			$dbh = Project_DB::get();

			$query = 'SELECT ID, FirstName, SurName, Salutation, Title, Email, FirmEmail, BusinessName FROM eshopclients WHERE ID=:id';

			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":id", $emailRecord['ClientID'], PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
		} else {

			$row = array();
		}

		$row['Code'] = $emailRecord['Code'];

		return $row;
	}
}
