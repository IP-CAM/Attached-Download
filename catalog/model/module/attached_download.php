<?php 
error_reporting(E_ALL);

Class ModelModuleAttachedDownload extends Model{

	public function getDownloadCount($customer_id){
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "attached_download` WHERE customer_id='" . $customer_id . "' AND access='1'");
		return $query->row['total'];
	}

	public function getDownloads($customer_id){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attached_download` WHERE customer_id='" . $customer_id . "' AND access='1'");
		return $query->rows;
	}
}