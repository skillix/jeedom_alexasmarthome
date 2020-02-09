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
				log::add('alexasmarthome', 'debug', 'CRON Refresh: '.$alexasmarthome->getName());
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
		if ($this->getConfiguration('applianceId') == "") return false;
		$family=$this->getConfiguration('family');
		log::add('alexasmarthome', 'info', 'Refresh du device : '.$this->getName().' ('.$family.')');
		log::add('alexasmarthome', 'info', 'Envoi de : '."http://" . config::byKey('internalAddr') . ":3456/querySmarthomeDevices?entityType=".$family."&device=".$this->getConfiguration('applianceId'));
		
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/querySmarthomeDevices?entityType=".$family."&device=".$this->getConfiguration('applianceId'));
		//log::add('alexasmarthome', 'info', '--------->retour : '.$json);
			//$json = json_encode($json, true);
		//log::add('alexasmarthome', 'info', '--------->applicanceId : '.$json('applicanceId'));
		$json = json_decode($json, true);
		/*log::add('alexasmarthome', 'debug', 'json:'.json_encode($json));
		foreach ($json[0] as $key => $value) {
		//log::add('alexasmarthome', 'debug', 'coucke-json:'.json_encode($value));
		log::add('alexasmarthome', 'info', $value.' <=> '.$key);
		}*/
		//log::add('alexasmarthome', 'info', 'name:'.$json[0]['name']);
		//log::add('alexasmarthome', 'info', 'value:'.$json[0]['value']);
		
		//On cherche la commande info qui correspond à $json[0]['name']
		if (isset($json[0]['name'])) {
		$cmd=$this->getCmd(null, $json[0]['name']);
				if (is_object($cmd)) { 
					$this->checkAndUpdateCmd($json[0]['name'], $json[0]['value']);					
					log::add('alexasmarthome', 'debug', $json[0]['name'].' a été mis à jour ('.$json[0]['value'].') sur '.$this->getName());
				} else {
					log::add('alexasmarthome', 'info', $json[0]['name'].' a été mis à jour, mais absent de '.$this->getName().', donc ignoré');
				} 
		}
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
						if (($LogicalId=='volumeinfo') || ($LogicalId=='volume')) {
						$cmd->setConfiguration('minValue', '0');
						$cmd->setConfiguration('maxValue', '100');
						$cmd->setDisplay('forceReturnLineBefore', true);
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
			$false=false;


			self::updateCmd ($F, 'turnOn', 'action', 'other', false, 'turnOn', true, true, "fas fa-circle", null, null, 'SmarthomeCommand?command=turnOn', "powerState", null, 2, $cas8);			
			self::updateCmd ($F, 'turnOff', 'action', 'other', false, 'turnOff', true, true, "far fa-circle", null, null, 'SmarthomeCommand?command=turnOff', "powerState", null, 3, $cas8);
			self::updateCmd ($F, 'powerState', 'info', 'binary', false, null, true, true, null, null, null, null, null, null, 1, $cas8);
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
		$actionInfo = alexasmarthomeCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
		if (is_object($actionInfo)) $this->setId($actionInfo->getId());
		if (($this->getType() == 'action') && ($this->getConfiguration('infoName') != '')) {//Si c'est une action et que Commande info est renseigné
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
		}
	}

	public function execute($_options = null) {
		if ($this->getLogicalId() == 'refresh') {
			$this->getEqLogic()->refresh();
			return;
		}

		$request = $this->buildRequest($_options);
		//$request="http://192.168.0.21:3456/volume?value=50&device=G090LF118173117U";
		log::add('alexasmarthome', 'info', 'Request : ' . $request);//Request : http://192.168.0.21:3456/volume?value=50&device=G090LF118173117U
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
				$cmd=$this->getEqLogic()->getCmd(null, $LogicalIdCmd);
				if (is_object($cmd)) { 
					$this->getEqLogic()->checkAndUpdateCmd($LogicalIdCmd, $resultjson[0][$LogicalIdCmd]);
					log::add('alexasmarthome', 'debug', $LogicalIdCmd.' prévu dans infoName de '.$this->getName().' et trouvé ! Valeur: '.$resultjson[0][$LogicalIdCmd]);				
					} else {
					log::add('alexasmarthome', 'warning', $LogicalIdCmd.' prévu dans infoName de '.$this->getName().' mais non trouvé ! donc ignoré');
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
				$request = $this->build_ControledeSliderSelectMessage();
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
		$cmd=$this->getEqLogic()->getCmd(null, 'volumeinfo');
		if (is_object($cmd))
			$lastvolume=$cmd->execCmd();
		
		$request = $this->getConfiguration('request');
		//log::add('alexasmarthome_node', 'info', '---->Request2:'.$request);
		//log::add('alexasmarthome_node', 'debug', '---->getName:'.$this->getEqLogic()->getCmd(null, 'volumeinfo')->execCmd());
		if ((isset($_options['slider'])) && ($_options['slider'] == "")) $_options['slider'] = $default;
		if ((isset($_options['select'])) && ($_options['select'] == "")) $_options['select'] = $default;
		if ((isset($_options['message'])) && ($_options['message'] == "")) $_options['message'] = $default;
		// Si on est sur une commande qui utilise volume, on va remettre après execution le volume courant
		if (strstr($request, '&volume=')) $request = $request.'&lastvolume='.$lastvolume;
		// Pour eviter l'absence de déclaration :
		if (isset($_options['slider'])) $_options_slider = $_options['slider']; else $_options_slider="";
		if (isset($_options['select'])) $_options_select = $_options['select']; else $_options_select="";
		if (isset($_options['message'])) $_options_message = $_options['message']; else $_options_message="";
		if (isset($_options['volume'])) $_options_volume = $_options['volume']; else $_options_volume="";
		$request = str_replace(array('#slider#', '#select#', '#message#', '#volume#'), 
		array($_options_slider, $_options_select, urlencode(self::decodeTexteAleatoire($_options_message)), $_options_volume), $request);
		//log::add('alexasmarthome_node', 'info', '---->RequestFinale:'.$request);
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