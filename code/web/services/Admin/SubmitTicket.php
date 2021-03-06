<?php
require_once ROOT_DIR . '/services/Admin/Admin.php';

class SubmitTicket extends Admin_Admin
{
	function launch()
	{
		global $interface;
		$user = UserAccount::getActiveUserObj();
		$interface->assign('name', $user->firstname . ' '. $user->lastname);
		$interface->assign('email', $user->email);

		if (isset($_REQUEST['submitTicket'])){
			$subject = $_REQUEST['subject'];
			$description = $_REQUEST['description'];
			$email = $_REQUEST['email'];
			$name = $_REQUEST['name'];
			$criticality = $_REQUEST['criticality'];
			if (isset($_REQUEST['component'])){
				$component = $_REQUEST['component'];
				if (is_array($component)){
					$component = implode(', ', $component);
				}
			}else{
				$component = '';
			}


			global $serverName;
			require_once ROOT_DIR . '/sys/Email/Mailer.php';
			$mailer = new Mailer();
			$description .= "\n";
			$description .= 'Server: ' . $serverName . "\n";
			$description .= 'From: ' . $name . "\n";
			$description .= 'Criticality: ' . $criticality . "\n";
			$description .= 'Component: ' . $component . "\n";

			try {
				require_once ROOT_DIR . '/sys/SystemVariables.php';
				$systemVariables = new SystemVariables();
				if ($systemVariables->find(true) && !empty($systemVariables->ticketEmail)) {
					$result = $mailer->send($systemVariables->ticketEmail, "Aspen Discovery: $subject", $description, $email);
				} else {
					$result = false;
				}
			}catch (Exception $e) {
				//This happens when the table has not been created
				$result = false;
			}
			if ($result == true){
				$this->display('submitTicketSuccess.tpl', 'Submit Ticket');
				die();
			}else{
				$interface->assign('error', 'There was an error submitting your ticket. ' . $result->message);
			}
		}

		$this->display('submitTicket.tpl', 'Submit Ticket');
	}

	function getAllowableRoles(){
		return array('opacAdmin', 'libraryAdmin');
	}
}