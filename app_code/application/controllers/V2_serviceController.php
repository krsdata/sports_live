<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// following files need to be included
require_once(PAYTM_CONFIG_URL);
require_once(PAYTM_ENCDEC_URL);

require_once("Paytm_PHP_Checksum-master/PaytmChecksum.php");

class V2_serviceController extends CI_Controller 
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

		$this->headers = ($this->input->request_headers()) ? array_change_key_case($this->input->request_headers()) : "";
		
		//get logged in user id from token
		if (isset($this->headers['token'])) 
		{
			$token = explode("-", $this->headers['token']);
			$this->loggedInUserId = (is_array($token)) ? $token[0] : "";
		}

		if (isset($this->headers['requestedtime'])) 
		{
			$seconds = $this->headers['requestedtime']/1000;
			$this->requestedtime = date("Y-m-d H:i:s", $seconds);
		}
		else
			$this->requestedtime = false;

		if(!empty($_POST))
     		$this->requestData = (object)$_POST;
     	else
     		$this->requestData = json_decode(file_get_contents("php://input"));

     	//current date
		$this->current_date = date("Y-m-d H:i:s");

		//get mobile agent
		// $userAgent = explode("/", $_SERVER['HTTP_USER_AGENT']);
		// if ($userAgent[0] != 'okhttp') 	die("disallowed");
	}

	public function initiateTransaction()
	{
		$this->isValidToken();

		$amount = isset($this->requestData->amount) ? base64_decode($this->requestData->amount) : "";
		$userId = $this->loggedInUserId;
		$ord_id = 'MATCHON_'.rand().'_'.time();

		//order data
		$order_data = array();
		$order_data['paytm_order_id'] = $ord_id;
		$order_data['paytm_trans_id'] = $ord_id.'_XXX';
		$order_data['status'] = "PENDING";
		$order_data['amount'] = $amount;
		$order_data['user_id'] = $userId;
		$order_data['create_date'] = $this->current_date;
		$order_data['update_date'] = $this->current_date;
		$order_data['system_message'] = "initial paytm order";
		
		//insert order detail
		$this->CommonModel->insertData('paytm_order1', $order_data);

		//check record exist or not
		$isExist_paytm_order_id = $this->CommonModel->selectRecord(array('paytm_order_id' => $ord_id), 'paytm_order1', 'paytm_order_id');
		if (!$isExist_paytm_order_id)
			$this->response('unable to insert order!');

		if ($amount <= 0)
			$this->updateTransactionFailedStatusNew($ord_id, "invalid amount", json_encode($order_data));

		$paytmParams = array();
		$paytmParams["body"] = array(
		    "requestType"   => "Payment",
		    "mid"           => MID,
		    "websiteName"   => "WEBPROD",
		    "orderId"       => $ord_id,
		    "callbackUrl"   => "https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=".$ord_id,
		    "txnAmount"     => array(
		        "value"     => $amount,
		        "currency"  => "INR",
		    ),
		    "userInfo"      => array(
		        "custId"    => $userId
		    ),
		);

		/*
		* Generate checksum by parameters we have in body
		* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
		*/
		$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), MKEY);

		$paytmParams["head"] = array(
		    "signature"	=> $checksum
		);

		$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

		// $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=".MID."&orderId=".$ord_id;

		/* for Production */
		$url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=".MID."&orderId=".$ord_id;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
		$response = curl_exec($ch);

		$response = json_decode(json_decode(json_encode($response)), true);
		// echo "<pre>"; print_r($response); die;
		$response['body']['orderId'] = $ord_id;
		$response['body']['MID'] = MID;

		//order data
		$order_data = array();
		$order_data['paytm_trans_id'] = $response['body']['txnToken'];
		$order_data['system_message'] = "paytm checksum response";
		$order_data['order_data'] = json_encode($paytmParams);
		$order_data['status'] = $response['body']['resultInfo']['resultStatus'];
		$order_data['update_date'] = $this->current_date;
		$this->CommonModel->updateData('paytm_order1', $order_data, array('paytm_order_id' => $ord_id));

		if ($response['body']['resultInfo']['resultStatus'] != 'S') 
			$this->response("response", FALSE, $response);
		else
			$this->response("response", TRUE, $response);
	}

	public function insertCashNew()
	{
		$this->isValidToken();

		// $amount = isset($this->requestData->amount) ? base64_decode($this->requestData->amount) : "";
		$ord_id = isset($this->requestData->orderId) ? base64_decode($this->requestData->orderId) : "";
		$paytm_order_data = isset($this->requestData->orderData) ? base64_decode($this->requestData->orderData) : ""; //json format
		$userId = $this->loggedInUserId;

		//get inserted transaction detail
		$order_detail = $this->CommonModel->selectRecord(array('paytm_order_id' => $ord_id), 'paytm_order1', '*');
		if (!$order_detail) 
			$this->updateTransactionFailedStatusNew($ord_id, "wrong order id", $paytm_order_data);

		//paytm status validation
		if ($order_detail['status'] == 'TXN_SUCCESS') 
			$this->updateTransactionFailedStatusNew($ord_id, "transaction already done", $paytm_order_data);

		//get time diffrance b/w insert time and current time
		$currentDate = strtotime($this->current_date);
		$userLastActivity = strtotime($order_detail['create_date']);
		$timeDiff = round(abs($currentDate - $userLastActivity) / 60); //time diffrance in minutes

		//transaction cannot be older than 15 minute
		if ($timeDiff >= 15)
			$this->updateTransactionFailedStatusNew($ord_id, "time exceed", $paytm_order_data);

		//order data and db data validation
		$paytm_order_data = json_decode($paytm_order_data);

		//MID validation
		if ($paytm_order_data->MID != MID) 
			$this->updateTransactionFailedStatusNew($ord_id, "MID mismatch", json_encode($paytm_order_data));

		//MID validation
		if ($paytm_order_data->ORDERID != $order_detail['paytm_order_id']) 
			$this->updateTransactionFailedStatusNew($ord_id, "order id mismatch", json_encode($paytm_order_data));

		//paytm status validation
		if ($paytm_order_data->STATUS != 'TXN_SUCCESS') 
			$this->updateTransactionFailedStatusNew($ord_id, "wrong status", json_encode($paytm_order_data));

		//validation transaction amount
		if ($paytm_order_data->TXNAMOUNT != $order_detail['amount']) 
			$this->updateTransactionFailedStatusNew($ord_id, "amount mismatch", json_encode($paytm_order_data));

		//update order detail
		$order_data = array();
		$order_data['status'] = "TXN_SUCCESS";
		$order_data['order_data'] = json_encode($paytm_order_data);
		$order_data['system_message'] = "cash deposit request";
		$order_data['update_date'] = $this->current_date;
		$this->CommonModel->updateData('paytm_order1', $order_data, array('paytm_order_id' => $ord_id));

		//get user wallet amount
		$wamount = $this->CommonModel->selectRecord(array('userid' => $userId), 'wallet1', 'damount');
		$params = array();
		$params['damount'] = $wamount['damount'];
		$params['amount'] = $order_detail['amount'];
		$params['usr_id'] = $userId;

		$result = $this->Model->depositMoney($params);
		if ($result)
			$this->updateTransactionFailedStatusNew($ord_id, "deposit done", json_encode($paytm_order_data));
		else
			$this->updateTransactionFailedStatusNew($ord_id, "deposit failed, due to server error", json_encode($paytm_order_data));
	}

	private function updateTransactionFailedStatusNew($ord_id, $message, $order_data='')
	{
		$a_order_data = array(
			'system_message' => $message,
			'update_date' => $this->current_date,
			'order_data' => $order_data
		);

		if ($order_data) 
		{
			$order_data_decoded = json_decode($order_data);
			if (isset($order_data_decoded->STATUS)) 
				$a_order_data['status'] = $order_data_decoded->STATUS;
		}

		$this->CommonModel->updateData(
			'paytm_order1', 
			$a_order_data, 
			array('paytm_order_id' => $ord_id)
		);
		
		// $status = ($message == "deposit done") ? 1 : 0;
		// redirect($_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/paytmStatus.php?status='.$status); 

		if ($message == "deposit done")
			$this->response('deposit done', TRUE);
		else
			$this->response('deposit failed');
	}

	public function isExist()
	{
		$email = isset($this->requestData->email) ? $this->requestData->email : "";
		$mobile = isset($this->requestData->mobile) ? $this->requestData->mobile : "";

		if (!$email && !$mobile)
			$this->response("email or mobile required");

		//check email exist or not
		if ($email) 
		{
			$where = array('email1' => $email);
		    $isExistEmail = $this->CommonModel->selectRecord($where, 'user');
		    if ($isExistEmail) 
		    	$this->response("email already exist");
		}

		//check number exist or not
		if ($mobile) 
		{
			$where = array('usr_registered_number' => $mobile);
		    $isExistNumber = $this->CommonModel->selectRecord($where, 'API_user_account');
		    if ($isExistNumber) 
		    	$this->response("mobile already exist");
		}		
	    
	    $this->response("not exist", TRUE, array());
	}

	public function forgotPassword()
	{
		$mobile = isset($this->requestData->mobile) ? $this->requestData->mobile : "";
		$email = isset($this->requestData->email) ? $this->requestData->email : "";

		if (!$email && !$mobile)
			$this->response("email or mobile number required");

		$usr_result = $this->Model->forgotPassword($email, $mobile);
		if ($usr_result) 
		{
			$message = 'Your match on sports email and password. password: '.$usr_result['password'].' email: '.$usr_result['email'];

			$mobile = str_replace("+91", "", $mobile);
			$this->sendSms($mobile, $message);

			$this->response($message, TRUE, $usr_result);
		}
		else
			$this->response('username not found');
	}

	public function updateUserProfile()
	{
		$this->isValidToken();
		//-- get request params

		$usr_data = array();
		// $usr_data['age'] = isset($this->requestData->age) ? $this->requestData->age : "";

		// $usr_data['gender'] = isset($this->requestData->gender) ? $this->requestData->gender : "";

		// $usr_data['state'] = isset($this->requestData->state) ? $this->requestData->state : "";

		// $usr_data['city'] = isset($this->requestData->city) ? $this->requestData->city : "";

		// $usr_data['pin'] = isset($this->requestData->pin) ? $this->requestData->pin : "";

	    $refferCode = isset($this->requestData->refferCode) ? $this->requestData->refferCode : "";

	    $userId = $this->loggedInUserId;



	    //insert user profile picture

        if (isset($_FILES['file']) && $_FILES['file']['name'] != '')

        {

            $profile_pic = $this->single_upload(USER_PROFILE_IMAGE_PATH);

            if (!$profile_pic)

            	$this->response('Error: Unable to upload profile picture!');



            $this->CommonModel->updateData('user', array('image' => $profile_pic), array('email' => $userId));



            $old_pic = $this->CommonModel->selectRecord(array('email' => $userId), 'user', 'image');

            if (is_file(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']))

				@unlink(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']);



            $usr_data['image'] = base_url().USER_PROFILE_IMAGE_PATH.$profile_pic;

            $this->response('profile updated successfully!', TRUE, $usr_data);

        }



        $this->response('Error: unable to upload profile image!');

	}

	public function logUpload()
	{
		//insert user profile picture
        if (isset($_FILES['file']) && $_FILES['file']['name'] != '')
        {
            $proof = $this->single_upload(LOG_FILE_PATH);
            if (!$proof)
            	$this->response('Error: Unable to upload proof!');
            else
            	$this->response('file uploaded successully!', TRUE, array());
        }
    	else
    		$this->response('Error: There is no file');
	}

	//-- function for image upload 
    public function single_upload($path, $name="", $obj_name='file')
    {        
        //$allowed_types = array('gif', 'jpg', 'png', 'jpeg', 'pdf', 'webp');
        $file_type = $_FILES[$obj_name]['type'];
        $extension = explode("/", $file_type);

        // if (!in_array($extension[1], $allowed_types))
        // 	$this->response("Error: allowed file types are => jpg, jpeg, png, gif, pdf, webp");

        if ($name)
            $_FILES['attatchment']['name'] = $name.'.'.$extension;
        else
            $_FILES['attatchment']['name'] = time().".".$extension[1];

        $_FILES['attatchment']['type'] = $file_type;
        $_FILES['attatchment']['tmp_name'] = $_FILES[$obj_name]['tmp_name'];
        $_FILES['attatchment']['error'] = $_FILES[$obj_name]['error'];
        $_FILES['attatchment']['size'] = $_FILES[$obj_name]['size'];

        $config['upload_path'] = $path;
        $config['allowed_types'] = '*';

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if($this->upload->do_upload('attatchment'))
        {
            $fileData = $this->upload->data();

            if ($fileData['file_name']) 
                return $fileData['file_name'];
            else
                return FALSE;
        }
        else
        	$this->response('"Error: "'.$this->upload->display_errors());
    }

    public function updateReferCode()
    {
    	//delete records from refer1 table
    	$this->CommonModel->deleteRecord('refer1', array(1 => 1));

    	//get user data
    	$users = $this->CommonModel->selectRecords(array(), 'user', 'email, username');
    	
    	$count = 0;
    	foreach ($users as $user) 
    	{
    		//generate random reffer code
		    $userName = str_shuffle(str_replace(' ', '', $user['username']));
		    $userRefferCode = strtoupper($userName[0].$userName[1].$this->generateRandomString(6));

		    //insert reffer data
    		$refer_data = array();
    		$refer_data['userid'] = $user['email'];
    		$refer_data['refid'] = $userRefferCode;
    		//$refer_data['count1'] = 0;
    		$this->CommonModel->insertData('refer1', $refer_data);

    		$count++;
    	}

    	echo $count;
    }

	public function userSignup()
	{
		//-- get request params
		$usr_data = array();
		$usr_data['name'] = isset($this->requestData->name) ? $this->requestData->name : "";
		$usr_data['email1'] = $usr_data['username'] = isset($this->requestData->email) ? $this->requestData->email : "";
	    $usr_data['mobile'] = isset($this->requestData->mobile) ? $this->requestData->mobile : "";
	    $usr_data['password'] = isset($this->requestData->password) ? $this->requestData->password : "";
	    $refferCode = isset($this->requestData->refferCode) ? $this->requestData->refferCode : "";
	    $referral_userId = '';

	    if (!$usr_data['email1'] || !$usr_data['password'] || !$usr_data['name'] || !$usr_data['mobile']) 
	    	$this->response('name, email, mobile and password are mandatory!');	    

	    //check email existance
	    if ($this->CommonModel->selectRecord(array('email1' => $usr_data['email1']), 'user', 'email'))
	    	$this->response('email already exist!');
	    else if ($this->CommonModel->selectRecord(array('username' => $usr_data['username']), 'user', 'email'))
	    	$this->response('username already exist!');

	    //insert user
	    $inserted_id = $this->CommonModel->insertData('user', $usr_data);

	    //insert data in wallet1 table
    	$wallet_data = array();
    	$wallet_data['userid'] = $inserted_id;
    	$wallet_data['damount'] = 0;
    	$wallet_data['wamount'] = 0;
    	$wallet_data['bamount'] = 100;
    	$this->CommonModel->insertData('wallet1', $wallet_data);

    	//insert transaction 
		//transaction data
		$trans_data = array();
		$trans_data['userid'] = $inserted_id;
		$trans_data['amount'] = 100;
		$trans_data['type'] = "BONUS";
		$this->CommonModel->insertData('transaction', $trans_data);
		
	    if ($refferCode) 
	    {
	    	//get user id of reffer code
	    	$isExistRefferCode = $this->CommonModel->selectRecord(array('refid' => $refferCode), 'refer1', 'userid');
	    	if ($isExistRefferCode)
	    	{	
	    		$referral_userId = $isExistRefferCode['userid'];

	    		//get wallet bonus amount
	    		$bamount = $this->CommonModel->selectRecord(array('userid' => $referral_userId), 'wallet1', 'bamount');

				//update wallet bonus amount of user
				$this->CommonModel->updateData('wallet1', array('bamount' => $bamount['bamount']+100), array('userid' => $referral_userId));

				//insert transaction 
				//transaction data
				$trans_data = array();
				$trans_data['userid'] = $referral_userId;
				$trans_data['amount'] = 100;
				$trans_data['type'] = "BONUS";
				$this->CommonModel->insertData('transaction', $trans_data);
	    	}
	    }

	    //generate random reffer code
	    $userName = str_shuffle(str_replace(' ', '', $usr_data['name']));
	    $userRefferCode = strtoupper($userName[0].$userName[1].$this->generateRandomString(6));

	    //insert refer code
	    $reffer_data = array();
    	$reffer_data['userid'] = $inserted_id;
    	$reffer_data['refid'] = $userRefferCode;
    	//$reffer_data['count1'] = 0;
    	$reffer_data['referral_userId'] = $referral_userId;
		$this->CommonModel->insertData('refer1', $reffer_data);

	    //get inserted user detail
	    $where = array('email' => $inserted_id);
	    $isExistUser = $this->CommonModel->selectRecord($where, 'user', 'email');
	    if ($isExistUser)
	    {
	    	$usr_res = $this->Model->getUserDetail($inserted_id);
	    	$usr_res["token"] = $this->createToken($inserted_id);

			$this->response('signup successully!', TRUE, $usr_res);
	    }
	    else
	    	$this->response('login failed!');
	}

	public function getReferCount()
	{
		$this->isValidToken();

		$userId = isset($this->requestData->userId) ? $this->requestData->userId : "";

		$referalCount = $this->Model->getReferCount($userId);
	    if ($referalCount) 
	    {
	    	if ($userId)
	    		$referalCount = $referalCount[0];
	    	
	    	$this->response('user detail', TRUE, $referalCount);
	    }
	    else
	    	$this->response('user not found');
	}

	public function userSignin()
	{
		//-- get request params
		$login_data = array();
		$login_data['email1'] = isset($this->requestData->email) ? $this->requestData->email : "";
	    $login_data['password'] = isset($this->requestData->password) ? $this->requestData->password : "";		

	    if (!$login_data['email1'] || !$login_data['password']) 
	    	$this->response('email and password are mandatory!');

	    $isExistUser = $this->CommonModel->selectRecord($login_data, 'user', 'email');
	    if ($isExistUser) 
	    {
	    	$usr_res = array();
	    	$userId = $isExistUser['email'];

			$usr_res = $this->Model->getUserDetail($userId);

	      	//get existing or create new token
	      	$token = ($this->isValidToken($userId."-".$usr_res['token'], TRUE));
	      	$usr_res["token"] = ($token) ? $token : $this->createToken($userId);	      

	        $this->response('login successully', TRUE, $usr_res);
	    }
	    else
	    	$this->response('Invalid Credentials');
	}

	public function getUser()
	{
		$this->isValidToken();

		$userId = isset($this->requestData->userId) ? $this->requestData->userId : "";

		$isExistUser = $this->CommonModel->selectRecord(array('email' => $userId), 'user');
	    if ($isExistUser) 
	    {
	    	$usr_res = $this->Model->getUserDetail($userId);

	    	$this->response('user detail', TRUE, $usr_res);
	    }
	    else
	    	$this->response('user not found');
	}

	//get match live/upcoming/result
	public function getMatch()
	{
		$this->isValidToken();		

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['matchType'] = isset($this->requestData->matchType) ? $this->requestData->matchType : "";
		$params['requestedtime'] = $this->requestedtime;

		$match = $this->Model->getMatch($params);
		if ($match) 
			$this->response('match', TRUE, $match);
		else
			$this->response('match not found');
	}

	//get match squad before starting match
	public function getMatchSquad()
	{
		$this->isValidToken();		

		$result = array();
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";

		if (!$matchId) 
	    	$this->response('matchId is mandatory!');

	    //get match suad players
	    $matchSquad = $this->Model->getMatchSquad($matchId, 1);

	    if ($matchSquad) 
	    {
	    	$players = array();
	    	$team1_id = $matchSquad[0]['sqd_team_id'];
	    	$team2_id = $matchSquad[1]['sqd_team_id'];

	    	$players[$team1_id] = $matchSquad[0]['sqd_ply_ids'] ? explode(",", trim($matchSquad[0]['sqd_ply_ids'])) : array();
	    	$players[$team2_id] = $matchSquad[1]['sqd_ply_ids'] ? explode(",", trim($matchSquad[1]['sqd_ply_ids'])) : array();	    	

	    	//get team players
	    	$result = $this->getPlayers($players, array($team1_id, $team2_id), $matchId);
	    	if ($result) 
	    	{
		    	$result['matchId'] = $matchId;
		    	$result['isLineup'] = ($matchSquad[0]['sqd_ply11_ids'] || $matchSquad[1]['sqd_ply11_ids']) ? 1 : 0;

		    	$this->response('match squad', TRUE, $result);
		    }
	    }

	    $this->response('there is no such upcoming match', FALSE, $result);
	}

	//create team on match squad
	public function createTeam()
	{
		log_message('error', json_encode($this->requestData));
		$this->isValidToken();		

		$userTeamId = isset($this->requestData->userTeamId) ? $this->requestData->userTeamId : "";
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$playersId = isset($this->requestData->playersId) ? $this->requestData->playersId : "";
		$captainId = isset($this->requestData->captainId) ? $this->requestData->captainId : "";
		$viceCaptainId = isset($this->requestData->viceCaptainId) ? $this->requestData->viceCaptainId : "";
		$userId = $this->loggedInUserId;

		if (!$matchId || !$playersId || !$captainId || !$viceCaptainId) 
	    	$this->response('matchId, playersId, captainId and viceCaptainId are mandatory!');

	    $isTeamExist = array_keys(get_object_vars(json_decode($playersId)));
	    if (!isset($isTeamExist[0]) || !isset($isTeamExist[1]))
	    	$this->response('players should be belongs to both team');

	    $is11PlayerExist = get_object_vars(json_decode($playersId));
	    $totalPlyCnt = array_merge($is11PlayerExist[$isTeamExist[0]], $is11PlayerExist[$isTeamExist[1]]);
	    if (count($totalPlyCnt) != 11)
	    	$this->response('please select 11 players');
	    
	    //check match is upcoming or not
	    $isUpcomingMatch = $this->CommonModel->selectRecord(array('mat_id' => $matchId, 'mat_status' => 1), 'API_matches', 'mat_id');
	    if (!$isUpcomingMatch) 
	    	$this->response('match moved to live!');

	    //check match exist or not for create team
		$isExistMatch = $this->CommonModel->selectRecord(array('mat_id' => $matchId, 'mat_status != ' => 1), 'API_matches', 'mat_id');
		if ($isExistMatch)
			$this->response('match not found.');

		//insert team data
		$usr_team_data = array();
		$usr_team_data['user_id'] = $userId;
		$usr_team_data['match_id'] = $matchId;
		$usr_team_data['players'] = serialize($playersId);
		$usr_team_data['caption'] = $captainId;
		$usr_team_data['vice_caption'] = $viceCaptainId;
		
		//get count of user created team
		$usr_team_cnt = $this->CommonModel->selectRecord(array('user_id' => $userId, 'match_id' => $matchId), 'user_team', 'COUNT(user_team_id) AS usr_team_cnt');
		$usr_team_data['user_team_number'] = $usr_team_cnt['usr_team_cnt']+1;

	    $inserted_id = $this->CommonModel->insertData('user_team', $usr_team_data);

	    //update player selected count (for select by percent)
	    $ply_ids = array_merge($is11PlayerExist[$isTeamExist[0]], $is11PlayerExist[$isTeamExist[1]]);
	    $this->Model->updatePlayerSelectCount($ply_ids, $matchId, 'INC', $captainId, $viceCaptainId);

	    $this->response('team created!', TRUE, array('userTeamId' => $inserted_id));
	}

	//create team on match squad
	public function editTeam()
	{
		log_message('error', json_encode($this->requestData));
		$this->isValidToken();		

		$userTeamId = isset($this->requestData->userTeamId) ? $this->requestData->userTeamId : "";
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$playersId = isset($this->requestData->playersId) ? $this->requestData->playersId : "";
		$captainId = isset($this->requestData->captainId) ? $this->requestData->captainId : "";
		$viceCaptainId = isset($this->requestData->viceCaptainId) ? $this->requestData->viceCaptainId : "";
		$userId = $this->loggedInUserId;

		if (!$matchId || !$playersId || !$captainId || !$viceCaptainId || !$userTeamId) 
	    	$this->response('matchId, playersId, userTeamId, captainId and viceCaptainId are mandatory!');

	    $isTeamExist = array_keys(get_object_vars(json_decode($playersId)));
	    if (!isset($isTeamExist[0]) || !isset($isTeamExist[1]))
	    	$this->response('players should be belongs to both team');

	    $is11PlayerExist = get_object_vars(json_decode($playersId));
	    $totalPlyCnt = array_merge($is11PlayerExist[$isTeamExist[0]], $is11PlayerExist[$isTeamExist[1]]);
	    if (count($totalPlyCnt) != 11)
	    	$this->response('please select 11 players');

	    //check match is upcoming or not
	    $isUpcomingMatch = $this->CommonModel->selectRecord(array('mat_id' => $matchId, 'mat_status' => 1), 'API_matches', 'mat_id');
	    if (!$isUpcomingMatch) 
	    	$this->response('match moved to live!');

	    //get old players
	    $old_team = $this->CommonModel->selectRecord(array('user_team_id' => $userTeamId), 'user_team', 'players, caption, vice_caption');
	    $old_plyrs = get_object_vars(json_decode(unserialize($old_team['players'])));
	    $old_teams = array_keys($old_plyrs);
	    $old_ply_ids = array_merge($old_plyrs[$old_teams[0]], $old_plyrs[$old_teams[1]]);

	    //get old players and update count (-1) for those players
	    //update player selected count (for select by percent)
	    $this->Model->updatePlayerSelectCount($old_ply_ids, $matchId, 'DEC', $old_team['caption'], $old_team['vice_caption']);

	    //update user players
		$usr_team_data = array();
		$usr_team_data['players'] = serialize($playersId);
		$usr_team_data['caption'] = $captainId;
		$usr_team_data['vice_caption'] = $viceCaptainId;
	    $this->CommonModel->updateData('user_team', $usr_team_data, array('user_team_id' => $userTeamId));

	    //update player selected count (for select by percent)
	    $ply_ids = array_merge($is11PlayerExist[$isTeamExist[0]], $is11PlayerExist[$isTeamExist[1]]);
	    $this->Model->updatePlayerSelectCount($ply_ids, $matchId, 'INC', $captainId, $viceCaptainId);

	    $this->response('team updated!', TRUE, array('userTeamId' => $userTeamId));
	}

	public function getTeam()
	{
		$params = array();
		$team = array();
		$result = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['userTeamId'] = isset($this->requestData->userTeamId) ? $this->requestData->userTeamId : "";
		$params['loggedInUserId'] = $this->loggedInUserId;
		$params['userId'] = isset($this->requestData->userId) ? $this->requestData->userId : "";

		if (!$params['matchId']) 
	    	$this->response('matchId is mandatory!');

		$usr_team = $this->Model->getTeam($params);

		foreach ($usr_team as $key => $value) 
		{
			$team_players = get_object_vars(json_decode(unserialize($value['players'])));
			$team_ids = array_keys($team_players);

		    $team_players = $this->getPlayers($team_players, $team_ids, $params['matchId']);

		    if ($team_players)
		    {
		    	$team[$key] = $team_players;
		    	$team[$key]['userTeamId'] = $value['user_team_id'];
		    	$team[$key]['matchId'] = $value['match_id'];
		    	$team[$key]['earnPoints'] = $value['usr_team_earn_points'];
		    	$team[$key]['teamNumber'] = $value['teamNumber'];
				$team[$key]['captainId'] = $value['caption'];
		    	$team[$key]['viceCaptainId'] = $value['vice_caption'];
		    	
		    	$sqd_ply11_ids = $this->CommonModel->selectRecord(array('sqd_mat_id' => $params['matchId'], 'sqd_ply11_ids != ' => ""), 'API_match_squad', 'COUNT(DISTINCT sqd_ply11_ids) AS sqd_ply11_ids');
		    	$team[$key]['isLineup'] = ($sqd_ply11_ids['sqd_ply11_ids']) ? 1 : 0;
			}
		}

		//print_r($team); die;
    	if ($params['userTeamId'] && $params['matchId'] && count($team) > 0)
    	{
    		$result = $team[0];

			$this->response('players', TRUE, $result);
    	}
		else if (count($team) > 0)
		{
			$result = $team;

			$this->response('players', TRUE, $result);
		}
		else
			$this->response('not found!');
	}

	private function getPlayers($result, $team_ids, $matchId)
	{
		$team1_players = array();
		$team2_players = array();
		$team1_deatil = array();
		$team2_deatil = array();
		//print_r($team_ids); die;

		//get team detail
		$columns = 'team_id AS teamId, team_name AS name, team_short_name AS shortName, team_logo_url AS logoImage';

		if (isset($team_ids[0])) 
		{
			//get team1 players
			$team1_players = $this->Model->getPlayerDetail($result[$team_ids[0]], $matchId, $team_ids[1]);

			//get player point of recent matches
			foreach ($team1_players as $key => $player) 
			{
				//get player recent played matches
				$ply_mat = $this->Model->getPlayerMatch($player['playersId'], $matchId);

				//get all points of match
				$team1_players[$key]['ply_fantasy_points'] = $ply_mat ? array_sum(array_column($ply_mat, 'pnt_total')) : 0;	
			}

			//get team name1
			$team1_deatil = $this->CommonModel->selectRecord(array('team_id' => $team_ids[0]), 'API_team', $columns);
		}

    	if (isset($team_ids[1])) 
    	{
    		//get team2 players
    		$team2_players = $this->Model->getPlayerDetail($result[$team_ids[1]], $matchId, $team_ids[1]);

    		//get player point of recent matches
			foreach ($team2_players as $key => $player) 
			{
				//get player recent played matches
				$ply_mat = $this->Model->getPlayerMatch($player['playersId'], $matchId);

				//get all points of match
				$team2_players[$key]['ply_fantasy_points'] = $ply_mat ? array_sum(array_column($ply_mat, 'pnt_total')) : 0;	
			}
			
    		//get team name2
			$team2_deatil = $this->CommonModel->selectRecord(array('team_id' => $team_ids[1]), 'API_team', $columns);
    	}

		$result = array();
	    $result['team1'] = $team1_deatil;
	    $result['team1']['players'] = $team1_players;
	    $result['team2'] = $team2_deatil;
	    $result['team2']['players'] = $team2_players;

	    return $result;
	}

	public function getMatchContestTypes()
	{
		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	

		if (!$params['matchId']) 
	    	$this->response('matchId is mandatory!');

	    $contest = $this->Model->getMatchContestTypes($params);

		$this->response('match contests name', TRUE, $contest);
	}

	//-- get Contest List Of Match contest
	public function getMatchContests()
	{
		$this->isValidToken();	

		$params = array();
		$result = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['contestType'] = isset($this->requestData->contestType) ? $this->requestData->contestType : "";
		$params['userId'] = $this->loggedInUserId;

		if (!$params['matchId']) 
	    	$this->response('matchId is mandatory!');

	    //get all contest type
	    if ($params['contestType'])
	    {
	    	$where = array('type' => $params['contestType']);
	    	$params['limit'] = '';
	    }
	    else
	    {
	    	$where = '';
	    	$params['limit'] = 'limit 2';
	    }

	    $contestTypes = $this->CommonModel->selectRecords($where, 'contestname', 'type AS contestType, name AS contestName, slogan AS contestTitle, CONCAT("'.base_url().CONTEST_TYPE_IMAGE_PATH.'", image) AS contestImage');

	    $i = 0;
	    $totalContestsCount = 0;
	    foreach ($contestTypes as $contestType) 
	    {
	    	$params['contestType'] = $contestType['contestType'];

	    	$result[$i] = $contestType;

	    	$contests = $this->Model->getMatchContest($params);

	    	$result[$i]['contests'] = $contests['contests'];
	    	$result[$i]['totalCount'] = $contests['totalCount'];

	    	$totalContestsCount = $totalContestsCount+array_sum(array_column($contests, 'loggedInUserJoinedCount'));

	    	$i++;
	    }

	    //get total created teams
	    $where = array('user_id' => $params['userId'], 'match_id' => $params['matchId']);	    
	    $myTeamsCount = $this->CommonModel->selectRecord($where, 'user_team', 'COUNT(user_team_id) AS myTeamsCount');

	    $response = array();
	    $response['all_contests'] = $result;
	    $response['myContestsCount'] = $totalContestsCount;
	    $response['myTeamsCount'] = $myTeamsCount['myTeamsCount'];

		$this->response('contests', TRUE, $response);
	}

	//-- get Contest detail
	public function getContestDetail()
	{
		$this->isValidToken();		

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$params['contestType'] = isset($this->requestData->contestType) ? $this->requestData->contestType : "";
		$params['userId'] = $this->loggedInUserId;

		if (!$params['matchId'] || !$params['contestId'] || !$params['contestType']) 
	    	$this->response('matchId, contestId and contestType are mandatory!');

	    $contest = $this->Model->getMatchContest($params);
	    $contestDetails = $contest['contests'][0];

		$contestDetails['prizeBreakup'] = $this->Model->getContestPrizePool($params);
		
		$this->response('contest detail', TRUE, $contestDetails);
	}

	public function getContestCount()
	{
		$this->isValidToken();		

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$params['contestType'] = isset($this->requestData->contestType) ? $this->requestData->contestType : "";
		$params['userId'] = $this->loggedInUserId;

		if (!$params['matchId'] || !$params['contestId'] || !$params['contestType']) 
	    	$this->response('matchId, contestId and contestType are mandatory!');

	    $contest = $this->Model->getMatchContest($params);
	    $contestCount = $contest['contests'][0];

		$contestCount['prizeBreakup'] = $this->Model->getContestPrizePool($params);
		
		$this->response('contest count', TRUE, $contestCount);
	}

	public function getContestPrizePool()
	{
		$this->isValidToken();
		
		$params = array();

		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";



		$prizePool = $this->Model->getContestPrizePool($params);



		$this->response('prize pool', TRUE, $prizePool);

	}

	//-- get User Joined Contest
	public function getUserJoinedMatch()
	{
		$this->isValidToken();

		$params = array();
		$params['matchType'] = isset($this->requestData->matchType) ? $this->requestData->matchType : "";	
		$params['userId'] = $this->loggedInUserId;

	    $joinedMatches = $this->Model->getUserJoinedMatch($params);

	    //get matches
	    $params = array();
	    $result = array();
	    $params['userId'] = $this->loggedInUserId;
	    $i = 0;

	    foreach ($joinedMatches as $key => $value) 
	    {
	    	$params['matchId'] = $value['matchId'];
	    	$params['matchType'] = $value['matchStatus'];
	    	$response = $this->Model->getMatch($params);

	    	if ($response) 
	    	{
	    		$a = array_merge($joinedMatches[$key], $response[0]);
	    		$result[$i] = $a;

	    		$i++;
	    	}
	    }

		$this->response('contests', TRUE, $result);
	}

	//-- get User Joined Contest
	public function getUserJoinedContestsOfMatch()
	{
		$this->isValidToken();

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		//$matchType = isset($this->requestData->matchType) ? $this->requestData->matchType : "";	
		$params['userId'] = $this->loggedInUserId;

		if (!$params['matchId'] && !$matchType) 
	    	$this->response('matchId, matchType is mandatory!');

	    //$params['matchType'] = explode(",", $matchType);
	    $contest = $this->Model->getUserJoinedContestsOfMatch($params);

		$this->response('contests', TRUE, $contest);
	}

	//-- get User Joined Contest
	public function getUserJoinedContestCount()
	{
		$this->isValidToken();

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$matchType = isset($this->requestData->matchType) ? $this->requestData->matchType : "";	
		$params['userId'] = $this->loggedInUserId;

		if (!$params['matchId'] && !$matchType) 
	    	$this->response('matchId, matchType is mandatory!');
	    else
	    	$params['matchType'] = explode(",", $matchType);
	    
	    $contest = $this->Model->getUserJoinedContestCount($params);

		$this->response('user joined contests count', TRUE, $contest);
	}

	//-- get Contest List Of Match
	public function getContestTeam()
	{
		$this->isValidToken();		

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	
		$params['userId'] = $this->loggedInUserId;
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";

		if (!$params['matchId'] || !$params['contestId']) 
	    	$this->response('matchId and contestId are mandatory!');

	    $joinedTeams = $this->CommonModel->selectRecords(array('user_id' => $params['userId'], 'contests_id' => $params['contestId'], 'match_id' => $params['matchId']), 'matches_joined', 'team_id');
	    $joinedTeamIds = array_column($joinedTeams, 'team_id');

	    $availableTeams = $this->CommonModel->selectRecords(array('match_id' => $params['matchId']), 'user_team', 'user_team_id', array(), '', array('user_team_id', $joinedTeamIds));
	    $availableTeamIds = array_column($availableTeams, 'user_team_id');

	    $result = array();
	    $result['joinedTeamIds'] = $joinedTeamIds;
	    $result['availableTeamIds'] = $availableTeamIds;

	    $this->response('user joined contest team', TRUE, $result);
	}

	public function getTeamsToJoinContest()
	{
		$this->isValidToken();		

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	
		$params['userId'] = $this->loggedInUserId;
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$params['contestType'] = isset($this->requestData->contestType) ? $this->requestData->contestType : "";

		if (!$params['matchId'] || !$params['contestId']) 
	    	$this->response('matchId and contestId are mandatory!');

	    $contest = $this->Model->getTeamsToJoinContest($params);

	    if ($contest)
			$this->response('contests', TRUE, $contest);
		else
			$this->response('not found');
	}

	public function joinContest()
	{
		log_message('error', json_encode($this->requestData));
		$this->isValidToken();

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	
		$params['userId'] = $this->loggedInUserId;
		$contestId = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$teamIds = isset($this->requestData->teamId) ? eval("return " . urldecode($this->requestData->teamId) . ";") : array();

		if (!$params['matchId'] || !$contestId || count($teamIds) == 0) 
	    	$this->response('matchId and contestId and teamId are mandatory!');
	    
	    if (count($teamIds) > 1)
	    {
	    	$contestRes = $this->CommonModel->selectRecord(array('contests_id' => $contestId), 'contests', 'multi, no_of_teams');

	    	//check multi join status
	    	if ($contestRes['multi'] == 1)
	    		$this->response("you can't join the contest with multiple teams");	
	    	else
	    	{
	    		$no_of_join = $this->CommonModel->selectRecord(array('contests_id' => $contestId), 'matches_joined', 'MAX(no_of_join) AS no_of_join');
	            if ($contestRes['no_of_teams']-$no_of_join['no_of_join'] < count($teamIds)) 
	            	$this->response("can not join more then ".($contestRes['no_of_teams']-$no_of_join['no_of_join'])." team(s).");
	    	}
	    }

	    foreach ($teamIds as $teamId) 
	    {
	    	$params['teamId'] = $teamId;
	    	$params['contestId'] = $contestId;

		    //$isJoined = $this->checkStatus($params);
			$isJoined = $this->Model->joinContest($params);
			if (!$isJoined[0])
				$this->response($isJoined[1]);
		}

		$this->response('contest joined', TRUE);
	}

	/*public function joinContest()
	{
		$this->isValidToken();

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	
		$params['userId'] = $this->loggedInUserId;
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$params['teamId'] = isset($this->requestData->teamId) ? $this->requestData->teamId : "";

		if (!$params['matchId'] || !$params['contestId'] || !$params['teamId']) 
	    	$this->response('matchId and contestId and teamId are mandatory!');

		//$isJoined = $this->checkStatus($params);
		$isJoined = $this->Model->joinContest($params);
		if ($isJoined[0])
			$this->response('contest joined', TRUE);
		else
			$this->response($isJoined[1]);
	}*/

	/*public function checkStatus($params)
	{
		// Open the file to get existing content
		$current = file_get_contents('test.txt');

		$params['cur_status'] = $current;
		log_message('ERROR', json_encode($params));

		if ($current == "FREE") 
			$isJoined = $this->Model->joinContest($params);
		else
		{
			while(true) 
			{
				if ($current == "BUSY") 
				{
					usleep(1000);
					$this->checkStatus($params);
				}
				else
					$isJoined = $this->Model->joinContest($params);
			}
		}

		return $isJoined;
	}*/

	public function getUserWallet()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;		

		//get user wallet
		$userWallet = $this->CommonModel->selectRecord(array('userid' => $userId), 'wallet1', 'damount AS depositAmount, wamount AS winningAmount, bamount AS bonusAmount');
		
		$userWallet['message'] = 'Due to COVID-19 corona virus, our team not able to process your withdraw request at this time. Please keep patience your money is safe. It will active soon.';
		$userWallet['withdraw'] = true;

		$this->response('user wallet', TRUE, $userWallet);
	}

	public function getScore()
	{
		$this->isValidToken();

		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";	

		if (!$matchId) 
	    	$this->response('matchId are mandatory!');

	    $where = array();
	    $where['mat_id'] = $matchId;

	    //get score
	    $scoreCard = $this->CommonModel->selectRecord($where, 'API_matches', 'mat_score');

	    // print_r($scoreCard); die;
	    if ($scoreCard && isset($scoreCard['mat_score']))
	    {
	    	$score = unserialize($scoreCard['mat_score']);
	    	//echo json_encode($score); die;
	    	// print_r($score); die;

	    	$result = array();
	    	$result['matchId'] = $score['response']['match_id'];
	    	$result['title'] = $score['response']['title'];
	    	$result['short_title'] = $score['response']['short_title'];
	    	$result['subtitle'] = $score['response']['subtitle'];
	    	$result['format'] = $score['response']['format'];
	    	$result['format_str'] = $score['response']['format_str'];
	    	$result['status'] = $score['response']['status'];
	    	$result['status_str'] = $score['response']['status_str'];
	    	$result['status_note'] = $score['response']['status_note'];
	    	$result['game_state'] = $score['response']['game_state'];
	    	$result['game_state_str'] = $score['response']['game_state_str'];
	    	$result['teama'] = $score['response']['teama'];
	    	$result['teamb'] = $score['response']['teamb'];
	    	$result['winning_team_id'] = $score['response']['winning_team_id'];    	

	    	$result['inning1'] = array();
	    	$result['inning2'] = array();

	    	if ($result['format'] == 2) 
	    	{
		    	$result['inning3'] = array();
		    	$result['inning4'] = array();
		    }
		    
		    $i = 1;
		    foreach ($score['response']['innings'] as $key => $inning) 
		    {

		    	$result['inning'.$i]['batting_team_id'] = $inning['batting_team_id'];
		    	$result['inning'.$i]['fielding_team_id'] = $inning['fielding_team_id'];
		    	$result['inning'.$i]['scores'] = $inning['scores'];
		    	$result['inning'.$i]['scores_full'] = $inning['scores_full'];

		    	$i++;
		    }	

	    	$this->response('match score', TRUE, $result);
	    }
	    else
	    	$this->response('match score not found!');
	}

	public function getLivePlayersPoint()
	{
		$this->isValidToken();

		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$userTeamId = isset($this->requestData->userTeamId) ? $this->requestData->userTeamId : "";
		$userId = $this->loggedInUserId;

		if (!$matchId || !$userTeamId) 
	    	$this->response('matchId and userTeamId are mandatory!');

		//get user match created team record
		$where = array(
			'match_id' => $matchId,
			'user_id' => $userId,
			'user_team_id' => $userTeamId
		);

		$teamPlayers = $this->CommonModel->selectRecord($where, 'user_team', 'user_team_id, players, caption, vice_caption');
		if ($teamPlayers) 
		{
			$team_ids = array();
			$captainId = $teamPlayers['caption'];
			$viceCaptainId = $teamPlayers['vice_caption'];

			$playersWithTeam = get_object_vars(json_decode(unserialize($teamPlayers['players'])));

			foreach ($playersWithTeam as $teamId => $players) 
				array_push($team_ids, $teamId);

			$matchPlayers = $this->getPlayers($playersWithTeam, $team_ids, $matchId);
			//print_r($matchPlayers); die;		

			foreach ($matchPlayers['team1']['players'] as $key => $value) 
			{
				$playerId = $value['playersId'];

				$playerPoint = $this->CommonModel->selectRecord(array('pnt_ply_id' => $playerId), 'API_player_point', 'pnt_total');
				$playerPoint = $playerPoint['pnt_total'];

				$points = ($playerId == $captainId) ? ($playerPoint*2) : (($playerId == $viceCaptainId) ? ($playerPoint*1.5) : $playerPoint);

				$matchPlayers['team1']['players'][$key]['points'] = $points;
			}

			foreach ($matchPlayers['team2']['players'] as $key => $value) 
			{
				$playerId = $value['playersId'];

				$playerPoint = $this->CommonModel->selectRecord(array('pnt_ply_id' => $playerId), 'API_player_point', 'pnt_total');

				$playerPoint = $playerPoint['pnt_total'];

				$points = ($playerId == $captainId) ? ($playerPoint*2) : (($playerId == $viceCaptainId) ? ($playerPoint*1.5) : $playerPoint);

				$matchPlayers['team2']['players'][$key]['points'] = $points;
			}

			$matchPlayers['captainId'] = $captainId;
			$matchPlayers['viceCaptainId'] = $viceCaptainId;
			$matchPlayers['matchId'] = $matchId;
			$matchPlayers['userTeamId'] = $userTeamId;

			$this->response('player points for match', TRUE, $matchPlayers);
		}
		else
			$this->response('points not available');
	}

	public function getLeaderboard()
	{
		$this->isValidToken();

		$params = array();
		$params['matchId'] = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$params['contestId'] = isset($this->requestData->contestId) ? $this->requestData->contestId : "";
		$userId = $this->loggedInUserId;

		if (!$params['matchId'] || !$params['contestId']) 
	    	$this->response('matchId and contestId are mandatory!');

	    $leaderboard = $this->Model->getLeaderboard($params);
	    
	    $key = array_keys(array_column($leaderboard, 'userId'), $userId);
	    
	    $data = array();
	    $data['loggedInUser'] = array_values(array_intersect_key($leaderboard, array_flip($key)));
	    $data['allUser'] = array_slice(array_diff_key($leaderboard, array_flip($key)), 0, 100);

	    $this->response('contest leaderboard', TRUE, $data);
	}

	public function savePANDetail()
	{
		$this->isValidToken();

		$params = array();
		// $params['usr_registered_number'] = isset($this->requestData->registeredNumber) ? $this->requestData->registeredNumber : "";
		// $params['usr_idProof_type'] = isset($this->requestData->idProofType) ? $this->requestData->idProofType : "";
		// $params['usr_idProof_number'] = isset($this->requestData->idProofNumber) ? $this->requestData->idProofNumber : "";
		$params['usr_pan_number'] = isset($this->requestData->panNumber) ? $this->requestData->panNumber : "";
		$params['usr_pan_name'] = isset($this->requestData->panName) ? $this->requestData->panName : "";
		$params['usr_id'] = $this->loggedInUserId;

		//insert user profile picture
        if (isset($_FILES['file']) && $_FILES['file']['name'] != '')
        {
            $proof = $this->single_upload(USER_PROOF_PATH);
            if (!$proof)
            	$this->response('Error: Unable to upload proof!');       

            $params['usr_pan_image'] = $proof;
        }
        else
        {
        	//--get usr_pan_image name
	        $result = $this->CommonModel->selectRecord(array('usr_id' => $params['usr_id']), 'API_user_account', 'usr_pan_image');

	        $params['usr_pan_image'] = $result['usr_pan_image'];
        }

	    $panId = $this->Model->savePANDetail($params);

	    $this->response('pancard detail updated successfully', TRUE, array('panId' => $panId));
	}

	public function saveAccountDetail()
	{
		$this->isValidToken();

		$params = array();
		$params['usr_bank_acHolderName'] = isset($this->requestData->bankAcHolderName) ? $this->requestData->bankAcHolderName : "";
		$params['usr_bank_acNumber'] = isset($this->requestData->bankAcNumber) ? $this->requestData->bankAcNumber : "";
		$params['usr_bank_name'] = isset($this->requestData->bankName) ? $this->requestData->bankName : "";
		$params['usr_bank_ifsc'] = isset($this->requestData->bankIFSC) ? $this->requestData->bankIFSC : "";
		$params['usr_bank_isVarified'] = 1;
		$params['usr_id'] = $this->loggedInUserId;
		$params['usr_paytm_number'] = isset($this->requestData->paytmNumber) ? $this->requestData->paytmNumber : "";
		$params['usr_paytm_isVarified'] = 1;
		$params['usr_pan_number'] = isset($this->requestData->panNumber) ? $this->requestData->panNumber : "";
		$params['usr_pan_name'] = isset($this->requestData->panName) ? $this->requestData->panName : "";

		$bank_acType = isset($this->requestData->bankAcType) ? $this->requestData->bankAcType : "";
		if (!in_array($bank_acType, array('CURRENT', 'SAVING')))
			$params['usr_bank_acType'] = 'SAVING';

		//insert/update user bank proof image
        if (isset($_FILES['bankImage']) && $_FILES['bankImage']['name'] != '')
        {
            $proof = $this->single_upload(USER_PROOF_PATH, "", "bankImage");
            if (!$proof)
            	$this->response('Error: Unable to upload proof!');

            $params['usr_bank_proofImage'] = $proof;
        }
        else
        {
        	//--get usr_bank_image name
	        $result = $this->CommonModel->selectRecord(array('usr_id' => $params['usr_id']), 'API_user_account', 'usr_bank_proofImage');

	        $params['usr_bank_proofImage'] = $result['usr_bank_proofImage'];
        }

        //insert/update user pan proof image
        if (isset($_FILES['panImage']) && $_FILES['panImage']['name'] != '')
        {
            $proof = $this->single_upload(USER_PROOF_PATH, "", "panImage");
            if (!$proof)
            	$this->response('Error: Unable to upload proof!');       

            $params['usr_pan_image'] = $proof;
        }
        else
        {
        	//--get usr_pan_image name
	        $result = $this->CommonModel->selectRecord(array('usr_id' => $params['usr_id']), 'API_user_account', 'usr_pan_image');

	        $params['usr_pan_image'] = $result['usr_pan_image'];
        }

        // if (count(array_filter($params)) != count($params)) 
        // 	$this->response('All fields are mandatory!');

        $this->Model->saveAccountDetail($params);

        /*$old_pic = $this->CommonModel->selectRecord(array('email' => $userId), 'user', 'image');
        if (is_file(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']))
			@unlink(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']);

        $usr_data['image'] = base_url().USER_PROFILE_IMAGE_PATH.$profile_pic;
        $this->response('profile updated successfully!', TRUE, $usr_data);*/

	    $this->response('account detail updated successfully', TRUE);
	}

	public function saveBankDetail()
	{
		$this->isValidToken();

		$params = array();
		$params['usr_bank_acHolderName'] = isset($this->requestData->bankAcHolderName) ? $this->requestData->bankAcHolderName : "";
		$params['usr_bank_acNumber'] = isset($this->requestData->bankAcNumber) ? $this->requestData->bankAcNumber : "";
		$params['usr_bank_name'] = isset($this->requestData->bankName) ? $this->requestData->bankName : "";
		$params['usr_bank_ifsc'] = isset($this->requestData->bankIFSC) ? $this->requestData->bankIFSC : "";
		$params['usr_bank_isVarified'] = 1;
		$params['usr_id'] = $this->loggedInUserId;

		$bank_acType = isset($this->requestData->bankAcType) ? $this->requestData->bankAcType : "";
		if (!in_array($bank_acType, array('CURRENT', 'SAVING')))
			$params['usr_bank_acType'] = 'SAVING';

		//insert user profile picture
        if (isset($_FILES['file']) && $_FILES['file']['name'] != '')
        {
            $proof = $this->single_upload(USER_PROOF_PATH);
            if (!$proof)
            	$this->response('Error: Unable to upload proof!');

            $params['usr_bank_proofImage'] = $proof;
        }
        else
        {
        	//--get usr_bank_image name
	        $result = $this->CommonModel->selectRecord(array('usr_id' => $params['usr_id']), 'API_user_account', 'usr_bank_proofImage');

	        $params['usr_bank_proofImage'] = $result['usr_bank_proofImage'];
        }

        if (count(array_filter($params)) != count($params)) 
        	$this->response('All fields are mandatory!');

        $this->Model->saveBankDetail($params);

        /*$old_pic = $this->CommonModel->selectRecord(array('email' => $userId), 'user', 'image');
        if (is_file(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']))
			@unlink(base_url().USER_PROFILE_IMAGE_PATH.$old_pic['image']);

        $usr_data['image'] = base_url().USER_PROFILE_IMAGE_PATH.$profile_pic;
        $this->response('profile updated successfully!', TRUE, $usr_data);*/

	    $this->response('Bank detail updated successfully', TRUE);
	}

	public function savePaytmDetail()
	{
		$this->isValidToken();

		$params = array();
		$params['usr_paytm_number'] = isset($this->requestData->paytmNumber) ? $this->requestData->paytmNumber : "";
		$params['usr_paytm_isVarified'] = 1;
		$params['usr_id'] = $this->loggedInUserId;

		if (!$params['usr_paytm_number']) 
			$this->response('paytm number is mandatory');

		$this->Model->savePaytmDetail($params);

	    $this->response('paytm detail updated successfully', TRUE);
	}

	public function withdrawalMoney()
	{
		$this->isValidToken();

		$params = array();
		$params['amount'] = isset($this->requestData->amount) ? $this->requestData->amount : "";

		$params['usr_id'] = $this->loggedInUserId;

		$params['withdrawalType'] = isset($this->requestData->withdrawalType) ? $this->requestData->withdrawalType : "";

		if ($params['amount'] <= 0 && !in_array($params['withdrawalType'], array('PAYTM', 'ACCOUNT')))
			$this->response("amount or withdrawal type is not valid");

		$result = $this->Model->withdrawalMoney($params);

		if ($result) 
			$this->response('amount deducted from you winning amount', TRUE, array('amount' => $params['amount']));
		else
			$this->response('could not process you request!');
	}

	private function updateTransactionFailedStatus($ord_id, $message)
	{
		$this->CommonModel->updateData('paytm_order', array('system_status' => 0, 'system_message' => $message), array('paytm_order_id' => $ord_id));
		
		$status = ($message == "deposit done") ? 1 : 0;
		redirect($_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/paytmStatus.php?status='.$status); 
	}

	public function paytmDeposit()
	{
		$this->isValidToken();

		$amount = isset($this->requestData->amount) ? base64_decode($this->requestData->amount) : "";
		$userId = $this->loggedInUserId;

		//order data
		$order_data = array();
		$order_data['status'] = "PENDING";
		$order_data['amount'] = $amount;
		$order_data['user_id'] = $userId;
		$order_data['system_message'] = "initial paytm order insertion";
		
		//insert order detail
		$ord_id = $this->CommonModel->insertData('paytm_order', $order_data);
		if (!$ord_id)
			$this->response('unable to insert order!');

		if ($amount <= 0)
			$this->updateTransactionFailedStatus($ord_id, "amount is not valid");
        
        //get user registered mobile number
        $user_mobile = $this->CommonModel->selectRecord(array('email' => $userId), 'user', 'mobile');

        $data = array(
        	'amount' => $amount,
        	'orderId' => $ord_id,
        	'userId' => $userId,
        	'mobile' => $user_mobile['mobile']
        );

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, PAYTM_PAGE_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

        if (curl_errno($ch))
        {
        	$errorMsg = curl_error($ch);
        	curl_close($ch);
        	$this->response($errorMsg);
        }

        curl_close($ch);
		// echo $result;
		// die;

		$this->response('paytm page', TRUE, array('html_content' => $result));
	}

	public function paytmCallBack($userId, $amount, $orderId)
	{
		$paytmChecksum = "";
		$paramList = array();
		$isValidChecksum = "FALSE";

		$paramList = $_POST;
		$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg

		//Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
		$isValidChecksum = verifychecksum_e($paramList, "%FSOOp#MZJVTPrY4", $paytmChecksum); //will return TRUE or FALSE string.

		$res = array();
		$res['userId'] = $userId;
		$res['amount'] = $amount;
		$res['orderId'] = $orderId;

		if($isValidChecksum == "TRUE") 
		{
			$res['isValidChecksum'] = true;

			if (isset($paramList) && count($paramList)>0 )
				$res['request'] = $paramList;
		}
		else
		{
			$res['isValidChecksum'] = false;
			$res['request'] = array();
		}

		$this->insertCash($res);
	}

	private function insertCash($paytm_order_data)
	{
		$ord_id = $paytm_order_data['orderId'];
		$userId = $paytm_order_data['userId'];
		$amount = $paytm_order_data['amount'];

		//update order detail
		$order_data = array();
		$order_data['paytm_trans_id'] = isset($paytm_order_data['request']['TXNID']) ? $paytm_order_data['request']['TXNID'] : "";
		$order_data['status'] = isset($paytm_order_data['request']['STATUS']) ? $paytm_order_data['request']['STATUS'] : "";
		$order_data['order_data'] = isset($paytm_order_data) ? json_encode($paytm_order_data) : "";
		$order_data['system_message'] = "paytm response";
		$this->CommonModel->updateData('paytm_order', $order_data, array('paytm_order_id' => $ord_id));

		if (!$paytm_order_data['isValidChecksum'])
			$this->updateTransactionFailedStatus($ord_id, "checksum not valid");

		if (!$paytm_order_data['request']['TXNID']) 
			$this->updateTransactionFailedStatus($ord_id, "TXNID required!");

		if (!$paytm_order_data['request']['STATUS']) 
			$this->updateTransactionFailedStatus($ord_id, "STATUS required!");

		if ($paytm_order_data['request']['TXNAMOUNT'] != $amount) 
			$this->updateTransactionFailedStatus($ord_id, "wrong amount!");

		if ($paytm_order_data['request']['STATUS'] == "TXN_SUCCESS") 
		{
			//get user wallet amount
			$wamount = $this->CommonModel->selectRecord(array('userid' => $userId), 'wallet1', 'damount');
			$params = array();
			$params['damount'] = $wamount['damount'];
			$params['amount'] = $amount;
			$params['usr_id'] = $userId;

			$result = $this->Model->depositMoney($params);
			if ($result)
				$this->updateTransactionFailedStatus($ord_id, "deposit done");
			else
				$this->updateTransactionFailedStatus($ord_id, "deposit failed, due to server error");
		}
		else
			$this->updateTransactionFailedStatus($ord_id, 'wrong status');
	}

	public function getPlayerStats() 
	{ 
		$this->isValidToken();		 
 
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";		 
 
		if (!$matchId) 
			$this->response("matchId is required!"); 
 
		$Playing11_ids = $this->CommonModel->selectRecords(array('sqd_mat_id' => $matchId), 'API_match_squad', 'sqd_ply11_ids'); 
		//print_r($Playing11_ids); die; 
 
		if ($Playing11_ids[0]['sqd_ply11_ids'] && $Playing11_ids[1]['sqd_ply11_ids'])  
		{ 
			$Playing11_ids_str = $Playing11_ids[0]['sqd_ply11_ids'].",".$Playing11_ids[1]['sqd_ply11_ids']; 
 
			$params = array(); 
			$params['matchId'] = $matchId; 
			$params['Playing11_ids'] = $Playing11_ids_str; 
			$PlayerStats = $this->Model->getPlayerStats($params); 
			//echo "<pre>"; print_r($PlayerStats); die; 
 
			$this->response('player stats', TRUE, $PlayerStats); 
		} 
 
		$this->response('There is no live team available!'); 
	}

	public function getPlayersStats() 
	{ 
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";		 
 		$result = array();

		if (!$matchId) 
			$this->response("matchId is required!"); 
 
		$Playing11_ids = $this->CommonModel->selectRecords(array('sqd_mat_id' => $matchId), 'API_match_squad', 'sqd_ply11_ids'); 
 
		if ($Playing11_ids[0]['sqd_ply11_ids'] && $Playing11_ids[1]['sqd_ply11_ids'])  
		{ 
			$ply_ids = array_merge(explode(",", $Playing11_ids[0]['sqd_ply11_ids']), explode(",", $Playing11_ids[1]['sqd_ply11_ids']));
 			
 			$i = 0;
 			foreach ($ply_ids as $key => $plyId) 
 			{
 				$ply_info_stats = $this->getPlayerStatsPrivate($matchId, $plyId);
 				
 				if (!$ply_info_stats)
 					continue;

 				$result[$i] = $ply_info_stats;
 				$i++;
 			}
		} 

		$this->response('player stats', TRUE, $result); 
	} 

	private function getPlayerStatsPrivate($matchId='', $plyId='') 
	{
		$result = array();
 
		if (!$matchId || !$plyId) 
			$this->response("matchId and plyId is required!"); 
 		
 		//get player record
		$ply_info = $this->CommonModel->selectRecord(array('ply_id' => $plyId), 'API_players', 'ply_id AS playerId, ply_first_name AS firstName, ply_middle_name AS middleName, ply_last_name AS lastName, IFNULL(ply_title, "") AS title, IFNULL(ply_shortName, "") AS shortName, ply_logo_url AS logo, ply_role AS role');

		if (!$ply_info)
			return false;

		//get player role
		$ply_role = $this->CommonModel->selectRecord(array('rol_ply_id' => $plyId, 'rol_mat_id' => $matchId), 'API_player_role', 'rol_name');
		$ply_info['role'] = $ply_role['rol_name'];
		
		//get player select by percent
		$ply_sel_by_per	= $this->Model->getPlayerSelectByPercent($matchId, $plyId);
		$ply_info['sel_by_per']	= $ply_sel_by_per ? $ply_sel_by_per['sel_by_per'] : "";

 		//get points
 		$ply_point = $this->CommonModel->selectRecord(array('pnt_mat_id' => $matchId, 'pnt_ply_id' => $plyId), 'API_player_point', 'pnt_starting11, pnt_run, pnt_four, pnt_six, pnt_sr, pnt_thirty, pnt_bonus, pnt_fifty, pnt_duck, pnt_wkts, pnt_maidenover, pnt_er, pnt_catch, pnt_runoutstumping, pnt_total'); 

 		//get actual detail
 		//bating
 		$ply_batting_actual = $this->CommonModel->selectRecord(array('batsman_id' => $plyId), 'API_player_batsman_actual', 'runs, fours, sixes, strike_rate, run0'); 

 		//bowling
 		$ply_bowling_actual = $this->CommonModel->selectRecord(array('bowler_id' => $plyId), 'API_player_bowler_actual', 'wickets, maidens, econ'); 

 		//fielding
 		$ply_fielding_actual = $this->CommonModel->selectRecord(array('fielder_id' => $plyId), 'API_player_fielder_actual', 'catches, runout_thrower+runout_catcher+runout_direct_hit+stumping AS runout_stumping'); 

 		$ply_stats = array(
			array(
				'key' => 'Starting 11'
			),
			array(
				'key' => 'Runs'
			),
			array(
				'key' => "4's"
			),
			array(
				'key' => "6's"
			),
			array(
				'key' => 'S/R'
			),
			array(
				'key' => '50',
				'actual' => 'NO'
			),
			array(
				'key' => 'Duck'
			),
			array(
				'key' => 'Wkts'
			),
			array(
				'key' => 'Maiden Over'
			),
			array(
				'key' => 'E/R'
			),
			array(
				'key' => 'Bonus'
			),array(
				'key' => 'Catch'
			),
			array(
				'key' => 'Run Out/Stumping'
			),
			array(
				'key' => 'Total'
			)
		);

		if ($ply_point) 
		{
			$ply_stats[0]['point'] = $ply_point['pnt_starting11'];	
			$ply_stats[0]['actual'] = $ply_point['pnt_starting11'] ? 'YES' : 'NO';	
			$ply_stats[1]['point'] = $ply_point['pnt_run'];
			$ply_stats[2]['point'] = $ply_point['pnt_four'];
			$ply_stats[3]['point'] = $ply_point['pnt_six'];
			$ply_stats[4]['point'] = $ply_point['pnt_sr'];
			$ply_stats[5]['point'] = $ply_point['pnt_fifty'];
			$ply_stats[6]['point'] = $ply_point['pnt_duck'];
			$ply_stats[7]['point'] = $ply_point['pnt_wkts'];
			$ply_stats[8]['point'] = $ply_point['pnt_maidenover'];
			$ply_stats[9]['point'] = $ply_point['pnt_er'];
			$ply_stats[10]['point'] = $ply_point['pnt_bonus'];
			$ply_stats[10]['actual'] = $ply_point['pnt_bonus'];
			$ply_stats[11]['point'] = $ply_point['pnt_catch'];
			$ply_stats[12]['point'] = $ply_point['pnt_runoutstumping'];
			$ply_stats[13]['point'] = $ply_point['pnt_total'];
		}
		else
		{
			$ply_stats[0]['point'] = "0.00";	
			$ply_stats[0]['actual'] = 'NO';	
			$ply_stats[1]['point'] = "0.00";
			$ply_stats[2]['point'] = "0.00";
			$ply_stats[3]['point'] = "0.00";
			$ply_stats[4]['point'] = "0.00";
			$ply_stats[5]['point'] = "0.00";
			$ply_stats[6]['point'] = "0.00";
			$ply_stats[7]['point'] = "0.00";
			$ply_stats[8]['point'] = "0.00";
			$ply_stats[9]['point'] = "0.00";
			$ply_stats[10]['point'] = "0.00";
			$ply_stats[11]['point'] = "0.00";
			$ply_stats[12]['point'] = "0.00";
			$ply_stats[13]['point'] = "0.00";
		}

		if ($ply_batting_actual) 
		{
			$ply_stats[1]['actual'] = $ply_batting_actual['runs'];
			$ply_stats[2]['actual'] = $ply_batting_actual['fours'];
			$ply_stats[3]['actual'] = $ply_batting_actual['sixes'];
			$ply_stats[4]['actual'] = $ply_batting_actual['strike_rate'];
			$ply_stats[6]['actual'] = $ply_batting_actual['run0'] ? 'NO' : 'YES';

			//if batsman has more than 49 runs
			if ($ply_batting_actual['runs'] > 49) 
			{
				//get match status
				$mat_status = $this->Model->getMatchStatus($matchId); 
				$mat_status = $mat_status['mat_status'];

				if ($mat_status == 3) //T20
					$point = 4;
				else if ($mat_status == 1 || $mat_status == 2) //ODI OR TEST
					$point = 2;
				else
					$point = 8;

				if ($ply_batting_actual['runs'] < 100) //if batsman makes fifty
					$ply_stats[5]['actual'] = 'YES'; //fifty
				else //if batsman makes hundred
				{
					$ply_stats[5]['key'] = '100';

					$ply_stats[5]['actual'] = 'YES'; //hundred
				}
			}
		}
		else
		{
			$ply_stats[1]['actual'] = "0.00";
			$ply_stats[2]['actual'] = "0.00";
			$ply_stats[3]['actual'] = "0.00";
			$ply_stats[4]['actual'] = "0.00";
			$ply_stats[6]['actual'] = 'NO';
		}

		if ($ply_bowling_actual) 
		{
			$ply_stats[7]['actual'] = $ply_bowling_actual['wickets'];
			$ply_stats[8]['actual'] = $ply_bowling_actual['maidens'];
			$ply_stats[9]['actual'] = $ply_bowling_actual['econ'] ? $ply_bowling_actual['econ'] : "0.00";
		}
		else
		{
			$ply_stats[7]['actual'] = "0.00";
			$ply_stats[8]['actual'] = "0.00";
			$ply_stats[9]['actual'] = "0.00";
		}

		if ($ply_fielding_actual) 
		{
			$ply_stats[11]['actual'] = $ply_fielding_actual['catches'];
			$ply_stats[12]['actual'] = $ply_fielding_actual['runout_stumping'];
		}
		else
		{
			$ply_stats[11]['actual'] = "0.00";
			$ply_stats[12]['actual'] = "0.00";
		}

		$ply_info['stats'] = $ply_stats;

		return $ply_info;
	}

	public function getSinglePlayerStats() 
	{
		$result = array();
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		$plyId = isset($this->requestData->plyId) ? $this->requestData->plyId : "";	 
 
		if (!$matchId || !$plyId) 
			$this->response("matchId and plyId is required!"); 
 		
 		$ply_info_stats = $this->getPlayerStatsPrivate($matchId, $plyId);

		$this->response('player stats', TRUE, $ply_info_stats);
	}

	private function isValidToken($token='', $isLoginRequest=false)
	{
		$token = ($token) ? $token : (isset($this->headers['token']) && !empty($this->headers['token']) ? $this->headers['token'] : "");

		//verify userId and token
		if ($token) 
		{
			$token = explode("-", $token);
			//print_r($token); die;

			if ($token[0] && $token[1]) 
			{
				$token_data = $this->CommonModel->selectRecord(array('email' => $token[0], 'token' => $token[1]), 'user', 'email, token');

				if ($token_data)
					return $token_data['email']."-".$token_data['token'];
			}
		}

		if ($isLoginRequest)
			return FALSE;
		else
			$this->response('authentication failed', false, array(), "00000");
	}

	private function createToken($userId)
	{
		if ($userId) 
		{
			$where = array('email' => $userId);
			$isExistToken = $this->CommonModel->selectRecord($where, 'user', 'token');

			if ($isExistToken && !empty($isExistToken['token'])) 
				return $isExistToken['token'];
			else
			{
				$new_token = md5(uniqid(rand()));
				$this->CommonModel->updateData('user', array('token' => $new_token), $where);
			}

			return $userId."-".$new_token;
		}

		$this->response('Your login session expired!');
	}

	public function appConfig()
	{
		$version = isset($this->requestData->version) ? $this->requestData->version : "";		

		//need to get this from db table
		$a = 27;	

		if($version >= $a)
			$mandatoryUpdate = false;
		else 
			$mandatoryUpdate = true;	

		$res = array(
			'mandatoryUpdate' => $mandatoryUpdate,
			'maintenance' => false,				
			'link' => 'http://matchonsports.com/newRelease/',
			'playstore' => false,
			'message' => 'Please download new update by clicking on update now button Type a message',
			'showPromoter' => true
		);

		$this->response('app config detail', true, $res);
	}

	public function getTransactionHistory()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;

		$history = $this->Model->getTransactionHistory($userId);

		if ($history)
			$this->response('user transaction history', TRUE, $history);
		else
			$this->response('user transaction history not available yet!');
	}

	// get bank detail
	public function getBankDetail()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;

		$bankDetail = $this->Model->getBankDetail($userId);

		if ($bankDetail && $bankDetail['isVarified'] != 0)
			$this->response('user bank detail', TRUE, $bankDetail);
		else
			$this->response('bank detail not found');
	}

	// get paytm detail
	public function getAccountDetail()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;

		$accountDetail = $this->Model->getAccountDetail($userId);

		if ($accountDetail)
			$this->response('user bank detail', TRUE, $accountDetail);
		else
			$this->response('bank detail not found');
	}

	// get paytm detail
	public function getPaytmDetail()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;

		$paytmDetail = $this->Model->getPaytmDetail($userId);

		if ($paytmDetail && $paytmDetail['isVarified'] != 0)
			$this->response('user paytm detail', TRUE, $paytmDetail);
		else
			$this->response('paytm detail not found');
	}

	// get pan detail
	public function getPANDetail()
	{
		$this->isValidToken();

		$userId = $this->loggedInUserId;

		$panDetail = $this->Model->getPANDetail($userId);

		if ($panDetail || $panDetail['panName'] != "" || $panDetail['usr_pan_number'])
			$this->response('user pan detail', TRUE, $panDetail);
		else
			$this->response('pan detail not found');
	}

	//create copy of team
	public function copyTeam()
	{
		$this->isValidToken();

		$teamId = isset($this->requestData->teamId) ? $this->requestData->teamId : "";		
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";		
		$userId = $this->loggedInUserId;

		if (!$teamId && !$matchId)
			$this->response("teamId and matchId are required!");

		//get user team record
		$userTeam = $this->CommonModel->selectRecord(array('user_team_id' => $teamId, 'user_id' => $userId, 'match_id' => $matchId), 'user_team', 'players, caption, vice_caption');
		if (!$userTeam)
			$this->response("There is no such team found!");

		//user can create only 10 teams for one match
		$usr_team_cnt = $this->CommonModel->selectRecord(array('user_id' => $userId, 'match_id' => $matchId), 'user_team', 'COUNT(user_team_id) AS usr_team_cnt');
		if ($usr_team_cnt['usr_team_cnt'] >= 10) 
			$this->response('Limit exceed, User can not create more then 10 teams!');

		//insert team record
		$teamData = array();
		$teamData['user_id'] = $userId;
		$teamData['match_id'] = $matchId;
		$teamData['user_team_number'] = $usr_team_cnt['usr_team_cnt']+1;
		$teamData['players'] = $userTeam['players'];
		$teamData['caption'] = $userTeam['caption'];
		$teamData['vice_caption'] = $userTeam['vice_caption'];
		$newTeamId = $this->CommonModel->insertData('user_team', $teamData);

		if ($newTeamId)
		{
			//get players of copied team
		    $plyrs = $this->CommonModel->selectRecord(array('user_team_id' => $newTeamId), 'user_team', 'players');
		    $plyrs = get_object_vars(json_decode(unserialize($plyrs['players'])));
		    $teams = array_keys($plyrs);
		    $ply_ids = array_merge($plyrs[$teams[0]], $plyrs[$teams[1]]);

		    //get old players and update count (-1) for those players
		    //update player selected count (for select by percent)
		    $this->Model->updatePlayerSelectCount($ply_ids, $matchId, 'INC', $userTeam['caption'], $userTeam['vice_caption']);

			$this->response('new team created', TRUE, $newTeamId);
		}
		else
			$this->response('unable to copy team');
	}

	//swap team
	public function swapTeam()
	{
		$this->isValidToken();

		$joinedId = isset($this->requestData->joinedId) ? $this->requestData->joinedId : "";		
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";		
		$oldTeamId = isset($this->requestData->oldTeamId) ? $this->requestData->oldTeamId : "";		
		$newTeamId = isset($this->requestData->newTeamId) ? $this->requestData->newTeamId : "";		
		$contestId = isset($this->requestData->contestId) ? $this->requestData->contestId : "";		
		$userId = $this->loggedInUserId;

		if (!$joinedId && !$matchId && !$oldTeamId && !$newTeamId && !$contestId)
			$this->response("joinedId, oldTeamId, newTeamId, contestId and matchId are required!");

		//check match is upcoming or not
	    $isUpcomingMatch = $this->CommonModel->selectRecord(array('mat_id' => $matchId, 'mat_status' => 1), 'API_matches', 'mat_id');
	    if (!$isUpcomingMatch) 
	    	$this->response('match moved to live!');
	    
		//get user joined team record
		$userJoinedTeam = $this->CommonModel->selectRecord(array('joined_id' => $joinedId, 'user_id' => $userId, 'match_id' => $matchId, 'team_id' => $oldTeamId, 'contests_id' => $contestId), 'matches_joined', 'joined_id');
		if (!$userJoinedTeam)
			$this->response("Record not found with old team");

		//update user joined record
		$this->CommonModel->updateData('matches_joined', array('team_id' => $newTeamId), array('joined_id' => $joinedId));

		$this->response('team changed successfully', TRUE);
	}

	//delete user team
	public function deleteTeam()
	{
		$this->isValidToken();

		$teamId = isset($this->requestData->teamId) ? $this->requestData->teamId : "";	
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";		
		$userId = $this->loggedInUserId;

		if (!$teamId && !$matchId)
			$this->response("teamId and matchId are required!");

		//get user team record
		$userJoinedTeam = $this->CommonModel->selectRecord(array('team_id' => $teamId, 'user_id' => $userId, 'match_id' => $matchId), 'matches_joined', 'joined_id');
		if ($userJoinedTeam)
			$this->response("Can't delete, You already joined contest with this team!");
		
		$this->CommonModel->deleteRecord('user_team', array('user_team_id' => $teamId));
		
		$this->response('team deleted successfully', TRUE, $teamId);
	}

	//get player detail
	public function getPlayerInfo()
	{
		$plyId = isset($this->requestData->plyId) ? $this->requestData->plyId : "";
		$matchId = isset($this->requestData->matchId) ? $this->requestData->matchId : "";
		
		if (!$plyId || !$matchId)
			$this->response("plyId and matchId required");

		//get player record
		$player = $this->CommonModel->selectRecord(array('ply_id' => $plyId), 'API_players', '*');
		
		//get player role
		$ply_role = $this->CommonModel->selectRecord(array('rol_ply_id' => $plyId, 'rol_mat_id' => $matchId), 'API_player_role', 'rol_name');
		$player['ply_role'] = $ply_role['rol_name'];

		//get player recent played matches
		$player['match'] = $this->Model->getPlayerMatch($plyId, $matchId);

		//get all points of match
		$player['ply_point'] = $player['match'] ? array_sum(array_column($player['match'], 'pnt_total')) : 0;

		$this->response('player detail', TRUE, $player);
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

	//-- function for generate random password string
    function generateRandomString($length, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        
        if(strpos($available_sets, 'l') !== false)
        	$sets[] = 'abcdefghjkmnpqrstuvwxyz';
        
        // if(strpos($available_sets, 'u') !== false)
        //     $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '123456789';
        
        // if(strpos($available_sets, 's') !== false)
        //     $sets[] = '!@#$%&*?';
        
        $all = '';
        $password = '';

        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        
        $password = str_shuffle($password);
        
        if(!$add_dashes)
            return $password;
        
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        
        $dash_str .= $password;
        
        return $dash_str;
    }

    public function sendOTP()
	{
		//-- get request params
		$mobile = isset($this->requestData->mobile) ? $this->requestData->mobile : '';
		$old_otp = isset($this->requestData->old_otp) ? $this->requestData->old_otp : "";

	    if (!$mobile) 
	    	$this->response('mobile is mandatory!');

	    //Your message to send, Add URL encoding here.
		$otp = ($old_otp) ? $old_otp : rand(1000,9999);
		$otp_msg = "Your MatchOnSports verification code is: ".$otp;

	    $this->sendSms($mobile, $otp_msg);

		$this->response('otp', TRUE, array('otp' => $otp));
	}

	//private function sendSms($mobile='', $message='')
	private function sendSms($mobile='', $message='')
	{
		if (!$mobile || !$message) 
	    	$this->response('mobile and message are mandatory!');

		//Prepare you post parameters
		$postData = array(
		    'authkey' => SMS_AUTH_KEY,
		    'mobiles' => $mobile, //Multiple mobiles numbers separated by comma
		    'message' => urlencode($message),
		    'sender' => SMS_SENDER_ID,
		    'route' => 4
		);

		// init the resource
		$ch = curl_init();
		curl_setopt_array($ch, array(
		    CURLOPT_URL => SMS_API_URL,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => $postData
		    //,CURLOPT_FOLLOWLOCATION => true
		));

		//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_exec($ch);

		//Print error if any
		if(curl_errno($ch))
			$this->response('send sms error', FALSE, array('error' => curl_error($ch)));

		curl_close($ch);

		return TRUE;
	}
}
?>
