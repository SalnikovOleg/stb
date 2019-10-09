<?php
require_once (DIR_TOOLS.'smarty/libs/Smarty.class.php');

class Template extends Smarty {

   function Template()
   {

        $this->Smarty();

        $this->template_dir = MAIN_DIR.'/templates';
        $this->compile_dir = MAIN_DIR.'/cache';
        $this->config_dir   = MAIN_DIR.'/conf';
        $this->cache_dir    = MAIN_DIR.'/cache';
   }
}

class AdminTemplate extends Smarty {

   function AdminTemplate()
   {

        $this->Smarty();

		$this->template_dir = MAIN_DIR.'/admin/templates';
        $this->compile_dir  = MAIN_DIR.'/admin/cache';
        $this->config_dir   = MAIN_DIR.'/conf';
        $this->cache_dir    = MAIN_DIR.'/admin/cache';
   }
}

?>