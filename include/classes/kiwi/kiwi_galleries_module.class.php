<?php
//TODO: otestovat
class Kiwi_Galleries_Module extends Kiwi_Module
{
	public function __construct($id = null, $name = null, $ggid = null, $ggroup = null, $ggsize = null, $gggsize = null, $gsize = null, $pagination = null, $detailpage = null)
	{
		parent::__construct($id, self::KIWI_MODULE_GALLERY);

		$this->_attributes->register('name', $name);
		$this->_attributes->register('ggid', $ggid);
		$this->_attributes->register('ggroup', $ggroup);
		$this->_attributes->register('ggshortdesc');
		$this->_attributes->register('gglongdesc');
		$this->_attributes->register('ggauthor');
		$this->_attributes->register('ggwhen');
		$this->_attributes->register('ggsize', $ggsize);
		$this->_attributes->register('gggsize', $gggsize);
		$this->_attributes->register('gsize', $gsize);
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

		$query = "SELECT M.ID, M.Type, X.GGID, GG.Title AS GGTitle, GG.ShortDesc AS GGShortDesc, GG.LongDesc AS GGLongDesc, GG.Author AS GGAuthor, GG.`When` AS `When`, M.Name, X.GGCount, X.GGGCount, X.GCount, X.ShowPages, X.DetailLink, M.LastChange, M.Active FROM modules AS M LEFT OUTER JOIN mod_galleries AS X ON M.ID=X.ID LEFT OUTER JOIN gallerygroups AS GG ON X.GGID=GG.ID WHERE$where";

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
				$this->ggid = $row['GGID'];
				$this->ggroup = $row['GGTitle'];
				$this->ggshortdesc = $row['GGShortDesc'];
				$this->gglongdesc = $row['GGLongDesc'];
				$this->ggauthor = $row['GGAuthor'];
				$this->ggwhen = $row['When'];
				$this->ggsize = $row['GGCount'];
				$this->gggsize = $row['GGGCount'];
				$this->gsize = $row['GCount'];
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

	public function loadGalleriesList(&$items, &$total, $page = 1, $size = null, $gsize = null)
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->ggid !== null)
		{
			$filter[] = ' GG.ID = :ggid';
			$values['ggid'] = (int)$this->ggid;
		}

		if ($this->ggroup !== null)
		{
			$filter[] = ' GG.Title = :ggroup';
			$values['ggroup'] = $this->ggroup;
		}

		$where = implode(' AND', $filter);
		if ($where !== '')
			$where .= ' AND';

		$query = "SELECT SQL_CALC_FOUND_ROWS G.ID AS GID, GB.Title AS GBTitle, G.Title AS Title, GB.ShortDesc AS GBShortDesc, G.ShortDesc AS ShortDesc, GB.LongDesc AS GBLongDesc, G.LongDesc AS LongDesc, GB.Author AS GBAuthor, G.Author AS Author, G.`When` AS `When`, G.Directory AS Directory FROM gallgbinds AS GB JOIN gallerygroups AS GG ON GB.GRID=GG.ID JOIN galleries AS G ON GB.GAID=G.ID WHERE$where GG.Active=1 AND GB.Active=1 ORDER BY GB.Priority ASC LIMIT :from, :to";

		if ($size === null)
			$size = (int)$this->ggsize;
		$values['from'] = ($page - 1) * $size;
		$values['to'] = $size;

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$items = array();
		if ($stmt->execute())
		{
			$stmt_b = $dbh->query('SELECT FOUND_ROWS()');
			if ($row_b = $stmt_b->fetch(PDO::FETCH_NUM))
				$total = $row_b[0];

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$item = new Var_Pool($row);
				$item->register('Pictures');
				$item->register('PicturesTotal');
				$this->loadGalleryPictures($item, $page, $gsize);
				$items[$row['GID']] = $item;
			}
		}
	}

	public function loadGallery(&$items, $item, $page = 1, $size = null)
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->ggid !== null)
		{
			$filter[] = ' GG.ID = :ggid';
			$values['ggid'] = (int)$this->ggid;
		}

		if ($this->ggroup !== null)
		{
			$filter[] = ' GG.Title = :ggroup';
			$values['ggroup'] = $this->ggroup;
		}

		$filter[] = ' G.ID = :item';
		$values['item'] = (int)$item;

		$where = implode(' AND', $filter);

		if ($this->ggid !== null || $this->ggroup !== null)		
			$query = "SELECT GB.GAID AS GID, GB.GRID AS GGID, GB.Title AS GBTitle, G.Title AS Title, GB.ShortDesc AS GBShortDesc, G.ShortDesc AS ShortDesc, GB.LongDesc AS GBLongDesc, G.LongDesc AS LongDesc, GB.Author AS GBAuthor, G.Author AS Author, G.`When` AS `When`, G.Directory AS Directory FROM gallgbinds AS GB JOIN gallerygroups AS GG ON GB.GRID=GG.ID JOIN galleries AS G ON GB.GAID=G.ID WHERE GB.Active=1 AND G.Active=1 AND $where";
		else
			$query = "SELECT G.ID AS GID, 0 AS GGID, '' AS GBTitle, G.Title AS Title, '' AS GBShortDesc, G.ShortDesc AS ShortDesc, '' AS GBLongDesc, G.LongDesc AS LongDesc, '' AS GBAuthor, G.Author AS Author, G.`When` AS `When`, G.Directory AS Directory FROM galleries AS G WHERE G.Active=1 AND $where";

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$items = array();
		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$item = new Var_Pool($row);
				$item->register('Pictures');
				$item->register('PicturesTotal');
				$this->loadGalleryPictures($item, $page, $size);
				$items = array($row['GID'] => $item);
			}
			else
				$items = array();
	}

	public function loadGalleryPictures(&$gallery, $page = 1, $size = null)
	{
		$dbh = Project_DB::get();

		$values = array();
		$values['gid'] = (int)$gallery->GID;

		$query = "SELECT SQL_CALC_FOUND_ROWS P.ID AS ID, GP.FileName AS GPFileName, P.FileName AS FileName, GP.Title AS GPTitle, P.Title AS Title, GP.ShortDesc AS GPShortDesc, P.ShortDesc AS ShortDesc, GP.LongDesc AS GPLongDesc, P.LongDesc AS LongDesc, GP.Author AS GPAuthor, P.Author AS Author, P.`When` AS `When` FROM gallpics AS P JOIN gallpbinds AS GP ON P.ID=GP.PID WHERE GP.GID=:gid AND GP.Active=1 AND P.Active=1 ORDER BY GP.Priority ASC LIMIT :from, :to";

		if ($size === null)
			$size = (int)$this->gsize;
		$values['from'] = ($page - 1) * $size;
		$values['to'] = $size;

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$pictures = array();
		if ($stmt->execute())
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$picture = new Var_Pool($row);
				$pictures[$row['ID']] = $picture;
			}
		$gallery->Pictures = $pictures;

		$stmt = $dbh->query('SELECT FOUND_ROWS()');
		if ($row = $stmt->fetch(PDO::FETCH_NUM))
			$gallery->PicturesTotal = $row[0];		
	}
}
?>
