<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'WebController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$v2_api_controller = 'V2_serviceController/';
$admin_controller = 'AdminController/';

$route['api/v2/userSignup'] = $v2_api_controller.'userSignup';
$route['api/v2/userSignin'] = $v2_api_controller.'userSignin';
$route['api/v2/getMatch'] = $v2_api_controller.'getMatch';
$route['api/v2/getMatchSquad'] = $v2_api_controller.'getMatchSquad';
$route['api/v2/createTeam'] = $v2_api_controller.'createTeam';
$route['api/v2/getTeam'] = $v2_api_controller.'getTeam';
$route['api/v2/getMatchContests'] = $v2_api_controller.'getMatchContests';
$route['api/v2/getUserJoinedMatch'] = $v2_api_controller.'getUserJoinedMatch';
$route['api/v2/getUserJoinedContestsOfMatch'] = $v2_api_controller.'getUserJoinedContestsOfMatch';
$route['api/v2/joinContest'] = $v2_api_controller.'joinContest';
$route['api/v2/getUserWallet'] = $v2_api_controller.'getUserWallet';
$route['api/v2/getScore'] = $v2_api_controller.'getScore';
$route['api/v2/getLeaderboard'] = $v2_api_controller.'getLeaderboard';
$route['api/v2/forgotPassword'] = $v2_api_controller.'forgotPassword';
$route['api/v2/getContestDetail'] = $v2_api_controller.'getContestDetail';
$route['api/v2/withdrawalMoney'] = $v2_api_controller.'withdrawalMoney';
$route['api/v2/getPlayerStats'] = $v2_api_controller.'getPlayerStats';
$route['api/v2/getTransactionHistory'] = $v2_api_controller.'getTransactionHistory';
$route['api/v2/getBankDetail'] = $v2_api_controller.'getBankDetail';
$route['api/v2/getPaytmDetail'] = $v2_api_controller.'getPaytmDetail';
$route['api/v2/getPANDetail'] = $v2_api_controller.'getPANDetail';
$route['api/v2/getAccountDetail'] = $v2_api_controller.'getAccountDetail';
$route['api/v2/copyTeam'] = $v2_api_controller.'copyTeam';
$route['api/v2/deleteTeam'] = $v2_api_controller.'deleteTeam';
$route['api/v2/swapTeam'] = $v2_api_controller.'swapTeam';
$route['api/v2/editTeam'] = $v2_api_controller.'editTeam';
$route['api/v2/isExist'] = $v2_api_controller.'isExist';
$route['api/v2/paytmDeposit'] = $v2_api_controller.'paytmDeposit';
$route['api/v2/sendOTP'] = $v2_api_controller.'sendOTP';
$route['api/v2/appConfig'] = $v2_api_controller.'appConfig';
$route['api/v2/getContestTeam'] = $v2_api_controller.'getContestTeam';
$route['api/v2/saveAccountDetail'] = $v2_api_controller.'saveAccountDetail';
$route['api/v2/getReferCount'] = $v2_api_controller.'getReferCount';
$route['api/v2/getPlayerInfo'] = $v2_api_controller.'getPlayerInfo';
$route['api/v2/getSinglePlayerStats'] = $v2_api_controller.'getSinglePlayerStats';
$route['api/v2/getPlayersStats'] = $v2_api_controller.'getPlayersStats';
$route['api/v2/updateUserProfile'] = $v2_api_controller.'updateUserProfile';
$route['api/v2/initiateTransaction'] = $v2_api_controller.'initiateTransaction';
$route['api/v2/insertCash'] = $v2_api_controller.'insertCashNew';

//admin controller
$route['createRemoteTeam/(:num)/(:num)'] = $admin_controller.'createRemoteTeam/$1/$2';
$route['joinContetsViaRemoteTeam/(:num)/(:num)/(:num)'] = $admin_controller.'joinContest/$1/$2/$3';

// $route['api/v2/savePANDetail'] = $v2_api_controller.'savePANDetail';
// $route['api/v2/getLivePlayersPoint'] = $v2_api_controller.'getLivePlayersPoint';
// $route['api/v2/savePaytmDetail'] = $v2_api_controller.'savePaytmDetail';
// $route['api/v2/saveBankDetail'] = $v2_api_controller.'saveBankDetail';
// $route['api/v2/getContestPrizePool'] = $v2_api_controller.'getContestPrizePool';
// $route['api/v2/getMatchContestTypes'] = $v2_api_controller.'getMatchContestTypes';
// $route['api/v2/getUser'] = $v2_api_controller.'getUser';
