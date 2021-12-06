<?php

namespace FFREWER\Frsupersized\Form\Element;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 Thomas Deuling <typo3@coding.ms>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SelectOrCheckbox extends AbstractFormElement
{

    /**
     *
     * @return array
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    public function render()
    {
        // Get configuration from the extension manager
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $frsupersizedConfigurationArray = $extensionConfiguration->get('frsupersized');
        /** @var NodeFactory $nodeFactory */
        $nodeFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\NodeFactory');
        if ((bool)$frsupersizedConfigurationArray['useSelectInsteadCheckbox']) {
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
            $conf = $this->data['parameterArray']['fieldConf']['config'];
            $this->data['parameterArray']['itemFormElValue'] = (is_numeric($this->data['parameterArray']['itemFormElValue']) && $this->data['parameterArray']['itemFormElValue'] != 2 ? $this->data['parameterArray']['itemFormElValue'] : $conf['checked']);
            $this->data['parameterArray']['fieldConf']['config'] = [
                'type' => 'check',
                'items' => [
                    [$this->getLanguageService()->sL('LLL:EXT:frsupersized/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform.yes'), 1],
                ],
            ];
            $formData['parameterArray'] = $this->data['parameterArray'];
            $formData['renderType'] = 'check';
            $formData['inlineStructure'] = [];
            $formResult = $nodeFactory->create($formData)->render();
        }
        $result['html'] = $formResult['html'];
        return $result;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}
