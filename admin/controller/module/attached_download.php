<?php
class ControllerModuleAttachedDownload extends Controller {

	/**
	 * Install Function
	 */
	public function install(){

	}

	public function index() {

		$this->language->load('module/attached_download');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		        $this->model_setting_setting->editSetting('attached_download', $this->request->post);
		
		        $this->session->data['success'] = $this->language->get('text_success');
		
		        $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		//Breadcrumbs
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/account', 'token=' . $this->session->data['token'], 'SSL')
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		//Set Language variables
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');

        //Load page view
       	$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//Page Variables
		$data['action'] = $this->url->link('module/account', 'token=' . $this->session->data['token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		//Load the page
        $this->response->setOutput($this->load->view('module/attached_download.tpl', $data));
	}



	/**
	 * Get list of downloads per order
	 * @return array json array
	 */
	public function get_order_download(){

		$sql = "SELECT * FROM `" . DB_PREFIX . "attached_download` WHERE order_id='" . $this->request->get['order_id'] . "'";

		$query = $this->db->query($sql);

		if($query->num_rows > 0){
			foreach ($query->rows as $result) {
				$data[] = [
					'id' => $result['id'],
					'filename' => $result['filename'],
					'mask' => $result['mask'],
					'date' => $result['date_added'],
					'access' => $result['access'],
					'link' => $this->url->link('module/attached_download/download&id=' . $result['id'], 'token=' . $this->session->data['token'], 'SSL')
				];
			}

			//Have some downloads!
			echo json_encode($data);
		}else{
			//echo json_encode(['err' => 'Module Attached Download: You see what had happend was... Unable to get attached downloads! :(']);
		}
	}

	/**
	 * Add To database
	 */
	public function upload(){
		if($this->request->get['order_id']){
			$order_id = $this->request->get['order_id'];
			$filename = $this->request->get['filename'];
			$mask = $this->request->get['mask'];
			$access = $this->request->get['access'];

			$sql = "INSERT INTO " . DB_PREFIX . "attached_download (`order_id`, `filename`, `mask`, `access`, `date_added`) VALUES ('$order_id', '$filename', '$mask', $access, NOW())";
			$query = $this->db->query($sql);

			if(!$query){
				echo json_encode(['err' => 'Unable to upload file.']);
			}else{
				echo json_encode(['err' => false]);
			}
		}
	}

	/**
	 * Update upload
	 */
	public function update(){
		$order_id = $this->request->get['order_id'];
		$mask = (isset($this->request->get['mask'])) ? $this->request->get['mask'] : '';
		$access = (isset($this->request->get['access'])) ? $this->request->get['access'] : '';
		$id = $this->request->get['id'];

		if($this->request->get['order_id']){
			$sql = "UPDATE " . DB_PREFIX . "attached_download SET 
				mask = IF('$mask' = '', mask, '$mask'),
				access = IF('$access' = '', access, '$access')
				WHERE id=$id AND order_id=$order_id;";

			$query = $this->db->query($sql);

			if(!$query){
				echo json_encode(['err' => 'Unable to upload file.']);
			}else{
				echo json_encode(['err' => false]);
			}
		}
	}

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

	/**
	 * Validate some shit.
	 * @return [type] [description]
	 */
	public function validate() {

        if (!$this->user->hasPermission('modify', 'module/attached_download')) {
                $this->error['warning'] = $this->language->get('error_permission');
        }
                
        if (!$this->error) {
                return true;
        } else {
                return false;
        }
	}
}

?>
