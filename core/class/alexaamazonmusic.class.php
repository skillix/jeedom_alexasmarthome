<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class alexaamazonmusic extends eqLogic {
	
    public static function templateWidget(){
		$return = array('info' => array('string' => array()));
		$return = array('action' => array('select' => array(), 'slider' => array()));
		$return['info']['string']['subText2'] = array('template' => 'album' );
		$return['info']['string']['alarmmusicalmusic'] = array('template' => 'alarmmusicalmusic', 'replace' => array("#hide_name#" => "hidden"));
		$return['info']['string']['title'] =    array('template' => 'title');
		$return['info']['string']['url'] =    array('template' => 'image');
		$return['info']['string']['interaction'] =    array('template' => 'cadre');
		$return['action']['message']['message'] =    array(
				'template' => 'message',
				'replace' => array("#_desktop_width_#" => "100","#_mobile_width_#" => "50", "#title_disable#" => "1", "#message_disable#" => "0")
		);
		$return['action']['select']['list'] =    array(
				'template' => 'table',
				'replace' => array("#_desktop_width_#" => "100","#_mobile_width_#" => "50", "#hide_name#" => "whidden")
		);		
		$return['action']['slider']['volume'] =    array(
				'template' => 'bouton',
				'replace' => array("#hide_name#" => "hidden", "#step#" => "10")
		);
		$return['info']['string']['state'] = array(
				'template' => 'tmplmultistate_alexaapi',
				'replace' => array("#hide_name#" => "hidden", "#hide_state#" => "hidden", "#marge_gauche#" => "5px", "#marge_haut#" => "-15px"),
				'test' => array(
					array('operation' => "#value# == 'PLAYING'", 'state_light' => "<img src='plugins/alexaamazonmusic/core/img/playing.png'  title ='" . __('Playing', __FILE__) . "'>",
							'state_dark' => "<img src='plugins/alexaamazonmusic/core/img/playing.png' title ='" . __('En charge', __FILE__) . "'>"),
					array('operation' => "#value# != 'PLAYING'",'state_light' => "<img src='plugins/alexaamazonmusic/core/img/paused.png' title ='" . __('En Pause', __FILE__) . "'>")
				)
			);
		$return['info']['string']['alarm'] = array(
				'template' => 'alarm',
				'replace' => array("#hide_name#" => "hidden", "#marge_gauche#" => "55px", "#marge_haut#" => "15px"),
				'test' => array(
					array('operation' => "#value# == ''", 
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Clock-Icon-Off.png' title ='" . __('Playing', __FILE__) . "'>",
					'state_dark'  => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Clock-Icon-Off_dark.png' title ='" . __('En charge', __FILE__) . "'>"),
					array('operation' => "#value# != ''",
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Clock-Icon-On.png' title ='" . __('En Pause', __FILE__) . "'>",
					'state_dark' =>  "<img src='plugins/alexaamazonmusic/core/img/Alarm-Clock-Icon-On_dark.png' title ='" . __('En Pause', __FILE__) . "'>")
				)
			);
		$return['info']['string']['alarmmusical'] = array(
				'template' => 'alarm',
				'replace' => array("#hide_name#" => "hidden", "#marge_gauche#" => "55px", "#marge_haut#" => "15px"),
				'test' => array(
					array('operation' => "#value# == ''", 
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Musical-Icon-Off.png' title ='" . __('Playing', __FILE__) . "'>",
					'state_dark'  => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Musical-Icon-Off_dark.png' title ='" . __('En charge', __FILE__) . "'>"),
					array('operation' => "#value# != ''",
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Musical-Icon-On.png' title ='" . __('En Pause', __FILE__) . "'>",
					'state_dark' =>  "<img src='plugins/alexaamazonmusic/core/img/Alarm-Musical-Icon-On_dark.png' title ='" . __('En Pause', __FILE__) . "'>")
				)
			);				
		$return['info']['string']['timer'] = array(
				'template' => 'alarm',
				'replace' => array("#hide_name#" => "hidden", "#marge_gauche#" => "55px", "#marge_haut#" => "15px"),
				'test' => array(
					array('operation' => "#value# == ''", 
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Timer-Icon-Off.png' title ='" . __('Playing', __FILE__) . "'>",
					'state_dark'  => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Timer-Icon-Off_dark.png' title ='" . __('En charge', __FILE__) . "'>"),
					array('operation' => "#value# != ''",
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Timer-Icon-On.png' title ='" . __('En Pause', __FILE__) . "'>",
					'state_dark' =>  "<img src='plugins/alexaamazonmusic/core/img/Alarm-Timer-Icon-On_dark.png' title ='" . __('En Pause', __FILE__) . "'>")
				)
			);			
			$return['info']['string']['reminder'] = array(
				'template' => 'alarm',
				'replace' => array("#hide_name#" => "hidden", "#marge_gauche#" => "55px", "#marge_haut#" => "4px"),
				'test' => array(
					array('operation' => "#value# == ''", 
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Reminder-Icon-Off.png' title ='" . __('Playing', __FILE__) . "'>",
					'state_dark'  => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Reminder-Icon-Off_dark.png' title ='" . __('En charge', __FILE__) . "'>"),
					array('operation' => "#value# != ''",
					'state_light' => "<img src='plugins/alexaamazonmusic/core/img/Alarm-Reminder-Icon-On.png' title ='" . __('En Pause', __FILE__) . "'>",
					'state_dark' =>  "<img src='plugins/alexaamazonmusic/core/img/Alarm-Reminder-Icon-On_dark.png' title ='" . __('En Pause', __FILE__) . "'>")
				)
			);	
	return $return;
	}	


	
	public static function cron($_eqlogic_id = null) {

		$r = new Cron\CronExpression('*/15 * * * *', new Cron\FieldFactory);// boucle refresh
//		$r = new Cron\CronExpression('* * * * *', new Cron\FieldFactory);// boucle refresh
		if ($r->isDue() && $deamon_info['state'] == 'ok') {
			$eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType('alexaamazonmusic', true);
			foreach ($eqLogics as $alexaamazonmusic) {
				//log::add('alexaamazonmusic_node', 'debug', 'CRON Refresh: '.$alexaamazonmusic->getName());
				$alexaamazonmusic->refresh(); 				
				sleep(2);
			}	
		}
	}


	public static function createNewDevice($deviceName, $deviceSerial) {
		$defaultRoom = intval(config::byKey('defaultParentObject','alexaapi','',true));
		//event::add('jeedom::alert', array('level' => 'success', 'page' => 'alexaamazonmusic', 'message' => __('Ajout de "'.$deviceName.'"', __FILE__),));
		$newDevice = new alexaamazonmusic();
		$newDevice->setName($deviceName);
		$newDevice->setLogicalId($deviceSerial);
		$newDevice->setEqType_name('alexaamazonmusic');
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
		$devicetype=$this->getConfiguration('devicetype');
		log::add('alexaamazonmusic', 'info', 'Refresh du device '.$this->getName().' ('.$devicetype.')');
		$widgetPlayer=($devicetype == "Player");
		$widgetSmarthome=($devicetype == "Smarthome");
		$widgetPlaylist=($devicetype == "PlayList");
		$widgetEcho=(!($widgetPlayer||$widgetSmarthome||$widgetPlaylist));
		$device=str_replace("_player", "", $this->getConfiguration('serial'));

		if ($widgetPlayer) {	// Refresh d'un player
			$url = network::getNetworkAccess('internal'). "/plugins/alexaamazonmusic/core/php/jeealexaamazonmusic.php?apikey=".jeedom::getApiKey('alexaamazonmusic')."&nom=refreshPlayer"; // Envoyer la commande Refresh via jeealexaamazonmusic
			$ch = curl_init($url);
			$data = array(
				'deviceSerialNumber' => $device,
				'audioPlayerState' => 'REFRESH'
			);
			$payload = json_encode($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			$_playlists=true;
		}
		else {
			$_playlists=false;			
		}

		if ($_playlists) {
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/playlists?device=".$device);
			$json = json_decode($json, true);	
			$ListeDesPlaylists = [];
			foreach ($json as $key => $value) {
				foreach ($value as $key2 => $playlist) {
					foreach ($playlist as $key3 => $value2) {
					$ListeDesPlaylists[]= $value2['playlistId'] . '|' . $value2['title']." (".$value2['trackCount'].")";
					}	
				}
			}		
			$cmd = $this->getCmd(null, 'playList');
			if (is_object($cmd)) { //Playlists existe on  met à jour la liste des Playlists
				$cmd->setConfiguration('listValue', join(';',$ListeDesPlaylists));
				$cmd->save();
				log::add('alexaamazonmusic', 'debug', 'Mise à jour de la liste des Playlists de '.$this->getName());
			}
		}

		if ($widgetEcho)	{
			log::add('alexaamazonmusic', 'debug', 'execute : refresh routines (WidgetEcho) avec la requète http://' . config::byKey('internalAddr') . ':3456/routines');
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/routines");
			$json = json_decode($json, true);	// Met à jour la liste des routines des commandes action "routine"
			self::sortBy('utterance', $json, 'asc');
			$ListeDesRoutines = [];
			foreach ($json as $item) {
				if ($item['utterance'] != '') 
					$ListeDesRoutines[]= $item['creationTimeEpochMillis'] . '|' . $item['utterance'];
				else {
					if ($item['triggerTime'] != '') {
						$resultattriggerTime = substr($item['triggerTime'], 0, 2) . ":" . substr($item['triggerTime'], 2, 2);
						$ListeDesRoutines[]= $item['creationTimeEpochMillis'] . '|' . $resultattriggerTime;
					}
				}
			}
			$cmd = $this->getCmd(null, 'routine');
			if (is_object($cmd)) {
				$cmd->setConfiguration('listValue', join(';',$ListeDesRoutines));
				$cmd->save();
			}

		
			// On va sauvegarder la valeur de chaque ANCIENNE prochaine Alarme/Rappel/Minuteur ...
			$maintenant = date("i H d m w Y");//40 18 20 11 3 2019

			$cmd = $this->getCmd(null, 'whennextalarminfo'); if (is_object($cmd)) $whennextalarminfo_derniereValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennextmusicalalarminfo'); if (is_object($cmd)) $whennextmusicalalarminfo_derniereValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennextreminderinfo'); if (is_object($cmd)) $whennextreminderinfo_derniereValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennexttimerinfo'); if (is_object($cmd)) $whennexttimerinfo_derniereValeur=$cmd->execCmd();
			log::add('alexaamazonmusic_node', 'debug', '--------------------------------------------->>maintenant:'.$maintenant);
			log::add('alexaamazonmusic_node', 'debug', '---->whennextalarminfo:'.$whennextalarminfo_derniereValeur);
			log::add('alexaamazonmusic_node', 'debug', '---->whennexttimerinfo:'.$whennexttimerinfo_derniereValeur);

			try {
							log::add('alexaamazonmusic', 'info', 'coucou');
				foreach ($this->getCmd('action') as $cmd) {
							log::add('alexaamazonmusic', 'info', 'Test refresh de la commande '.$cmd->getName().' valeur -> '.$cmd->getConfiguration('RunWhenRefresh', 0));

					if ($cmd->getConfiguration('RunWhenRefresh', 0) != '1') {
						continue; // si le lancement n'est pas prévu, ça va au bout de la boucle foreach
					}
							//log::add('alexaamazonmusic', 'info', 'OUI pour '.$cmd->getName());
					$value = $cmd->execute();
				}
			}
			catch(Exception $exc) {log::add('alexaamazonmusic', 'error', __('Erreur pour ', __FILE__) . $this->getHumanName() . ' : ' . $exc->getMessage());}
			
			// On va sauvegarder la valeur de chaque NOUVELLE prochaine Alarme/Rappel/Minuteur ...
			$cmd = $this->getCmd(null, 'whennextalarminfo'); if (is_object($cmd)) $whennextalarminfo_actuelleValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennextmusicalalarminfo'); if (is_object($cmd)) $whennextmusicalalarminfo_actuelleValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennextreminderinfo'); if (is_object($cmd)) $whennextreminderinfo_actuelleValeur=$cmd->execCmd();
			$cmd = $this->getCmd(null, 'whennexttimerinfo'); if (is_object($cmd)) $whennexttimerinfo_actuelleValeur=$cmd->execCmd();
			log::add('alexaamazonmusic_node', 'debug', '---->whennextalarminfo2:'.$whennextalarminfo_actuelleValeur);
			log::add('alexaamazonmusic_node', 'debug', '---->whennexttimerinfo2:'.$whennexttimerinfo_actuelleValeur);

				if (($whennextalarminfo_derniereValeur != $whennextalarminfo_actuelleValeur) && ($whennextalarminfo_derniereValeur==$maintenant))
				{
						log::add('alexaamazonmusic_node', 'debug', '-------------------------------->today:ALLLLLAAARRRRMMMMMMEEEE'.$today);
					
				}

				if (($whennexttimerinfo_derniereValeur != $whennexttimerinfo_actuelleValeur) && ($whennexttimerinfo_derniereValeur==$maintenant))
				{
						log::add('alexaamazonmusic_node', 'debug', '-------------------------------->today:TTTIIIIMMMMMEEEERRRR'.$today);
					
				}	


			
		}
	}
		


	public function updateCmd ($forceUpdate, $LogicalId, $Type, $SubType, $RunWhenRefresh, $Name, $IsVisible, $title_disable, $setDisplayicon, $infoNameArray, $setTemplate_lien, $request, $infoName, $listValue, $Order, $Test) {
		if ($Test) {
			try {
				if (empty($Name)) $Name=$LogicalId;
				$cmd = $this->getCmd(null, $LogicalId);
				if ((!is_object($cmd)) || $forceUpdate) {
					if (!is_object($cmd)) $cmd = new alexaamazonmusicCmd();
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
				log::add('alexaamazonmusic', 'error', __('Erreur pour ', __FILE__) . ' : ' . $exc->getMessage());
			}
		} else {
							//log::add('alexaamazonmusic', 'debug', 'PAS de **'.$LogicalId.'*********************************');

		$cmd = $this->getCmd(null, $LogicalId);
			if (is_object($cmd)) {
				$cmd->remove();
			}
		}
	}


	public function postSave() {
		//log::add('alexaamazonmusic', 'debug', '**********************postSave '.$this->getName().'***********************************');
		$F=$this->getStatus('forceUpdate');// forceUpdate permet de recharger les commandes à valeur d'origine, mais sans supprimer/recréer les commandes
				$capa=$this->getConfiguration('capabilities','');
				$type=$this->getConfiguration('type','');
		if(!empty($capa)) {
					if (strstr($this->getName(), "Alexa Apps")) {
						self::updateCmd ($F, 'push', 'action', 'message', false, 'Push', true, true, 'fa jeedomapp-audiospeak', null, null, 'push?text=#message#', null, null, 1, true);
						return;
					}
			$widgetPlayer=($this->getConfiguration('devicetype') == "Player");
			$widgetSmarthome=($this->getConfiguration('devicetype') == "Smarthome");
			$widgetPlaylist=($this->getConfiguration('devicetype') == "PlayList");

			$cas1=(($this->hasCapaorFamilyorType("AUDIO_PLAYER")) && $widgetPlayer);
			$cas1bis=(($this->hasCapaorFamilyorType("AUDIO_PLAYER")) && !$widgetPlayer);
			$cas2=(($this->hasCapaorFamilyorType("TIMERS_AND_ALARMS")) && !$widgetPlayer);
			$cas3=(($this->hasCapaorFamilyorType("REMINDERS")) && !$widgetPlayer);
			$cas4=(($this->hasCapaorFamilyorType("REMINDERS")) && !$widgetSmarthome);
			$cas5=($this->hasCapaorFamilyorType("VOLUME_SETTING"));
			$cas6=($cas5 && (!$this->hasCapaorFamilyorType("WHA")));
			$cas7=((!$this->hasCapaorFamilyorType("WHA")) && ($this->getConfiguration('devicetype') != "Player") &&(!$this->hasCapaorFamilyorType("FIRE_TV")) && !$widgetSmarthome && (!$this->hasCapaorFamilyorType("AMAZONMOBILEMUSIC_ANDROID")));
			$cas8=(($this->hasCapaorFamilyorType("turnOff")) && $widgetSmarthome);
			$false=false;

			//Anciennes functions a supprimer si elles existent encore
			self::updateCmd ($F, 'musicalalarmmusicentity', 'action', 'other', true, 'musicalalarmmusicentity', false, false, null, null, null, 'musicalalarmmusicentity?position=1', 'Musical Alarm Music', null, 1, $false);
			self::updateCmd ($F, 'whennextmusicalalarm', 'action', 'other', true, 'Next Musical Alarm When', false, false, 'fa-bell', null, null, 'whennextmusicalalarm?position=1', 'Next Musical Alarm Hour', null, 1, $false);			
			self::updateCmd ($F, 'whennextreminder', 'action', 'other', true, 'Next Reminder When', false, false, null, null, null, 'whennextreminder?position=1', 'Next Reminder Hour', null, 33, $false);
			self::updateCmd ($F, 'whennextreminderlabel', 'action', 'other', true, 'whennextreminderlabel', false, false, null, null, null, 'whennextreminderlabel?position=1', 'Reminder Label', null, 34, $false);

			self::updateCmd ($F, 'interactioninfo', 'info', 'string', false, 'Dernier dialogue avec Alexa', true, false, null, null,'alexaapi::interaction', null, null, null, 2, $cas7);	
			self::updateCmd ($F, 'bluetoothDevice', 'info', 'string', false, 'Est connecté en Bluetooth', true, false, null, null,'alexaapi::interaction', null, null, null, 2, $cas7);	
			self::updateCmd ($F, 'updateallalarms', 'action', 'other', true, 'UpdateAllAlarms', false, false, null, ["musicalalarmmusicentityinfo","whennextalarminfo","whennextmusicalalarminfo","whennextreminderinfo","whennexttimerinfo","whennextreminderlabelinfo"] , null, 'updateallalarms', null , null, 2, $cas2);	
			self::updateCmd ($F, 'deleteReminder', 'action', 'message', false, 'DeleteReminder', false, false, 'maison-poubelle', null, null, 'deleteReminder?id=#id#', null, null, 2, $cas3);			
			self::updateCmd ($F, 'subText2', 'info', 'string', false, null, true, false, null, null, 'alexaapi::subText2', null, null, null, 2, $cas1);
			self::updateCmd ($F, 'subText1', 'info', 'string', false, null, true, false, null, null, 'alexaapi::title', null, null, null, 4, $cas1);			
			self::updateCmd ($F, 'url', 'info', 'string', false, null, true, false, null, null, 'alexaapi::image', null, null, null, 5, $cas1);			
			self::updateCmd ($F, 'title', 'info', 'string', false, null, true, false, null, null, 'alexaapi::title', null, null, null, 9, $cas1);
			self::updateCmd ($F, 'previous', 'action', 'other', false, 'Previous', true, true, 'fa fa-step-backward', null, null, 'command?command=previous', null, null, 16, $cas1);
			self::updateCmd ($F, 'pause', 'action', 'other', false, 'Pause', true, true, 'fa fa-pause', null, null, 'command?command=pause', null, null, 17, $cas1);
			self::updateCmd ($F, 'play', 'action', 'other', false, 'Play', true, true, 'fa fa-play', null, null, 'command?command=play', null, null, 18, $cas1);
			self::updateCmd ($F, 'next', 'action', 'other', false, 'Next', true, true, 'fa fa-step-forward', null, null, 'command?command=next', null, null, 19, $cas1);
			//self::updateCmd ($F, 'multiplenext', 'action', 'other', false, 'Multiple Next', true, true, 'fa fa-step-forward', null, null, 'multiplenext?text=3', null, null, 19, $cas1);			
			self::updateCmd ($F, 'providerName', 'info', 'string', false, 'Fournisseur de musique :', true, true, 'loisir-musical7', null, null , null, null, null, 20, $cas1);
			self::updateCmd ($F, 'contentId', 'info', 'string', false, 'Amazon Music Id', false, true, 'loisir-musical7', null, null , null, null, null, 21, $cas1);			
			self::updateCmd ($F, 'routine', 'action', 'select', false, 'Lancer une routine', true, false, null, null,'alexaapi::list', 'routine?routine=#select#', null, 'Lancer Refresh|Lancer Refresh', 21, $cas3);			
			self::updateCmd ($F, 'playList', 'action', 'select', false, 'Ecouter une playlist', true, false, null, null, 'alexaapi::list', 'playlist?playlist=#select#', null, 'Lancer Refresh|Lancer Refresh', 24, $cas1);
			self::updateCmd ($F, 'radio', 'action', 'select', false, 'Ecouter une radio', true, false, null, null, 'alexaapi::list', 'radio?station=#select#', null, 's2960|Nostalgie;s6617|RTL;s6566|Europe1', 25, $cas1);	
			self::updateCmd ($F, 'playMusicTrack', 'action', 'select', false, 'Ecouter une piste musicale', true, false, null, null, 'alexaapi::list', 'playmusictrack?trackId=#select#', null, '53bfa26d-f24c-4b13-97a8-8c3debdf06f0|Piste1;7b12ee4f-5a69-4390-ad07-00618f32f110|Piste2', 26, $cas1);
			self::updateCmd ($F, 'volume', 'action', 'slider', false, 'Volume', true, true, 'fa fa-volume-up', null,'alexaapi::volume', 'volume?value=#slider#', null, null, 27, $cas5);			
			self::updateCmd ($F, 'volumeinfo', 'info', 'string', false, 'Volume Info', false, false, 'fa fa-volume-up', null, null, null, null, null, 28, $cas6);	
			self::updateCmd ($F, 'whennextalarminfo', 'info', 'string', false, 'Prochaine Alarme', true, false, null, null,'alexaapi::alarm', null, null, null, 29, $cas2);			
			self::updateCmd ($F, 'whennextmusicalalarminfo', 'info', 'string', false, 'Prochaine Alarme Musicale', true, false, null, null,'alexaapi::alarmmusical', null, null, null, 30, $cas2);	
			self::updateCmd ($F, 'musicalalarmmusicentityinfo', 'info', 'string', false, 'Musical Alarm Music', true, false, 'loisir-musical7', null,'alexaapi::alarmmusicalmusic', null, null, null, 31, $cas2);			
			self::updateCmd ($F, 'whennextreminderinfo', 'info', 'string', false, 'Prochain Rappel', true, false, null, null,'alexaapi::reminder', null, null, null, 32, $cas3);
			self::updateCmd ($F, 'whennexttimerinfo', 'info', 'string', false, 'Prochain Minuteur', true, false, null, null,'alexaapi::timer', null, null, null, 32, $cas3);			
			self::updateCmd ($F, 'whennextreminderlabelinfo', 'info', 'string', false, 'Reminder Label', true, false, 'loisir-musical7', null,'alexaapi::alarmmusicalmusic', null, null, null, 35, $cas2);
			self::updateCmd ($F, 'alarm', 'action', 'select', false, 'Lancer une alarme', true, false, null, null, 'alexaapi::list', 'alarm?when=#when#&recurring=#recurring#&sound=#sound#', null, 'system_alerts_melodic_01|Alarme simple;system_alerts_melodic_01|Timer simple;system_alerts_melodic_02|A la dérive;system_alerts_atonal_02|Métallique;system_alerts_melodic_05|Clarté;system_alerts_repetitive_04|Comptoir;system_alerts_melodic_03|Focus;system_alerts_melodic_06|Lueur;system_alerts_repetitive_01|Table de chevet;system_alerts_melodic_07|Vif;system_alerts_soothing_05|Orque;system_alerts_atonal_03|Lumière du porche;system_alerts_rhythmic_02|Pulsar;system_alerts_musical_02|Pluvieux;system_alerts_alarming_03|Ondes carrées', 36, $cas3);
			//self::updateCmd ($F, 'rwd', 'action', 'other', false, 'Rwd', true, true, 'fa fa-fast-backard', null, null, 'command?command=rwd', null, null, 15, $cas1);
			//self::updateCmd ($F, 'fwd', 'action', 'other', false, 'Fwd', true, true, 'fa fa-step-forward', null, null, 'command?command=fwd', null, null, 20, $cas1);
			//self::updateCmd ($F, 'repeat', 'action', 'other', false, 'Repeat', true, true, 'fa fa-refresh', null, null, 'command?command=repeat', null, null, 25, $cas1);
			//self::updateCmd ($F, 'shuffle', 'action', 'other', false, 'Shuffle', true, true, 'fa fa-random', null, null, 'command?command=shuffle', null, null, 26, $cas1);
			self::updateCmd ($F, 'playlistName', 'info', 'string', false, null, true, true, null, null, null, null, null, null, 79, $widgetPlaylist);
			//self::updateCmd ($F, 'playlistName', 'info', 'string', false, null, false, true, null, null, null, null, null, null, 2, $widgetPlayer);
			//self::updateCmd ($F, 'songName', 'info', 'string', false, null, true, false, null, null, null, null, null, null, 79, ($widgetPlaylist || $widgetPlayer));
			self::updateCmd ($F, 'playlisthtml', 'info', 'string', false, null, true, true, null, null, null, null, null, null, 79, $widgetPlaylist);
			self::updateCmd ($F, 'turnOn', 'action', 'other', false, 'turnOn', true, true, "fas fa-circle", null, null, 'SmarthomeCommand?command=turnOn', null, null, 79, $cas8);			
			self::updateCmd ($F, 'turnOff', 'action', 'other', false, 'turnOff', true, true, "far fa-circle", null, null, 'SmarthomeCommand?command=turnOff', null, null, 79, $cas8);

			self::updateCmd ($F, 'command', 'action', 'message', false, 'Command', false, true, "fa fa-play-circle", null, null, 'command?command=#select#', null, null, 79, $cas1);		
			self::updateCmd ($F, 'speak', 'action', 'message', false, 'Faire parler Alexa', true, false, null, null, 'alexaapi::message', 'speak?text=#message#&volume=#volume#', null, null, 40, $cas1bis);
			self::updateCmd ($F, 'speaklegacy', 'action', 'message', false, 'Faire parler Alexa (legacy)', false, false, null, null, 'alexaapi::message', 'speak?text=#message#&volume=#volume#&legacy=1', null, null, 43, $cas1bis);			self::updateCmd ($F, 'announcement', 'action', 'message', false, 'Lancer une annonce', true, true, null, null, 'alexaapi::message', 'speak?text=#message#&volume=#volume#&jingle=1', null, null, 44, $cas1bis);			
			self::updateCmd ($F, 'speakssml', 'action', 'message', false, 'Faire parler Alexa en SSML', false, true, null, null, 'alexaapi::message', 'speak?text=#message#&volume=#volume#&ssml=1', null, null, 42, $cas1bis);			
			self::updateCmd ($F, 'mediaLength', 'info', 'string', false, null, false, false, null, null, null , null, null, null, 79, $cas1);
			self::updateCmd ($F, 'mediaProgress', 'info', 'string', false, null, false, false, null, null, null , null, null, null, 79, $cas1);
			self::updateCmd ($F, 'state', 'info', 'string', false, null, true, false, null, null, 'alexaapi::state', null, null, null, 79, $cas1);
			//self::updateCmd ($F, 'playlistName', 'info', 'string', false, null, false, false, null, null, null, null, null, null, 2, $cas1);
			self::updateCmd ($F, 'nextState', 'info', 'string', false, null, false, true, null, null, null, null, null, null, 79, $cas1);
			self::updateCmd ($F, 'previousState', 'info', 'string', false, null, false, true, null, null, null, null, null, null, 79, $cas1);
			self::updateCmd ($F, 'playPauseState', 'info', 'string', false, null, false, true, null, null, null, null, null, null, 79, $cas1);
			//self::updateCmd ($F, 'loopMode', 'info', 'string', false, null, true, false, null, null, null, null, null, null, 79, $cas1);
			//self::updateCmd ($F, 'playBackOrder', 'info', 'string', false, null, true, false, null, null, null, null, null, null, 79, $cas1);
			//self::updateCmd ($F, 'alarm', 'action', 'message', false, 'Alarm', false, true, 'fa fa-bell', null, null, 'alarm?when=#when#&recurring=#recurring#&sound=#sound#', null, null, 79, $cas2);
			self::updateCmd ($F, 'deleteallalarms', 'action', 'message', false, 'Delete All Alarms', false, false, 'maison-poubelle', null, null, 'deleteallalarms?type=alarm&status=all', null, null, 79, $cas2);
				if($type == "A15ERDAKK5HQQG") {
					log::add('alexaamazonmusic', 'warning', '****Rencontre du type A15ERDAKK5HQQG = Sonos Première Génération sur : '.$this->getName());
					log::add('alexaamazonmusic', 'warning', '****On ne crée pas les commandes REMINDERS dessus car bug!');
				}
			self::updateCmd ($F, 'reminder', 'action', 'message', false, 'Envoyer un rappel', true, false, null, null, 'alexaapi::message', 'reminder?text=#message#&when=#when#&recurring=#recurring#', null, null, 79, $cas3);	


			$volinfo = $this->getCmd(null, 'volumeinfo');
			$vol = $this->getCmd(null, 'volume');
					if((is_object($volinfo)) && (is_object($vol))) {
					$vol->setValue($volinfo->getId());// Lien entre volume et volumeinfo
					$vol->save();
					}
		// Pour la commande Refresh, on garde l'ancienne méthode
			if (($this->hasCapaorFamilyorType("REMINDERS")) && !($this->getConfiguration('devicetype') == "Smarthome")) { 
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
						$refresh = new alexaamazonmusicCmd();
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
		} else {
		log::add('alexaamazonmusic', 'warning', 'Pas de capacité détectée sur '.$this->getName().' , assurez-vous que le démon est OK');
		}

		//event::add('jeedom::alert', array('level' => 'success', 'page' => 'alexaamazonmusic', 'message' => __('Mise à jour de "'.$this->getName().'"', __FILE__),));
		$this->refresh(); 

		/*if ($widgetPlayer) {
				$device_playlist=str_replace("_player", "", $this->getConfiguration('serial'))."_playlist"; //Nom du device de la playlist
				// Si la case "Activer le widget Playlist" est cochée, on rend le device _playlist visible sinon on le passe invisible		
				$eq=eqLogic::byLogicalId($device_playlist,'alexaamazonmusic');
						if(is_object($eq)) {
							$eq->setIsVisible((($this->getConfiguration('widgetPlayListEnable'))?1:0));
							$eq->setIsEnable((($this->getConfiguration('widgetPlayListEnable'))?1:0));
							//$eq->setObject_id($this->getObject_id()); // Attribue au widget Playlist la même pièce que son Player
							$eq->save();
						}
			}
*/


		$this->setStatus('forceUpdate', false); //dans tous les cas, on repasse forceUpdate à false
	}


	public function preRemove () {
		if ($this->getConfiguration('devicetype') == "Player") { // Si c'est un type Player, il faut supprimer le Device Playlist
			$device_playlist=str_replace("_player", "", $this->getConfiguration('serial'))."_playlist"; //Nom du device de la playlist
		$eq=eqLogic::byLogicalId($device_playlist,'alexaamazonmusic');
				if(is_object($eq)) $eq->remove();
		}
	}
	
	public function preSave() {
	}

// https://github.com/NextDom/NextDom/wiki/Ajout-d%27un-template-a-votre-plugin	
// https://jeedom.github.io/documentation/dev/fr_FR/widget_plugin	

  public function toHtml($_version = 'dashboard') {
	$replace = $this->preToHtml($_version);
	//log::add('alexaamazonmusic_widget','debug','************Début génération Widget de '.$replace['#logicalId#']);  
	$typeWidget="alexaamazonmusic";	
	if ((substr($replace['#logicalId#'], -7))=="_player") $typeWidget="alexaamazonmusic_player";
	if ((substr($replace['#logicalId#'], -9))=="_playlist") $typeWidget="alexaamazonmusic_playlist";
    if ($typeWidget!="alexaamazonmusic_playlist") return parent::toHtml($_version);
	//log::add('alexaamazonmusic_widget','debug',$typeWidget.'************Début génération Widget de '.$replace['#name#']);        
	if (!is_array($replace)) {
		return $replace;
	}
	$version = jeedom::versionAlias($_version);
	if ($this->getDisplay('hideOn' . $version) == 1) {
		return '';
	}
	foreach ($this->getCmd('info') as $cmd) {
		 	//log::add('alexaamazonmusic_widget','debug',$typeWidget.'dans boucle génération Widget');        
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
		if ($typeWidget=="alexaamazonmusic_playlist") {
			if ("#playlistName#" != "") {
				$replace['#name_display#']='#playlistName#';
			}
		}
	//log::add('alexaamazonmusic_widget','debug',$typeWidget.'***************************************************************************Fin génération Widget');        
	return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $typeWidget, 'alexaamazonmusic')));
	}
}

class alexaamazonmusicCmd extends cmd {

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
		$actionInfo = alexaamazonmusicCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
		if (is_object($actionInfo)) $this->setId($actionInfo->getId());
		if (($this->getType() == 'action') && ($this->getConfiguration('infoName') != '')) {//Si c'est une action et que Commande info est renseigné
			$actionInfo = alexaamazonmusicCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getConfiguration('infoName'));
			if (!is_object($actionInfo)) {//C'est une commande qui n'existe pas
				$actionInfo = new alexaamazonmusicCmd();
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
		log::add('alexaamazonmusic', 'info', 'Request : ' . $request);//Request : http://192.168.0.21:3456/volume?value=50&device=G090LF118173117U
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
		//log::add('alexaamazonmusic', 'info', 'resultjson:'.json_encode($resultjson));
					// Ici, on va traiter une commande qui n'a pas été executée correctement (erreur type "Connexion Close")
						if (isset($resultjson['value'])) $value = $resultjson['value']; else $value="";
						if (isset($resultjson['detail'])) $detail = $resultjson['detail']; else $detail="";					
						if (($value =="Connexion Close") || ($detail =="Unauthorized")){
						//$value = $resultjson['value'];
						//$detail = $resultjson['detail'];
						log::add('alexaamazonmusic', 'debug', '**On traite '.$value.$detail.' Connexion Close** dans la Class');
						sleep(6);
							if (ob_get_length()) {
							ob_end_flush();
							flush();
							}	
						log::add('alexaamazonmusic', 'debug', '**On relance '.$request);
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
					//log::add('alexaamazonmusic', 'info', $LogicalIdCmd.' prévu dans infoNameArray de '.$this->getName().' trouvé ! '.$resultjson[0]['whennextmusicalalarminfo'].' OK !');
				} else {
					log::add('alexaamazonmusic', 'warning', $LogicalIdCmd.' prévu dans infoNameArray de '.$this->getName().' mais non trouvé ! donc ignoré');
				} 
			}
		} 
		elseif (($this->getType() == 'action') && ($this->getConfiguration('infoName') != '')) {
			// Boucle non testée !!
				$LogicalIdCmd=$this->getConfiguration('infoName');
				$cmd=$this->getEqLogic()->getCmd(null, $LogicalIdCmd);
				if (is_object($cmd)) { 
					$this->getEqLogic()->checkAndUpdateCmd($LogicalIdCmd, $resultjson[$LogicalIdCmd]);
				} else {
					log::add('alexaamazonmusic', 'warning', $LogicalIdCmd.' prévu dans infoName de '.$this->getName().' mais non trouvé ! donc ignoré');
				} 
		}
		return true;
	}


	private function buildRequest($_options = array()) {
		if ($this->getType() != 'action') return $this->getConfiguration('request');
		list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
	log::add('alexaamazonmusic', 'info', '----Command:*'.$command.'* Request:'.json_encode($_options));
		switch ($command) {
			case 'volume':
				$request = $this->build_ControledeSliderSelectMessage($_options, '50');
			break;
			case 'playlist':
			case 'routine':
				$request = $this->build_ControledeSliderSelectMessage($_options, "");
			break;			
			case 'playmusictrack':
				$request = $this->build_ControledeSliderSelectMessage($_options, "53bfa26d-f24c-4b13-97a8-8c3debdf06f0");
			break;				
			case 'speak':
			case 'announcement':
			case 'push':
			//case 'multiplenext':
				$request = $this->build_ControledeSliderSelectMessage($_options);
			break;
			case 'reminder':
			case 'alarm':
				$now=date("Y-m-d H:i:s", strtotime('+3 second'));
				$request = $this->build_ControleWhenTextRecurring($now, "Ceci est un essai", $_options);
			break;			
			case 'radio':
				$request = $this->build_ControledeSliderSelectMessage($_options, 's2960');
			break;
			case 'SmarthomeCommand':
				$request = $this->build_ControledeSliderSelectMessage();
			break;			
			case 'command':
				$request = $this->build_ControledeSliderSelectMessage($_options, 'pause');
			break;
			case 'whennextalarm':
			case 'whennextmusicalalarm':
			case 'musicalalarmmusicentity':
			case 'whennextreminderlabel':
			case 'whennextreminder':
				$request = $this->build_ControlePosition($_options);
			break;			
			case 'updateallalarms':
				$request = $this->build_ControleRien($_options);
			break;				
			case 'deleteallalarms':
				$request = $this->buildDeleteAllAlarmsRequest($_options);
			break;
			case 'deleteReminder':
				$request = $this->buildDeleteReminderRequest($_options);
			break;			
			case 'restart':
				$request = $this->buildRestartRequest($_options);
			break;				
			default:
				$request = '';
			break;
		}
		//log::add('alexaamazonmusic_debug', 'debug', '----RequestFinale:'.$request);
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
		//log::add('alexaamazonmusic_node', 'info', '---->Request2:'.$request);
		//log::add('alexaamazonmusic_node', 'debug', '---->getName:'.$this->getEqLogic()->getCmd(null, 'volumeinfo')->execCmd());
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
		//log::add('alexaamazonmusic_node', 'info', '---->RequestFinale:'.$request);
		return $request;
	}	

	//private function trouveVolumeDevice() {
	//	$logical_id = $this->getEqLogic()->getCmd(null, 'volumeinfo')->getValue();
	//	$alexaamazonmusic=alexaamazonmusic::byLogicalId($logical_id, 'alexaamazonmusic');getValue
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
		log::add('alexaamazonmusic', 'debug', '----build_ControledeSliderSelectMessage RequestFinale:'.$request);
		log::add('alexaamazonmusic', 'debug', '----build_ControledeSliderSelectMessage _optionsAVANT:'.json_encode($_options));
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
		log::add('alexaamazonmusic_debug', 'debug', '------buildRestartRequest---UTILISE QUAND ???--A simplifier--------------------------------------');
		$request = $this->getConfiguration('request')."?truc=vide";
		return str_replace('#volume#', $_options['slider'], $request);
	}
	
	public function getWidgetTemplateCode($_version = 'dashboard', $_noCustom = false) {
		if ($_version != 'scenario') return parent::getWidgetTemplateCode($_version, $_noCustom);
		list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
		if (($command == 'speak') || ($command == 'announcement'))
			return getTemplate('core', 'scenario', 'cmd.speak.volume', 'alexaamazonmusic');
		if ($command == 'reminder') 
			return getTemplate('core', 'scenario', 'cmd.reminder', 'alexaamazonmusic');
		if ($command == 'deleteallalarms') 
			return getTemplate('core', 'scenario', 'cmd.deleteallalarms', 'alexaamazonmusic');
		if ($command == 'command' && strpos($arguments, '#select#')) 
			return getTemplate('core', 'scenario', 'cmd.command', 'alexaamazonmusic');
		if ($command == 'alarm') 
			return getTemplate('core', 'scenario', 'cmd.alarm', 'alexaamazonmusic');
		return parent::getWidgetTemplateCode($_version, $_noCustom);
	}
}