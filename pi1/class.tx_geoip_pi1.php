<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Georg Ringer (just2b) <http://www.ringer.it/>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Plugin 'IP based client information' for the 'geoip' extension.
 *
 * @author	Georg Ringer (just2b) <http://www.ringer.it/>
 * @package	TYPO3
 * @subpackage	tx_geoip
 */
class tx_geoip_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId      = 'tx_geoip_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_geoip_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'geoip';	// The extension key.
	var $GEOIP_REGION_NAME = array();
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

			// Template
		$this->templateCode = $this->cObj->fileResource($conf['templateFile']);
  	$template['total'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE###');	
		
		$library = ($conf['lib']) ? $conf['lib'] : '';	
		$this->init($library);
		
		$info = $this->getGeoIP($conf['ip']);
		foreach ($info as $key=>$value) {
  		$markerArray['###'.strtoupper($key).'###'] = $value;
  		$markerArray['###LL_'.strtoupper($key).'###'] = $this->getLanguageKey($key);

  	}
		
		$content.= $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Initialize the geoip
	 *
	 * @return	void
	 */		
	function init($dbPath='') {
		$path = t3lib_extMgm::extPath('geoip');
		
		require_once($path.'lib/geoip.inc');
		require_once($path.'lib/geoipcity.inc');
		require_once($path.'lib/geoipregionvars.php');
		$this->GEOIP_REGION_NAME = $GEOIP_REGION_NAME;
		if ($dbPath=='') {
			$dbPath = '/usr/share/GeoIP/GeoLiteCity.dat'; 
		}
		$this->DBpath = $dbPath;
	}
	
	
	/**
	 * Get the place of the dat file
	 *
	 * @return string path
	 */		
	function getDBpath() {
		return $this->DBpath;
	}
	
	
	
	/**
	 * GeoIP with the DB of maxmind
	 *
	 * @return	void
	 */	
	function getGeoIP($ip='') {
			// maxmind GeoIP

		
			// get all the infos based on the IP of the client
		if ($ip=='') {
			$ip = t3lib_div::getIndpEnv('REMOTE_ADDR');
		}
		
		$gi = geoip_open($this->getDBpath(),GEOIP_MEMORY_CACHE);		
		$record = geoip_record_by_addr($gi,$ip);

			// save info to this
		$info['ip']						= $ip;
		$info['countryCode'] 	= $record->country_code3;
		$info['countryName'] 	= $record->country_name;
		$info['region'] 			= $this->GEOIP_REGION_NAME[$record->country_code][$record->region];
		$info['city'] 				= $record->city;
		$info['zip'] 					= $record->postal_code;
		$info['lng'] 					= $record->longitude;
		$info['lat'] 					= $record->latitude;
		$info['dmaCode'] 			= $record->dma_code;
		$info['areaCode'] 		= $record->area_code;	
		
		geoip_close($gi);
		
		return $info;
	}
	
	function getLanguageKey($key) {
		return $this->pi_getLL($key);
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/geoip/pi1/class.tx_geoip_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/geoip/pi1/class.tx_geoip_pi1.php']);
}

?>