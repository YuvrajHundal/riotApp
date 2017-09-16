<?php 

	class team{
		private $teamID;
		private $winOrLoss;
		private $teamOne = array();
		private $teamTwo = array();



	function __construct($__first,$__second){
		$this->teamOne = $__first;
		$this->teamTwo =$__second;
	}


	function setTeamOne($__player){
		array_push($this->teamOne,$__player);

	}

	function setTeamTwo($__player){
		array_push($this->teamTwo,$__player);
	}

	function display(){
		foreach ($this->teamOne as $one) {
			$one->displayInfo(0);
		}
		echo "</br>";
		foreach ($this->teamTwo as $two) {
			$two->displayInfo(1);
		}
	}

}

?>