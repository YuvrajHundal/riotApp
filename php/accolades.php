<?php
	include '../includes/header.php';
	include 'player.php';
	include 'team.php';



	class accoladeGenerator{

		private $gameId;
		private $gameData;
		private $requestHandler;
		private $gameURL;
		private $championJSONURL;
		private $playerObjectList;
		private $teamList;
		private $teamOne;
		private $teamTwo;
		private $teamOb;

		function __construct($__gameNumber){
			$this->gameId = $__gameNumber;
			$this->teamList = array();
			$this->playerObjectList = array();
			$this->gameURL = "https://na1.api.riotgames.com/lol/match/v3/matches/".$this->gameId."?api_key=RGAPI-a9d1df16-db7f-469c-9cb1-7d1f5eda0b31";
			$this->championJSONURL = "http://ddragon.leagueoflegends.com/cdn/7.17.1/data/en_US/champion.json";
			$this->requestHandler = curl_init();

			curl_setopt($this->requestHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->requestHandler, CURLOPT_HEADER, 0);

		}

		function returnTeam(){
			return $this->teamOb;
		}

		function makeRequest(){
			curl_setopt($this->requestHandler, CURLOPT_URL,$this->gameURL ); // set the url request
		}

		//function to set the data for each player
		//once a player is created we will add them to the list of players
		//from a list of players we can create a team
		function initiatePlayers(){
			$json = curl_exec($this->requestHandler);
			$jsonData = json_decode($json,true);
			curl_setopt($this->requestHandler, CURLOPT_URL,$this->championJSONURL ); // set the url request
			$jsonOne = curl_exec($this->requestHandler);
			$champData = json_decode($jsonOne,true);
			$champInfo = $champData['data'];
			$participants = $jsonData['participantIdentities'];
			$playerStats = $jsonData['participants'];
			$teamData = $jsonData['teams'];

			$x=0;
			$teamOneArray = array();
			$teamTwoArray = array();
			$imageArray = array();

			//set the champion played for each champion
			foreach ($playerStats as $stats) {
				$championPlayed =(string)$stats['championId'];
				foreach ($champInfo as $champ) {
				$champKey = (string)$champ['key'];
					if(strcmp($champKey, $championPlayed)==0){
						$champName = (string)$champ['id'];
						$champImage = "<img src = http://ddragon.leagueoflegends.com/cdn/7.17.1/img/champion/".$champName.".png class =img-circle alt = champion_played>";
						array_push($imageArray,$champImage);
					}
				}
			}


			//set the highest season rank for each player
			foreach ($participants as $player) {
				$name =$player['player']['summonerName'];
				$id = $player['participantId'];
				$highestRank =$playerStats[$x]['highestAchievedSeasonTier'];
				$teamID = $playerStats[$x]['teamId'];
				$playerInstance = new player($id,$name,$highestRank,$teamID,$imageArray[$x]);
				if(strcmp($teamID, '100')==0){
					array_push($teamOneArray,$playerInstance);
				}
				else
					array_push($teamTwoArray,$playerInstance);

				array_push($this->playerObjectList,$playerInstance);
				$x++;
			}
			$this->teamOb = new team($teamOneArray,$teamTwoArray);

		}

		//get all the stats from the json object
		//data such as damage and healing done will come from this function
		function getTotalStats(){
			curl_setopt($this->requestHandler, CURLOPT_URL,$this->gameURL ); // set the url request
			$json = curl_exec($this->requestHandler);
			$jsonData = json_decode($json,true);
			$gameStats = $jsonData['participants'];
			$objectCounter =0;
			$statList = array();
			$totalKills = 0;
			$totalAssists = 0;

			//loop through the game stats and retreive player totals
			foreach ($gameStats as $player) {
				$playerObject =$this->playerObjectList[$objectCounter];
				array_push($statList,$player['stats']['kills']);
				array_push($statList,$player['stats']['deaths']);
				array_push($statList,$player['stats']['assists']);
				array_push($statList,$player['stats']['largestKillingSpree']);
				array_push($statList,$player['stats']['killingSprees']);
				array_push($statList,$player['stats']['longestTimeSpentLiving']);
				array_push($statList,$player['stats']['doubleKills']);
				array_push($statList,$player['stats']['tripleKills']);
				array_push($statList,$player['stats']['quadraKills']);
				array_push($statList,$player['stats']['pentaKills']);
				array_push($statList,$player['stats']['totalDamageDealt']);
				array_push($statList,$player['stats']['magicDamageDealt']);
				array_push($statList,$player['stats']['physicalDamageDealt']);
				array_push($statList,$player['stats']['totalDamageDealtToChampions']);
				array_push($statList,$player['stats']['totalHeal']);
				array_push($statList,$player['stats']['damageDealtToTurrets']);
				array_push($statList,$player['stats']['visionScore']);
				array_push($statList,$player['stats']['timeCCingOthers']);
				array_push($statList,$player['stats']['totalDamageTaken']);
				array_push($statList,$player['stats']['goldSpent']);
				array_push($statList,$player['stats']['totalTimeCrowdControlDealt']);
				$playerObject->setStats($statList);
				$objectCounter++;
				$statList = array();
			}


		}

		//find the champion who did the most damage
		//then assign the accolade to the winning player
		function mostChampionDamageDone(){
			$damageTotals = array();
			$playerIdCounter =0;
			foreach ($this->playerObjectList as $player) {
				$stats = $player->returnStatList();
				array_push($damageTotals,$stats[13]);

			}
			$highestValue =(string)max($damageTotals);
			$index = array_search($highestValue, $damageTotals);
			$awardedPlayer = $this->playerObjectList[$index];
			$insert = "Most Damage Done:".$highestValue;
			$awardedPlayer->assignAccolades($insert);

		}

		//find the champion who did the most healing
		//then assign the accolade to the winning player
		function mostHealingDone(){
			$healingTotals = array();
			$playerIdCounter  = 0;
			foreach ($this->playerObjectList as $player) {
				$stats = $player->returnStatList();
				array_push($healingTotals,$stats[14]);
			}
			$highestValue = max($healingTotals);
			$index = array_search($highestValue,$healingTotals);
			$awardedPlayer = $this->playerObjectList[$index];
			$insert = "Most Healing Done:".$highestValue;
			$awardedPlayer->assignAccolades($insert);

		}

		//find the champion who did the most gold spent
		//then assign the accolade to the winning player
		function mostGoldSpent(){
			$goldSpent = array();
			$playerIdCounter  = 0;
			foreach ($this->playerObjectList as $player) {
				$stats = $player->returnStatList();
				array_push($goldSpent,$stats[19]);
			}
			$highestValue = max($goldSpent);
			$index = array_search($highestValue,$goldSpent);
			$awardedPlayer = $this->playerObjectList[$index];
			$insert = "Most Gold Spent:".$highestValue;
			$awardedPlayer->assignAccolades($insert);

		}

		//find the champion who had the longest life
		//then assign the accolade to the winning player
		function longestLife(){
			$lifeArray = array();
			$playerIdCounter  = 0;
			foreach ($this->playerObjectList as $player) {
				$stats = $player->returnStatList();
				array_push($lifeArray,$stats[5]);
			}
			$highestValue = max($lifeArray);
			$index = array_search($highestValue,$lifeArray);
			$awardedPlayer = $this->playerObjectList[$index];
			$rounded = (round($highestValue/60)*60)/100;
			$time = date('h:i:s A',$rounded);
			$insert = "Longest Life:".$rounded." mins";
			$awardedPlayer->assignAccolades($insert);

		}

		//find the champion who did the most damage taken
		//then assign the accolade to the winning player
		function mostDamageTaken(){
			$damageTaken = array();
			$playerIdCounter  = 0;
			foreach ($this->playerObjectList as $player) {
				$stats = $player->returnStatList();
				array_push($damageTaken,$stats[18]);
			}
			$highestValue = max($damageTaken);
			$index = array_search($highestValue,$damageTaken);
			$awardedPlayer = $this->playerObjectList[$index];
			$insert = "Most Damage Taken:".$highestValue;
			$awardedPlayer->assignAccolades($insert);

		}



	}

	$statsOb = new accoladeGenerator($gameID = $_GET['gameId']);
	$statsOb->makeRequest();
	$statsOb->initiatePlayers();
	$statsOb->getTotalStats();
	$statsOb->mostChampionDamageDone();
	$statsOb->mostHealingDone();
	$statsOb->mostGoldSpent();
	$statsOb->longestLife();
	$statsOb->mostDamageTaken();
	$teamOb = $statsOb->returnTeam();

?>
	<div class = "row">
		<div class = "summoner_profiles col-lg-7 col-md-8 col-xs-5 col-xs-offset-3 col-lg-offset-2">
			<?php 	$teamOb->display(); ?>
		</div>
	</div>
	<?php 	include '../includes/footer.php'; ?>
