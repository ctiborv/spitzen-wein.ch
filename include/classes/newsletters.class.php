<?php
class Newsletters
{
	const STATUS_WAITING = 0,
		STATUS_STARTED = 1,
		STATUS_DONE = 2,
		STATUS_CANCELLED = 3;

	const SUBSCRIBED = 1;

	public static function get($id)
	{
		if (!($record = self::getRecord($id))) {
			return FALSE;
		}
		$record['Products'] = self::getProducts($id);
		return $record;
	}

	public static function getPendingRecords()
	{
		$dbh = Project_DB::get();

		$sql_params['started'] = self::STATUS_STARTED;
		$sql_params['active'] = 1;
		$query = 'SELECT * FROM newsletters WHERE Status=:started AND Active=:active';

		$stmt = $dbh->prepare($query);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$result = $stmt->execute();
		if (!$result) return array();

		$records = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$records[] = $row;
		}

		return $records;
	}

	public static function getRecord($id)
	{
		$dbh = Project_DB::get();

		$query = 'SELECT * FROM newsletters WHERE ID=:id';

		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":id", (int) $id, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function getProducts($id)
	{
		$conf = new Project_Config;
		$newsletters_config = $conf->newsletters;

		if (!isset($newsletters_config['top_groups'])) {
			throw new Config_Exception('The top_groups field not specified in newsletters config.');
		}

		$top_groups = $newsletters_config['top_groups'];

		if (!is_array($top_groups) || empty($top_groups)) {
			throw new Config_Exception('Invalid top_groups field in newsletters config.');
		}

		$top_groups_sql = implode(',', $top_groups);

		$dbh = Project_DB::get();

		$query = "SELECT PB.ID, PB.PID, P.Title, P.ShortDesc, P.URL, P.OriginalCost, P.NewCost, P.Photo, P.Discount, P.Action, P.Novelty, P.Sellout FROM products AS P JOIN nlproducts AS NLP ON P.ID=NLP.PID JOIN prodbinds AS PB ON P.ID=PB.PID WHERE NLP.NLID=:id AND P.Active=1 AND PB.Active=1 AND PB.GID IN (SELECT Contained FROM eshoph WHERE Container IN ($top_groups_sql)) GROUP BY P.ID ORDER BY NLP.Priority";

		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":id", (int) $id, PDO::PARAM_INT);
		$stmt->execute();

		$products = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$products[] = new Var_Pool($row);
		}

		return $products;
	}

	public static function startPending()
	{
		$dbh = Project_DB::get();

		$sql_params['waiting'] = self::STATUS_WAITING;
		$sql_params['started'] = self::STATUS_STARTED;
		$sql_params['active'] = 1;
		$sql_params['now'] = date('Y-m-d H:i:s');

		$query = 'UPDATE newsletters SET Status=:started, Started=NOW() WHERE Status=:waiting AND Active=:active AND Start<=:now';

		$stmt = $dbh->prepare($query);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$stmt->execute();

		return $stmt->rowCount();
	}
}