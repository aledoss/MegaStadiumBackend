<?php

class MegaStadiumModel extends CI_Model {

	public function getTimes($dateInMillis){
		$times = $this->getTimesJson();

		if(is_null($times) || empty($times)){
			return array('status' => 404,'message' => 'No se pudieron obtener los horarios');
		}else{
			return array('status' => 200,'message' => 'Horarios obtenidos correctamente', 'response' => $times);
		}
	}

	public function getCourts(){
		//$courts = $this->db->query("SELECT * from TipoCancha")->result_array();
		$courts = $this->getCourtsJson();

		if(is_null($courts) || empty($courts)){
			return array('status' => 404,'message' => 'No se pudieron obtener las canchas');
		}else{
			return array('status' => 200,'message' => 'Canchas obtenidos correctamente', 'response' => $courts);
		}
	}

	public function getTableSheetReservations($dateInMillis){
		$reservations = $this->getReservationsJson();

		if(is_null($reservations) || empty($reservations)){
			return array('status' => 404,'message' => 'No se pudieron obtener las reservas para el paneo');
		}else{
			return array('status' => 200,'message' => 'Reservas para el paneo obtenidos correctamente', 'response' => $reservations);
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
		return array($court1, $court2);
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

		$court2["id"] = "2";
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