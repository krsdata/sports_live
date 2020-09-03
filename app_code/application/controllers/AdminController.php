<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// require_once 'FantasyController.php';
// require_once 'V1/ServiceController.php';
// require_once 'WebController.php';
// require_once 'CronController.php';

class AdminController extends CI_Controller 
{
	public function __construct()
    {
        parent::__construct();

        // $this->fantasyCtrlObj = new FantasyController();
        // $this->serviceCtrlObj = new ServiceController();
        // $this->webCtrlObj = new WebController();
        // $this->cronCtrlObj = new CronController();
	}

	//create remote team from playing11
	public function createRemoteTeam($n_match_id, $n_num)
	{
		//get match status
		$n_match_status = $this->Model->getMatchStatus($n_match_id);
		
		if (!$n_match_status) 
			die('match status not found.');

		//get match squad players
	    $a_matchSquad = $this->Model->getMatchSquad($n_match_id, $n_match_status['mat_status']);
	    if (
	    	$a_matchSquad && 
	    	$a_matchSquad[0]['sqd_ply11_ids'] && 
	    	$a_matchSquad[1]['sqd_ply11_ids']
	    ) 
	    {
	    	$a_players = array();
	    	$n_team1_id = $a_matchSquad[0]['sqd_team_id'];
	    	$n_team2_id = $a_matchSquad[1]['sqd_team_id'];

	    	$a_players[$n_team1_id] = $a_matchSquad[0]['sqd_ply11_ids'] ? explode(",", trim($a_matchSquad[0]['sqd_ply11_ids'])) : array();
	    	$a_players[$n_team2_id] = $a_matchSquad[1]['sqd_ply11_ids'] ? explode(",", trim($a_matchSquad[1]['sqd_ply11_ids'])) : array();	    	

	    	//get team players
	    	$a_result = $this->getPlayers($a_players, array($n_team1_id, $n_team2_id), $n_match_id);
	    	if ($a_result) 
	    	{
		    	$a_result['matchId'] = $n_match_id;
		    	$a_result['isLineup'] = ($a_matchSquad[0]['sqd_ply11_ids'] || $a_matchSquad[1]['sqd_ply11_ids']) ? 1 : 0;

		    	$n_combination=0;
		    	$a_teamCombination = array(
		    		array(7, 4),
		    		array(4, 7),
		    		array(6, 5),
		    		array(5, 6)
		    	);

		    	for ($i=0; $i < $n_num; $i++) 
		    	{ 
			    	$bat=0;
			    	$wk=0;
			    	$bowl=0;
			    	$all=0;
			    	$remoteTeam=0;
			    	$failed=0;
			    	$team1=array();
			    	$team2=array();
			    	$teams=array();
					shuffle($a_result['team1']['players']);
					shuffle($a_result['team2']['players']);

			    	//create team1
			    	foreach ($a_result['team1']['players'] as $value) 
			    	{
			    		//batting
			    		if ($value['role'] == 'bat' && $bat==5) continue;
						else if ($value['role'] == 'bat') $bat++;

			    		//bowling
			    		if ($value['role'] == 'bowl' && $bowl==5) continue;
						else if ($value['role'] == 'bowl') $bowl++;

			    		//wicketkeeper
			    		if ($value['role'] == 'wk' && $wk==2) continue;
			    		else if ($value['role'] == 'wk') $wk++;

			    		//allrounder
			    		if ($value['role'] == 'all' && $all==5) continue;
						else if ($value['role'] == 'all') $all++;

						$remoteTeam++;

			    		array_push($team1, $value['playersId']);

			    		if ($remoteTeam==$a_teamCombination[$n_combination][0]) break;
			    	}

			    	//create team2
			    	foreach ($a_result['team2']['players'] as $value) 
			    	{
			    		//batting
			    		if ($value['role'] == 'bat' && $bat==5) continue;
						else if ($value['role'] == 'bat') $bat++;

			    		//bowling
			    		if ($value['role'] == 'bowl' && $bowl==5) continue;
						else if ($value['role'] == 'bowl') $bowl++;

			    		//wicketkeeper
			    		if ($value['role'] == 'wk' && $wk==2) continue;
			    		else if ($value['role'] == 'wk') $wk++;

			    		//allrounder
			    		if ($value['role'] == 'all' && $all==5) continue;
						else if ($value['role'] == 'all') $all++;

			    		$remoteTeam++;

			    		array_push($team2, $value['playersId']);

			    		if ($remoteTeam==11) break;
			    	}

			    	if ($bat==0 || $wk==0 || $bowl==0 || $all==0) 
			    		$failed++;
			    	else
			    	{
				    	//create serialize array of both team
				    	$teams[$a_result['team1']['teamId']] = $team1;
				    	$teams[$a_result['team2']['teamId']] = $team2;

				    	//random caption and vice captain
				    	shuffle($team1);
				    	shuffle($team2);
				    	
				    	//get random remote user, which are not in user_team for a specific match
				    	$remoteUser = $this->Model->getRemoteUser($n_match_id);
				    	
				    	//insert remote team
				    	$remote_team_data = array();
			    		$remote_team_data['user_id'] = $remoteUser['email'];
			    		$remote_team_data['match_id'] = $n_match_id;
			    		$remote_team_data['players'] = serialize(json_encode($teams));
			    		$remote_team_data['caption'] = $team1[0];
			    		$remote_team_data['vice_caption'] = $team2[0];
			    		$remote_team_data['user_team_number'] = rand(1,10);

			    		$this->CommonModel->insertData('user_team', $remote_team_data);

			    		$n_combination++;

			    		if ($n_combination==4)
			    			$n_combination=0;
		    		}	
			    }

			    echo json_encode(
			    	array(
			    		'total' => $n_num, 
			    		'success' => $n_num-$failed, 
			    		'failed' => $failed
			    	)
			    );
			    die;
		    }
		    else
		    	die('players detail not found');
	    }
	    else
	    	die('playing11 not found');
	}

	//get players details 
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
    		shuffle($team2_players);
    		
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

	public function joinContest($n_match_id, $n_contest_id, $n_num)
	{	
		$errorResponse = array();
		$userTeams = $this->Model->getRemoteUserTeams($n_match_id, $n_num);
		
		foreach ($userTeams as $userTeam) 
		{
			$request = array();
			$request['matchId'] = $n_match_id;
	        $request['contestId'] = $n_contest_id;
	        $request['userId'] = $userTeam['user_id'];
			$request['teamId'] = $userTeam['user_team_id'];

			$result = $this->Model->joinContestViaRemoteUser($request);

			if (!$result[0]) 
				array_push($errorResponse, $result[1]);
		}

		echo json_encode($errorResponse);
	}
}
?>