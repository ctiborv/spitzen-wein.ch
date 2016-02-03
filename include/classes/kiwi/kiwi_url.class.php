<?php
class Kiwi_URL
{
	const DEFAULT_CATALOG_ROOT = 1;

	protected function __construct()
	{
	}

	private static function getSubGroups($catalogRoot)
	{
		$dbh = Project_DB::get();

		$query = "SELECT Contained FROM eshoph WHERE Container=:pmi";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(':pmi', $catalogRoot, PDO::PARAM_INT);
		$stmt->execute();
		$sub_groups = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			$sub_groups[] = $row['Contained'];
		$stmt->closeCursor();
		return $sub_groups;

		if (!empty($sub_groups))
		{
			$sub_groups_str = implode(',', $sub_groups);
			$search_pmi_sql = " AND PB.GID IN ($sub_groups_str)";
		}

		if (!isset($search_pmi_sql)) $search_pmi_sql = "";
		return $search_pmi_sql;
	}

	public static function loadProductID($url, $catalogRoot = NULL)
	{
		if ($catalogRoot === NULL) $catalogRoot = self::DEFAULT_CATALOG_ROOT;
		$sub_groups = self::getSubGroups($catalogRoot);
		if (!empty($sub_groups))
		{
			$sub_groups_str = implode(',', $sub_groups);
			$search_pmi_sql = " AND PB.GID IN ($sub_groups_str)";
		}
		else
			$search_pmi_sql = "";

		$dbh = Project_DB::get();
		$query = "SELECT P.ID FROM products AS P JOIN prodbinds AS PB ON P.ID=PB.PID WHERE P.Active=1 AND PB.Active=1 AND P.URL=:url$search_pmi_sql";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(':url', $url, PDO::PARAM_STR);

		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				return $row['ID'];

		return null;
	}
	
	public static function loadProductBindID($url, $catalogRoot = NULL)
	{
		if ($catalogRoot === NULL) $catalogRoot = self::DEFAULT_CATALOG_ROOT;
		$sub_groups = self::getSubGroups($catalogRoot);
		if (!empty($sub_groups))
		{
			$sub_groups_str = implode(',', $sub_groups);
			$search_pmi_sql = " AND PB.GID IN ($sub_groups_str)";
		}
		else
			$search_pmi_sql = "";

		$dbh = Project_DB::get();

		$url_parts = explode('/', $url, 2);
		if (sizeof($url_parts) != 2) return null; // jde o ryzí url produktu

		$query = "SELECT PB.ID FROM prodbinds AS PB JOIN products AS P ON PB.PID=P.ID JOIN eshop AS E ON PB.GID=E.ID WHERE PB.Active=1 AND P.Active=1 AND E.Active=1 AND P.URL=:purl AND E.URL=:lurl$search_pmi_sql";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(':purl', $url_parts[1], PDO::PARAM_STR);
		$stmt->bindValue(':lurl', $url_parts[0], PDO::PARAM_STR);

		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				return $row['ID'];

		return null;
	}

	public static function loadProductLineID($url, $catalogRoot = NULL)
	{
		if ($catalogRoot === NULL) $catalogRoot = self::DEFAULT_CATALOG_ROOT;
		$sub_groups = self::getSubGroups($catalogRoot);
		if (!empty($sub_groups))
		{
			$sub_groups_str = implode(',', $sub_groups);
			$search_pmi_sql = " AND ID IN ($sub_groups_str)";
		}
		else
			$search_pmi_sql = "";

		$dbh = Project_DB::get();
		$query = "SELECT ID FROM eshop WHERE Active=1 AND URL=:url$search_pmi_sql";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(':url', $url, PDO::PARAM_STR);

		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				return $row['ID'];
	}
}
?>
