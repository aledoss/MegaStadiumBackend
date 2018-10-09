<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MegaStadiumApi extends CI_Controller {

	public function getTimes($dateInMillis){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getTimes($dateInMillis);

			json_output($response);
		}
	}

	public function getCourts(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getCourts();

			json_output($response);
		}
	}

	public function getTableSheetReservations($dateInMillis){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getTableSheetReservations($dateInMillis);

			json_output($response);
		}
	}

	public function getContacts(){
	$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getContacts();

			json_output($response);
		}
	}

	public function updateContacts(){

	}

	private function getBody(){
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		return json_decode($stream_clean,true);
	}


}

?>