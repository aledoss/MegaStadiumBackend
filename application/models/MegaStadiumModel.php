<?php

class MegaStadiumModel extends CI_Model {

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
		$court1 = $this->getTableSheetReservationForCourt($date, $dayFlag, 1);
		$court2 = $this->getTableSheetReservationForCourt($date, $dayFlag, 2);
		$court3 = $this->getTableSheetReservationForCourt($date, $dayFlag, 3);
		$court4 = $this->getTableSheetReservationForCourt($date, $dayFlag, 4);
		$court5 = $this->getTableSheetReservationForCourt($date, $dayFlag, 5);
		$court6 = $this->getTableSheetReservationForCourt($date, $dayFlag, 6);
		
		//$reservations = $this->getReservationsJson();
		$reservations = array($court1, $court2, $court3, $court4, $court5, $court6);

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
		//$contacts = $this->db->query("SELECT * from Contacto")->result_array();
		$contacts = $this->getContactsJson();

		if(is_null($contacts) || empty($contacts)){
			return array('status' => 404,'message' => 'No se pudieron obtener los contactos');
		}else{
			return array('status' => 200,'message' => 'Contactos obtenidos correctamente', 'response' => $contacts);
		}

	}

	private function getCourtsJson(){
		$court1['id'] = 1;
		$court1['descripcion'] = "5 (1)";

		$court2['id'] = 2;
		$court2['descripcion'] = "5 (2)";

		$court3['id'] = 3;
		$court3['descripcion'] = "5 (3)";

		$court4['id'] = 4;
		$court4['descripcion'] = "7";

		$court5['id'] = 5;
		$court5['descripcion'] = "8";

		$court6['id'] = 6;
		$court6['descripcion'] = "9";

		$courts = array($court1, $court2, $court3, $court4, $court5, $court6);
		return $courts;
	}

	private function getTimesJson(){
		
		$hora1['id'] = 1;
		$hora1['hour'] = "17:00";
		$hora1['mandatory'] = false;

		$hora2['id'] = 2;
		$hora2['hour'] = "18:00";
		$hora2['mandatory'] = true;

		$hora3['id'] = 3;
		$hora3['hour'] = "19:00";
		$hora3['mandatory'] = true;

		$hora4['id'] = 4;
		$hora4['hour'] = "20:00";
		$hora4['mandatory'] = true;

		$hora5['id'] = 5;
		$hora5['hour'] = "21:00";
		$hora5['mandatory'] = true;

		$hora6['id'] = 6;
		$hora6['hour'] = "22:00";
		$hora6['mandatory'] = true;

		$hora7['id'] = 7;
		$hora7['hour'] = "23:00";
		$hora7['mandatory'] = true;

		$hora8['id'] = 8;
		$hora8['hour'] = "24:00";
		$hora8['mandatory'] = true;

		$hora9['id'] = 9;
		$hora9['hour'] = "01:00";
		$hora9['mandatory'] = true;

		$horas = array($hora1, $hora2, $hora3, $hora4, $hora5, $hora6, $hora7, $hora8, $hora9);

		return $horas;
	}

	private function getReservationsJson() {
		$court1 = $this->getReservationsCourt1();
		$court2 = $this->getReservationsCourt2();
		$court3 = $this->getReservationsCourt2();
		$court4 = $this->getReservationsCourt2();
		$court5 = $this->getReservationsCourt2();
		$court6 = $this->getReservationsCourt2();
		return array($court1, $court2, $court3, $court4, $court5, $court6);
	}

	private function getEmptyState(){
		$emptyState["id"] = 1;
		$emptyState["desc"] = "Vacio";
		$emptyState["color"] = "#bbdefb";
		return $emptyState;
	}

	private function getReservedState(){
		$reservedState["id"] = 1;
		$reservedState["desc"] = "Reservado";
		$reservedState["color"] = "#64cac0";
		return $reservedState;
	}

	private function getReservationsCourt1(){
		$reserves1["id"] = null;
		$reserves1["estado"] = $this->getEmptyState();
		$reserves2["id"] = null;
		$reserves2["estado"] = $this->getEmptyState();
		$reserves3["id"] = null;
		$reserves3["estado"] = $this->getEmptyState();
		$reserves4["id"] = null;
		$reserves4["estado"] = $this->getEmptyState();
		$reserves5["id"] = 5;
		$reserves5["estado"] = $this->getReservedState();
		$reserves6["id"] = 6;
		$reserves6["estado"] = $this->getReservedState();
		$reserves7["id"] = 7;
		$reserves7["estado"] = $this->getReservedState();
		$reserves8["id"] = 8;
		$reserves8["estado"] = $this->getReservedState();
		$reserves9["id"] = 9;
		$reserves9["estado"] = $this->getReservedState();

		$court1["id"] = 1;
		$court1["descripcion"] = "5 (1)";
		$court["court"] = $court1;
		$court["reservas"] = array($reserves1, $reserves2, $reserves3, $reserves4, $reserves5, $reserves6, $reserves7, $reserves8, $reserves9);
		return $court;
	}

	private function getReservationsCourt2(){
		$reserves1["id"] = 11;
		$reserves1["estado"] = $this->getReservedState();
		$reserves2["id"] = 12;
		$reserves2["estado"] = $this->getReservedState();
		$reserves3["id"] = 13;
		$reserves3["estado"] = $this->getReservedState();
		$reserves4["id"] = 14;
		$reserves4["estado"] = $this->getReservedState();
		$reserves5["id"] = 15;
		$reserves5["estado"] = $this->getReservedState();
		$reserves6["id"] = 16;
		$reserves6["estado"] = $this->getReservedState();
		$reserves7["id"] = 17;
		$reserves7["estado"] = $this->getReservedState();
		$reserves8["id"] = null;
		$reserves8["estado"] = $this->getEmptyState();
		$reserves9["id"] = null;
		$reserves9["estado"] = $this->getEmptyState();

		$court2["id"] = 2;
		$court2["descripcion"] = "5 (2)";
		$court["court"] = $court2;
		$court["reservas"] = array($reserves1, $reserves2, $reserves3, $reserves4, $reserves5, $reserves6, $reserves7, $reserves8, $reserves9);
		return $court;
	}

	private function getContactsJson(){
		$contact1['id']= 1;
	    $contact1['name']= "Jose Perez";
	    $contact1['tel']= "15 1234 6192";
	    $contact1['mail']= "jperez@gmail.com";
	    $contact1['facebook']= "www.facebook.com/jperez";
	    $contact1['code']= "01";

	    $contact2['id']= 2;
	    $contact2['name']= "Ruben Iglesias";
	    $contact2['tel']= "15 6092 1259";
	    $contact2['mail']= "riglesias@gmail.com";
	    $contact2['facebook']= "www.facebook.com/riglesias";
	    $contact2['code']= "02";

	    $contact3['id']= 3;
	    $contact3['name']= "Sergio Ramos";
	    $contact3['tel']= "15 2319 1569";
	    $contact3['mail']= "sramos@gmail.com";
	    $contact3['facebook']= "www.facebook.com/sramos";
	    $contact3['code']= "03";

	    $contact4['id']= 4;
	    $contact4['name']= "Colo Ramirez";
	    $contact4['tel']= "15 6902 1240";
	    $contact4['mail']= "cramirez@gmail.com";
	    $contact4['facebook']= "www.facebook.com/cramirez";
	    $contact4['code']= "04";

	    $contact5['id']= 5;
	    $contact5['name']= "Pelado Gutierrez";
	    $contact5['tel']= "15 2319 1569";
	    $contact5['mail']= "pgutierrez@gmail.com";
	    $contact5['facebook']= "www.facebook.com/pgutierrez";
	    $contact5['code']= "05";

		$contacts = array($contact1, $contact2, $contact3, $contact4, $contact5);

		return $contacts;
	}
	
}
?>	