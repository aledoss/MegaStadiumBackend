<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MegaStadiumApi extends CI_Controller {

	private function getActualDateTime(){
		date_default_timezone_set('America/Argentina/Buenos_Aires');
		return date('Y-m-d H:i:s'); 
	}

	public function getAllTimes(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getAllTimes();

			json_output($response);
		}
	}

	public function getTimes($dateInMillis, $dayFlag){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getTimes($dateInMillis, $dayFlag);

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

	public function getTableSheetReservations($dateInMillis, $dayFlag){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getTableSheetReservations($dateInMillis, $dayFlag);

			json_output($response);
		}
	}

	public function getReservationDetails($reservationId){
	$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getReservationDetails($reservationId);

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

	public function insertReservation() {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
			$body = json_decode($stream_clean,true);

			$reservation = array(
				'Sena' => $body['Sena'],
				'FechaAlquiler' => $body['FechaAlquiler'],
				'AlquilaSinSena' => $body['AlquilaSinSena'],
				'Adicional' => $body['Adicional'],
				'PagadoCliente' => $body['PagadoCliente'],
				'Nota' => $body['Nota'],
				'FechaCreacion' => $this->getActualDateTime(),
				'FechaModificacion' => $this->getActualDateTime(),
				'IdContacto1' => $body['contacto1']['Id'],
				'IdContacto2' => $body['contacto2']['Id'],
				'IdTipoCancha' => $body['tipoCancha']['Id'],
				'IdHorario' => $body['horario']['Id'],
				'IdEstado' => $body['estado']['Id']
			);

			$this->load->model('MegaStadiumModel');
			$response = $this->MegaStadiumModel->insertReservation($reservation);

			json_output($response);
		}
	}

	public function updateReservation($reservationId){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
			$body = json_decode($stream_clean,true);

			$reservation = array(
				'Sena' => $body['Sena'],
				'AlquilaSinSena' => $body['AlquilaSinSena'],
				'Adicional' => $body['Adicional'],
				'PagadoCliente' => $body['PagadoCliente'],
				'Nota' => $body['Nota'],
				'FechaModificacion' => $this->getActualDateTime(),
				'IdContacto1' => $body['contacto1']['Id'],
				'IdContacto2' => $body['contacto2']['Id'],
				'IdEstado' => $body['estado']['Id'],
				'IdHorario' => $body['horario']['Id']
			);

			$this->load->model('MegaStadiumModel');
			$response = $this->MegaStadiumModel->updateReservation($reservationId, $reservation);

			json_output($response);
		}
	}

	public function cancelReservation($reservationId) {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->cancelReservation($reservationId);

			json_output($response);
		}
	}

	public function copyReservation($reservationId) {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->copyReservation($reservationId);

			json_output($response);
		}
	}

	public function getAvailableTimesForDateAndCourts($courtId, $date, $dayFlag) {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getAvailableTimesForDateAndCourts($courtId, $date, $dayFlag);

			json_output($response);
		}
	}

	public function updateContacts(){

	}

	public function getStates() {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET'){
			json_output(array('status' => 400,'message' => 'Error de petición.'));
		} else {
			$this->load->model('MegaStadiumModel');
	        $response = $this->MegaStadiumModel->getStates();

			json_output($response);
		}
	}

	public function pingServer(){
		//Nothing to do
	}

	private function getBody(){
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		return json_decode($stream_clean,true);
	}


}

?>