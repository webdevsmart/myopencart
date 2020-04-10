<?php
class ModelExtensionXshippingproXshippingpro extends Model
{
	
   public function addData($data) {
   
        $row_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$data['tab_id'] . "'")->row;
        
        if ($row_exist) {
            $sql="UPDATE `" . DB_PREFIX . "xshippingpro` SET method_data= '" . $this->db->escape($data['method_data']) . "'";
            $sql.="WHERE tab_id = '" . (int)$data['tab_id'] . "'";
        } else {
            $sql="INSERT INTO `" .DB_PREFIX . "xshippingpro` SET method_data= '" . $this->db->escape($data['method_data']) . "'";
            $sql.= ", `tab_id` = '".(int)$data['tab_id']."'";
        }
        
        $this->db->query($sql);
        
		return true;
    }
    
    public function getData() {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro`")->rows;
    }
    
    public function getDataByTabId($tab_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$tab_id . "'")->row;
    }
    
     public function deleteData($tab_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$tab_id . "'");
		return true;
    }
    
    public function install(){
        
        $sql = "
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."xshippingpro` (
              `id` int(8) NOT NULL AUTO_INCREMENT,
			  `method_data` longtext NULL,
              `tab_id` int(8) NULL,
               PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
        ";
        $query = $this->db->query($sql);
    }
    
    public function uninstall(){
    
       $query = $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."xshippingpro`");
         
    }
    
    public function isDBBUPdateAvail() {
		  
	      $tables=array('xshippingpro');	  
		  foreach($tables as $table){
			  if(!$this->db->query("SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX.$table."' LIMIT 1")->row){
				   return true;
			  }
		  }
		  return false;
	}
	
}

?>