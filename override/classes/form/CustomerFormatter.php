<?php

/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use Symfony\Component\Translation\TranslatorInterface;

class CustomerFormatter extends CustomerFormatterCore
{
    private $translator;
    private $language;

    //translator and language are private in parent class, override need
    public function __construct(
        TranslatorInterface $translator,
        Language $language
    ) {
        $this->translator = $translator;
        $this->language = $language;

        parent::__construct($translator, $language);
    }

    public function getFormat()
    {
        $format = parent::getFormat();

        //override/add all customisations for fields
        $format['birthday'] = (new FormField())
            ->setName('birthday')
            ->setType('text')
            ->setLabel(
                $this->translator->trans(
                    'Birthdate',
                    [],
                    'Shop.Forms.Labels'
                )
            )
            ->addAvailableValue('placeholder', Tools::getDateFormat())
            ->addAvailableValue(
                'comment',
                $this->translator->trans(
                    '(E.g.: %date_format%)',
                    array('%date_format%' => Tools::formatDateStr('31 May 1970')),
                    'Shop.Forms.Help'
                )
            )
            ->setRequired(true);

        //As addConstraints method is private we need to call the logic here.
        //We don't need to iterate over all the fields again, just the changed ones.
        $constraints = Customer::$definition['fields'];
        $field = $format['birthday'];

        if (!empty($constraints[$field->getName()]['validate'])) {
            $field->addConstraint(
                $constraints[$field->getName()]['validate']
            );
        }

        return $format;
    }
}
