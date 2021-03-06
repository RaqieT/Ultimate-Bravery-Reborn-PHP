<!DOCTYPE html>
<html>
  <head>
    <title>Ultimate Bravery: Reborn</title>
    <meta charset="utf-8">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<link rel="stylesheet" href="cbakill.css">
	<link rel="stylesheet" href="main.css">
	<meta name="description" content="Ultimate Bravery - Build your champion RANDOMLY!">
	<meta name="keywords" content="Ultimate Bravery,ultimate,bravery,reborn,rakie.tk,ub.rakie.tk,random build,League of Legends">
  </head>
  <div class="cbakiller"><body></div>
  <center>
	<h1>Ultimate Bravery</h1><h2>Reborn</h2><hr>
	<div class=container>
		<?php
		//Get Rand Champ
		$version = json_decode(file_get_contents('https://global.api.pvp.net/api/lol/static-data/euw/v1.2/versions?api_key=8b6d388b-22a1-4084-bb53-b36429dd0d4c'), true)[0]; //data version
		echo '<form action="index.php" method="get">';
		if(isset($_GET['map']))
			$mapGetter = $_GET['map'];
		else
			$mapGetter = 'ARAM';
		if ($mapGetter == 'CLASSIC')
		{
			$aramSelection='';
			$classicSelection='selected';
			$selectedMap = 'CLASSIC'; //messy, CLASSIC, Summoner's Rift can't use the API here
			$mapId = 11;
		}
		else
		{
			$aramSelection='selected';
			$classicSelection='';
			$selectedMap = 'ARAM';
			$mapId = 12;
		}

		 
		echo "Choose map: <select name=map>
		<option value=ARAM ".$aramSelection.">ARAM</option>
		<option value=CLASSIC ".$classicSelection.">Summoner's Rift</option>
		</select>";
		
		$championsList = json_decode(file_get_contents('https://euw.api.pvp.net/api/lol/euw/v1.2/champion?api_key=8b6d388b-22a1-4084-bb53-b36429dd0d4c'), true);
		$picked=array_rand($championsList['champions']);
		$champId = $championsList['champions'][$picked]['id'];
		$championInfo = json_decode(file_get_contents("https://global.api.pvp.net/api/lol/static-data/euw/v1.2/champion/$champId?champData=image&api_key=8b6d388b-22a1-4084-bb53-b36429dd0d4c"), true);
		$summIco = $championInfo['image']['full'];
		echo '<br><div class=infoContainer><table class=champ><tr><td colspan="2" class=champ>'."<img class=champ src=http://ddragon.leagueoflegends.com/cdn/$version/img/champion/$summIco><br>".$championInfo['name'].'</td>'; //print champ

		//Get Rand 2 SS
		$summonerList=json_decode(file_get_contents('https://global.api.pvp.net/api/lol/static-data/euw/v1.2/summoner-spell?spellData=modes&api_key=8b6d388b-22a1-4084-bb53-b36429dd0d4c'), true);
		foreach ($summonerList['data'] as $key => $value) {
			if(!in_array($selectedMap, $value['modes']))
				unset($summonerList['data'][$key]);
		}

		$ssArray=array_rand($summonerList['data'],2); //this array containts 2 random summoner spells,  we need to know which "ss" have been chosen to roll items with or without smite requirement

		echo '</tr><tr>';
		
		
		
		//print summoners
		$haveSmite = false;
		for ($i=0; $i < 2; $i++) { 
			echo '<td class=ss>'."<img src=http://ddragon.leagueoflegends.com/cdn/$version/img/spell/$ssArray[$i].png><br>".$summonerList['data'][$ssArray[$i]]['name'].'</td>';
			if($ssArray[$i] == 'SummonerSmite')
			{
				$haveSmite = true;
			}		
		}
		echo '</tr></table>';
		
		//random masteries generator
		$randMast=[];
		array_push($randMast,rand(0,18));

		$minValue = 12 - $randMast[0];
		if($minValue < 0)
			$minValue = 0;

		$maxValue = 30 - $randMast[0];
		if($maxValue > 18)
			$maxValue = 18;
		
		array_push($randMast, rand($minValue, $maxValue));
		
		array_push($randMast,30-($randMast[1]+$randMast[0]));
		
		shuffle($randMast);
		echo '<div class=invisible>
				<table class=masteries><tr><td colspan=3>Masteries</td></tr>
				<tr>';
				$colorMast["Red"] = $randMast[0];
				$colorMast["Blue"] = $randMast[1];
				$colorMast["Green"] = $randMast[2];
				
		
				foreach($colorMast as $key => $value)
				{
					echo "<td class=mastery$key>".$value."</td>";
				}
				echo '</tr></table>';
		
		$maxSpell = array('Q','W','E');
		echo '
			<table class=maxSpell><tr><td class=item>Bravery spell</td></tr>
			<tr><td class=spell>';
			echo $maxSpell[array_rand($maxSpell)];
			echo '</td></tr></table>
			</div>
		
		</div>';
		//checkSmite

		//ITEMS - MOST ENJOYABLE PART
		$itemsJson=json_decode(file_get_contents('https://global.api.pvp.net/api/lol/static-data/euw/v1.2/item?itemListData=all&api_key=8b6d388b-22a1-4084-bb53-b36429dd0d4c'), true);
		$itemsList = $itemsJson['data'];

		//Remove some of notmatching items

		//declare boots array
		$bootsArray=[];
		$smiteItems=[];
		foreach ($itemsList as $key => $value) {
			
			//remove trinkets + lane items
			foreach ($value['tags'] as $bkey => $bvalue) {
				if($bvalue == 'Trinket')
					unset($itemsList[$key]);
				if($bvalue == 'Lane' && $value['name'] != "Zz'Rot Portal") //Guardian Horn could be here, but... weakshit
					unset($itemsList[$key]);
				if($bvalue == 'Boots' && $key != '1001')
				{
						array_push($bootsArray, $key);
				}
					
			}
			
			//only items without into items, fullbuild items
			
			if(isset($value['into']))
			{
				if(!in_array($key,$bootsArray))
					unset($itemsList[$key]);
			}
			//remove shitty items
			if($value['gold']['total'] < 1000 && !in_array($key,$bootsArray))
			{
				unset($itemsList[$key]);
			}
			
			//rqitems remove
			if(isset($value['requiredChampion']))
				unset($itemsList[$key]);
			//map check
			if(!$value['maps'][$mapId])
				unset($itemsList[$key]);
			if($value['consumed'])
				unset($itemsList[$key]);
			if(isset($value['inStore']) && !$value['inStore'])
				unset($itemsList[$key]);
			//smite items
			if(!$haveSmite)
				if($value['hideFromAll'])
					unset($itemsList[$key]);

			//quick charge item block, dunno why they're enabled on summoner's rift?!?
			if(strpos($value['name'],'Quick Charge'))
				unset($itemsList[$key]);
		}
		//remember about good guy Viktor!
		if($championInfo['name'] == 'Viktor')
		{
			$randomItemsArray=array_rand($itemsList,5);
			array_unshift($randomItemsArray, 3200); //3200 hex rod
			array_push($itemsList,$itemsJson['data'][3200]);
			
		}
		else
			$randomItemsArray=array_rand($itemsList,6);
		
		
		echo '<div class=spacer></div>
		<table class=items>
		<td class=head>Item set</td>
		<tr>';
		$foundSmiteItem=false;
		$foundBoots=false;
		$bootsFoundInBuild=0;
		//remove multi boots from item list
		foreach($randomItemsArray as $key)
		{
			if(in_array($key, $bootsArray))
				{
						foreach ($bootsArray as $bkey => $bvalue) {
							if($bvalue != $key || $championInfo['name'] == 'Cassiopeia') //cassio has no legs
							{
								unset($itemsList[$bvalue]);	
								foreach ($randomItemsArray as $ckey => $cvalue) {
									if($cvalue == $bvalue)
									{
										$bootsFoundInBuild++;
										unset($randomItemsArray[$ckey]);
									}
								}
								
							}
						}
						break;
				}
		}
		
		$finalItems = $randomItemsArray;
		//remove multiboots from set
		while($bootsFoundInBuild)
		{
			array_push($finalItems, array_rand($itemsList));
			$bootsFoundInBuild--;
		}
			
			foreach ($finalItems as $key) {
				echo '<td class=item>';
				//smite item multiroll protection
				if($itemsList[$key]['hideFromAll'])
				{
					if($foundSmiteItem)
					{	
						do {
							$key = array_rand($itemsList);
						} while(isset($itemsList[$key]['hideFromAll']));
					}
					$foundSmiteItem=true;
				}
				echo "<img src=http://ddragon.leagueoflegends.com/cdn/$version/img/item/$key.png><br>";
				echo $itemsList[$key]['name'];
				
				
				
				echo '</td>';
			}
		echo '</tr></table>';
			
		
		
		//var_dump($colorMast);
		
		//var_dump($randMast);
		echo '<input class="button" type="submit" value="Randomize!">
	</form>';
		echo "<div class=version>API Data Version: ".$version."</div>";
	?>
	
	</div>
  </center>
  <div class="cbakiller"></body></div>
</html>
