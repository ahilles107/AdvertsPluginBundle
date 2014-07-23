<?php

/*
 * This file is part of the Adverts Plugin.
 *
 * (c) Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 */

/**
 * Newscoop list_categories block plugin
 *
 * Type:     block
 * Name:     list_categories
 *
 * @param array $params
 * @param mixed $content
 * @param object $smarty
 * @param bool $repeat
 * @return string
 */
function smarty_block_list_categories($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');
    $paginatorService = \Zend_Registry::get('container')->get('newscoop.listpaginator.service');
    $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');

    if (!isset($content)) { // init
        $start = $context->next_list_start('AHS\AdvertsPluginBundle\TemplateList\CategoriesList');
        $list = new \AHS\AdvertsPluginBundle\TemplateList\CategoriesList(
            new \AHS\AdvertsPluginBundle\TemplateList\CategoryCriteria(),
            $paginatorService,
            $cacheService
        );

        $list->setPageParameterName($context->next_list_id($context->getListName($list)));
        $list->setPageNumber(\Zend_Registry::get('container')->get('request')->get($list->getPageParameterName(), 1));

        $list->getList($start, $params);
        if ($list->isEmpty()) {
            $context->setCurrentList($list, array());
            $context->resetCurrentList();
            $repeat = false;

            return null;
        }

        $context->setCurrentList($list, array('anouncements_category', 'pagination'));
        $context->anouncements_category = $context->current_categories_list->current;
        $repeat = true;
    } else { // next
        $context->current_categories_list->defaultIterator()->next();
        if (!is_null($context->current_categories_list->current)) {
            $context->anouncements_category = $context->current_categories_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
