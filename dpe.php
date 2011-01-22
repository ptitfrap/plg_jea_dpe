<?php
/**
 * This file is part of Joomla Estate Agency - Joomla! extension for real estate agency
 * 
 * @package		Jea
 * @copyright	Copyright (C) 2011 PHILIP Sylvain. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 * Joomla Estate Agency is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 * 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );



jimport('joomla.event.plugin');
JPlugin::loadLanguage('plg_jea_dpe', JPATH_ADMINISTRATOR);

class plgJeaDpe extends JPlugin
{

    /**
     * onInitTableProperty Method
     *
     * @param TableProperties $table
     */
    function onInitTableProperty(&$table)
    {
        $db =& JFactory::getDBO();
        
        $db->setQuery('SHOW COLUMNS FROM #__jea_properties');
        $cols = $db->loadObjectList('Field');
        if(!isset($cols['dpe_energie']) && !isset($cols['dpe_ges'])){
            $query = 'ALTER TABLE `#__jea_properties` '
                   . "ADD `dpe_energie` ENUM('0','A','B','C','D','E','F','G') NOT NULL DEFAULT '0',"
                   . "ADD `dpe_ges`     ENUM('0','A','B','C','D','E','F','G') NOT NULL DEFAULT '0'";

            $db->setQuery($query);
            $db->query();
        }
        $table->dpe_energie = null;
        $table->dpe_ges = null;
    }
    
    /**
     * onBeforeSaveProperty method
     *
     * @param TableProperties $row
     * @param boolean $is_new
     * @return boolean
     */
    function onBeforeSaveProperty(&$row, $is_new)
    {
        $row->dpe_energie = JRequest::getVar( 'dpe_energie', '', 'POST' );
        $row->dpe_ges     = JRequest::getVar( 'dpe_ges', '', 'POST' );
        return true;
    }
    
    

    /**
     * onBeforeEndPane method (Called in the admin property form)
     *
     * @param JPane $pane
     * @param TableProperties $row
     */
    function onBeforeEndPane(&$pane, &$row)
    {
        $list = array();
        $list[] = JHTML::_('select.option', '0', '-- ' . JText::_('Select class') . ' --' );
        $list[] = JHTML::_('select.option', 'A', 'A' );
        $list[] = JHTML::_('select.option', 'B', 'B' );
        $list[] = JHTML::_('select.option', 'C', 'C' );
        $list[] = JHTML::_('select.option', 'D', 'D' );
        $list[] = JHTML::_('select.option', 'E', 'E' );
        $list[] = JHTML::_('select.option', 'F', 'F' );
        $list[] = JHTML::_('select.option', 'G', 'G' );

        $energie_list = JHTML::_('select.genericlist', $list, 'dpe_energie', 'class="inputbox" size="1" ', 'value', 'text', $row->dpe_energie );
        $ges_list = JHTML::_('select.genericlist', $list, 'dpe_ges', 'class="inputbox" size="1" ', 'value', 'text', $row->dpe_ges );

        $html ='
         <table>
          <tr>
    		<td class="label" ><label for="energie_dpe">' . JText::_('Consommation energetique') .' : </label></td>
    		<td>'.$energie_list.'</td>
    	  </tr>
    	  
    	  <tr>
    		<td class="label"><label for="energie_dpe">' . JText::_('Emissions GES') .' : </label></td>
    		<td>'.$ges_list.'</td>
    	  </tr>
			
    	</table>';

        echo $pane->startPanel( JText::_('DPE') , "dpe-pane" );
        echo $html;
        echo $pane->endPanel();

    }

    
    /**
     * onAfterShowDescription method (called in the default_item.php tpl)
     *
     * @param stdClass $row
     */
    function onAfterShowDescription(&$row)
    {
        if (empty($row->dpe_energie) && empty($row->dpe_ges)) {
            return; 
        }
        
        echo '<h3 class="jea_dpe">'. JText::_('DPE') .'</h3>'. PHP_EOL;
        
        echo '<div class="jea_dpe">'. PHP_EOL;
             
        if(!empty($row->dpe_energie)) {
            echo JHTML::_('image.site', 'energie_'.$row->dpe_energie.'.gif', '/plugins/jea/dpe/images/');
        }
             
        if(!empty($row->dpe_ges)) {
            echo JHTML::_('image.site', 'ges_'.$row->dpe_ges.'.gif', '/plugins/jea/dpe/images/');
        }
        
        echo '</div>'. PHP_EOL;
    }

}
