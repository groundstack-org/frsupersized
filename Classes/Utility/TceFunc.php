<?php
namespace FFREWER\Frsupersized\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Frank Frewer <info@frankfrewer.de>, medienbüro // FRANK FREWER
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
 
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * 'TceFunc' for the 'frsupersized' extension.
 *
 * @author     Frank Frewer <info@frankfrewer.de>
 * @package    TYPO3
 * @subpackage tx_frsupersized
 */
class TceFunc {

	/**
	 * @var array $frsupersizedConfigurationArray Settings from the extension manager
	 */
	public $frsupersizedConfigurationArray = array();
	
	/**
	 * This will render a selectorbox instead of a checkbox, so its possible to select "from TypoScript".
	 *
	 * @param	array		$params An array with additional configuration options.
	 * @param	object		$fobj TCEForms object reference
	 * @return	string		The HTML code for the TCEform field
	 */
	public function getSelectOrCheckbox($params, &$ref) {
		// Get configuration from the extension manager
		$this->setFrsupersizedConfigurationArray();		
		if(version_compare (TYPO3_version,'7.3.0','<=')) {
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['frsupersized']);
			if ($confArr['useSelectInsteadCheckbox']) {
				$params['fieldConf']['config'] = array(
					'type' => 'select',
					'items' => array(
						array('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.from_ts', 'fromTS'),
						array('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.yes', 1),
						array('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.no', 0),
					),
				);
			} else {
				$conf = $params['fieldConf']['config'];
				$params['itemFormElValue'] = (is_numeric($params['itemFormElValue']) && $params['itemFormElValue'] != 2 ? $params['itemFormElValue'] : $conf['checked']);
				$params['fieldConf']['config'] = array(
					'type' => 'check',
				);
			}
			$tceforms = &$params['pObj'];
			return $tceforms->getSingleField_SW($params['table'], $params['field'], $params['row'], $params);
		} else {
			/** @var NodeFactory $nodeFactory */
			$nodeFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\NodeFactory');
			if ($this->frsupersizedConfigurationArray['useSelectInsteadCheckbox']) {
				$params['fieldConf']['config'] = [
						'type' => 'select',
						'items' => [
								[$this->getLanguageService()->sL('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.from_ts'), 'fromTS'],
								[$this->getLanguageService()->sL('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.yes'), 1],
								[$this->getLanguageService()->sL('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.no'), 0],
						],
				];
				$formData['parameterArray'] = $params;
				// The selected value comes with $params['itemFormElValue'],
				// but in TYPO3\CMS\Backend\Form\Element\SelectSingleElement::render it is asked with (string)$parameterArray['itemFormElValue'][0]
				// We have to transform it
				$formData['parameterArray']['itemFormElValue'] = [];
				$formData['parameterArray']['itemFormElValue'][0] = $params['itemFormElValue'];
				$formData['renderType'] = 'selectSingle';
				$formData['inlineStructure'] = [];
				$formResult = $nodeFactory->create($formData)->render();
			} else {
				$conf = $params['fieldConf']['config'];
				$params['itemFormElValue'] = (is_numeric($params['itemFormElValue']) && $params['itemFormElValue'] != 2 ? $params['itemFormElValue'] : $conf['checked']);
				$params['fieldConf']['config'] = [
						'type' => 'check',
						'items' => array(
								[$this->getLanguageService()->sL('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.yes'), 1],
						),
				];
				$formData['parameterArray'] = $params;
				$formData['renderType'] = 'check';
				$formData['inlineStructure'] = [];
				$formResult = $nodeFactory->create($formData)->render();
			}
			$html = $formResult['html'];
			return $html;				
		}
	}

	/**
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}
	
	/**
	 * @return array settings array from the extension manager
	 */
	protected function setFrsupersizedConfigurationArray() {
		$this->frsupersizedConfigurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['frsupersized']);
	}
}
