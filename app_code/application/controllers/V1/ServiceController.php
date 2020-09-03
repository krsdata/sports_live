<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceController extends CI_Controller 
{
	public function __construct()
    {
        parent::__construct();

        header("Access-Control-Allow-Headers: Content-Type");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		header("Access-Control-Allow-Origin: *");  
		header("Pragma: no-cache");
		header("Cache-Control: no-cache");
		header("Expires: 0");

		date_default_timezone_set('Asia/Kolkata');

		//$this->headers = (apache_request_headers()) ? array_change_key_case(apache_request_headers()) : "";
		$this->headers = ($this->input->request_headers()) ? array_change_key_case($this->input->request_headers()) : "";

		//get logged in user id from token
		if (isset($this->headers['token'])) 
		{
			$token = explode("-", $this->headers['token']);
			$this->loggedInUserId = (is_array($token)) ? $token[0] : "";
		}

		if(!empty($_POST))
     		$this->requestData = (object)$_POST;
     	else
     		$this->requestData = json_decode(file_get_contents("php://input"));
	}

	public function appConfig()
	{
		$version = isset($this->requestData->version) ? $this->requestData->version : "";		

		//need to get this from db table
		$a = 18;	

		if($version >= $a)
			$mandatoryUpdate = false;
		else 
			$mandatoryUpdate = true;	

		$res = array(
			'mandatoryUpdate' => $mandatoryUpdate,
			'maintenance' => true,				
			'link' => 'http://matchonsports.com/newRelease/',
			'playstore' => false,
			'message' => 'Please download new update by clicking on update now button Type a message'
		);

		$this->response('app config detail', true, $res);
	}

	private function response($message, $status=FALSE, $data=array(), $errCode="11111")
	{
		$response = array();
		$response['status'] = $status;

		if($status)
			$response['object'] = $data;

		$response['message'] = $message;
		$response['errCode'] = $errCode;

		echo json_encode($response);
		die;
	}
}
?>
