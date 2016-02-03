<?php
class Kiwi_EShop_Indexer
{
	protected static $_locked = null;

	protected static function lockTables($lock)
	{
		if (self::$_locked === null)
		{
			mysql_query("LOCK TABLES eshoph WRITE, eshoph as EHR READ, eshop READ, eshoph_del WRITE");
			self::$_locked = $lock;
		}
	}

	protected static function unlockTables($lock)
	{
		if (self::$_locked === $lock)
		{
			mysql_query("UNLOCK TABLES");
			self::$_locked = null;
		}
	}

	public static function reindexAll()
	{
		self::lockTables('reindexAll');
		mysql_query("DELETE FROM eshoph");
		self::indexDeep(0, 0);
		self::unlockTables('reindexAll');
	}

	// Indexes $groups as a members of group $parent
	// If no parent given (or is NULL), it is acquired from database
	// If parent is 0, groups have no parent
	public static function indexDeep($groups, $parent = null)
	{
		self::lockTables('indexDeep');
		if (!is_array($groups))
			$groups = array($groups);
		if ($parent === null)
		{
			$result = mysql_query("SELECT Parent FROM eshop WHERE ID={$groups[0]}");
			if ($row = mysql_fetch_row($result))
				$parent = $row[0];
			else
			{
				self::unlockTables('index');
				throw new Exception('Invalid group ID: ' . $gid);
			}
		}
		self::_indexDeep($groups, $parent);
		self::unlockTables('indexDeep');
	}

	public static function unindex($groups)
	{
		self::lockTables('unindex');
		if (!is_array($groups))
			$groups = array($groups);
		$result = array();
		foreach ($groups as $group)
			$result[] = self::_unindex($group);
		return $result;
		self::unlockTables('unindex');
	}

	protected static function _indexDeep($groups, $parent)
	{
		if ($parent > 0)
			foreach ($groups as $group)
				self::index($group, $parent);

		while (!empty($groups))
		{
			$gid = array_shift($groups);
			$result = mysql_query("SELECT ID FROM eshop WHERE Parent=$gid");
			while ($row = mysql_fetch_row($result))
			{
				$groups[] = $row[0];
				self::index($row[0], $gid);
			}
			mysql_free_result($result);
		}
	}

	protected static function _unindex($gid)
	{
		mysql_query("CREATE TEMPORARY TABLE eshoph_del (Container INT, Contained INT) SELECT EHR.Container, EHR.Contained FROM eshoph as EHR WHERE EHR.Container IN (SELECT EHR.Contained FROM eshoph AS EHR WHERE EHR.Container=$gid) OR Contained IN (SELECT EHR.Contained FROM eshoph AS EHR WHERE EHR.Container=$gid)");
		mysql_query("DELETE FROM eshoph USING eshoph INNER JOIN eshoph_del USING (Container, Contained)");
		mysql_query("DROP TEMPORARY TABLE eshoph_del");
	}

	// Indexes group $gid as a member of group $parent
	// If no parent given, it is acquired from database
	public static function index($gid, $parent = null)
	{
		self::lockTables('index');
		if ($parent === null)
		{
			$result = mysql_query("SELECT Parent FROM eshop WHERE ID=$gid");
			if ($row = mysql_fetch_row($result))
				$parent = $row[0];
			else
			{
				self::unlockTables('index');
				throw new Exception('Invalid group ID: ' . $gid);
			}
		}
		return mysql_query("REPLACE INTO eshoph(Container, Contained, Distance) SELECT EHR.Container, $gid, EHR.Distance+1 FROM eshoph AS EHR WHERE EHR.Contained=$parent UNION SELECT $gid, $gid, 0");
		self::unlockTables('index');
	}
}
?>