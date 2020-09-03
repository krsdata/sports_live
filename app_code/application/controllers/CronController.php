<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'FantasyController.php';

class CronController extends FantasyController 
{
    public function __construct()
    {
        parent::__construct();

        $this->token = 'token='.FANTASY_API_TOKEN;
        $this->per_page = 10;
    }

    public function getUpcomingMatch()
    {
        $this->getUpcomingMatch(1);
    }

    public function getLiveMatch()
    {
        $this->getLiveMatch(1);
    }

    //copy contest from contests1 table to contests table for new matches
    public function copyContestAndPrize()
    {
        $this->copyContestAndPrize();
    }

    public function getLiveScore()
    {
        $this->getLiveScore();
    }
    
    //need to make cron for this
    public function getPlaying11Squad()
    {
        $this->getPlaying11Squad();
    }
	
	public function contestAutoCancel()
	{
		$this->FantasyModel->contestAutoCancel(41895);
	}
	
    //delete match after specific time
}
?>