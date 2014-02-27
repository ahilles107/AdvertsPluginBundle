<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Newscoop list_announcements block plugin
 *
 * Type:     block
 * Name:     list_announcements
 *
 * @param array $params
 * @param mixed $content
 * @param object $smarty
 * @param bool $repeat
 * @return string
 */
function smarty_block_list_announcements($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');
    $paginatorService = \Zend_Registry::get('container')->get('newscoop.listpaginator.service');
    $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');

    if (!isset($content)) { // init
        $start = $context->next_list_start('AHS\AdvertsPluginBundle\TemplateList\AnnouncementsList');
        $list = new \AHS\AdvertsPluginBundle\TemplateList\AnnouncementsList(
            new \AHS\AdvertsPluginBundle\TemplateList\AnnouncementCriteria(),
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

        $context->setCurrentList($list, array('announcement', 'pagination'));
        $context->announcement = $context->current_announcements_list->current;
        $repeat = true;
    } else { // next
        $context->current_announcements_list->defaultIterator()->next();
        if (!is_null($context->current_announcements_list->current)) {
            $context->announcement = $context->current_announcements_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
