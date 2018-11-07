<?php

class MegaStadiumModel extends CI_Model {

	function __construct(){
		date_default_timezone_set('America/Argentina/Buenos_Aires');
	}

	private function isNullOrEmpty($object){
		return is_null($object) || empty($object);
	}

	private function convertMillisToDate($dateInMillis){
		$seconds = $dateInMillis / 1000;
		return date("Y/m/d", $seconds);
	}

	private function convertDateToMillis($date){
		return strtotime($date) * 1000;
	}

	public function getAllTimes(){
		$allTimes = $this->db->query("SELECT * from horario")->result_array();

		if(is_null($allTimes) || empty($allTimes)){
			return array('status' => 404,'message' => 'No se pudieron obtener todos los horarios');
		}else{
			return array('status' => 200,'message' => 'Todos los horarios obtenidos correctamente', 'response' => $allTimes);
		}
	}

	public function getTimes($dateInMillis, $dayFlag){
		//$times = $this->getTimesJson();
		$date = $this->convertMillisToDate($dateInMillis);
		$times = $this->db->query("
				SELECT *
				FROM (
						SELECT H.Id, H.Descripcion
						   FROM HORARIO H
						  WHERE (H.DESTACADOFS = TRUE AND '$dayFlag' = 'FS')
							 OR (H.DESTACADO = TRUE AND '$dayFlag' = 'S')
						UNION
						SELECT DISTINCT H.Id, H.Descripcion
						  FROM ALQUILER A
							 JOIN HORARIO H
								ON A.IdHorario = H.Id
						 WHERE A.FECHAALQUILER = '$date'
					 ) HORARIOSFECHA
				ORDER BY ID ASC
			")->result_array();

		if(is_null($times) || empty($times)){
			return array('status' => 404,'message' => 'No se pudieron obtener los horarios');
		}else{
			return array('status' => 200,'message' => 'Horarios obtenidos correctamente', 'response' => $times);
		}
	}

	public function getCourts(){
		$courts = $this->db->query("SELECT * from TipoCancha")->result_array();
		//$courts = $this->getCourtsJson();

		if(is_null($courts) || empty($courts)){
			return array('status' => 404,'message' => 'No se pudieron obtener las canchas');
		}else{
			return array('status' => 200,'message' => 'Canchas obtenidos correctamente', 'response' => $courts);
		}
	}

	public function getTableSheetReservations($dateInMillis, $dayFlag){
		$date = $this->convertMillisToDate($dateInMillis);
		$courts = $this->db->query("SELECT * from TipoCancha")->result_array();
		$reservations = array();

		for ($i = 0; $i < sizeof($courts); $i++){
			$reservations[$i] = $this->getTableSheetReservationForCourt($date, $dayFlag, $courts[$i]["Id"]);
		}

		//$reservations = $this->getReservationsJson();

		if(is_null($reservations) || empty($reservations)){
			return array('status' => 404,'message' => 'No se pudieron obtener las reservas para el paneo');
		}else{
			return array('status' => 200,'message' => 'Reservas para el paneo obtenidos correctamente', 'response' => $reservations);
		}
	}

	private function getTableSheetReservationForCourt($date, $dayFlag, $courtId){
		$courtReservation = $this->db->query("
			SELECT A.IdHorario,
			   H.Descripcion DescHorario, 
			   A.Id IdAlquiler,
			   A.IdContacto1,
			   C1.Nombre NombreContacto1,
			   C1.Codigo CodigoContacto1,
			   A.IdContacto2,
			   C2.Nombre NombreContacto2,
			   C2.Codigo CodigoContacto2,
			   IFNULL(E.DESCRIPCION, 'Vacio') DescEstado,
			   IFNULL(E.COLOR, '#FFFFFF') ColorEstado,
					       TC.Descripcion
			FROM HORARIO H
			LEFT JOIN ALQUILER A
				ON H.Id = A.IdHorario
			    AND A.IDTIPOCANCHA = $courtId
			    AND A.FECHAALQUILER = '$date'
			LEFT JOIN ESTADO E
				ON A.IDESTADO = E.ID
			LEFT JOIN CONTACTO C1
				ON C1.Id = A.IdContacto1
			LEFT JOIN CONTACTO C2
				ON C2.Id = A.IdContacto2
			LEFT JOIN TipoCancha TC
							ON TC.id = $courtId
			WHERE  (
				 (
				   (A.ID IS NULL AND '$dayFlag' = 'FS' AND H.DESTACADOFS = TRUE)
					 OR 
				   (A.ID IS NULL AND '$dayFlag' = 'S' AND H.DESTACADO = TRUE)
				 )
				   OR 
				 (A.ID IS NOT NULL)
			       OR
				 (A.ID IS NULL AND EXISTS (SELECT 1
										     FROM Alquiler a2
			                                WHERE a2.FECHAALQUILER = '$date'
			                                  AND a2.idHorario = H.id)
				 ) 
			   )
			ORDER BY H.ID ASC
		")->result_array();

		$court['Id'] = $courtId;
		$court['Descripcion'] = $courtReservation[0]['Descripcion'];
		if (!$this->isNullOrEmpty($courtReservation)){
			for ($i = 0; $i < sizeof($courtReservation); $i++){
				if (!$this->isNullOrEmpty($courtReservation[$i]['NombreContacto1'])){
					$contacto1['Nombre'] = $courtReservation[$i]['NombreContacto1'];
					$contacto1['Codigo'] = $courtReservation[$i]['CodigoContacto1'];
					$courtReservation[$i]['contacto1'] = $contacto1;
				}

				if (!$this->isNullOrEmpty($courtReservation[$i]['NombreContacto2'])){
					$contacto2['Nombre'] = $courtReservation[$i]['NombreContacto2'];
					$contacto2['Codigo'] = $courtReservation[$i]['CodigoContacto2'];
					$courtReservation[$i]['contacto2'] = $contacto2;
				}


				$state['Descripcion'] = $courtReservation[$i]['DescEstado'];
				$state['Color'] = $courtReservation[$i]['ColorEstado'];
				$courtReservation[$i]['estado'] = $state;
			}
		}

		$courtReservations['court'] = $court;
		$courtReservations['reservas'] = $courtReservation;

		return $courtReservations;
	}

	public function getReservationDetails($reservationId) {
		$reservation = $this->db->query("SELECT *, Id IdAlquiler FROM Alquiler WHERE Id = $reservationId;")->row();

		if ($this->isNullOrEmpty($reservation)) {
			return array('status' => 404,'message' => 'No se pudo obtener la reserva');
		} else {
			$reservation->FechaAlquiler = $this->convertDateToMillis($reservation->FechaAlquiler);
			$reservation->FechaCreacion = $this->convertDateToMillis($reservation->FechaCreacion);
			$contact1Id = $reservation->IdContacto1;
			$contact2Id = $reservation->IdContacto2;
			$courtId = $reservation->IdTipoCancha;
			$timeId = $reservation->IdHorario;
			$stateId = $reservation->IdEstado;

			if (!$this->isNullOrEmpty($contact1Id)){
				$contact1 = $this->db->query("SELECT * FROM contacto C WHERE C.id = $contact1Id;")->row();
			}
			if (!$this->isNullOrEmpty($contact2Id)){
				$contact2 = $this->db->query("SELECT * FROM contacto C WHERE C.id = $contact2Id;")->row();
			}
			if (!$this->isNullOrEmpty($courtId)){
				$court = $this->db->query("SELECT * FROM tipocancha TC WHERE TC.id = $courtId;")->row();
			}
			if (!$this->isNullOrEmpty($timeId)){
				$time = $this->db->query("SELECT * FROM horario H WHERE H.id = $timeId;")->row();
			}
			if (!$this->isNullOrEmpty($stateId)){
				$state = $this->db->query("SELECT * FROM estado E WHERE E.id = $stateId;")->row();
			}			
			

			if($this->isNullOrEmpty($state) || $this->isNullOrEmpty($time) || $this->isNullOrEmpty($court)){
				return array('status' => 404,'message' => 'No se pudieron obtener los detalles de la reserva');
			}else{
				if (!$this->isNullOrEmpty($contact1Id)){
					$reservation->contacto1 = $contact1;
				}
				if (!$this->isNullOrEmpty($contact2Id)){
					$reservation->contacto2 = $contact2;
				}
				$reservation->estado = $state;
				$reservation->horario = $time;
				$reservation->tipoCancha = $court;

				return array('status' => 200,'message' => 'Reserva para el paneo obtenidos correctamente', 'response' => $reservation);
			}
		}
	}

	public function getContacts(){
		$contacts = $this->db->query("SELECT * FROM contacto;")->result_array();

		if(is_null($contacts) || empty($contacts)){
			return array('status' => 404,'message' => 'No se pudieron obtener los contactos');
		}else{
			return array('status' => 200,'message' => 'Contactos obtenidos correctamente', 'response' => $contacts);
		}
	}

	public function insertReservation($reservation) {
		$reservation['FechaAlquiler'] = $this->convertMillisToDate($reservation['FechaAlquiler']);
		$this->db->trans_start();
		$this->db->insert('ALQUILER', $reservation);
		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return array('status' => 500,'message' => 'No se pudo generar el alquiler');
		} else {
			$this->db->trans_commit();
			return array('status' => 200,'message' => 'Alquiler generada correctamente');
		}
	}

	public function updateReservation($reservationId, $reservation){
		$this->db->trans_start();
		$this->db->where('Id', $reservationId);
		$this->db->update('ALQUILER', $reservation);
		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return array('status' => 500,'message' => 'No se pudo actualizar el alquiler');
		} else {
			$this->db->trans_commit();
			return array('status' => 200,'message' => 'Alquiler actualizado correctamente');
		}
	}

	public function cancelReservation($reservationId) {
		$result = $this->db->delete('ALQUILER', array('Id' => $reservationId));
		
		if(is_null($result) || empty($result)){
			return array('status' => 404,'message' => 'No se cancelar el alquiler');
		}else{
			return array('status' => 200,'message' => 'Alquiler cancelado correctamente');
		}
	}

	public function copyReservation($reservationId) {
		$couldCopy = $this->db->query("CALL sp_CopiarReserva($reservationId)");
		
		if(!$couldCopy){
			return array('status' => 404,'message' => 'No se generar el alquiler');
		}else{
			return array('status' => 200,'message' => 'Alquiler generado correctamente');
		}
	}

	public function getAvailableTimesForDateAndCourts($courtId, $date, $dayFlag) {
		$formattedDate = $this->convertMillisToDate($date);
		$times = $this->db->query("
			SELECT H.Id Id,
			   H.Descripcion Descripcion
			FROM HORARIO H
			LEFT JOIN ALQUILER A
				ON H.Id = A.IdHorario
			    AND A.IDTIPOCANCHA = $courtId
			    AND A.FECHAALQUILER = '$formattedDate'
			LEFT JOIN ESTADO E
				ON A.IDESTADO = E.ID
			LEFT JOIN CONTACTO C1
				ON C1.Id = A.IdContacto1
			LEFT JOIN CONTACTO C2
				ON C2.Id = A.IdContacto2
			WHERE  (
				 (
				   (A.ID IS NULL AND '$dayFlag' = 'FS' AND H.DESTACADOFS = TRUE)
					 OR 
				   (A.ID IS NULL AND '$dayFlag' = 'S' AND H.DESTACADO = TRUE)
				 )
				   OR 
				 /*(A.ID IS NOT NULL)
			       OR*/
				 (A.ID IS NULL AND EXISTS (SELECT 1
										     FROM Alquiler a2
			                                WHERE a2.FECHAALQUILER = '$formattedDate'
			                                  AND a2.idHorario = H.id)
				 ) 
			   )
			ORDER BY H.ID ASC

		")->result_array();

		if(is_null($times) || empty($times)){
			return array('status' => 404,'message' => "No se pudieron obtener los horarios para la cancha $courtId para la fecha $formattedDate");
		}else{
			return array('status' => 200,'message' => "Horarios para la cancha $courtId para la fecha $formattedDate obtenidos correctamente", 'response' => $times);
		}
	}

	public function getStates() {
		$result = $this->db->query("SELECT * FROM Estado")->result_array();
		
		if(is_null($result) || empty($result)) {
			return array('status' => 404,'message' => 'No se pudieron obtener los estados');
		}else{
			return array('status' => 200,'message' => 'Estados obtenidos correctamente', 'response' => $result);
		}
	}

}
?>	