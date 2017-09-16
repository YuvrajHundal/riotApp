<?php

	class player{

		private $participantId;
		private $summonerName;
		private $highestRank;
		private $statList;
		private $teamID;
		private $medals;
		private $teamOne;
		private $teamTwo;
		private $championImage;
		private $echoString;


		function __construct($__participantId,$__summonerName,$__highestRank,$__teamID,$__champion){
			$this->participantId = $__participantId;
			$this->summonerName = $__summonerName;
			$this->highestRank = $__highestRank;
			$this->teamID = $__teamID;
			$this->statList = array();
			$this->medals = array();
			$this->teamOne = array();
			$this->teamTwo = array();
			$this->championImage = $__champion;
			$this->echoString = "stats_win";

		}


		function setStats($__value){
			$this->statList = $__value;
		}

		function returnStatList(){
			return $this->statList;
		}

		function assignAccolades($__value){
			array_push($this->medals,$__value);

		}


		function setTeams(){
			$onehundred = '100';
			$twoHundred = '200';
			$id = $this->teamID;
			if(strcmp('100',$id) == 0){
				array_push($this->teamOne, $this);
			}
			if(strcmp('200',$id) == 0){
				array_push($this->teamTwo, $this);
			}
		}

		function returnTeamOne(){
			return $this->teamOne;
		}

		function returnTeamTwo(){
			return $this->teamTwo;
		}



		function displayInfo($__value){
			$count = 0;
			if($__value == 0){
				$string = "stats_win";
			}
			if($__value == 1){
				$string ="stats_loss";
			}
			echo "<div class = player>";
			echo "<ul class =".$string.">";
			echo "<li>".$this->championImage."</li>";
			echo "<li>".$this->summonerName."</li>";
			echo "<li>Highest Rank:".$this->highestRank."</li>";
			foreach ($this->medals as $medal) {
				echo "<li>".$medal."</li>";
			}
			echo "</ul>";
			echo "</div>";
			$count++;
		}
	}
?>
