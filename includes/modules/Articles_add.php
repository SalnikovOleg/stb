<?php
/*	
		// создать html карту сайта
		public function CreateHtmlSiteMap($id, $root)
		{
			$this->sitemap = "<b>".$this->Caption."</b><br><ul>\n";
			$this->GetHtmlNode($id, 0, "");
			$this->sitemap .= "</ul>\n";
		
			write_to_file($root."/cache/".$this->sitemap_file, $this->sitemap);		
		}

		// создать XML карту сайта
		public function CreateXMLSiteMap($id, $root)
		{
			$this->sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
			$this->GetXMLNode($id, 0, "");
			$this->sitemap .= '</urlset>';
			
			write_to_file($root.$this->table.".xml", $this->sitemap);
		}
		
		// получить ветку дерева для карты сайта
		private function GetHtmlNode($cId, $level, $preUrl)
		{
			$ds = $this->SelectItems($cId);
		
			for ($i=0; $i<count($ds); $i++)
			{
				if ($ds[$i]['absolute_url'] == 1)
				{
					$url = trim($ds[$i]['url']);
					//$preUrl = "";
				}	
				else
					$url = addEndSlash($this->module_url).$preUrl.addEndSlash(trim($ds[$i]['url']));
					
				if ($ds[$i]['node'] > 0)
				{
					$this->sitemap .= '<li><a class="node" href="'.HOST.langUrl($_SESSION['lang_url']).$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a>";				
					$this->sitemap .="\n<ul>\n";
					$this->GetHtmlNode($ds[$i]['id'], $level + 1, $preUrl.addEndSlash(trim($ds[$i]['url'])));
					$this->sitemap .="</ul>\n";
				}
				else
					$this->sitemap .= '<li><a href="'.HOST.langUrl($_SESSION['lang_url']).$url.'" title="'.$ds[$i]['name'].'">'.$ds[$i]['name']."</a>";				
				$this->sitemap .="</li>\n";
			}
		}

		// получить ветку дерева для карты сайта
		private function GetXMLNode($cId, $level, $preUrl)
		{
			$ds = $this->SelectItems($cId);
		
			for ($i=0; $i<count($ds); $i++)
			{
				if ($ds[$i]['absolute_url'] == 1)
				{
					$url = trim($ds[$i]['url']);
				//	$preUrl = "";
				}	
				else
				{	
					$url = addEndSlash($this->module_url).$preUrl.addEndSlash(trim($ds[$i]['url']));
				}
				$this->sitemap .= "<url>\n\t<loc>".HOST.langUrl($_SESSION['lang_url']).$url."</loc>\n\t<lastmod>".$ds[$i]['insert_date']."</lastmod>\n</url>\n";				
				
				if ($ds[$i]['node'] > 0)
					$this->GetXMLNode($ds[$i]['id'], $level + 1, $preUrl.addEndSlash(trim($ds[$i]['url'])));
			}
		}
		
		// выбрать список подкатегорий
		private function SelectItems($id)
		{
			$sql = "SELECT n.`id`, n.`parent`, IFNULL(children.cnt,0) AS `node`, n.`url`, n.`name`, n.`insert_date`, n.`absolute_url` 
			FROM ".$this->table." n 
			LEFT JOIN (SELECT parent, COUNT(*) cnt FROM ".$this->table." 
				WHERE disabled=0 AND deleted = 0 AND lang=".$_SESSION['lang_id']."
				GROUP BY parent
				) AS children ON children.parent = n.id
			WHERE n.`parent` = ".$id." AND n.`disabled`=0 AND n.`deleted` = 0 AND n.`lang`=".$_SESSION['lang_id'];
		
			return $this->db->db_dataset_array($sql);
		}
		

*/
?>