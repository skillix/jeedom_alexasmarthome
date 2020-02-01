<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
					
					

if (!jeedom::apiAccess(init('apikey'), 'alexaamazonmusic')) {
	echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
	log::add('alexaamazonmusic_mqtt', 'debug',  'Clé Plugin Invalide');
	die();
}

	//log::add('alexaamazonmusic_mqtt', 'debug',  'Clé Plugin Valide');

if (init('test') != '') {
	echo 'OK';
	die();
}

$chaineRecuperee=file_get_contents("php://input");
$nom=$_GET["nom"];
log::add('alexaamazonmusic', 'debug',  'Réception données sur jeealexaamazonmusic ['.$nom.']');
log::add('alexaamazonmusic_mqtt', 'info',  " -------------------------------------------------------------------------------------------------------------" );
log::add('alexaamazonmusic_widget', 'info',  " -------------------------------------------------------------------------------------------------------------" );

log::add('alexaamazonmusic_mqtt', 'debug',  "chaineRecuperee: ".$chaineRecuperee);

$debut=strpos($chaineRecuperee, "{");
$fin=strrpos($chaineRecuperee, "}");
$longeur=1+intval($fin)-intval($debut);
$chaineRecupereeCorrigee=substr($chaineRecuperee, $debut, $longeur);

	if ($nom !="commandesEnErreur") {
		$chaineRecupereeCorrigee=str_replace ("[", "", $chaineRecupereeCorrigee);
		$chaineRecupereeCorrigee=str_replace ("]", "", $chaineRecupereeCorrigee);
	}

log::add('alexaamazonmusic_mqtt', 'debug',  "chaineRecupereeCorrigee: ".$chaineRecupereeCorrigee);
log::add('alexaamazonmusic_mqtt', 'debug',  "nom: ".$nom);

$result = json_decode($chaineRecupereeCorrigee, true);


if (!is_array($result)) {
	log::add('alexaamazonmusic_mqtt', 'debug', 'Format Invalide');
	die();
}
log::add('alexaamazonmusic_mqtt', 'debug',  'deviceSerialNumber:'.$result['deviceSerialNumber']);
$logical_id = $result['deviceSerialNumber']."_player";
$alexaamazonmusic=alexaamazonmusic::byLogicalId($logical_id, 'alexaamazonmusic');
$alexaamazonmusic2=alexaamazonmusic::byLogicalId($result['deviceSerialNumber'], 'alexaamazonmusic'); // Le device Amazon Echo
$alexaamazonmusic3=alexaamazonmusic::byLogicalId($result['deviceSerialNumber']."_playlist", 'alexaamazonmusic'); // Le device PlayList

/*$alexaamazonmusic->emptyCacheWidget();	
$alexaamazonmusic2->emptyCacheWidget();
$alexaamazonmusic3->emptyCacheWidget();

clearCacheWidget();
*/

log::add('alexaamazonmusic_node', 'info',  'Alexa-jee: '.$nom);

	switch ($nom) {
		
			case 'commandesEnErreur':
			log::add('alexaamazonmusic_node', 'warning',  "Alexa-jee: Il va falloir relancer: ".$chaineRecupereeCorrigee." Pause 8s");
			sleep(8);
				$commandeaRelancer = json_decode($chaineRecupereeCorrigee, true);
				$queryEnErreur = $commandeaRelancer['queryEnErreur'];
				$listeCommandesEnErreur = $commandeaRelancer['listeCommandesEnErreur'];
				$listeCommandesEnErreur=str_replace ("[", "", $listeCommandesEnErreur);
				$listeCommandesEnErreur=str_replace ("]", "", $listeCommandesEnErreur);
				
				if (is_array($listeCommandesEnErreur)) { // s'il y a un groupe de commandes à relancer
					foreach ($listeCommandesEnErreur as $CommandesEnErreur){
						$url="http://" . config::byKey('internalAddr') . ":3456/".$CommandesEnErreur['command']."?replay=1&".http_build_query($queryEnErreur);		
						$json=file_get_contents($url);
					}
				} else {								// s'il n'y a qu'une commande à relancer
					//faudra surement ajouter un test ici pour voir si c'ets pas vide
						$url="http://" . config::byKey('internalAddr') . ":3456/".$listeCommandesEnErreur."?replay=1&".http_build_query($queryEnErreur);		
						$json=file_get_contents($url);	
				}
			break;
			
			case 'ws-bluetooth-state-change':
			if ($result['bluetoothEvent'] == 'DEVICE_CONNECTED') metAJour("bluetoothDevice", "Connexion en cours", 'bluetoothDevice', false , $alexaamazonmusic2);
			if ($result['bluetoothEvent'] == 'DEVICE_DISCONNECTED') metAJour("bluetoothDevice", "Déconnexion en cours", 'bluetoothDevice', false , $alexaamazonmusic2);				
				metAJourBluetooth($result['deviceSerialNumber'], $result['audioPlayerState'], $alexaamazonmusic2, $alexaamazonmusic);
			break;	
			
			case 'ws-volume-change':
				metAJour("Volume", $result['volume'], 'volumeinfo', false , $alexaamazonmusic);
				metAJour("Volume", $result['volume'], 'volumeinfo', false , $alexaamazonmusic2);
			break;	
			
			case 'ws-notification-change': //changement d'une alarme/rappel
			log::add('alexaamazonmusic_node', 'info',  'Alexa-jee: notificationVersion: '.$result['notificationVersion']);

				$alexaamazonmusic2->refresh();	// Lance un refresh du device principal
			break;	
			
			case 'ws-media-queue-change':
				metAJour("loopMode", $result['loopMode'], 'loopMode', false , $alexaamazonmusic);
				metAJour("playBackOrder", $result['playBackOrder'], 'playBackOrder', false , $alexaamazonmusic);
				
				metAJourPlayList($logical_id, $result['audioPlayerState'], $alexaamazonmusic3, $alexaamazonmusic);

			//break; // il ne faut pas s'arrêter mais aller tout mettre à jour.	
			
			case 'ws-device-activity':

				metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true , $alexaamazonmusic);
				metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true , $alexaamazonmusic2);
				
				metAJour("activityStatus", $result['activityStatus'], 'activityStatus', true , $alexaamazonmusic);

				metAJour("Radio", $result['domainAttributes']['nBestList']['stationCallSign'], 'radioinfo', false , $alexaamazonmusic);
				
				metAJour("Radio", $result['domainAttributes']['nBestList']['stationName'], 'radioinfo', false , $alexaamazonmusic);
				
				metAJour("playlistName", $result['domainAttributes']['nBestList']['playlistName'], 'playlistName', false , $alexaamazonmusic);
				metAJour("playlistName", $result['domainAttributes']['nBestList']['playlistName'], 'playlistName', false , $alexaamazonmusic3);
				
				metAJourPlayer($logical_id, $result['audioPlayerState'], $alexaamazonmusic);
				metAJourPlayList($logical_id, $result['audioPlayerState'], $alexaamazonmusic3, $alexaamazonmusic);
				metAJourPlayer($logical_id, $result['audioPlayerState'], $alexaamazonmusic); //par sécurité

				//metAJour("songName", $result['domainAttributes']['nBestList']['songName'], 'songName', true , $alexaamazonmusic);
				
			break;			
		
			case 'ws-audio-player-state-change': // elle a visiblement disparue cette balise des logs mqtt
				metAJour("Audio Player State", $result['audioPlayerState'], 'audioPlayerState', true , $alexaamazonmusic);
			case 'refreshPlayer':
				metAJourPlayer($logical_id, $result['audioPlayerState'], $alexaamazonmusic);
				metAJourPlayList($logical_id, $result['audioPlayerState'], $alexaamazonmusic3, $alexaamazonmusic);
			break;
			
			default:

				if (!is_object($alexaamazonmusic)) {
				log::add('alexaamazonmusic_mqtt', 'debug',  'Device non trouvé: '.$logical_id);
				die();
				}
				else{
				log::add('alexaamazonmusic_mqtt', 'debug',  'Device trouvé: '.$logical_id);
				}
		
	}
	log::add('alexaamazonmusic_mqtt', 'info',  " ----------------------------------------------------------------------------------------------------------------------------------------------" );
	log::add('alexaamazonmusic_widget', 'info',  " ----------------------------------------------------------------------------------------------------------------------------------------------" );	if (is_object($alexaamazonmusic)) $alexaamazonmusic->refreshWidget();
	/*
// ----------------- VOLUME ------------------
			
if ($result['volume']!=null)
{
log::add('alexaamazonmusic_mqtt', 'debug',  'Volume trouvé: '.$result['volume']);
				$alexaamazonmusic->checkAndUpdateCmd('volumeinfo', $result['volume']);
				die();
}

// ----------------- INTERACTION ------------------
	
			
if ($result['description']['summary']!=null)
{
log::add('alexaamazonmusic_mqtt', 'debug',  'Intéraction trouvée: '.$result['description']['summary']);
				$alexaamazonmusic->checkAndUpdateCmd('interactioninfo', $result['description']['summary']);
				die();
}

// ----------------- audioPlayerState ------------------
	
			
if ($result['audioPlayerState']!=null)
{
log::add('alexaamazonmusic_mqtt', 'debug',  'Changement état Audio Player: '.$result['audioPlayerState']);
				$alexaamazonmusic->checkAndUpdateCmd('audioPlayerState', $result['audioPlayerState']);
				die();
}
*/

function metAJour($nom, $variable, $commandejeedom, $effaceSiNull, $_alexaamazonmusic) {
	try {
		if (isset($variable)) {
			log::add('alexaamazonmusic_widget', 'info',  '   ['.$nom.':'.$commandejeedom.'] find: '.json_encode($variable). " sur {".$_alexaamazonmusic->getName()."}");
			$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, $variable);
			}
			else {
			log::add('alexaamazonmusic_widget', 'info',  '   ['.$nom.':'.$commandejeedom.'] non trouvé: '.$variable);
				if ($effaceSiNull) {
					$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, null);
					log::add('alexaamazonmusic_widget', 'info',  '   ['.$nom.':'.$commandejeedom.'] non trouvé et vidé');
				}
			}	
	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
		} catch (Error $e) {
				log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

			}	
}

function metAJourBoutonPlayer($nom, $variable, $commandejeedom, $nomBouton, $_alexaamazonmusic) {
	try {
		if (isset($variable)) {
			log::add('alexaamazonmusic_widget', 'info',  '   ['.$nom.':'.$commandejeedom.':'.$nomBouton.'] find: '.json_encode($variable));
			$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, $variable);
			if ($variable=='ENABLED') $visible=1; else $visible=0;
				$cmd = $_alexaamazonmusic->getCmd(null, $nomBouton);
				if (is_object($cmd)) {
				//log::add('alexaamazonmusic_widget', 'info',  ' ok invisible');
				$cmd->setIsVisible($visible);
				$cmd->save();
				}
			}
	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
		} catch (Error $e) {
				log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

			}	
}

function metAJourImage($nom, $variable, $commandejeedom, $effaceSiNull, $_alexaamazonmusic) {
	
	try {
		
		
		//if ($variable!=null)
		if (isset($variable)) {
			log::add('alexaamazonmusic_widget', 'info',  '   ['.$nom.':'.$commandejeedom.'] find: '.json_encode($variable));
			//$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, $variable);
			//$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, "<img width='150' height='150' src='".$variable."' />");
			$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, $variable);
			//die();
			}
			else
			{
			log::add('alexaamazonmusic_widget', 'debug',  '['.$nom.':'.$commandejeedom.'] non trouvé');
			$_alexaamazonmusic->checkAndUpdateCmd($commandejeedom, "plugins/alexaamazonmusic/core/img/vide.gif");
			}	
	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
	} catch (Error $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

	}	
}	

function metAJourPlayer($serialdevice, $audioPlayerState, $alexaamazonmusic) {
		//log::add('alexaamazonmusic_widget', 'debug',  'zzzzzzzzzzzzzzzzz metAJourPlayer:');
//log::add('alexaamazonmusic_node', 'info',  " ***********************[metAJourPlayer]*********************************" );

	try {
		
		//log::add('alexaamazonmusic_widget', 'debug',  'zzzzzzzzzzzzzzzzzzz metAJourPlayer:'.$audioPlayerState);
		//if (($audioPlayerState=="PLAYING") || ($audioPlayerState=="REFRESH") || ($audioPlayerState=="PAUSED"))	{
		//if ($audioPlayerState!="FINISHED") 	{
		//log::add('alexaamazonmusic_widget', 'debug',  ' metAJourPlayer:'.$serialdevice);

		$json=file_get_contents("http://" . config::byKey('internalAddr') . ":3456/playerInfo?device=".str_replace ("_player", "", $serialdevice));
		$result = json_decode($json,true);		
		log::add('alexaamazonmusic_widget', 'debug',  ' JSON:'.$json);
	
		
		//}
		//else {
//	metAJour("state", $audioPlayerState, 'state', false , $alexaamazonmusic);		
	// Pour supprimer les éléments MQTT qui étaient arrivés précédemment
		//metAJour("playlistName", "", 'playlistName', true , $alexaamazonmusic);
	//	}
		
metAJour("subText1", $result['playerInfo']['infoText']['subText1'], 'subText1', true , $alexaamazonmusic);
metAJour("subText2", $result['playerInfo']['infoText']['subText2'], 'subText2', true , $alexaamazonmusic);
metAJour("title", $result['playerInfo']['infoText']['title'], 'title', true , $alexaamazonmusic);
metAJourImage("url", $result['playerInfo']['mainArt']['url'], 'url', true , $alexaamazonmusic);
metAJour("mediaLength", $result['playerInfo']['progress']['mediaLength'], 'mediaLength', true , $alexaamazonmusic);
metAJour("mediaProgress", $result['playerInfo']['progress']['mediaProgress'], 'mediaProgress', true , $alexaamazonmusic);
metAJour("providerName", $result['playerInfo']['provider']['providerName'], 'providerName', true , $alexaamazonmusic);
metAJour("state", $result['playerInfo']['state'], 'state', false , $alexaamazonmusic);




//log::add('alexaamazonmusic_widget', 'debug',  '5>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> resultplayerInfo:'.json_encode($result['playerInfo']['provider']['providerName']));



// Affecte le statut Playing du device Player
$alexaamazonmusic->setStatus('Playing', ($result['playerInfo']['state']=="PLAYING"));




/*
// NEXT ET PREVIOUS MIS A JOUR PAR requete Player Info
metAJourBoutonPlayer("nextState", $result['playerInfo']['transport']['next'], 'nextState', 'next' , $alexaamazonmusic);
metAJourBoutonPlayer("previousState", $result['playerInfo']['transport']['previous'], 'previousState', 'previous' , $alexaamazonmusic);
// Play et Pause Mis à jour en fonction de state et plus $audioPlayerState
	//if ($audioPlayerState=="PLAYING") {
	if (isset($result['playerInfo']['state'])) {
		if ($result['playerInfo']['state']=="PLAYING") {
				$etatdePlay='DISABLED'; 
				$etatdePause='ENABLED';
		}
			else {
				$etatdePlay='ENABLED';
				$etatdePause='DISABLED';
			}
	}
metAJourBoutonPlayer("playPauseState", $etatdePause , 'playPauseState', 'pause' , $alexaamazonmusic);
metAJourBoutonPlayer("playPauseState", $etatdePlay, 'playPauseState', 'play' , $alexaamazonmusic);
// Ancienne mise à jour par Amazon
//metAJourBoutonPlayer("playPauseState", $result['playerInfo']['transport']['playPause'], 'playPauseState', 'pause' , $alexaamazonmusic);//PAR requete Player Info
// metAJourBoutonPlayer("playPauseState", $result['playerInfo']['transport']['playPause'], 'playPauseState', 'play' , $alexaamazonmusic); //PAR requete Player Info
	*/

	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
	} catch (Error $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

	}
//log::add('alexaamazonmusic_node', 'info',  " ************************************************************************" );
if (is_object($alexaamazonmusic)) $alexaamazonmusic->refreshWidget(); //refresh Tuile Player
log::add('alexaamazonmusic_widget', 'debug',  '** Mise à jour Tuile du Player **');

}

function metAJourPlaylist($serialdevice, $audioPlayerState, $alexaamazonmusic3, $alexaamazonmusic) {
		//log::add('alexaamazonmusic_widget', 'debug',  'zzzzzzzzzzzzzzzzz metAJourPlayer:');

	try {
		if ($audioPlayerState!="FINISHED") 	{		
		
		//Pour avoir la piste en cours, on va aller chercher la valeur de playerinfo/mainArt/url pour pouvoir la comparer aux images de la playlist
		$json=file_get_contents("http://" . config::byKey('internalAddr') . ":3456/playerinfo?device=".str_replace ("_player", "", $serialdevice));
		$result = json_decode($json,true);		
		$imageURLenCoursdeLecture=$result['playerInfo']['miniArt']['url']; //Modif 09/12/2019 proposée par Aidom, annulée 10/12/2019
		$etatPlayer=$result['playerInfo']['state'];
		
		//log::add('alexaamazonmusic_widget', 'debug',  'zzzzzzzzzzzzzzzzzzz metAJourPlayer:'.$audioPlayerState);
		//if (($audioPlayerState=="PLAYING") || ($audioPlayerState=="REFRESH") || ($audioPlayerState=="PAUSED"))	{

		//log::add('alexaamazonmusic_widget', 'debug',  ' metAJourPlayer:'.$serialdevice);
		$json=file_get_contents("http://" . config::byKey('internalAddr') . ":3456/media?device=".str_replace ("_player", "", $serialdevice));
		$result = json_decode($json,true);		
		//log::add('alexaamazonmusic_widget', 'debug',  '++++++++++++++++++++++++++++++++++ JSON:'.$json);
		//$imageURLenCoursdeLecture=$result['imageURL'];
	
		}
		else {
	//metAJour("state", $audioPlayerState, 'state', false , $alexaamazonmusic);		
	// Pour supprimer les éléments MQTT qui étaient arrivés précédemment
		//metAJour("playlistName", "", 'playlistName', true , $alexaamazonmusic);
		}

//ON RECUPERE CE QUIE ST AU D2BUT DE MEDIA
metAJour("contentId", $result['contentId'], 'contentId', true , $alexaamazonmusic);
//log::add('alexaamazonmusic_widget', 'debug',  '++++++>+++++++++>+++++++++>++++++++++ $contentId:'.$result['contentId']);



			//$image=$result['queue']['0']['imageURL'];
			//log::add('alexaamazonmusic_widget', 'debug',  '++++++>+++++++++>+++++++++>++++++++++ $image:'.$image);
			//log::add('alexaamazonmusic_widget', 'debug', '-->'.json_encode($result));
			$html="<table style='border-collapse: separate; border-spacing : 10px; ' border='0' width='100%'>";
			$compteurQueue=1;		
	foreach ($result['queue'] as $key => $value) {
				log::add('alexaamazonmusic_widget', 'debug', '-----------------album:'.$value['album']);
				log::add('alexaamazonmusic_widget', 'debug', '-----------------artist:'.$value['artist']);
				log::add('alexaamazonmusic_widget', 'debug', '-----------------imageURL:'.$value['imageURL']);			
				log::add('alexaamazonmusic_widget', 'debug', '-----------------title:'.$value['title']);			
				log::add('alexaamazonmusic_widget', 'debug', '-----------------durationSeconds:'.$value['durationSeconds']);			
	
	if (($value['imageURL']==$imageURLenCoursdeLecture) && $compteurQueue>3){
			$html="<table style='border-collapse: separate; border-spacing : 10px; ' border='0' width='100%'>";
		}

	$html.="<tr><td style='padding: 8px;'  rowspan='2' width='50'>";
	//log::add('alexaamazonmusic_widget', 'debug',  '++++++++++++++++++++++++++++++++++ '.$value['imageURL']."//".$imageURLenCoursdeLecture);
	if (($value['imageURL']==$imageURLenCoursdeLecture) && $etatPlayer=="PLAYING") $html.="<img style='position:absolute' src='plugins/alexaamazonmusic/core/img/playing_petit.gif' />";
	$html.="<img style='height: 60px;width: 60px;border-radius: 30%;' src='".$value['imageURL']."'/></td>
        <td width='100%'>".$value['title']."</td>
    </tr>
    <tr>
        <td width='100%'><small>".$value['artist']." - <font size=1><em>".date('i:s', $value['durationSeconds'])."</em></font></small></td>
    </tr>
	
	";

	$compteurQueue++;
	}	
$html.="</table>";

metAJour("playlisthtml", $html, 'playlisthtml', true , $alexaamazonmusic3);

$alexaamazonmusic3->refreshWidget(); //refresh Tuile Playlist


	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
	} catch (Error $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

	}	
	
}	


function metAJourBluetooth($serialdevice, $audioPlayerState, $alexaamazonmusic2, $alexaamazonmusic) {
		//log::add('alexaamazonmusic_widget', 'debug',  'zzzzzzzzzzzzzzzzz metAJourPlayer:');

	try {
		
		//Pour avoir la piste en cours, on va aller chercher la valeur de playerinfo/mainArt/url pour pouvoir la comparer aux images de la playlist
		$json=file_get_contents("http://" . config::byKey('internalAddr') . ":3456/bluetooth");
		$result = json_decode($json,true);		

//log::add('alexaamazonmusic_widget', 'debug', '-->--->--->--->--deviceSerialNumber:'.$result['bluetoothStates']['0']['deviceSerialNumber']);		
		
		
		//$result=array_filter($result, "odd");
		
		//$imageURLenCoursdeLecture=$result['playerInfo']['miniArt']['url'];
		//$etatPlayer=$result['playerInfo']['state'];

		//log::add('alexaamazonmusic_widget', 'debug',  '------------->'.json_encode($result));
		
		//if (($audioPlayerState=="PLAYING") || ($audioPlayerState=="REFRESH") || ($audioPlayerState=="PAUSED"))	{
	
		foreach ($result['bluetoothStates'] as $key => $value) {
				//log::add('alexaamazonmusic_widget', 'debug', '-------------------------------------------------------------------------------');
				//log::add('alexaamazonmusic_widget', 'debug', '-----------------deviceType:'.$value['deviceType']);
				//log::add('alexaamazonmusic_widget', 'debug', '-----------------friendlyName:'.$value['friendlyName']);			
				//log::add('alexaamazonmusic_widget', 'debug', '-----------------online:'.$value['online']);			
				//log::add('alexaamazonmusic_widget', 'debug', '-----------------pairedDeviceList:'.$value['pairedDeviceList']);			
				if (is_array($value['pairedDeviceList'])) {
					foreach ($value['pairedDeviceList'] as $key2 => $value2) {
						if ($value['deviceSerialNumber'] == $serialdevice) {
						//log::add('alexaamazonmusic_widget', 'debug', '-----------------$serialdevice:'.$serialdevice);
						//log::add('alexaamazonmusic_widget', 'debug', '-----------------deviceSerialNumber:'.$value['deviceSerialNumber']);
						//log::add('alexaamazonmusic_widget', 'debug', '********** friendlyName:'.$value2['friendlyName']);
						//log::add('alexaamazonmusic_widget', 'debug', '********** connected:'.$value2['connected']);
							if (isset($value2['connected']) && (($value2['connected']) == '1')) {
								metAJour("bluetoothDevice", $value2['friendlyName'], 'bluetoothDevice', false , $alexaamazonmusic2);
								}
								else {
								metAJour("bluetoothDevice", "", 'bluetoothDevice', false , $alexaamazonmusic2);
								}
						}
					}

	
				}

		}	





	} catch (Exception $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);
				
	} catch (Error $e) {
			log::add('alexaamazonmusic_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur2: '.$e);

	}	
	
}	

	
	
?>
