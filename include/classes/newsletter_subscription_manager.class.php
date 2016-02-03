<?php
class Newsletter_Subscription_Manager
{
	protected $_resubscriptionMessageBuilder;

	// subscription statuses
	const STATUS_NOTSUBSCRIBED = 0,
		STATUS_SUBSCRIBED = 1,
		STATUS_UNSUBSCRIBED = 2,
		STATUS_BLOCKED = -1;

	// unsubscription/subscription return values
	const UNSUBSCRIBED = 1,
		DATA_INSUFFICIENT = 2,
		NOT_FOUND = 3,
		NOT_SUBSCRIBED = 4,
		ALREADY_UNSUBSCRIBED = 5,
		SUBSCRIBED = 6,
		ALREADY_SUBSCRIBED = 7,
		BLOCKED = 8,
		RESUBSCRIPTION_MAIL_SENT = 9,
		RESUBSCRIPTION_MAIL_ALREADY_SENT = 10,
		UNKNOWN_ERROR = 11;

	public function setResubscriptionMessageBuilder(Message_Builder $resubscriptionMessageBuilder)
	{
		$this->_resubscriptionMessageBuilder = $resubscriptionMessageBuilder;
	}

	public function newClient($clientId, $email)
	{
		$dbh = Project_DB::get();

		$cleanupSql = 'DELETE FROM nlemails WHERE ClientID=:clientId';
		$stmt = $dbh->prepare($cleanupSql);
		$stmt->bindValue(':clientId', (int) $clientId, PDO::PARAM_INT);
		$stmt->execute();

		$lookupSql = 'SELECT * FROM nlemails WHERE Email=:email';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();

		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id = (int) $row['ID'];
			$updateSql = 'UPDATE nlemails SET ClientID=:clientId WHERE ID=:id';
			$stmt = $dbh->prepare($updateSql);
			$stmt->bindValue(':clientId', (int) $clientId, PDO::PARAM_INT);
			$stmt->bindValue(':id', (int) $row['ID'], PDO::PARAM_INT);
			$stmt->execute();

			$cleanup2Sql = 'DELETE FROM nlemails WHERE Email=:email AND ID!=:id';
			$stmt = $dbh->prepare($cleanup2Sql);
			$stmt->bindValue(':email', $email, PDO::PARAM_STR);
			$stmt->bindValue(':id', (int) $row['ID'], PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	public function updateClient($clientId, $email)
	{
		$dbh = Project_DB::get();

/*
		$stmt = $dbh->prepare($cleanupSql);
		$stmt->bindValue(':clientId', (int) $clientId, PDO::PARAM_INT);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
*/

		$lookupSql = 'SELECT * FROM nlemails WHERE ClientID=:clientId';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':clientId', (int) $clientId, PDO::PARAM_INT);
		$stmt->execute();

		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($row['Email'] == $email) return;
			$updateSql = 'UPDATE nlemails SET Email=:email WHERE ID=:id';
			$stmt = $dbh->prepare($updateSql);
			$stmt->bindValue(':email', $email, PDO::PARAM_STR);
			$stmt->bindValue(':id', (int) $row['ID'], PDO::PARAM_INT);
			$stmt->execute();
			return;
		}

		$lookupSql = 'SELECT * FROM nlemails WHERE Email=:email';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();

		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($row['ClientID']) throw new Exception('E-mail address already belongs to another client');
			$updateSql = 'UPDATE nlemails SET ClientID=:clientId WHERE ID=:id';
			$stmt = $dbh->prepare($updateSql);
			$stmt->bindValue(':clientId', (int) $clientId, PDO::PARAM_INT);
			$stmt->bindValue(':id', (int) $row['ID'], PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	public function subscribe($clientId, $email)
	{
		$dbh = Project_DB::get();

		$status = self::STATUS_SUBSCRIBED;
		$lookupSql = 'SELECT * FROM nlemails WHERE Email=:email';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();

		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id = (int) $row['ID'];
			if ($row['Status'] == self::STATUS_NOTSUBSCRIBED || ($row['Status'] == self::STATUS_UNSUBSCRIBED && $clientId !== NULL)) {
				$updateSql = 'UPDATE nlemails SET Status=:status WHERE ID=:id';
				$stmt = $dbh->prepare($updateSql);
				$stmt->bindValue(':status', $status, PDO::PARAM_INT);
				$stmt->bindValue(':id', $id, PDO::PARAM_INT);
				if ($stmt->execute()) {
					$this->createNewCode($id);
				}
				return self::SUBSCRIBED;
			} elseif ($row['Status'] == self::STATUS_UNSUBSCRIBED) {
				if ($row['Code']) {
					return self::RESUBSCRIPTION_MAIL_ALREADY_SENT;
				} else {
					$code = $this->createNewCode($id);
					$this->sendResubscriptionMail($clientId, $email, $code);
					return self::RESUBSCRIPTION_MAIL_SENT;
				}
			} elseif ($row['Status'] == self::STATUS_SUBSCRIBED) {
				return self::ALREADY_SUBSCRIBED;
			} elseif ($row['Status'] == self::STATUS_BLOCKED) {
				return self::BLOCKED;
			} else {
				throw new Exception('Unexpected status code.');
			}
		} else {
			$sql_params = array();
			$sql_params['email'] = $email;
			$sql_params['status'] = $status;
			if ($clientId) {
				$insertSql = 'INSERT INTO nlemails(ClientID, Email, Status) VALUES (:clientId, :email, :status)';
				$sql_params['clientId'] = (int) $clientId;
			} else {
				$client = $this->getClientFromEmail($email);
				if ($client) {
					$insertSql = 'INSERT INTO nlemails(ClientID, Email, Status) VALUES (:clientId, :email, :status)';
					$sql_params['clientId'] = (int) $client['ID'];
				} else {
					$insertSql = 'INSERT INTO nlemails(Email, Status) VALUES (:email, :status)';
				}
			}

			$stmt = $dbh->prepare($insertSql);
			foreach ($sql_params as $spk => $spv)
				$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

			if ($stmt->execute()) {
				$id = $dbh->lastInsertId();
				$this->createNewCode($id);
				return self::SUBSCRIBED;
			}

			return self::UNKNOWN_ERROR;
		}
	}

	public function unsubscribe($clientId, $email = NULL, $code = NULL)
	{
		$dbh = Project_DB::get();

		$where = array();
		$sql_params = array();
		if ($clientId) {
			$sql_params['clientId'] = (int) $clientId;
			$where[] = 'ClientID=:clientId';
		}

		if ($email) {
			$sql_params['email'] = $email;
			$where[] = 'Email=:email';
		}

		if ($code) {
			$sql_params['code'] = $code;
			$where[] = 'Code=:code';
		}

		if (empty($where)) {
			return self::DATA_INSUFFICIENT;
		}

		$whereSql = implode(' AND ', $where);
		$sql = "SELECT ID, Status FROM nlemails WHERE $whereSql";
		$stmt = $dbh->prepare($sql);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return self::NOT_FOUND;
		}

		if ($row['Status'] == self::STATUS_NOTSUBSCRIBED) {
			return self::NOT_SUBSCRIBED;
		}

		if ($row['Status'] == self::STATUS_UNSUBSCRIBED) {
			return self::ALREADY_UNSUBSCRIBED;
		}

		if ($row['Status'] == self::STATUS_BLOCKED) {
			return self::ALREADY_UNSUBSCRIBED;
		}

		$status = self::STATUS_UNSUBSCRIBED;
		$updateSql = 'UPDATE nlemails SET Status=:status, Code=NULL WHERE ID=:id';
		$stmt = $dbh->prepare($updateSql);
		$stmt->bindValue(':status', $status, PDO::PARAM_INT);
		$stmt->bindValue(':id', (int) $row['ID'], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return self::UNSUBSCRIBED;
		}

		return self::UNKNOWN_ERROR;
	}

	public function resubscribe($clientId, $email = NULL, $code = NULL)
	{
		$dbh = Project_DB::get();

		$where = array();
		$sql_params = array();
		if ($clientId) {
			$sql_params['clientId'] = (int) $clientId;
			$where[] = 'ClientID=:clientId';
		}

		if ($email) {
			$sql_params['email'] = $email;
			$where[] = 'Email=:email';
		}

		if ($code) {
			$sql_params['code'] = $code;
			$where[] = 'Code=:code';
		}

		if (empty($where)) {
			return self::DATA_INSUFFICIENT;
		}

		$whereSql = implode(' AND ', $where);
		$sql = "SELECT ID, Status FROM nlemails WHERE $whereSql";
		$stmt = $dbh->prepare($sql);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return self::NOT_FOUND;
		}

		if ($row['Status'] == self::STATUS_SUBSCRIBED) {
			return self::ALREADY_SUBSCRIBED;
		}

		if ($row['Status'] == self::STATUS_BLOCKED) {
			return self::BLOCKED;
		}

		$status = self::STATUS_SUBSCRIBED;
		$updateSql = 'UPDATE nlemails SET Status=:status WHERE ID=:id';
		$stmt = $dbh->prepare($updateSql);
		$stmt->bindValue(':status', $status, PDO::PARAM_INT);
		$stmt->bindValue(':id', $id = (int) $row['ID'], PDO::PARAM_INT);

		if ($stmt->execute()) {
			$this->createNewCode($id);
			return self::SUBSCRIBED;
		}

		return self::UNKNOWN_ERROR;
	}

	protected function createNewCode($id)
	{
		$dbh = Project_DB::get();

		$newCodeSql = "UPDATE nlemails SET Code=:code WHERE ID=:id";
		$stmt = $dbh->prepare($newCodeSql);
		$stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
		for ($attempts = 0, $maxAttempts = 50; $attempts < $maxAttempts; ++$attempts) {
			$code = $this->generateCode();
			$stmt->bindValue(':code', $code, PDO::PARAM_STR);
			if ($stmt->execute()) return $code;
		}

		throw new Exception('Failed to create unique random code.');
	}

	protected function getConfigValue($key)
	{
		$conf = new Project_Config;
		if (isset($conf->newsletters, $conf->newsletters[$key])) return $conf->newsletters[$key];
		return isset($config->$key) ? $config->$key : NULL;
	}

	protected function sendResubscriptionMail($clientId, $email, $code)
	{
		if (!$this->_resubscriptionMessageBuilder) {
			throw new Exception('Resubscription message builder not set!');
		}

		$from_email = $this->getConfigValue('contact_email');
		$client = $this->getClientFromID($clientId);
		$client['Code'] = $code;

		$message = $this->_resubscriptionMessageBuilder->build(array(
			'client' => $client,
		));

		$headers =
			"From: $from_email\n" .
			"Return-Path: $from_email\n" .
			"Reply-to: $from_email\n" .
			"MIME-Version: 1.0\n" .
			"Content-Type: text/html; charset=utf-8\n";

		$subject = 'Newsletter subscription';
		$encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

		$success = mail($email, $encoded_subject, $message, $headers);
		return $success;
	}

	protected function getClientFromID($clientId)
	{
		$dbh = Project_DB::get();

		$lookupSql = 'SELECT ID, FirstName, SurName, Salutation, Title, Email, FirmEmail, BusinessName FROM eshopclients WHERE ID=:clientId';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':clientId', $clientId, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
	}

	protected function getClientFromEmail($email)
	{
		$dbh = Project_DB::get();

		$lookupSql = 'SELECT ID, FirstName, SurName, Salutation, Title, Email, FirmEmail, BusinessName FROM eshopclients WHERE Email=:email OR FirmEmail=:firmEmail';
		$stmt = $dbh->prepare($lookupSql);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->bindValue(':firmEmail', $email, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
	}

	protected function generateCode()
	{
		return self::randomString(16);
	}

	/**
	 * Generate random string.
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function randomString($length = 10, $charlist = '0-9a-z')
	{
		$charlist = str_shuffle(preg_replace_callback('#.-.#', array(__CLASS__, 'rangeHelperCallback'), $charlist));
		$chLen = strlen($charlist);

		$s = '';
		for ($i = 0; $i < $length; $i++) {
			if ($i % 5 === 0) {
				$rand = lcg_value();
				$rand2 = microtime(TRUE);
			}
			$rand *= $chLen;
			$s .= $charlist[($rand + $rand2) % $chLen];
			$rand -= (int) $rand;
		}
		return $s;
	}

	public static function rangeHelperCallback($m)
	{
		return implode('', range($m[0][0], $m[0][2]));
	}
}
