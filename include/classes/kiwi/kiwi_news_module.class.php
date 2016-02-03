<?php
class Kiwi_News_Module extends Kiwi_Module
{
	public function __construct($id = null, $name = null, $ngid = null, $newsgroup = null, $size = null, $listmode = null, $pagination = null, $detailpage = null)
	{
		parent::__construct($id, self::KIWI_MODULE_NEWS);

		$this->_attributes->register('name', $name);
		$this->_attributes->register('ngid', $ngid);
		$this->_attributes->register('newsgroup', $newsgroup);
		$this->_attributes->register('size', $size);
		$this->_attributes->register('listmode', $listmode);
		$this->_attributes->register('pagination', $pagination);
		$this->_attributes->register('detailpage', $detailpage);
		$this->_attributes->register('lastchange');

		if ($this->id !== null || $this->name !== null) $this->loadData();
	}

	protected function loadData()
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->id !== null)
		{
			$filter[] = ' M.ID = :id';
			$values['id'] = (int)$this->id;
		}

		if ($this->name !== null)
		{
			$filter[] = ' M.Name = :name';
			$values['name'] = $this->name;
		}

		$where = implode(' AND', $filter);

		$query = "SELECT M.ID, M.Type, X.NGID, G.Title AS NGTitle, M.Name, X.Count, X.ListMode, X.ShowPages, X.DetailLink, M.LastChange, M.Active FROM modules AS M LEFT OUTER JOIN mod_news AS X ON M.ID=X.ID LEFT OUTER JOIN newsgroups AS G ON X.NGID=G.ID WHERE$where";

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if ($row['Type'] != $this->type)
					throw new Kiwi_Bad_Module_Type_Exception($row['Type'], $this->type);
				$this->id = $row['ID'];
				$this->name = $row['Name'];
				$this->ngid = $row['NGID'];
				$this->newsgroup = $row['NGTitle'];
				$this->size = $row['Count'];
				$this->listmode = $row['ListMode'];
				$this->pagination = $row['ShowPages'];
				$this->detailpage = $row['DetailLink'];
				$this->lastchange = $row['LastChange'];
				$this->active = $row['Active'];
			}
			else
			{
				$idstr = $this->id !== null ? "id = $this->id" : "name = $this->name";
				throw new Kiwi_No_Such_Module_Exception($idstr);
			}
	}

	public function loadNewsList(&$items, &$total, $page = 1, $size = null)
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->ngid !== null)
		{
			$filter[] = ' N.NGID = :ngid';
			$values['ngid'] = (int)$this->ngid;
		}

		if ($this->newsgroup !== null)
		{
			$filter[] = ' G.Title = :newsgroup';
			$values['newsgroup'] = $this->newsgroup;
		}

		$where = implode(' AND', $filter);
		if ($where !== '')
			$where .= ' AND';

		$query = "SELECT SQL_CALC_FOUND_ROWS N.ID, N.Name, N.Author, N.Sample, N.Content, N.`When`, N.Start, N.End FROM news AS N JOIN newsgroups AS G ON N.NGID=G.ID WHERE$where N.Start<=CURRENT_DATE AND N.End>=CURRENT_DATE AND G.Active=1 AND N.Active=1 ORDER BY N.`When` DESC, N.LastChange DESC LIMIT :from, :to";

		if ($size === null)
			$size = (int)$this->size;
		$values['from'] = ($page - 1) * $size;
		$values['to'] = $size;

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$items = array();
		if ($stmt->execute())
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				$items[] = new Var_Pool($row);

		$stmt = $dbh->query('SELECT FOUND_ROWS()');
		if ($row = $stmt->fetch(PDO::FETCH_NUM))
			$total = $row[0];
	}

	public function loadNewsItem(&$items, $item)
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->ngid !== null)
		{
			$filter[] = ' N.NGID = :ngid';
			$values['ngid'] = (int)$this->ngid;
		}

		if ($this->newsgroup !== null)
		{
			$filter[] = ' G.Title = :newsgroup';
			$values['newsgroup'] = $this->newsgroup;
		}

		$filter[] = ' N.ID = :item';
		$values['item'] = (int)$item;

		$where = implode(' AND', $filter);

		$query = "SELECT N.ID, N.Name, N.Author, N.Sample, N.Content, N.`When`, N.Start, N.End FROM news AS N JOIN newsgroups AS G ON N.NGID=G.ID WHERE$where";

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$items = array();
		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				$items = array(new Var_Pool($row));
	}
}
?>
