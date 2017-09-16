<?php
	include "includes/header.php";
?>
	<div class = "container-fluid">
		<div class = " field_container row">
			<div class ="input_field panel panel-default col-lg-4 col-lg-offset-4 col-md-5 col-md-offset-4 col-sm-5 col-sm-offset-4 col-xs-10 col-xs-offset-1">
			<p> Hello, and welcome to lolrevamp, the one place where you can truly be acknowledged for your skills on the rift</br></br>
			Enter your summoner name and region below</p>
				<form action = "index.php"  method = "post">
				  <div class="form-inline form-group">
				    <input type="text" class=" form-control input_form"  name = "summoner_name"  placeholder="Summoner Name">
				     <select class="custom-select form-control " id="inlineFormCustomSelect" name ="region_selector">
					    <option value = "0" selected>NA</option>
					    <option value="1">BR</option>
					    <option value="2">EUNE</option>
					    <option value="3">EUW</option>
					    <option value="4">JP</option>
					    <option value="5">KR</option>
					    <option value="6">LAN</option>
					    <option value="7">LAS</option>
					    <option value="8">OCE</option>
					    <option value="9">TR</option>
					    <option value="10">RU</option>
					  </select>

				    <button type="submit" name = "submit_name" class=" form-control input_form btn btn-default">Search</button>
				  </div>
				</form>
			</div>
		</div>


<?php


	class introHandler{

		function inputHandle(){
			//check if name and button are pressed then send to the next page
			//triming white ends and spaces in summoner name
			$curlHandler = curl_init();
			$regionList = array();
			array_push($regionList,'NA1');
			array_push($regionList,'BR1');
			array_push($regionList,'EUN1');
			array_push($regionList,'EUW1');
			array_push($regionList,'JP1');
			array_push($regionList,'KR');
			array_push($regionList,'LA1');
			array_push($regionList,'LA2');
			array_push($regionList,'OC1');
			array_push($regionList,'TR1');
			array_push($regionList,'RU');


			//once the submit button is pressed do filtering and verifications
			if(isset($_POST['summoner_name'],$_POST['submit_name'])){
				$rawRummonerName = (string)$_POST['summoner_name'];
				$optionRegion = (int)$_POST['region_selector'];

				//the valid values for a region are 0-10, check if the region is valid
				$valid = false;
				for($x=0;$x<11;$x++){
					if($x==$optionRegion){
						$valid = true;
					}
				}
				//if a valid value for region was not set alert the user with a javascript script
				if($valid == false){
					echo '<script language="javascript">';
					echo 'alert("Must provide a valid region")';
					echo '</script>';
					header("Refresh:0");

				}

				//of the user did not enter a summoner name then we alert with javascript
				if(strlen($rawRummonerName) <= 0){
					echo '<script language="javascript">';
					echo 'alert("Must provide a valid Summoner Name")';
					echo '</script>';
					header("Refresh:0");
				}


				//filter and sanitize the input string
				$region = $regionList[$optionRegion];
				$trimSummonerName =trim($rawRummonerName, " ");
				$cleanSummonerName = str_replace(" ","",$trimSummonerName);
				//create the url where we will pull the json data from
				$accountNameURL = "https://".$region.".api.riotgames.com/lol/summoner/v3/summoners/by-name/".$cleanSummonerName."?api_key=RGAPI-a9d1df16-db7f-469c-9cb1-7d1f5eda0b31";

				//initialize our curl seettings
				curl_setopt($curlHandler, CURLOPT_URL,$accountNameURL ); // set the url request
				curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curlHandler, CURLOPT_HEADER, 0);

				$json = curl_exec($curlHandler);
				$http_status = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);

				echo $http_status;
				$requestStatus = true;
				//if the api request failed then we will display a javascript alert to notify the user
				if($http_status != 200){
					$requestStatus = false;
					echo '<script language="javascript">';
					echo 'alert("Please enter valid Summoner information")';
					echo '</script>';
					header("Refresh:0");
				}
				//if the response is 200 ok then we can send data to the recent games page
				if($http_status == 200){
					$jsonData = json_decode($json,true);
					$accountId = $jsonData['accountId'];
					header("Location: php/recentGames.php?id=".$accountId."&region=".$region);

				}

			}

		}
	}

	include 'includes/footer.php';
	$obj = new IntroHandler();
	$obj->inputHandle();



?>
