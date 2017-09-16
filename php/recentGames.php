<?php
	include "../includes/header.php";
?>

<?php


	class previewHandler{

		private $accountNumber;
		private $championPerData;
		private $gameIdArray;
		private $gameDataArray;
		private $championIdArray;
		private $participantIdArray;
		private $curlHandler;
		private $championImageArray;
		private $gameKdaArray;
		private $summonerSpellArray;
		private $summonerSpellData;
		private $spellImageArray;
		private $itemIdArray;
		private $playerCsArray;
		private $itemImageArray;
		private $itemDataSet;
		private $error;
		private $regionArea;
		private $goldEarnedArray;


		function __construct($accNum,$champInfo,$gameList,$ssData,$itemInfo){
			$this->accountNumber = $accNum;
			$this->championPerData = $champInfo;
			$this->gameIdArray = $gameList;
			$this->summonerSpellData = $ssData;
			$this->itemDataSet = $itemInfo;
			$this->gameDataArray =  array();
			$this->championIdArray = array();
			$this->playerCsArray = array();
			$this->goldEarnedArray = array();
			$this->participantId = -1;
			$this->itemImageArray = array();
			$this->participantIdArray = array();
			$this->championImageArray = array();
			$this->gameKdaArray = array();
			$this->summonerSpellArray = array();
			$this->spellImageArray = array();
			$this->itemIdArray = array();
			$this->curlHandler  = curl_init();
			$this->error = false;
			curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->curlHandler, CURLOPT_HEADER, 0);
		}

		//function to set the region to what the user entered
		function setRegion($__region){
			$this->regionArea = $__region;
		}

		//function will get match data depending on the gameID
		function getMatchData(){
			for($j=0;$j<15;$j++){
				$matchURl= "https://".$this->regionArea.".api.riotgames.com/lol/match/v3/matches/".$this->gameIdArray[$j]."?api_key=RGAPI-a9d1df16-db7f-469c-9cb1-7d1f5eda0b31";
				curl_setopt($this->curlHandler, CURLOPT_URL,$matchURl ); // set the url request
				$json = curl_exec($this->curlHandler);
				$gameData = json_decode($json,true);
				array_push($this->gameDataArray,$gameData);
			}

		}

		//function will parse the JSON object and find the summoner we are looking for
		function findSummoner(){
			for($y=0;$y<15;$y++){
				$game = $this->gameDataArray[$y];
				for($x=0;$x<10;$x++){
					$accNumber = $game['participantIdentities'][$x]['player']['accountId'];
					if(strcmp($this->accountNumber,$accNumber) == 0){
						$this->participantId = $x;
						$champId = $game['participants'][$this->participantId]['championId'];
						array_push($this->participantIdArray,$this->participantId);
						array_push($this->championIdArray,$champId);

					}
				}
			}
	}


		//Match champion played with the static data set
		function findChampion(){
			if(sizeof($this->championIdArray) == 0){
				echo "<p> There are no matches to reterive for this account</p>";
				$this->error = true;
				return;
			}
			for($k=0;$k<15;$k++){
				$passedChampID = $this->championIdArray[$k];
				$champs = $this->championPerData['data'];
				foreach ($champs as $champion) {
					$champKey = (string)$champion['key'];
					if(strcmp($passedChampID, $champKey) == 0){
						$champName = (string)$champion['id'];
						$champImage = "<img src = http://ddragon.leagueoflegends.com/cdn/7.17.1/img/champion/".$champName.".png alt = champion_played>";
						array_push($this->championImageArray,$champImage);
					}
				}

			}

		}

		//function will set a KDA for each match in history
		function getKDA(){
			for($k=0;$k<15;$k++){
				$game = $this->gameDataArray[$k];
				$id = $this->participantIdArray[$k];
				$kills =  $game['participants'][$id]['stats']['kills']." ";
				$deaths = $game['participants'][$id]['stats']['deaths']." ";
				$assists =  $game['participants'][$id]['stats']['assists']." ";
				$score = (string) "KDA: ".$kills."/".$deaths."/".$assists;
				array_push($this->gameKdaArray,$score);
			}

		}

		//function will set the creep score for each match in history
		function getCs(){
			for($k=0;$k<15;$k++){
				$game = $this->gameDataArray[$k];
				$id = $this->participantIdArray[$k];
				$cs = $game['participants'][$id]['stats']['totalMinionsKilled'];
				$minionsKilled = "CS:".$cs;
				array_push($this->playerCsArray, $minionsKilled);
			}
		}

		//function will set the gold earned by player for each match
		function getGold(){
			for($k=0;$k<15;$k++){
				$game = $this->gameDataArray[$k];
				$id = $this->participantIdArray[$k];
				$gold = $game['participants'][$id]['stats']['goldEarned'];
				$gold = number_format($gold/1000,1);
				$goldAmount = "Gold:".$gold;
				array_push($this->goldEarnedArray, $goldAmount);
			}
		}

		//function will set the summoner spells for the player's match history
		function findSummonerSpells(){
			for($k=0;$k<15;$k++){
				$game = $this->gameDataArray[$k];
				$id = $this->participantIdArray[$k];
				$first = $game['participants'][$id]['spell1Id'];
				$second = $game['participants'][$id]['spell2Id'];
				array_push($this->summonerSpellArray,$first);
				array_push($this->summonerSpellArray,$second);
			}
			//once the summoner spells are found match the strings with the static data set
			$summonersSpells = $this->summonerSpellData['data'];
			for($k=0;$k<30;$k++){
				$spell = (string)$this->summonerSpellArray[$k];
				foreach ($summonersSpells as $ss) {
					$key = (string)$ss['key'];
					if(strcmp($spell, $key)==0){
						$name = (string)$ss['id'];
						$image ="<img src = http://ddragon.leagueoflegends.com/cdn/7.17.1/img/spell/".$name.".png alt = summoner_spell>";
						array_push($this->spellImageArray, $image);
						}
					}

				}

		}

		//function to set the items purchased by each player
		function findItems(){
			$itemData = $this->itemDataSet['data'];
			$z =0;
			for($k=0;$k<15;$k++){
				$game = $this->gameDataArray[$k];
				$id = $this->participantIdArray[$k];
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item0']);
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item1']);
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item2']);
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item3']);
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item4']);
				array_push($this->itemIdArray, (string)$game['participants'][$id]['stats']['item5']);
				//once the items are found pull the images from the static data set
				for($q=0;$q<6;$q++){
					foreach ($itemData as $data) {
						$strImg = $data['image']['full'];
						$returnArray = explode('.', $strImg);
						$itemNumber = $this->itemIdArray[$q];
						if($itemNumber == 0){
							$image = "<img src = https://thecliparts.com/wp-content/uploads/2016/07/back-x-clip-art.png width = 50 height = 50 alt = no_item>";
							array_push($this->itemImageArray, $image);
							break;
						}
						if($returnArray[0] == $itemNumber){
							$image = "<img src = http://ddragon.leagueoflegends.com/cdn/7.17.1/img/item/".$returnArray[0].".png alt = item>";
							array_push($this->itemImageArray,$image);
						}
					}

				}
				$this->itemIdArray = array();

			}

		}


		//Up till this point we have been aquiring the data and images'
		//now in this function we will output to browser
		function outputPreview(){
			$ssCounter =0;
			$itemCounter =0;
			for($x=0;$x<15;$x++){
				$id = $this->participantIdArray[$x];
				$game = $this->gameDataArray[$x];
				$result = $game['participants'][$id]['stats']['win'];
				if($result == 1){
					$string = "win_block";
				}
				else
					$string = "loss_block";
				echo "<a href = 'accolades.php?gameId=".$this->gameIdArray[$x]."'</a>";
				echo "<div class =".$string.">";
				echo $this->championImageArray[$x];
				echo "<div class = item_summary>";
				echo $this->spellImageArray[$ssCounter];
				echo $this->spellImageArray[$ssCounter+1];
				echo $this->itemImageArray[$itemCounter];
				echo $this->itemImageArray[$itemCounter+1];
				echo $this->itemImageArray[$itemCounter+2];
				echo $this->itemImageArray[$itemCounter+3];
				echo $this->itemImageArray[$itemCounter+4];
				echo $this->itemImageArray[$itemCounter+5];
				echo "<ul>";
				echo "<li>".$this->gameKdaArray[$x]."</li>";
				echo "<li>".$this->playerCsArray[$x]."</li>";
				echo "<li>".$this->goldEarnedArray[$x]."</li>";
				echo "</ul>";
				echo "</div>";
				echo "</div>";
				$ssCounter+=2;
				$itemCounter+=6;
			}
		}

		//function used for debugging
		function checkError(){
			return $this->error;
		}
	}

		//this block of code is used to initialize the static data
		//also we start to make requests here via CURL
		$accountID = $_GET['id'];
		$region = $_GET['region'];
		$staticChampionURL = "http://ddragon.leagueoflegends.com/cdn/7.17.1/data/en_US/champion.json";
		$staticSummonerURL = "http://ddragon.leagueoflegends.com/cdn/7.17.1/data/en_US/summoner.json";
		$staticItemURL = "http://ddragon.leagueoflegends.com/cdn/7.17.1/data/en_US/item.json";
		$json = file_get_contents($staticChampionURL);
		$championData = json_decode($json,true);
		$json = file_get_contents($staticSummonerURL);
		$summonerSpellFile = json_decode($json,true);
		$json = file_get_contents($staticItemURL);
		$itemFile = json_decode($json,true);

		//from the account id we can get the recent 20 games
		$matchListURL = "https://".$region.".api.riotgames.com/lol/match/v3/matchlists/by-account/".$accountID."?queue=420&season=9&api_key=RGAPI-a9d1df16-db7f-469c-9cb1-7d1f5eda0b31";
		$json = file_get_contents($matchListURL);
		$jsonMatches = json_decode($json,true);
		$gameIdList = array();
		for($i=0;$i<15;$i++){
			$jsonMatches['matches'][$i]['gameId'];
			$gameId = $jsonMatches['matches'][$i]['gameId'];
			array_push($gameIdList,$gameId);
		}
	?>

	<div class = "container-fluid">
		<div class = "row">
			<div class ="match_history col-lg-6 col-md-8 col-sm-10 col-sm-offset-1 col-md-offset-2 col-lg-offset-3 col-xs-12">
			<?php
			//here is where we begin the sequence of function calls
			$previewOb = new previewHandler($accountID,$championData,$gameIdList,$summonerSpellFile,$itemFile);
			$previewOb->setRegion($region);
			$previewOb->getMatchData();
			$previewOb->findSummoner();
			$previewOb->findChampion();
			$errorCheck = $previewOb->checkError();
			if($errorCheck == false){
				$previewOb->getKDA();
				$previewOb->getCs();
				$previewOb->getGold();
				$previewOb->findSummonerSpells();
				$previewOb->findItems();
				$previewOb->outputPreview();
			}
			?>

			</div>
		</div>
	</div>


<?php
		include '../includes/footer.php';

?>
