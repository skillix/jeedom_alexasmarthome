<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class alexasmarthome extends eqLogic {
		
	public static function cron($_eqlogic_id = null) {

//		$r = new Cron\CronExpression('*/15 * * * *', new Cron\FieldFactory);// boucle refresh
		$r = new Cron\CronExpression('* * * * *', new Cron\FieldFactory);// boucle refresh
		$deamon_info = alexaapi::deamon_info();
		if ($r->isDue() && $deamon_info['state'] == 'ok') {
			$eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType('alexasmarthome', true);
			foreach ($eqLogics as $alexasmarthome) {
				log::add('alexasmarthome_cron', 'debug', '-----------------------------------------------------------------------------');
				log::add('alexasmarthome_cron', 'debug', 'CRON Refresh: '.$alexasmarthome->getName());
				$alexasmarthome->refresh(); 				
				sleep(2);
			}	
		}
	}
	
	public static function createNewDevice($deviceName, $deviceSerial) {
		$defaultRoom = intval(config::byKey('defaultParentObject','alexaapi','',true));
		//event::add('jeedom::alert', array('level' => 'success', 'page' => 'alexasmarthome', 'message' => __('Ajout de "'.$deviceName.'"', __FILE__),));
		$newDevice = new alexasmarthome();
		$newDevice->setName($deviceName);
		$newDevice->setLogicalId($deviceSerial);
		$newDevice->setEqType_name('alexasmarthome');
		$newDevice->setIsVisible(1);
		if($defaultRoom) $newDevice->setObject_id($defaultRoom);
		$newDevice->setDisplay('height', '500');
		$newDevice->setConfiguration('device', $deviceName);
		$newDevice->setConfiguration('serial', $deviceSerial);
		$newDevice->setIsEnable(1);
		return $newDevice;
	}

	public function hasCapaorFamilyorType($thisCapa) {
		
		// Si c'est la bonne famille, on dit OK tout de suite
		$family=$this->getConfiguration('family',"");	
		if($thisCapa == $family) return true; // ajouté pour filtrer sur la famille (pour les groupes par exemple)
		// Si c'est le bon type, on dit OK tout de suite
		$type=$this->getConfiguration('type',"");	
		if($thisCapa == $type) return true; // 
		$capa=$this->getConfiguration('capabilities',"");
		if(((gettype($capa) == "array" && in_array($thisCapa,$capa))) || ((gettype($capa) == "string" && strpos($capa, $thisCapa) !== false))) {
			if($thisCapa == "REMINDERS" && $type == "A15ERDAKK5HQQG") return false;
			return true;
		} else {
			return false;
		}
	}
	
	public function sortBy($field, &$array, $direction = 'asc') {
		usort($array, create_function('$a, $b', '
		$a = $a["' . $field . '"];
		$b = $b["' . $field . '"];
		if ($a == $b) return 0;
		$direction = strtolower(trim($direction));
		return ($a ' . ($direction == 'desc' ? '>' : '<') . ' $b) ? -1 : 1;
    	'));
		return true;
	}

	public function refresh() { //$_routines c'est pour éviter de charger les routines lors du scan
		$deamon_info = alexaapi::deamon_info();
		if ($deamon_info['state'] != 'ok') return false;
		$family=$this->getConfiguration('family');
		$type=$this->getConfiguration('type');
		
		
		if (($this->getConfiguration('applianceId') == "") && ($family!="GROUP")) return false;
		
		
		$device=$this->getConfiguration('applianceId');
		if ($family=="GROUP") $device=$this->getLogicalId();
		if ($family=="GROUP") log::add('alexasmarthome_cron', 'info', 'logicalidGROUP : '.$this->getLogicalId());
		log::add('alexasmarthome_cron', 'info', 'Refresh du device : '.$this->getName().' ('.$family.')');
		log::add('alexasmarthome_cron', 'info', 'Envoi de : '."http://" . config::byKey('internalAddr') . ":3456/querySmarthomeDevices?entityType=".$family."&type=".$type."&device=".$device);
		
		$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/querySmarthomeDevices?entityType=".$family."&type=".$type."&device=".$device);
		//log::add('alexasmarthome', 'info', '--------->retour : '.$json);
			//$json = json_encode($json, true);
		//log::add('alexasmarthome', 'info', '--------->applicanceId : '.$json('applicanceId'));
		$json = json_decode($json, true);
		log::add('alexasmarthome_cron', 'debug', 'json:'.json_encode($json));
		/*foreach ($json[0] as $key => $value) {
		//log::add('alexasmarthome', 'debug', 'coucke-json:'.json_encode($value));
		log::add('alexasmarthome', 'info', $value.' <=> '.$key);
		}*/
		
	
		foreach ($json[0]['capabilityStates'] as $capabilityState) {
			$capabilityState_array=json_decode($capabilityState, true);

		//log::add('alexasmarthome', 'info', 'name:::'.$capabilityState_array['name']);
		//log::add('alexasmarthome', 'info', 'value:::'.$capabilityState_array['value']);
		//On cherche la commande info qui correspond à $json[0]['name']
		
		
		
		$valeuraEnregistrer=$capabilityState_array['value'];
		
		if (isset($capabilityState_array['name'])) {
			
			if 	($capabilityState_array['name']=="color") 
				$valeuraEnregistrer="#".self::fGetRGB($capabilityState_array['value']['hue'], $capabilityState_array['value']['saturation'], $capabilityState_array['value']['brightness']);
			
			if 	($capabilityState_array['name']=="colorProperties") {
				$valeuraEnregistrer=$capabilityState_array['value']['localizationMap']['fr'];
				if ($valeuraEnregistrer == '')
					$valeuraEnregistrer =$capabilityState_array['value']['name'];
			}
			
			if 	(($capabilityState_array['name']=="temperature") || ($capabilityState_array['name']=="targetSetpoint")) {
				$valeuraEnregistrer=$capabilityState_array['value']['value'];
			}	
			
			if 	($capabilityState_array['name']=="connectivity") {
				//https://developer.amazon.com/fr-FR/docs/alexa/device-apis/alexa-endpointhealth.html
				//The connectivity status of the device; one of OK or UNREACHABLE.
				//log::add('alexasmarthome_cron', 'info', '**************connectivity ********');
				//log::add('alexasmarthome_cron', 'info', 'value:::'.json_encode($capabilityState_array['value']));
				//log::add('alexasmarthome_cron', 'info', 'fr:::'.json_encode($capabilityState_array['value']['value']));
				if ($capabilityState_array['value']['value']=="OK") $valeuraEnregistrer=1; else $valeuraEnregistrer=0;
			}			
			
			$cmd=$this->getCmd(null, $capabilityState_array['name']);
				if (is_object($cmd)) { 
					$this->checkAndUpdateCmd($capabilityState_array['name'], $valeuraEnregistrer);					
					log::add('alexasmarthome_cron', 'debug', $capabilityState_array['name'].' a été mis à jour ('.$valeuraEnregistrer.') sur '.$this->getName());
				} else {
					log::add('alexasmarthome_cron', 'debug', $capabilityState_array['name'].' a été mis à jour ('.$valeuraEnregistrer.'), mais absent de '.$this->getName().', donc ignoré');
				} 
		}		
		}

		

		
	}
	
   /*
    **  Converts HSV to RGB values
	https://gist.github.com/vkbo/2323023
    ** –––––––––––––––––––––––––––––––––––––––––––––––––––––
    **  Reference: http://en.wikipedia.org/wiki/HSL_and_HSV
    **  Purpose:   Useful for generating colours with
    **             same hue-value for web designs.
    **  Input:     Hue        (H) Integer 0-360
    **             Saturation (S) Integer 0-100
    **             Lightness  (V) Integer 0-100
    **  Output:    String "R,G,B"
    **             Suitable for CSS function RGB().
    */

    public function fGetRGB($iH, $iS, $iV) {

        if($iH < 0)   $iH = 0;   // Hue:
        if($iH > 360) $iH = 360; //   0-360
        if($iS < 0)   $iS = 0;   // Saturation:
        if($iS > 1) $iS = 1; //   0-100
        if($iV < 0)   $iV = 0;   // Lightness:
        if($iV > 1) $iV = 1; //   0-100

        $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dV = $iV/100.0; // Lightness:  0.0-1.0
        $dS = $iS; // Saturation: 0.0-1.0
        $dV = $iV; // Lightness:  0.0-1.0
        $dC = $dV*$dS;   // Chroma:     0.0-1.0
        $dH = $iH/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable

        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link

        switch(floor($dH)) {
            case 0:
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case 1:
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case 2:
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case 3:
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case 4:
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case 5:
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }

        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;


        //return round($dR).",".round($dG).",".round($dB);

$dR = str_pad(dechex(round($dR)), 2, "0", STR_PAD_LEFT);
$dG = str_pad(dechex(round($dG)), 2, "0", STR_PAD_LEFT);
$dB = str_pad(dechex(round($dB)), 2, "0", STR_PAD_LEFT);
return $dR.$dG.$dB;

    }


	
	public static function forcerDefaultCmd($_id = null) {
		if (!is_null($_id)) { 
		$device = alexasmarthome::byId($_id);
				if (is_object($device)) {
				$device->setStatus('forceUpdate',true);
				$device->save();
				}
		}		
	}

	public function updateCmd ($forceUpdate, $LogicalId, $Type, $SubType, $RunWhenRefresh, $Name, $IsVisible, $title_disable, $setDisplayicon, $infoNameArray, $setTemplate_lien, $request, $infoName, $listValue, $Order, $Test) {
		if ($Test) {
			try {
				if (empty($Name)) $Name=$LogicalId;
				$cmd = $this->getCmd(null, $LogicalId);
				if ((!is_object($cmd)) || $forceUpdate) {
					if (!is_object($cmd)) $cmd = new alexasmarthomeCmd();
					$cmd->setType($Type);
					$cmd->setLogicalId($LogicalId);
					$cmd->setSubType($SubType);
					$cmd->setEqLogic_id($this->getId());
					$cmd->setName($Name);
					$cmd->setIsVisible((($IsVisible)?1:0));
					if (!empty($setTemplate_lien)) {
						$cmd->setTemplate("dashboard", $setTemplate_lien);
						$cmd->setTemplate("mobile", $setTemplate_lien);
					}						
					if (!empty($setDisplayicon)) $cmd->setDisplay('icon', '<i class="'.$setDisplayicon.'"></i>');
					if (!empty($request)) $cmd->setConfiguration('request', $request);
					if (!empty($infoName)) $cmd->setConfiguration('infoName', $infoName);
					if (!empty($infoNameArray)) $cmd->setConfiguration('infoNameArray', $infoNameArray);
					if (!empty($listValue)) $cmd->setConfiguration('listValue', $listValue);
					$cmd->setConfiguration('RunWhenRefresh', $RunWhenRefresh);				
					$cmd->setDisplay('title_disable', $title_disable);
					$cmd->setOrder($Order);
					//cas particulier
						if (($LogicalId == 'speak') || ($LogicalId == 'announcement')){
						//$cmd->setDisplay('title_placeholder', 'Options');
						$cmd->setDisplay('message_placeholder', 'Phrase à faire lire par Alexa');
						}
						if (($LogicalId == 'reminder')){
						//$cmd->setDisplay('title_placeholder', 'Options');
						$cmd->setDisplay('message_placeholder', 'Texte du rappel');
						}						
						if ($LogicalId=='brightness-set') {
						$cmd->setConfiguration('minValue', '0');
						$cmd->setConfiguration('maxValue', '100');
						//$cmd->setDisplay('forceReturnLineBefore', true);
						}					
				}
				$cmd->save();
			}
			catch(Exception $exc) {
				log::add('alexasmarthome', 'error', __('Erreur pour ', __FILE__) . ' : ' . $exc->getMessage());
			}
		} else {
							//log::add('alexasmarthome', 'debug', 'PAS de **'.$LogicalId.'*********************************');

		$cmd = $this->getCmd(null, $LogicalId);
			if (is_object($cmd)) {
				$cmd->remove();
			}
		}
	}


	public function postSave() {
		//log::add('alexasmarthome', 'debug', '**********************postSave '.$this->getName().'***********************************');
		$F=$this->getStatus('forceUpdate');// forceUpdate permet de recharger les commandes à valeur d'origine, mais sans supprimer/recréer les commandes
				$capa=$this->getConfiguration('capabilities','');
				$type=$this->getConfiguration('type','');
		if(!empty($capa)) {
					if (strstr($this->getName(), "Alexa Apps")) {
						self::updateCmd ($F, 'push', 'action', 'message', false, 'Push', true, true, 'fa jeedomapp-audiospeak', null, null, 'push?text=#message#', null, null, 1, true);
						return;
					}

			$widgetSmarthome=($this->getConfiguration('devicetype') == "Smarthome");

			$cas8=(($this->hasCapaorFamilyorType("turnOff")) && $widgetSmarthome);
			$cas7=(($this->hasCapaorFamilyorType("setBrightness")) && $widgetSmarthome);
			$cas6=(($this->hasCapaorFamilyorType("setColor")) && $widgetSmarthome);
			$cas5=(($this->hasCapaorFamilyorType("setColorTemperature")) && $widgetSmarthome);
			$cas4=(($this->hasCapaorFamilyorType("setTargetTemperature")) && $widgetSmarthome);
			// commande connectivity n'a pas de capa, on utilise cas6 pour l'instant
			$false=false;
			self::updateCmd ($F, 'powerState', 'info', 'binary', false, "Etat", true, true, null, null, null, null, null, null, 1, $cas8);
			self::updateCmd ($F, 'connectivity', 'info', 'binary', false, "Connectivité", true, true, null, null, null, null, null, null, 2, $cas6); 
			self::updateCmd ($F, 'brightness', 'info', 'numeric', false, "Luminosité", true, true, null, null, null, null, null, null, 3, $cas7);
			self::updateCmd ($F, 'brightness-set', 'action', 'slider', false, 'Définir Luminosité', true, true, null, null, null, 'SmarthomeCommand?command=setBrightness&brightness=#slider#', "brightness", null, 4, $cas7);
			self::updateCmd ($F, 'turnOn_jaune', 'action', 'other', false, 'Allume en Jaune', true, true, 'fas fa-circle" style="color:yellow', null, null, 'SmarthomeCommand?command=setColor&color=yellow', "refresh", null, 10, $cas6);			
			self::updateCmd ($F, 'turnOn_bleu', 'action', 'other', false, 'Allume en Bleu', true, true, 'fas fa-circle" style="color:blue', null, null, 'SmarthomeCommand?command=setColor&color=blue', "refresh", null, 11, $cas6);			
			self::updateCmd ($F, 'turnOn_rose', 'action', 'other', false, 'Allume en Rose', true, true, 'fas fa-circle" style="color:pink', null, null, 'SmarthomeCommand?command=setColor&color=pink', "refresh", null, 12, $cas6);			
			self::updateCmd ($F, 'turnOn_violet', 'action', 'other', false, 'Allume en Violet', true, true, 'fas fa-circle" style="color:purple', null, null, 'SmarthomeCommand?command=setColor&color=purple', "refresh", null, 13, $cas6);			
			self::updateCmd ($F, 'turnOn_rouge', 'action', 'other', false, 'Allume en Rouge', true, true, 'fas fa-circle" style="color:red', null, null, 'SmarthomeCommand?command=setColor&color=red', "refresh", null, 14, $cas6);			
			self::updateCmd ($F, 'turnOn_vert', 'action', 'other', false, 'Allume en Vert', true, true, 'fas fa-circle" style="color:green', null, null, 'SmarthomeCommand?command=setColor&color=green', "refresh", null, 15, $cas6);			
			self::updateCmd ($F, 'colorProperties', 'info', 'string', false, "Couleur", true, true, null, null, null, null, null, null, 16, $cas6);
			self::updateCmd ($F, 'thermostatMode', 'info', 'string', false, "Mode du thermostat", true, true, null, null, null, null, null, null, 16, $cas4);
			self::updateCmd ($F, 'temperature', 'info', 'numeric', false, "Température", true, true, null, null, null, null, null, null, 16, $cas4);
			self::updateCmd ($F, 'targetSetpoint', 'info', 'numeric', false, "Consigne du thermostat", true, true, null, null, null, null, null, null, 16, $cas4);
//https://www.openhab.org/docs/ecosystem/alexa/
//https://github.com/alexa/alexa-smarthome
			self::updateCmd ($F, 'turnOn', 'action', 'other', false, 'Allume', false, true, 'fas fa-circle" style="color:white', null, null, 'SmarthomeCommand?command=turnOn', "powerState", null, 17, $cas8);			
			self::updateCmd ($F, 'turnOff', 'action', 'other', false, 'Eteint', true, true, 'far fa-circle" style="color:black', null, null, 'SmarthomeCommand?command=turnOff', "powerState", null, 18, $cas8);
			self::updateCmd ($F, 'rgb-set', 'action', 'select', false, 'Définir Couleur', false, true, null, null, null, 'SmarthomeCommand?command=setColor&color=#select#', "refresh", 'red|Rouge;crimson|Cramoisie;salmon|Saumon;orange|Orange;gold|Or;yellow|Jaune;green|Vert;turquoise|Turquoise;cyan|Cyan;sky_blue|Bleu ciel;blue|Bleu;purple|Violet;magenta|Magenta;pink|Rose;lavender|Lavande', 16, $cas6);
			self::updateCmd ($F, 'temperature-set', 'action', 'select', false, 'Définir Température du blanc', false, true, null, null, null, 'SmarthomeCommand?command=setColorTemperature&color=#select#', "refresh", 'warm_white|Blanc chaud;soft_white|Blanc doux;white|Blanc;daylight_white|Blanc lumière du jour;cool_white|Blanc froid', 16, $cas5);
			//self::updateCmd ($F, 'color', 'info', 'string', false, null, false, true, null, null, null, null, null, null, 1, $cas6);
			//self::updateCmd ($F, 'state', 'info', 'binary', false, null, true, true, null, null, null, null, null, null, 1, $cas8);
	//public function updateCmd ($forceUpdate, $LogicalId, $Type, $SubType, $RunWhenRefresh, $Name, $IsVisible, $title_disable, $setDisplayicon, $infoNameArray, $setTemplate_lien, $request, $infoName, $listValue, $Order, $Test) {


			$volinfo = $this->getCmd(null, 'volumeinfo');
			$vol = $this->getCmd(null, 'volume');
					if((is_object($volinfo)) && (is_object($vol))) {
					$vol->setValue($volinfo->getId());// Lien entre volume et volumeinfo
					$vol->save();
					}
		// Pour la commande Refresh, on garde l'ancienne méthode
				//Commande Refresh
				$createRefreshCmd = true;
				$refresh = $this->getCmd(null, 'refresh');
				if (!is_object($refresh)) {
					$refresh = cmd::byEqLogicIdCmdName($this->getId(), __('Rafraichir', __FILE__));
					if (is_object($refresh)) {
						$createRefreshCmd = false;
					}
				}
				if ($createRefreshCmd) {
					if (!is_object($refresh)) {
						$refresh = new alexasmarthomeCmd();
						$refresh->setLogicalId('refresh');
						$refresh->setIsVisible(1);
						$refresh->setDisplay('icon', '<i class="fa fa-sync"></i>');
						$refresh->setName(__('Refresh', __FILE__));
					}
					$refresh->setType('action');
					$refresh->setSubType('other');
					$refresh->setEqLogic_id($this->getId());
					$refresh->save();
				}
		} 

		//event::add('jeedom::alert', array('level' => 'success', 'page' => 'alexasmarthome', 'message' => __('Mise à jour de "'.$this->getName().'"', __FILE__),));
		$this->refresh(); 

		/*if ($widgetPlayer) {
				$device_playlist=str_replace("_player", "", $this->getConfiguration('serial'))."_playlist"; //Nom du device de la playlist
				// Si la case "Activer le widget Playlist" est cochée, on rend le device _playlist visible sinon on le passe invisible		
				$eq=eqLogic::byLogicalId($device_playlist,'alexasmarthome');
						if(is_object($eq)) {
							$eq->setIsVisible((($this->getConfiguration('widgetPlayListEnable'))?1:0));
							$eq->setIsEnable((($this->getConfiguration('widgetPlayListEnable'))?1:0));
							//$eq->setObject_id($this->getObject_id()); // Attribue au widget Playlist la même pièce que son Player
							$eq->save();
						}
			}
*/


		$this->setStatus('forceUpdate', false); //dans tous les cas, on repasse forceUpdate à false
		
		//self::scanAmazonSmartHome();
		
	}


	public function preRemove () {
		if ($this->getConfiguration('devicetype') == "Player") { // Si c'est un type Player, il faut supprimer le Device Playlist
			$device_playlist=str_replace("_player", "", $this->getConfiguration('serial'))."_playlist"; //Nom du device de la playlist
		$eq=eqLogic::byLogicalId($device_playlist,'alexasmarthome');
				if(is_object($eq)) $eq->remove();
		}
	}
	
	public function preSave() {
	}

// https://github.com/NextDom/NextDom/wiki/Ajout-d%27un-template-a-votre-plugin	
// https://jeedom.github.io/documentation/dev/fr_FR/widget_plugin	

  public function toHtml($_version = 'dashboard') {
	$replace = $this->preToHtml($_version);
	//log::add('alexasmarthome_widget','debug','************Début génération Widget de '.$replace['#logicalId#']);  
	$typeWidget="alexasmarthome";	
	if ((substr($replace['#logicalId#'], -7))=="_player") $typeWidget="alexasmarthome_player";
	if ((substr($replace['#logicalId#'], -9))=="_playlist") $typeWidget="alexasmarthome_playlist";
    if ($typeWidget!="alexasmarthome_playlist") return parent::toHtml($_version);
	//log::add('alexasmarthome_widget','debug',$typeWidget.'************Début génération Widget de '.$replace['#name#']);        
	if (!is_array($replace)) {
		return $replace;
	}
	$version = jeedom::versionAlias($_version);
	if ($this->getDisplay('hideOn' . $version) == 1) {
		return '';
	}
	foreach ($this->getCmd('info') as $cmd) {
		 	//log::add('alexasmarthome_widget','debug',$typeWidget.'dans boucle génération Widget');        
            $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            if ($cmd->getLogicalId() == 'encours'){
                $replace['#thumbnail#'] = $cmd->getDisplay('icon');
            }
            if ($cmd->getIsHistorized() == 1) {
                $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
        }
	$replace['#height#'] = '800';
		if ($typeWidget=="alexasmarthome_playlist") {
			if ("#playlistName#" != "") {
				$replace['#name_display#']='#playlistName#';
			}
		}
	//log::add('alexasmarthome_widget','debug',$typeWidget.'***************************************************************************Fin génération Widget');        
	return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $typeWidget, 'alexasmarthome')));
	}
}

class alexasmarthomeCmd extends cmd {

	public function dontRemoveCmd() {
		if ($this->getLogicalId() == 'refresh') {
			return true;
		}
		return false;
	}
	
	public function postSave() {

	}
	
	
	
	
	public function preSave() {
		if ($this->getLogicalId() == 'refresh') {
			return;
		}
		if ($this->getType() == 'action') {
			$eqLogic = $this->getEqLogic();
			$this->setConfiguration('value', 'http://' . config::byKey('internalAddr') . ':3456/' . $this->getConfiguration('request') . "&device=" . $eqLogic->getConfiguration('serial'));
		}
		
		
		/* C'est la section qui ajoute automatiquement une commande info qui porte le nom du infoName d'une commande action
		$actionInfo = alexasmarthomeCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
		if (is_object($actionInfo)) $this->setId($actionInfo->getId());
		if (($this->getType() == 'action') && ($this->getConfiguration('infoName') != '')
			&& ($this->getConfiguration('infoName') != 'refresh')) {//Si c'est une action et que Commande info est renseigné
			$actionInfo = alexasmarthomeCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getConfiguration('infoName'));
			if (!is_object($actionInfo)) {//C'est une commande qui n'existe pas
				$actionInfo = new alexasmarthomeCmd();
				$actionInfo->setType('info');
				$actionInfo->setSubType('string');
				$actionInfo->setConfiguration('taskid', $this->getID());
				$actionInfo->setConfiguration('taskname', $this->getName());
			}
			$actionInfo->setName($this->getConfiguration('infoName'));
			$actionInfo->setEqLogic_id($this->getEqLogic_id());
			$actionInfo->save();
			$this->setConfiguration('infoId', $actionInfo->getId());
		}*/
	}

	public function execute($_options = null) {
		if ($this->getLogicalId() == 'refresh') {
			$this->getEqLogic()->refresh();
			return;
		}

		$request = $this->buildRequest($_options);
		//$request="http://192.168.0.21:3456/volume?value=50&device=G090LF118173117U";
		log::add('alexasmarthome', 'debug', 'Request : ' . $request);//Request : http://192.168.0.21:3456/volume?value=50&device=G090LF118173117U
		$request_http = new com_http($request);
		$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
		if ($this->getConfiguration('noSslCheck') == 1) $request_http->setNoSslCheck(true);
		if ($this->getConfiguration('doNotReportHttpError') == 1) $request_http->setNoReportError(true);
		if (isset($_options['speedAndNoErrorReport']) && $_options['speedAndNoErrorReport'] == true) {// option non activée 
			$request_http->setNoReportError(true);
			$request_http->exec(0.1, 1);
			return;
		}
		$result = $request_http->exec($this->getConfiguration('timeout', 3), $this->getConfiguration('maxHttpRetry', 3));//Time out à 3s 3 essais
		if (!$result) throw new Exception(__('Serveur injoignable', __FILE__));
		// On traite la valeur de resultat (dans le cas de whennextalarm par exemple)
		$resultjson = json_decode($result, true);


					// Ici, on va traiter une commande qui n'a pas été executée correctement (erreur type "Connexion Close")
						if (isset($resultjson['value'])) $value = $resultjson['value']; else $value="";
						if (isset($resultjson['detail'])) $detail = $resultjson['detail']; else $detail="";					
						if (($value =="Connexion Close") || ($detail =="Unauthorized")){
						//$value = $resultjson['value'];
						//$detail = $resultjson['detail'];
						log::add('alexasmarthome', 'debug', '**On traite '.$value.$detail.' Connexion Close** dans la Class');
						sleep(6);
							if (ob_get_length()) {
							ob_end_flush();
							flush();
							}	
						log::add('alexasmarthome', 'debug', '**On relance '.$request);
						$result = $request_http->exec($this->getConfiguration('timeout', 2), $this->getConfiguration('maxHttpRetry', 3));
						if (!result) throw new Exception(__('Serveur injoignable', __FILE__));
						$jsonResult = json_decode($json, true);
						if (!empty($jsonResult)) throw new Exception(__('Echec de l\'execution: ', __FILE__) . '(' . $jsonResult['title'] . ') ' . $jsonResult['detail']);
						$resultjson = json_decode($result, true);
						$value = $resultjson['value'];
					}
		
		
		if (($this->getType() == 'action') && (is_array($this->getConfiguration('infoNameArray')))) {
			foreach ($this->getConfiguration('infoNameArray') as $LogicalIdCmd) {
				$cmd=$this->getEqLogic()->getCmd(null, $LogicalIdCmd);
				if (is_object($cmd)) { 
					$this->getEqLogic()->checkAndUpdateCmd($LogicalIdCmd, $resultjson[0][$LogicalIdCmd]);					
					//log::add('alexasmarthome', 'info', $LogicalIdCmd.' prévu dans infoNameArray de '.$this->getName().' trouvé ! '.$resultjson[0]['whennextmusicalalarminfo'].' OK !');
				} else {
					log::add('alexasmarthome', 'warning', $LogicalIdCmd.' prévu dans infoNameArray de '.$this->getName().' mais non trouvé ! donc ignoré');
				} 
			}
		} 
		elseif (($this->getType() == 'action') && ($this->getConfiguration('infoName') != '')) {
				$LogicalIdCmd=$this->getConfiguration('infoName');
				if ($LogicalIdCmd=="refresh") {//c'est qu'on fait un refresh
						log::add('alexasmarthome', 'debug', 'Refresh demandé dans 3s');	
						sleep(3);
						$this->getEqLogic()->refresh();
				}						
				else {
					$cmd=$this->getEqLogic()->getCmd(null, $LogicalIdCmd);
					if (is_object($cmd)) { 
						$this->getEqLogic()->checkAndUpdateCmd($LogicalIdCmd, $resultjson[0][$LogicalIdCmd]);
						log::add('alexasmarthome', 'debug', $LogicalIdCmd.' prévu dans infoName de '.$this->getName().' et trouvé ! Valeur: '.$resultjson[0][$LogicalIdCmd]);				
						} else {
						log::add('alexasmarthome', 'warning', $LogicalIdCmd.' prévu dans infoName de '.$this->getName().' mais non trouvé ! donc ignoré');
					} 
				}
		}
		return true;
	}


	private function buildRequest($_options = array()) {
		if ($this->getType() != 'action') return $this->getConfiguration('request');
		list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
	log::add('alexasmarthome', 'info', '----Command:*'.$command.'* Request:'.json_encode($_options));
		switch ($command) {
			case 'SmarthomeCommand':
				$request = $this->build_ControledeSliderSelectMessage($_options, '2960');
			break;			
			default:
				$request = '';
			break;
		}
		//log::add('alexasmarthome_debug', 'debug', '----RequestFinale:'.$request);
		$request = scenarioExpression::setTags($request);
		if (trim($request) == '') throw new Exception(__('Commande inconnue ou requête vide : ', __FILE__) . print_r($this, true));
		$device=str_replace("_player", "", $this->getEqLogic()->getConfiguration('serial'));
		return 'http://' . config::byKey('internalAddr') . ':3456/' . $request . '&device=' . $device;
	}

	private function build_ControledeSliderSelectMessage($_options = array(), $default = "Ceci est un message de test") {
		/*$cmd=$this->getEqLogic()->getCmd(null, 'volumeinfo');
		if (is_object($cmd))
			$lastvolume=$cmd->execCmd();
		*/
		$request = $this->getConfiguration('request');
		log::add('alexasmarthome', 'info', '---->Request2:'.$request.'---->_options:'.json_encode($_options));
		//log::add('alexasmarthome_node', 'debug', '---->getName:'.$this->getEqLogic()->getCmd(null, 'volumeinfo')->execCmd());
		if ((isset($_options['slider'])) && ($_options['slider'] == "")) $_options['slider'] = $default;
		if ((isset($_options['select'])) && ($_options['select'] == "")) $_options['select'] = $default;
		if ((isset($_options['message'])) && ($_options['message'] == "")) $_options['message'] = $default;
		if ((isset($_options['color'])) && ($_options['color'] == "")) $_options['color'] = $default;
		// Si on est sur une commande qui utilise volume, on va remettre après execution le volume courant
		if (strstr($request, '&volume=')) $request = $request.'&lastvolume='.$lastvolume;
		// Pour eviter l'absence de déclaration :
		if (isset($_options['slider'])) $_options_slider = $_options['slider']; else $_options_slider="";
		if (isset($_options['select'])) $_options_select = $_options['select']; else $_options_select="";
		if (isset($_options['color'])) $_options_color = str_replace('#', '', $_options['color']); else $_options_color="";
		if (isset($_options['message'])) $_options_message = $_options['message']; else $_options_message="";
		if (isset($_options['volume'])) $_options_volume = $_options['volume']; else $_options_volume="";
		$request = str_replace(array('#slider#', '#select#', '#message#', '#volume#', '#color#'), 
		array($_options_slider, $_options_select, urlencode(self::decodeTexteAleatoire($_options_message)), $_options_volume, $_options_color), $request);
		log::add('alexasmarthome', 'info', '---->RequestFinale:'.$request);
		return $request;
	}	

	//private function trouveVolumeDevice() {
	//	$logical_id = $this->getEqLogic()->getCmd(null, 'volumeinfo')->getValue();
	//	$alexasmarthome=alexasmarthome::byLogicalId($logical_id, 'alexasmarthome');getValue
	//}


	public static function decodeTexteAleatoire($_text) {
		$return = $_text;
		if (strpos($_text, '|') !== false && strpos($_text, '[') !== false && strpos($_text, ']') !== false) {
			$replies = interactDef::generateTextVariant($_text);
			$random = rand(0, count($replies) - 1);
			$return = $replies[$random];
		}
		preg_match_all('/{\((.*?)\) \?(.*?):(.*?)}/', $return, $matches, PREG_SET_ORDER, 0);
		$replace = array();
		if (is_array($matches) && count($matches) > 0) {
			foreach ($matches as $match) {
				if (count($match) != 4) {
					continue;
				}
				$replace[$match[0]] = (jeedom::evaluateExpression($match[1])) ? trim($match[2]) : trim($match[3]);
			}
		}
		return str_replace(array_keys($replace), $replace, $return);
	}




	private function build_ControleWhenTextRecurring($defaultWhen, $defaultText, $_options = array()) {
		$request = $this->getConfiguration('request');
		log::add('alexasmarthome', 'debug', '----build_ControledeSliderSelectMessage RequestFinale:'.$request);
		log::add('alexasmarthome', 'debug', '----build_ControledeSliderSelectMessage _optionsAVANT:'.json_encode($_options));
		if ((!isset($_options['sound'])) && (!isset($_options['message'])) && (!isset($_options['when']))) {
			if (isset($_options['select'])) { // On est dans le cas d'un son d'alarme envoyé depuis le widget
				$_options['sound']=urlencode($_options['select']);
				$_options['select']="";
			}
		}
		if ($_options['when'] == "") $_options['when'] = $defaultWhen;		
		if ($_options['message'] == "") $_options['message'] = $defaultText;	
		if ($_options['sound'] == "") $_options['sound'] = 'system_alerts_melodic_01';	
		$request = str_replace(array('#when#', '#message#', '#recurring#', '#sound#'), array(urlencode($_options['when']), urlencode($_options['message']), urlencode($_options['select']), $_options['sound']), $request);
		return $request;
	}
	
	private function build_ControlePosition($_options = array()) {
		$request = $this->getConfiguration('request');
		$request = str_replace('#position#', urlencode($_options['position']), $request);
		return $request;
	}
	
	private function build_ControleRien($_options = array()) {
		return $this->getConfiguration('request')."?truc=vide";
	}
	
	private function buildDeleteAllAlarmsRequest($_options = array()) {
		$request = $this->getConfiguration('request');
		if ($_options['type'] == "") $_options['type'] = "alarm";
		if ($_options['status'] == "") $_options['status'] = "ON";
		return str_replace(array('#type#', '#status#'), array($_options['type'], $_options['status']), $request);
	}
	
	private function builddeleteReminderRequest($_options = array()) {
		$request = $this->getConfiguration('request');
		if ($_options['id'] == "") $_options['id'] = "coucou";
		if ($_options['status'] == "") $_options['status'] = "ON";
		return str_replace(array('#id#', '#status#'), array($_options['id'], $_options['status']), $request);
	}	
		
	private function buildRestartRequest($_options = array()) {
		log::add('alexasmarthome_debug', 'debug', '------buildRestartRequest---UTILISE QUAND ???--A simplifier--------------------------------------');
		$request = $this->getConfiguration('request')."?truc=vide";
		return str_replace('#volume#', $_options['slider'], $request);
	}
	
	public function getWidgetTemplateCode($_version = 'dashboard', $_noCustom = false) {
		if ($_version != 'scenario') return parent::getWidgetTemplateCode($_version, $_noCustom);
		list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
		if (($command == 'speak') || ($command == 'announcement'))
			return getTemplate('core', 'scenario', 'cmd.speak.volume', 'alexasmarthome');
		if ($command == 'reminder') 
			return getTemplate('core', 'scenario', 'cmd.reminder', 'alexasmarthome');
		if ($command == 'deleteallalarms') 
			return getTemplate('core', 'scenario', 'cmd.deleteallalarms', 'alexasmarthome');
		if ($command == 'command' && strpos($arguments, '#select#')) 
			return getTemplate('core', 'scenario', 'cmd.command', 'alexasmarthome');
		if ($command == 'alarm') 
			return getTemplate('core', 'scenario', 'cmd.alarm', 'alexasmarthome');
		return parent::getWidgetTemplateCode($_version, $_noCustom);
	}
}