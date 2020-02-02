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
try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');
    /*$eqLogics = alexasmarthome::byType('alexasmarthome');
    foreach ($eqLogics as $eqLogic) {
    log::add('alexasmarthome', 'info', $eqLogic->getConfiguration('ip'));
    }
    */
    if (!isConnect('admin')) {
        throw new \Exception('401 Unauthorized');
    }
//            $('.deamonCookieState').empty().append('<span class="label label-success" style="font-size:1em;">00012300</span>');
    //log::add('alexasmarthome', 'info', 'Lancement Serveur pour Cookie - action='.init('action'));
    switch (init('action')) {
        case 'createCookie':
            //log::add('alexasmarthome', 'info', 'Debut');
            $sensor_path = realpath(dirname(__FILE__) . '/../../resources');
            //Par sécurité, on Kill un éventuel précédent proessus initCookie.js
            $cmd = 'kill $(ps aux | grep "/initCookie.js" | awk \'{print $2}\')';
            log::add('alexasmarthome', 'debug', '---- Kill initCookie.js: ' . $cmd);
            $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('alexasmarthome_cookie') . ' 2>&1 &');
            $cmd = 'nice -n 19 nodejs ' . $sensor_path . '/initCookie.js ' . config::byKey('internalAddr');
            log::add('alexasmarthome', 'debug', '---- Lancement démon Alexa-API-Cookie sur port 3457 : ' . $cmd);
            $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('alexasmarthome_cookie') . ' 2>&1 &');
            if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
                log::add('alexasmarthome', 'error', $result);
                return false;
            }
            log::add('alexasmarthome', 'info', 'Fin lancement Serveur pour Cookie');
            ajax::success();
        break;
        case 'closeCookie':
            $sensor_path = realpath(dirname(__FILE__) . '/../../resources');
            //Par sécurité, on Kill un éventuel précédent proessus cookie.js
            $cmd = 'kill $(ps aux | grep "/initCookie.js" | awk \'{print $2}\')';
            log::add('alexasmarthome', 'debug', '---- Kill initCookie.js: ' . $cmd);
            $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('alexasmarthome_cookie') . ' 2>&1 &');
            log::add('alexasmarthome', 'info', 'Fin lancement Serveur pour Cookie');
            ajax::success();
        break;
        case 'scanAmazonAlexa':
            alexasmarthome::scanAmazonAlexa();
            ajax::success();
        break;
        case 'forcerDefaultAllCmd':
            alexasmarthome::forcerDefaultAllCmd();
            ajax::success();
        break;        
		case 'forcerDefaultCmd':
				$eqLogic = alexasmarthome::byId(init('id'));
				if (!is_object($eqLogic)) {
					throw new Exception(__('alexasmarthome eqLogic non trouvé : ', __FILE__) . init('id'));
				}
            alexasmarthome::forcerDefaultCmd(init('id'));
            ajax::success();
        break;		
		case 'VerifiePresenceCookie':
        $request = realpath(dirname(__FILE__) . '/../../resources/data/alexa-cookie.json');
        if (file_exists($request))
		ajax::success();
		else
 		ajax::error();
       break;
        case 'deamonCookieStart':
					//on va vérifier que les dépendances sont bien installées
					$request = realpath(dirname(__FILE__) . '/../../resources/node_modules');
					if (!(file_exists($request)))
					ajax::error("Dépendances non présentes, génération manuelle du cookie Amazon impossible !!");

            log::add('alexasmarthome', 'info', 'Lancement Serveur pour Cookie - DEBUT deamonCookieStart');
            alexasmarthome::deamonCookie_start();
            log::add('alexasmarthome', 'info', 'Lancement Serveur pour Cookie - DEBUT deamon_info');

        $i = 0;
        while ($i < 10) {
            log::add('alexasmarthome', 'info', 'Test si serveur cookie lance');

        $pid = trim(shell_exec('ps ax | grep "alexasmarthome/resources/initCookie.js" | grep -v "grep" | wc -l'));
        if ($pid != '' && $pid != '0') {
            break;
            }
            sleep(1);
            $i++;
        }
        if ($i >= 10) {
            log::add('alexasmarthome', 'info', 'SOUCI LANCEMENT SERVEUR COOKIE');
        }

            alexasmarthome::deamon_info();
            log::add('alexasmarthome', 'info', 'Lancement Serveur pour Cookie - FIN   deamonCookieStart');
            ajax::success();
        break;
        case 'deamonCookieStop':
            alexasmarthome::deamonCookie_stop();
            alexasmarthome::deamon_info();
            ajax::success();
        break;
	case 'reinstallNodeJS':
		$ret=alexasmarthome::reinstallNodeJS();
		ajax::success($ret);
	break;
	case 'supprimeTouslesDevices':
		$ret=alexasmarthome::supprimeTouslesDevices();
		ajax::success($ret);
	break;    }
    throw new \Exception('Aucune methode correspondante');
}
catch(\Exception $e) {
    ajax::error(displayException($e), $e->getCode());
    log::add('alexasmarthome', 'error', $e);
}
