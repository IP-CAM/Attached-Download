<?php 

Class ControllerModuleAttachedDownload extends Controller {

	/**
	 * Download a file
	 */
	public function download(){
		$sql = "SELECT * FROM " . DB_PREFIX . "attached_download WHERE id='" . $this->request->get['id'] . "'"; 
		$query = $this->db->query($sql);

		$download_info = $query->row;

		if ($download_info) {
			$file = DIR_DOWNLOAD . $download_info['filename'];
			$mask = basename($download_info['mask']);

			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}
					readfile($file, 'rb');
					exit();
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		}
	}

}