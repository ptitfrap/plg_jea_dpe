<?php
/**
 * This file is part of Joomla Estate Agency - Joomla! extension for real estate agency
 *
 * @version     $Id: script.php 257 2012-02-05 23:04:04Z ilhooq $
 * @package		Jea
 * @copyright	Copyright (C) 2008 PHILIP Sylvain. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 * Joomla Estate Agency is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
/**
 * Install Script file of JEA component
 */
class plgjeadpeInstallerScript
{
    /**
     * method to install the extension
     *
     * @return void
     */
    function install($parent)
    {
        if (!JFolder::exists(JPATH_ROOT . '/images/com_jea/dpe')) {
            JFolder::create(JPATH_ROOT . '/images/com_jea/dpe');
        }
    }

    /**
     * method to uninstall the extension
     *
     * @return void
     */
    function uninstall($parent)
    {
        if (JFolder::exists(JPATH_ROOT . '/images/com_jea/dpe')) {
            JFolder::delete(JPATH_ROOT . '/images/com_jea/dpe');
        }
    }

    /**
     * method to update the extension
     *
     * @return void
     */
    function update($parent)
    {

    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent)
    {
        $db = JFactory::getDbo();
        $db->setQuery('SHOW COLUMNS FROM #__jea_properties');
        $cols = $db->loadObjectList('Field');
        if(!isset($cols['dpe_energy']) && !isset($cols['dpe_ges'])){
            $query = 'ALTER TABLE `#__jea_properties` '
            . "ADD `dpe_energy` SMALLINT(4) NOT NULL DEFAULT '-1',"
            . "ADD `dpe_ges` SMALLINT(4) NOT NULL DEFAULT '-1'";

            $db->setQuery($query);
            $db->query();
        } elseif (isset($cols['dpe_energie']) && isset($cols['dpe_ges'])) {
            $query = 'ALTER TABLE `#__jea_properties` '
                   . "CHANGE `dpe_energie` `dpe_energy` SMALLINT(4) NOT NULL DEFAULT '-1',"
                   . "CHANGE `dpe_ges` `dpe_ges` SMALLINT(4) NOT NULL DEFAULT '-1'";
            $db->setQuery($query);
            $db->query();
        }
    }

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent)
    {

    }
}


