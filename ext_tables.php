<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');


if (TYPO3_MODE == 'BE') {
	// add wizicon to 'add content element'
	$GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['FFREWER\Frsupersized\Utility\WizardIcon'] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('frsupersized') . 'Classes/Utility/WizardIcon.php';
	// TCEForms user functions
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('frsupersized') . 'Classes/Utility/TceFunc.php');
}
