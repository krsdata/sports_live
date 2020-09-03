<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class FantasyModel extends CI_Model
{
    function __construct() 
    {
        parent::__construct();
    }

    public function saveMatch($mat_data)
    {
        foreach ($mat_data as $value) 
            $new_mat_data[] = '("'.implode('","', $value).'")';

        $sql = 'INSERT INTO API_matches (mat_id, mat_title, mat_short_title, mat_sub_title, mat_format, mat_status, mat_comp_id, mat_team1_id, mat_team2_id, mat_startdate, mat_enddate)
                VALUES '.implode(',', $new_mat_data).'
                ON DUPLICATE KEY UPDATE 
                mat_title = VALUES(mat_title),
                mat_short_title = VALUES(mat_short_title),
                mat_sub_title = VALUES(mat_sub_title),
                mat_format = VALUES(mat_format),
                mat_status = VALUES(mat_status),
                mat_comp_id = VALUES(mat_comp_id),
                mat_team1_id = VALUES(mat_team1_id),
                mat_team2_id = VALUES(mat_team2_id),
                mat_startdate = VALUES(mat_startdate),
                mat_enddate = VALUES(mat_enddate)';

        $this->db->query($sql);
    }

    public function copyContestAndPrize()
    {
        //get all upcoming matches
        $sql = 'SELECT mat_id
                FROM API_matches
                WHERE mat_id NOT IN (SELECT matchid FROM contests) AND mat_status = 1';
        $query = $this->db->query($sql);
        $result = $query->result_array();

        $mat_ids = array_column($result, 'mat_id');

        foreach ($mat_ids as $mat_id) 
        {
            //copy contest from one table to another contest table
            $sql = 'INSERT INTO contests(amount, no_of_teams, fee, matchid, repeat1, fill1, multi, type)
                    SELECT amount, no_of_teams, fee, '.$mat_id.', repeat1, fill1, multi, type
                    FROM contests1';
            $this->db->query($sql);

            //get inserted contest ids for new match
            $contestIds = $this->CommonModel->selectRecords(array('matchid' => $mat_id), 'contests', 'contests_id', array('contests_id' => 'ASC'));
            $contestIds = array_column($contestIds, 'contests_id');

            //copy prize from one table to another prize table
            foreach ($contestIds as $key => $contestId) 
            {
                $prizeContestId = intval($key)+1;
                $sql = 'INSERT INTO prize(contestid, min1, max1, prize)
                        SELECT '.$contestId.', min1, max1, prize
                        FROM prize1
                        WHERE contestid = '.$prizeContestId;
                $this->db->query($sql);          
            }
        }

        return TRUE;
    }

    public function saveCompetition($comp_data)
    {
        foreach ($comp_data as $value) 
            $new_comp_data[] = '("'.implode('","', $value).'")';

        $sql = 'INSERT INTO API_competition (comp_id, comp_title, comp_type, comp_category, comp_format, comp_status, comp_startdate, comp_enddate, comp_total_matches, comp_total_teams)
                VALUES '.implode(',', $new_comp_data).'
                ON DUPLICATE KEY UPDATE 
                comp_title = VALUES(comp_title),
                comp_type = VALUES(comp_type),
                comp_category = VALUES(comp_category),
                comp_format = VALUES(comp_format),
                comp_status = VALUES(comp_status),
                comp_startdate = VALUES(comp_startdate),
                comp_enddate = VALUES(comp_enddate),
                comp_total_matches = VALUES(comp_total_matches),
                comp_total_teams = VALUES(comp_total_teams)';

        $this->db->query($sql);
    }

    public function saveTeam($team_data)
    {
        foreach ($team_data as $value)
            $new_team_data[] = '("'.implode('","', $value).'")';

        $sql = 'INSERT INTO API_team (team_id, team_name, team_short_name, team_logo_url)
                VALUES '.implode(',', $new_team_data).'
                ON DUPLICATE KEY UPDATE 
                team_name = VALUES(team_name),
                team_short_name = VALUES(team_short_name),
                team_logo_url = VALUES(team_logo_url)';

        $this->db->query($sql);
    }

    public function savePlayer($ply_data)
    {
        foreach ($ply_data as $value) 
            $new_ply_data[] = '("'.implode('","', $value).'")';
        
        $sql = 'INSERT INTO API_players (
                    ply_id, 
                    ply_first_name, 
                    ply_middle_name, 
                    ply_last_name, 
                    ply_title, 
                    ply_shortName, 
                    ply_birthdate, 
                    ply_country, 
                    ply_logo_url, 
                    ply_batting_style, 
                    ply_bowling_style, 
                    ply_recent_match_id, 
                    ply_fantasy_rating, 
                    ply_nationality
                )
                VALUES '.implode(',', $new_ply_data).'
                ON DUPLICATE KEY UPDATE 
                    ply_first_name = VALUES(ply_first_name),
                    ply_middle_name = VALUES(ply_middle_name),
                    ply_last_name = VALUES(ply_last_name),
                    ply_title = VALUES(ply_title),
                    ply_shortName = VALUES(ply_shortName),
                    ply_birthdate = VALUES(ply_birthdate),
                    ply_country = VALUES(ply_country),
                    ply_logo_url = VALUES(ply_logo_url),
                    ply_batting_style = VALUES(ply_batting_style),
                    ply_bowling_style = VALUES(ply_bowling_style),
                    ply_recent_match_id = VALUES(ply_recent_match_id),
                    ply_fantasy_rating = VALUES(ply_fantasy_rating),
                    ply_nationality = VALUES(ply_nationality)';

        $this->db->query($sql);
    }

    public function savePlayerRole($ply_data)
    {
        foreach ($ply_data as $value) 
            $new_ply_data[] = '("'.implode('","', $value).'")';
        
        $sql = 'INSERT INTO API_player_role (
                    rol_ply_id, 
                    rol_mat_id, 
                    rol_name
                )
                VALUES '.implode(',', $new_ply_data).'
                ON DUPLICATE KEY UPDATE 
                    rol_name = VALUES(rol_name)';

        $this->db->query($sql);
    }

    public function saveInitialCountOfPlayer($ply_data)
    {
        foreach ($ply_data as $value) 
            $new_ply_data[] = '("'.implode('","', $value).'")';
        
        $sql = 'INSERT INTO API_player_select (
                    sel_ply_id,
                    sel_mat_id
                )
                VALUES '.implode(',', $new_ply_data).'
                ON DUPLICATE KEY UPDATE 
                    sel_id = LAST_INSERT_ID(sel_id)';

        $this->db->query($sql);
    }

    public function saveSquad($sqd_data)
    {
        $sql = $this->db->insert_string('API_match_squad', $sqd_data). 
                ' ON DUPLICATE KEY UPDATE 
                    sqd_team_id = VALUES(sqd_team_id),
                    sqd_mat_id = VALUES(sqd_mat_id),
                    sqd_ply_ids = VALUES(sqd_ply_ids)';
        $this->db->query($sql);
        $id = $this->db->insert_id();

        return $id;
    }

    public function calculatePlayerPoints($request)
    {
        $matchId = $request['matchId'];
        $playerIdsArray = $request['matchPlayers'];

        $sql = "SELECT pnt_ply_matchPoint
                (CASE 
                    WHEN pnt_ply_id = ".$request['captainId']."
                        THEN pnt_ply_matchPoint*2
                    WHEN pnt_ply_id = ".$request['viceCaptainId']."
                        THEN pnt_ply_matchPoint*1.5
                    ELSE pnt_ply_matchPoint 
                END) AS pnt_ply_matchPoint
                FROM API_player_point
                WHERE pnt_mat_id = ".$matchId." AND pnt_ply_id IN (".implode(',', $playerIdsArray).")";

        $query = $this->db->query($sql); 
        $result = $query->result_array();

        return $result;
    }

    public function savePlayerPoints($ply_point_data)
    {
        //print_r($ply_point_data); die;
        foreach ($ply_point_data as $value) 
            $new_ply_point_data[] = '("'.implode('","', $value).'")';

        //echo "<pre>"; print_r($new_ply_point_data); die;
        if (isset($new_ply_point_data)) 
        {
            $sql = 'INSERT INTO API_player_point (pnt_mat_id, pnt_ply_id, pnt_total, pnt_rating, pnt_starting11, pnt_run, pnt_four, pnt_six, pnt_sr, pnt_fifty, pnt_duck, pnt_wkts, pnt_maidenover, pnt_er, pnt_catch, pnt_runoutstumping, pnt_runoutthrower, pnt_runoutcatcher, pnt_directrunout, pnt_stumping, pnt_thirty, pnt_bonus)
                    VALUES '.implode(',', $new_ply_point_data).'
                    ON DUPLICATE KEY UPDATE 
                        pnt_mat_id = VALUES(pnt_mat_id),
                        pnt_ply_id = VALUES(pnt_ply_id),
                        pnt_total = VALUES(pnt_total),
                        pnt_rating = VALUES(pnt_rating),
                        pnt_starting11 = VALUES(pnt_starting11),
                        pnt_run = VALUES(pnt_run),
                        pnt_four = VALUES(pnt_four),
                        pnt_six = VALUES(pnt_six),
                        pnt_sr = VALUES(pnt_sr),
                        pnt_fifty = VALUES(pnt_fifty),
                        pnt_duck = VALUES(pnt_duck),
                        pnt_wkts = VALUES(pnt_wkts),
                        pnt_maidenover = VALUES(pnt_maidenover),
                        pnt_er = VALUES(pnt_er),
                        pnt_catch = VALUES(pnt_catch),
                        pnt_runoutstumping = VALUES(pnt_runoutstumping),
                        pnt_runoutthrower = VALUES(pnt_runoutthrower),
                        pnt_runoutcatcher = VALUES(pnt_runoutcatcher),
                        pnt_directrunout = VALUES(pnt_directrunout),
                        pnt_stumping = VALUES(pnt_stumping),
                        pnt_thirty = VALUES(pnt_thirty),
                        pnt_bonus = VALUES(pnt_bonus)';

            $this->db->query($sql);
        }
        
        return TRUE;
    }
    
    public function decideWinner()
    {
        $status = FALSE;
        $result = array();

        //get completed matches where prize not destributed yet
        $matchIds = $this->CommonModel->selectRecords(array('mat_status' => 2, 'mat_isPrizeDesctributed' => 0), 'API_matches', 'mat_id');

        if (count($matchIds) > 0) 
        {   
            //echo "<pre>"; print_r($matchIds);
            foreach ($matchIds as $match_id) 
            {
                $matchId = $match_id['mat_id'];

                //get all contests of one match
                $contestIds = $this->CommonModel->selectRecords(array('matchid' => $matchId), 'contests', 'contests_id');
                //echo "<pre>"; print_r($contestIds); die;

                //get rank of user for specific contest
                foreach ($contestIds as $value) 
                {
                    $count = 0;
                    $contestId = $value['contests_id'];

                    $sql = 'SELECT 
                                username, email AS userId, user_team_number AS teamNumber, team_id AS teamId, usr_team_name AS userTeamName, usr_team_earn_points AS points,
                                CONCAT("'.base_url().USER_PROFILE_IMAGE_PATH.'", image) AS photo
                            FROM user 
                            INNER JOIN matches_joined 
                                ON email = user_id AND match_id = '.$matchId.' AND contests_id = '.$contestId.'
                            INNER JOIN user_team
                                ON user_team_id = team_id
                            ORDER BY usr_team_earn_points DESC, username ASC';

                    $query = $this->db->query($sql); 
                    $usersWithRank = $query->result_array();

                    //echo "<pre>"; print_r($usersWithRank); 
                    if (count($usersWithRank) > 0) 
                    {
                        $usersWithRank = $this->calculatePriceAndRank($usersWithRank, $contestId);

                        //echo "<pre>"; print_r($usersWithRank); die;
                        //deposit amount in winner's account
                        foreach ($usersWithRank as $value) 
                        {
                            if (isset($value['winningAmount']) && $value['winningAmount'] > 0) 
                            {
                                //get old winning amount value of user
                                $old_wamount = $this->CommonModel->selectRecord(array('userid' => $value['userId']), 'wallet1', 'wamount');

                                //update wallet balance
                                $this->db->trans_begin();

                                $this->db->query('INSERT INTO transaction(userid, amount, type, contestid) VALUES('.$value['userId'].', '.$value['winningAmount'].', "winning", '.$contestId.')');

                                $totalAmount = $old_wamount['wamount']+$value['winningAmount'];
                                $this->db->query('UPDATE wallet1 SET wamount = '.$totalAmount.' WHERE userid = '.$value['userId']);

                                //update prize destributed status
                                $this->db->query('UPDATE API_matches SET mat_isPrizeDesctributed = 1 WHERE mat_id = '.$matchId);

                                if ($this->db->trans_status() === FALSE)
                                {
                                    $status = FALSE;
                                    $this->db->trans_rollback();
                                }
                                else
                                {
                                    $status = TRUE;
                                    $this->db->trans_commit();
                                }
                            }
                        }
                    }
                } 
            }
        }
    }

    public function earnMoney()
    {
        $sql = "INSERT INTO earn_money_transaction
                    (user_id, amount, match_id)
                SELECT 
                    r2.userid,
                    ROUND((fee*r2.percent)/100, 2),
                    contests.matchid
                FROM API_matches
                INNER JOIN contests
                    ON matchid = mat_id
                INNER JOIN matches_joined 
                    ON matches_joined.contests_id = contests.contests_id
                INNER JOIN refer1 AS r1 
                    ON r1.userid = user_id AND r1.referral_userId IS NOT NULL 
                INNER JOIN refer1 AS r2
                    ON r2.userid = r1.referral_userId AND r2.percent > 0
                WHERE mat_status = 2 AND mat_isPrizeDesctributed = 0";
        $query = $this->db->query($sql); 
        echo $first_inserted_id = $this->db->insert_id();
        echo " === ";
        echo $total_inserted = $this->db->affected_rows();
    }

    public function updateUserRankAndPrice($matchId, $mat_status='')
    {
        //get all contests of one match
        $contestIds = $this->CommonModel->selectRecords(array('matchid' => $matchId), 'contests', 'contests_id');
        //echo "<pre>"; print_r($contestIds); die;

        //get rank of user for specific contest
        foreach ($contestIds as $value) 
        {
            $count = 0;
            $contestId = $value['contests_id'];

            $sql = 'SELECT 
                        email AS userId, team_id, usr_team_earn_points AS points
                    FROM user 
                    INNER JOIN matches_joined 
                        ON email = user_id AND match_id = '.$matchId.' AND contests_id = '.$contestId.'
                    INNER JOIN user_team
                        ON user_team_id = team_id
                    ORDER BY usr_team_earn_points DESC, username ASC';

            $query = $this->db->query($sql); 
            $usersWithRank = $query->result_array();

            if (count($usersWithRank) > 0) 
            {
                //echo "<pre>"; print_r($usersWithRank);

                $usersWithRank = $this->calculatePriceAndRank($usersWithRank, $contestId);
                //echo "<pre>"; print_r($usersWithRank);
                
                //deposit amount in winner's account
                foreach ($usersWithRank as $value) 
                {
                    //update user rank
                    $sql = 'UPDATE matches_joined
                            SET rank = '.$value['rank'].'
                            WHERE team_id = '.$value['team_id'].' AND match_id = '.$matchId.' AND contests_id = '.$contestId;
                    $this->db->query($sql);
                    //echo $sql; die;

                    if ($mat_status == 2 && $value['winningAmount'] > 0) 
                    {
                        //update user winning amount
                        $sql = 'UPDATE matches_joined
                                SET amount = '.$value['winningAmount'].'
                                WHERE team_id = '.$value['team_id'].' AND match_id = '.$matchId.' AND contests_id = '.$contestId;
                        $this->db->query($sql);
                    }
                }
            }
        }
    }

    public function calculatePriceAndRank($records, $contestId)
    {
        $rank = 0;
        $last_score = false;
        $rows = 1;
        $count = 1;

        foreach ($records as $key => $value) 
        {
            if($last_score != $value['points'])
            {
                if ($last_score)
                    $rows = $key+1;

                $last_score = $value['points'];
                $rank = $rows;
            }

            if ($value['points'] > 0)
                $records[$key]['rank'] = $rank;
            else
                $records[$key]['rank'] = 0;
        }

        //devide prize money if rank are same
        $getRankArr = array_count_values(array_column($records, 'rank'));

        if (!array_key_exists("", $getRankArr)) 
        {  
            foreach ($getRankArr as $key => $value)
            {
                $sum = 0;
                $rank = $key;

                for ($i = 1; $i <= $value; $i++) 
                { 
                    $prize = $this->getContestRankPrizeDetail($contestId, $rank);
                    $rank++;
                    if ($prize)
                        $sum += $prize['prize'];
                }

                foreach ($records as $recKey => $recValue) 
                {
                    if ($recValue['rank'] == $key) 
                    {
                        $winningAmount = $sum/$value; 
                            
                        $records[$recKey]['winningAmount'] = round($winningAmount, 2);
                    }
                }
            }
        }

        return $records;
    }
    
    private function getContestRankPrizeDetail($contestId, $rank)
    {
        //get current rank values
        $sql = 'SELECT prize, min1, max1 FROM prize WHERE '.$rank.' BETWEEN min1 AND max1 AND contestid = '.$contestId;
        $query = $this->db->query($sql); 
        $min_max_prize = $query->row_array();
        
        return $min_max_prize;
    }
    
    public function contestAutoCancel()
    {
        //get not full contests
        $sql = 'SELECT contests_id, fee, mat_id
                FROM contests
                INNER JOIN API_matches on mat_id = matchid AND mat_status = 3
                WHERE fill1 = 1 AND isFull = 0';
        $query = $this->db->query($sql); 
        $contestIds = $query->result_array();
        //echo "<pre>"; print_r($contestIds); die;
        
        foreach ($contestIds as $key => $value) 
        {
            $contestId = $value['contests_id'];
            $matchId = $value['mat_id'];
            $amount = $value['fee'];

            //get joined user of not full conest
            $sql = 'SELECT joined_id, user_id 
                    FROM matches_joined 
                    WHERE contests_id = '.$contestId;
            $query = $this->db->query($sql); 
            $joinedUsers = $query->result_array();
            //echo "<pre>"; print_r($joinedUsers);

            if (count($joinedUsers)>0) 
            {
                foreach ($joinedUsers as $value) 
                {
                    $userId = $value['user_id'];

                    $this->db->trans_begin();

                    //delete user records from joined table
                    $sql = 'DELETE FROM `matches_joined` WHERE joined_id = '.$value['joined_id'];
                    $this->db->query($sql);

                    //return money to user's respective account in deposit way
                    //get user's old damount
                    $sql = 'SELECT damount, bamount, wamount 
                            FROM wallet1 
                            WHERE userid = '.$userId;
                    $query = $this->db->query($sql); 
                    $old_amount = $query->row_array();

                    //get user's deducated amounts
                    $sql = 'SELECT damount, bamount, wamount 
                            FROM wallet_contest_transaction 
                            WHERE mat_joined_id = '.$value['joined_id'];
                    $query = $this->db->query($sql); 
                    $deducted_amount = $query->row_array();

                    $depoistAmount = $deducted_amount['damount']+$old_amount['damount'];
                    $winningAmount = $deducted_amount['wamount']+$old_amount['wamount'];
                    $bonusAmount = $deducted_amount['bamount']+$old_amount['bamount'];

                    //update user's wallet damount
                    $sql = 'UPDATE wallet1 SET damount = '.$depoistAmount.', bamount = '.$bonusAmount.', wamount = '.$winningAmount.'
                            WHERE userid = '.$userId;
                    $this->db->query($sql); 

                    //maintain transiction history
                    $sql = 'INSERT INTO transaction (userid, amount, type, contestid) VALUES ('.$userId.', '.$amount.', "cancelled", '.$contestId.')';
                    $this->db->query($sql);

                    //update status
                    $sql = 'UPDATE wallet_contest_transaction 
                            SET isRefunded = 1 
                            WHERE mat_joined_id = '.$value['joined_id'];
                    $this->db->query($sql);

                    if ($this->db->trans_status() === FALSE)
                        $this->db->trans_rollback();
                    else
                        $this->db->trans_commit();
                }
            }
        }
    }

    public function cancelMatch($matchId)
    {
        //get contests of match
        $sql = 'SELECT contests_id, fee
                FROM contests
                INNER JOIN API_matches on matchid = mat_id
                WHERE matchid = '.$matchId;
        $query = $this->db->query($sql); 
        $contestIds = $query->result_array();
        //echo "<pre>"; print_r($contestIds);
        
        if (count($contestIds) > 0) 
        {
            foreach ($contestIds as $key => $value) 
            {
                $contestId = $value['contests_id'];
                $amount = $value['fee'];

                //get joined users contest
                $sql = 'SELECT joined_id, user_id 
                        FROM matches_joined 
                        WHERE contests_id = '.$contestId;
                $query = $this->db->query($sql); 
                $joinedUsers = $query->result_array();
                
                if (count($joinedUsers)>0) 
                {
                    //echo "<pre>"; print_r($joinedUsers);
                    foreach ($joinedUsers as $value) 
                    {
                        $userId = $value['user_id'];

                        $this->db->trans_begin();

                        //delete user records from joined table
                        $sql = 'DELETE FROM `matches_joined` WHERE joined_id = '.$value['joined_id'];
                        $this->db->query($sql);
        
                        //delete teams related to match
                        $sql = 'DELETE FROM `user_team` WHERE match_id = '.$matchId;
                        $this->db->query($sql);

                        //return money to user's respective account in deposit way
                        //get user's old damount
                        $sql = 'SELECT damount 
                                FROM wallet1 
                                WHERE userid = '.$userId;
                        $query = $this->db->query($sql); 
                        $damount = $query->row_array();
                        $depoistAmount = $damount['damount']+$amount;

                        //update user's wallet damount
                        $sql = 'UPDATE wallet1
                                SET damount = '.$depoistAmount.'
                                WHERE userid = '.$userId;
                        $this->db->query($sql);

                        //maintain transiction history
                        $sql = 'INSERT INTO transaction (userid, amount, type, contestid) VALUES ('.$userId.', '.$amount.', "cancelled", '.$contestId.')';
                        $this->db->query($sql);

                        if ($this->db->trans_status() === FALSE)
                            $this->db->trans_rollback();
                        else
                            $this->db->trans_commit();
                    }
                }
            }
        }
    }

    /*public function deductWinnerAmount($matchId)
    {
        $status = FALSE;
        $result = array();

        //get all contests of one match
        $sql = 'SELECT 
                    contests_id, 
                    fee
                FROM contests
                INNER JOIN API_matches 
                ON matchid = mat_id
                WHERE 
                    mat_isPrizeDesctributed = 1 AND 
                    mat_isVisible = 1 AND 
                    mat_status = 2 AND 
                    matchid = '.$matchId;
        $query = $this->db->query($sql); 
        $contests = $query->result_array();
        //echo "<pre>"; print_r($contests); die;

        //get rank of user for specific contest
        foreach ($contests as $contest) 
        {
            //get joined users of contest
            $sql = 'SELECT userid AS userId
                    FROM transaction
                    WHERE 
                        type = "winning" AND 
                        contestid = '.$contest['contests_id'];
            $query = $this->db->query($sql); 
            $resp = $query->result_array();
            //echo "<pre>"; print_r($resp); die;

            if (count($resp) > 0) 
            {
                //echo "<pre>"; print_r($resp);
                foreach ($resp as $value) 
                {
                    //get reffer id for winner and find percentage for winner
                    $sql = "SELECT 
                                r2.userid, 
                                r2.percent 
                            FROM refer1 AS r1
                            INNER JOIN refer1 AS r2 
                            ON r2.userid = r1.referral_userId
                            INNER JOIN user
                            ON 
                                r2.userid = email AND 
                                status = 4
                            WHERE 
                                r1.userid = ".$value['userId'];
                    $query = $this->db->query($sql); 
                    $referral_user = $query->row_array();
                    //echo "<pre>"; print_r($referral_user); die;

                    if ($referral_user) 
                    {
                        $referral_user_amount = ($contest['fee']*$referral_user['percent'])/100;
                    
                        //get old winning amount value of user
                        $old_wamount = $this->CommonModel->selectRecord(array('userid' => $value['userId']), 'wallet1', 'wamount');

                        //update wallet balance
                        $this->db->trans_begin();

                        $this->db->query('INSERT INTO earn_money_transaction(user_id, amount, match_id) VALUES('.$value['userId'].', '.$referral_user_amount.', '.$matchId.')');

                        $totalAmount = $old_wamount['wamount']+$referral_user_amount;
                        $this->db->query('UPDATE wallet1 SET wamount = '.$totalAmount.' WHERE userid = '.$value['userId']);

                        if ($this->db->trans_status() === FALSE)
                            $this->db->trans_rollback();
                        else
                            $this->db->trans_commit();
                    }
                }
            }
        }

        exit;
    }*/

    /*public function distributePromoterBonus($matchId)
    {
        $status = FALSE;
        $result = array();

        //get all contests of one match
        $sql = 'SELECT 
                    contests_id, 
                    fee
                FROM '.DB_WRITE.'.contests
                INNER JOIN API_matches 
                ON matchid = mat_id
                WHERE 
                    mat_isPrizeDesctributed = 1 AND 
                    mat_isVisible = 1 AND 
                    mat_status = 2 AND 
                    matchid = '.$matchId.' AND 
                    matchid NOT IN (SELECT match_id FROM earn_money_transaction)';
        $query = $this->db->query($sql); 
        $contests = $query->result_array();
        //echo "<pre>"; print_r($contests); die;

        foreach ($contests as $contest) 
        {
            //get reffer id for contest joined user and find percentage for promoter
            $sql = "SELECT 
                        r2.userid, 
                        r2.percent 
                    FROM ".DB_WRITE.".refer1 AS r1
                    INNER JOIN ".DB_WRITE.".refer1 AS r2 
                        ON r2.userid = r1.referral_userId
                    INNER JOIN ".DB_WRITE.".user
                        ON r2.userid = email AND status = 4
                    INNER JOIN ".DB_WRITE.".matches_joined
                        ON user_id = r1.userid AND contests_id = ".$contest['contests_id'];
            $query = $this->db->query($sql); 
            $referral_user = $query->result_array();
            if ($referral_user) 
            {
                foreach ($referral_user as $value) 
                {
                    $referral_user_amount = ($contest['fee']*$value['percent'])/100;
                    
                    //get old winning amount value of user
                    $old_wamount = $this->CommonModel->selectRecord(array('userid' => $value['userid']), DB_WRITE.'.wallet1', 'wamount');

                    //update wallet balance
                    $this->db->trans_begin();

                    $this->db->query('INSERT INTO earn_money_transaction(user_id, amount, match_id) VALUES('.$value['userid'].', '.$referral_user_amount.', '.$matchId.')');

                    $totalAmount = $old_wamount['wamount']+$referral_user_amount;
                    $this->db->query('UPDATE '.DB_WRITE.'.wallet1 SET wamount = '.$totalAmount.' WHERE userid = '.$value['userid']);

                    if ($this->db->trans_status() === FALSE)
                        $this->db->trans_rollback();
                    else
                        $this->db->trans_commit();
                }
            }
        }

        exit;
    }*/

    public function distributePromoterBonus($matchId, $promoterId)
    {
        $status = FALSE;
        $result = array();

        //get percent of promoter user id
        $promoter_percent = $this->CommonModel->selectRecord(array('userid' => $promoterId), 'refer1', 'percent');
        $promoter_percent = $promoter_percent['percent'];

        //get all contests of one match
        $sql = 'SELECT 
                    contests.contests_id, 
                    fee
                FROM contests
                INNER JOIN API_matches 
                    ON matchid = mat_id
                INNER JOIN matches_joined 
                    ON 
                        matchid = '.$matchId.' AND 
                        matches_joined.contests_id = contests.contests_id
                INNER JOIN refer1 AS r1
                    ON r1.userid = matches_joined.user_id
                INNER JOIN refer1 AS r2 
                    ON 
                        r2.userid = r1.referral_userId AND 
                        r2.userid = '.$promoterId.' 
                INNER JOIN user 
                    ON 
                        r2.userid = email AND 
                        status = 4 
                WHERE 
                    mat_isPrizeDesctributed = 1 AND 
                    mat_isVisible = 1 AND 
                    mat_status = 2 AND 
                    matchid = '.$matchId.' AND 
                    matchid NOT IN (SELECT match_id 
                        FROM earn_money_transaction 
                        WHERE 
                            match_id = '.$matchId.' AND 
                            user_id = '.$promoterId.')';
        $query = $this->db->query($sql); 
        $contests = $query->result_array();
        // echo "<pre>"; print_r($contests); die;

        //get old winning amount value of promoter
        $old_wamount = $this->CommonModel->selectRecord(array('userid' => $promoterId), 'wallet1', 'wamount');
        $old_wamount = $old_wamount['wamount'];

        foreach ($contests as $contest) 
        {
            $referral_user_amount = ($contest['fee']*$promoter_percent)/100;

            $this->db->query('INSERT INTO earn_money_transaction(
                user_id, 
                amount, 
                match_id
            ) VALUES(
                '.$promoterId.', 
                '.$referral_user_amount.', 
                '.$matchId.'
            )');

            $old_wamount = $old_wamount+$referral_user_amount;
        }

        $this->db->query('UPDATE wallet1 SET wamount = '.$old_wamount.' WHERE userid = '.$promoterId);
    }

    public function saveBatsmanActualData($bat_ply_data)
    {        
        $sql = 'INSERT INTO API_player_batsman_actual (
                    batsman_id,
                    batting,
                    position,
                    role,
                    role_str,
                    strike_rate,
                    runs,
                    balls_faced,
                    fours,
                    sixes,
                    run0,
                    run1,
                    run2,    
                    run3,
                    run5,
                    how_out,
                    dismissal,
                    bowler_id,
                    first_fielder_id,
                    second_fielder_id,
                    third_fielder_id
                )
                VALUES (
                    "'.$bat_ply_data['batsman_id'].'",
                    "'.$bat_ply_data['batting'].'",
                    "'.$bat_ply_data['position'].'",
                    "'.$bat_ply_data['role'].'",
                    "'.$bat_ply_data['role_str'].'",
                    "'.$bat_ply_data['strike_rate'].'",
                    "'.$bat_ply_data['runs'].'",
                    "'.$bat_ply_data['balls_faced'].'",
                    "'.$bat_ply_data['fours'].'",
                    "'.$bat_ply_data['sixes'].'",
                    "'.$bat_ply_data['run0'].'",
                    "'.$bat_ply_data['run1'].'",
                    "'.$bat_ply_data['run2'].'",
                    "'.$bat_ply_data['run3'].'",
                    "'.$bat_ply_data['run5'].'",
                    "'.$bat_ply_data['how_out'].'",
                    "'.$bat_ply_data['dismissal'].'",
                    "'.$bat_ply_data['bowler_id'].'",
                    "'.$bat_ply_data['first_fielder_id'].'",
                    "'.$bat_ply_data['second_fielder_id'].'",
                    "'.$bat_ply_data['third_fielder_id'].'"
                )
                ON DUPLICATE KEY UPDATE 
                    batting = VALUES(batting),
                    position = VALUES(position),
                    role = VALUES(role),
                    role_str = VALUES(role_str),
                    strike_rate = VALUES(strike_rate),
                    runs = VALUES(runs),
                    balls_faced = VALUES(balls_faced),
                    fours = VALUES(fours),
                    sixes = VALUES(sixes),
                    run0 = VALUES(run0),
                    run1 = VALUES(run1),
                    run2 = VALUES(run2),
                    run3 = VALUES(run3),
                    run5 = VALUES(run5),
                    how_out = VALUES(how_out),
                    dismissal = VALUES(dismissal),
                    bowler_id = VALUES(bowler_id),
                    first_fielder_id = VALUES(first_fielder_id),
                    second_fielder_id = VALUES(second_fielder_id),
                    third_fielder_id = VALUES(third_fielder_id)';

        $this->db->query($sql);
    }

    public function saveBowlerActualData($bow_ply_data)
    {
        $sql = 'INSERT INTO API_player_bowler_actual (
                    bowler_id,
                    bowling,
                    position,
                    overs,
                    maidens,
                    runs_conceded,
                    wickets,
                    wides,
                    econ,
                    run0
                )
                VALUES (
                    "'.$bow_ply_data['bowler_id'].'",
                    "'.$bow_ply_data['bowling'].'",
                    "'.$bow_ply_data['position'].'",
                    "'.$bow_ply_data['overs'].'",
                    "'.$bow_ply_data['maidens'].'",
                    "'.$bow_ply_data['runs_conceded'].'",
                    "'.$bow_ply_data['wickets'].'",
                    "'.$bow_ply_data['wides'].'",
                    "'.$bow_ply_data['econ'].'",
                    "'.$bow_ply_data['run0'].'"
                )
                ON DUPLICATE KEY UPDATE 
                    bowling = VALUES(bowling),
                    position = VALUES(position),
                    overs = VALUES(overs),
                    maidens = VALUES(maidens),
                    runs_conceded = VALUES(runs_conceded),
                    wickets = VALUES(wickets),
                    wides = VALUES(wides),
                    econ = VALUES(econ),
                    run0 = VALUES(run0)';

        $this->db->query($sql);
    }

    public function saveFielderActualData($field_ply_data)
    {
        $sql = 'INSERT INTO API_player_fielder_actual (
                    fielder_id,
                    fielder_name,
                    catches,
                    runout_thrower,
                    runout_catcher,
                    runout_direct_hit,
                    stumping,
                    is_substitute
                )
                VALUES (
                    "'.$field_ply_data['fielder_id'].'",
                    "'.$field_ply_data['fielder_name'].'",
                    "'.$field_ply_data['catches'].'",
                    "'.$field_ply_data['runout_thrower'].'",
                    "'.$field_ply_data['runout_catcher'].'",
                    "'.$field_ply_data['runout_direct_hit'].'",
                    "'.$field_ply_data['stumping'].'",
                    "'.$field_ply_data['is_substitute'].'"
                )
                ON DUPLICATE KEY UPDATE 
                    fielder_name = VALUES(fielder_name),
                    catches = VALUES(catches),
                    runout_thrower = VALUES(runout_thrower),
                    runout_catcher = VALUES(runout_catcher),
                    runout_direct_hit = VALUES(runout_direct_hit),
                    stumping = VALUES(stumping),
                    is_substitute = VALUES(is_substitute)';

        $this->db->query($sql);
    }

    public function resetMatchPlayersActual($n_matchId)
    {
        //get all players of match
        $s_sql = "SELECT sqd_ply_ids
                FROM API_match_squad
                WHERE sqd_mat_id = ".$n_matchId;

        $query = $this->db->query($s_sql);   
        $result = $query->result_array();

        if (isset($result[0]['sqd_ply_ids']) && isset($result[1]['sqd_ply_ids']))
            $sqd_ply_ids = $result[0]['sqd_ply_ids'].','.$result[1]['sqd_ply_ids'];
        else
            return false;
        
        //reset all players of bowlers
        $s_sql = 'UPDATE API_player_bowler_actual
                SET maidens = 0,
                    wickets = 0,
                    wides = 0,
                    econ = "",
                    run0 = 0
                WHERE bowler_id IN ('.$sqd_ply_ids.')';
        $this->db->query($s_sql);

        //reset all players of fielder
        $s_sql = 'UPDATE API_player_fielder_actual
                SET catches = 0,
                    runout_thrower = 0,
                    runout_catcher = 0,
                    runout_direct_hit = 0,
                    stumping = 0
                WHERE fielder_id IN ('.$sqd_ply_ids.')';
        $this->db->query($s_sql);

        //reset all players of batsman
        $s_sql = 'UPDATE API_player_batsman_actual
                SET strike_rate = 0,
                    runs = 0,
                    balls_faced = 0,
                    fours = 0,
                    sixes = 0,
                    run0 = 0,
                    run1 = 0,
                    run2 = 0,
                    run3 = 0,
                    run5 = 0
                WHERE batsman_id IN ('.$sqd_ply_ids.')';
        $this->db->query($s_sql);
    }

    public function deductDuplicateAutoCancel($data)
    {
        /*$s_sql = "SELECT contests_id 
                FROM contests 
                INNER JOIN API_matches 
                    ON mat_status = 2 AND mat_id = matchid AND mat_isVisible = 1 
                WHERE 
                    date1 <= '2020-08-03 00:00:01' AND 
                    date1 >= '2020-08-01 00:00:01' AND 
                    isFull = 0 AND 
                    type <> 1";
        $a_query = $this->db->query($s_sql);
        $a_contests = $a_query->result_array();
        echo "<pre>"; print_r($a_contests); die;*/
        // $a_contests = array(array('contests_id' => 179796));
    
        $s_sql = 'SELECT 
                    contestid,
                    userid,
                    count(*) as NumDuplicates 
                FROM transaction 
                WHERE 
                    type = "fee" AND 
                    date1 >= "'.$data.' 00:00:01" AND 
                    date1 <= "'.$data.' 23:59:59" 
                GROUP BY 
                    userid, 
                    contestid, 
                    amount, 
                    date1 
                ORDER BY transaction.date1 ASC';
        $a_query = $this->db->query($s_sql);
        $a_fee_transactions = $a_query->result_array();
        // echo "<pre>"; print_r($a_fee_transactions); die;

        foreach ($a_fee_transactions as $a_fee_transaction) 
        {
            $s_sql = "SELECT 
                        transaction_id,
                        userid,
                        amount, 
                        type, 
                        contestid, 
                        date1
                    FROM transaction 
                    WHERE 
                        type = 'cancelled' AND 
                        userid = ".$a_fee_transaction['userid']." AND 
                        contestid = ".$a_fee_transaction['contestid'];
            $a_query = $this->db->query($s_sql);     
            $a_cancelled_transactions = $a_query->result_array();

            if (count($a_cancelled_transactions)>$a_fee_transaction['NumDuplicates']) 
            {
                // echo $feeCount = $a_fee_transaction['NumDuplicates'];
                // echo "<pre>"; print_r($a_cancelled_transactions);

                //delete transaction row for $rowse['NumDuplicates']-1 times
                $n_diff = count($a_cancelled_transactions)-$a_fee_transaction['NumDuplicates'];
                $sum=0;
                for ($i=0; $i < $n_diff; $i++) 
                { 
                    // echo $i."<br />";
                    // print_r($a_cancelled_transactions[$i]);

                    //delete records from transaction table
                    $sql = 'DELETE FROM `transaction` 
                            WHERE transaction_id = '.$a_cancelled_transactions[$i]['transaction_id'];
                    $this->db->query($sql);

                    //insert new entry in transaction table
                    $this->db->query('INSERT INTO transaction(userid,amount,type,contestid) VALUES('.$a_cancelled_transactions[$i]['userid'].','.$a_cancelled_transactions[$i]['amount'].', "technical issue",'.$a_fee_transaction['contestid'].')');

                    $sum=$sum+$a_cancelled_transactions[$i]['amount'];
                }

                if ($sum>0) 
                {
                    //get user's old damount
                    $sql = 'SELECT damount, bamount, wamount 
                            FROM wallet1 
                            WHERE userid = '.$a_fee_transaction['userid'];
                    $query = $this->db->query($sql); 
                    $old_amount = $query->row_array();

                    // $depoistAmount = $old_amount['damount'];
                    $winningAmount = $old_amount['wamount']-$sum;
                    /*if ($winningAmount<0) 
                    {
                        $depoistAmount = $old_amount['damount']+$winningAmount;
                        if ($depoistAmount<0)
                            $depoistAmount=0;
                        $winningAmount = 0;
                        $bonusAmount = $old_amount['bamount'];
                    }*/

                    //update user's wallet damount
                    $sql = 'UPDATE wallet1 
                            SET wamount = '.$winningAmount.' 
                            WHERE userid = '.$a_fee_transaction['userid'];
                    $this->db->query($sql); 
                }
            }
        }
    }
}
