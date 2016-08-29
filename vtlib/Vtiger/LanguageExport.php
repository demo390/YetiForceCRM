<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to package vtiger CRM language files.
 * @package vtlib
 */
class LanguageExport extends Package
{

	const TABLENAME = 'vtiger_language';

	/**
	 * Generate unique id for insertion
	 * @access private
	 */
	static function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();
		return $adb->getUniqueID(self::TABLENAME);
	}

	/**
	 * Initialize Export
	 * @access private
	 */
	function __initExport($languageCode)
	{
		// Security check to ensure file is withing the web folder.
		Utils::checkFileAccessForInclusion("languages/$languageCode/Vtiger.php");

		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Export Module as a zip file.
	 * @param Module Instance of module
	 * @param Path Output directory path
	 * @param String Zipfilename to use
	 * @param Boolean True for sending the output as download
	 */
	function export($languageCode, $todir = '', $zipfilename = '', $directDownload = false)
	{

		$this->__initExport($languageCode);

		// Call language export function
		$this->export_Language($languageCode);

		$this->__finishExport();

		// Export as Zip
		if ($zipfilename == '')
			$zipfilename = "$languageCode-" . date('YmdHis') . ".zip";
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = new Zip($zipfilename);

		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), "manifest.xml");

		// Copy module directory
		$zip->copyDirectoryFromDisk("languages/$languageCode", "modules");

		$zip->save();

		if ($todir) {
			copy($zipfilename, $todir);
		}

		if ($directDownload) {
			$zip->forceDownload($zipfilename);
			unlink($zipfilename);
		}
		$this->__cleanupExport();
	}

	/**
	 * Export Language Handler
	 * @access private
	 */
	function export_Language($prefix)
	{
		$db = \PearDatabase::getInstance();
		$sqlresult = $db->pquery('SELECT * FROM vtiger_language WHERE prefix = ?', array($prefix));
		$languageresultrow = $db->fetch_array($sqlresult);
		$langname = decode_html($languageresultrow['name']);
		$langlabel = decode_html($languageresultrow['label']);
		$this->openNode('module');
		$this->outputNode('language', 'type');
		$this->outputNode($langname, 'name');
		$this->outputNode($langlabel, 'label');
		$this->outputNode($prefix, 'prefix');
		$this->outputNode('language', 'type');
		$this->outputNode(\AppConfig::main('default_charset'), 'encoding');
		$this->outputNode('YetiForce - yetiforce.com', 'author');
		$this->outputNode('YetiForce - yetiforce.com', 'license');
		// Export dependency information
		$this->export_Dependencies($moduleInstance);

		$this->closeNode('module');
	}

	/**
	 * Export vtiger dependencies
	 * @access private
	 */
	function export_Dependencies($moduleInstance)
	{
		$vtigerMinVersion = \AppConfig::main('YetiForce_current_version');
		$vtigerMaxVersion = current(explode('.', $vtigerMinVersion)) . '.*';
		$this->openNode('dependencies');
		$this->outputNode($vtigerMinVersion, 'vtiger_version');
		if ($vtigerMaxVersion !== false)
			$this->outputNode($vtigerMaxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Initialize Language Schema
	 * @access private
	 */
	static function __initSchema()
	{
		$hastable = Utils::CheckTable(self::TABLENAME);
		if (!$hastable) {
			Utils::CreateTable(
				self::TABLENAME, '(id INT NOT NULL PRIMARY KEY,
				name VARCHAR(50), prefix VARCHAR(10), label VARCHAR(30), lastupdated DATETIME, sequence INT, isdefault INT(1), active INT(1))', true
			);
			$adb = \PearDatabase::getInstance();
			foreach (vglobal('languages') as $langkey => $langlabel) {
				$uniqueid = self::__getUniqueId();
				$adb->pquery('INSERT INTO ' . self::TABLENAME . '(id,name,prefix,label,lastupdated,active) VALUES(?,?,?,?,?,?)', Array($uniqueid, $langlabel, $langkey, $langlabel, date('Y-m-d H:i:s', time()), 1));
			}
		}
	}

	/**
	 * Register language pack information.
	 */
	static function register($prefix, $label, $name = '', $isdefault = false, $isactive = true, $overrideCore = false)
	{
		self::__initSchema();

		$prefix = trim($prefix);
		// We will not allow registering core language unless forced
		if (strtolower($prefix) == 'en_us' && $overrideCore == false)
			return;

		$useisdefault = ($isdefault) ? 1 : 0;
		$useisactive = ($isactive) ? 1 : 0;

		$adb = \PearDatabase::getInstance();
		$checkres = $adb->pquery(sprintf('SELECT id FROM %s WHERE prefix = ?', self::TABLENAME), [$prefix]);
		$datetime = date('Y-m-d H:i:s');
		if ($adb->num_rows($checkres)) {
			$id = $adb->query_result($checkres, 0, 'id');
			$adb->update(self::TABLENAME, [
				'label' => $label,
				'name' => $name,
				'lastupdated' => $datetime,
				'isdefault' => $useisdefault,
				'active' => $useisactive,
				], 'id=?', [$adb->getSingleValue($checkres)]
			);
		} else {
			$adb->insert(self::TABLENAME, [
				'id' => self::__getUniqueId(),
				'name' => $name,
				'prefix' => $prefix,
				'label' => $label,
				'lastupdated' => $datetime,
				'isdefault' => $useisdefault,
				'active' => $useisactive,
			]);
		}
		self::log("Registering Language $label [$prefix] ... DONE");
	}

	/**
	 * De-Register language pack information
	 * @param String Language prefix like (de_de) etc
	 */
	static function deregister($prefix)
	{
		$prefix = trim($prefix);
		// We will not allow deregistering core language
		if (strtolower($prefix) == 'en_us')
			return;

		self::__initSchema();

		$adb = \PearDatabase::getInstance();
		$adb->delete(self::TABLENAME, 'prefix=?', [$prefix]);
		self::log("Deregistering Language $prefix ... DONE");
	}

	/**
	 * Get all the language information
	 * @param Boolean true to include in-active languages also, false (default)
	 */
	static function getAll($includeInActive = false)
	{
		$adb = \PearDatabase::getInstance();
		$hastable = Utils::CheckTable(self::TABLENAME);

		$languageinfo = [];

		if ($hastable) {
			if ($includeInActive)
				$result = $adb->pquery(sprintf('SELECT * FROM %s', self::TABLENAME), []);
			else
				$result = $adb->pquery(sprintf('SELECT * FROM %s WHERE active = ?', self::TABLENAME), [1]);

			for ($index = 0; $index < $adb->num_rows($result); ++$index) {
				$resultrow = $adb->fetch_array($result);
				$prefix = $resultrow['prefix'];
				$label = $resultrow['label'];
				$languageinfo[$prefix] = $label;
			}
		} else {
			$languages = vglobal('languages');
			foreach ($languages as $prefix => $label) {
				$languageinfo[$prefix] = $label;
			}
		}

		return $languageinfo;
	}
}
