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

class plgJeaDpe extends JPlugin
{
    /**
     * Constructor
     *
     * @param       object  $subject The object to observe
     * @param       array   $config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * onBeforeSaveProperty method
     *
     * @param string $namespace
     * @param TableProperties $row
     * @param boolean $is_new
     * @return boolean
     */
    public function onBeforeSaveProperty($namespace, $row, $is_new)
    {
        $input = JFactory::getApplication()->input;
        if ($input->get('dpe_energy') || $input->get('dpe_ges')) {
            $row->dpe_energy = $input->getFloat('dpe_energy', -1.0);
            $row->dpe_ges    = $input->getFloat( 'dpe_ges', -1.0);
        }

        return true;
    }

    /**
     * onBeforeEndPane method (Called in the admin property form)
     *
     * @param TableProperties $row
     * @return void
     */
    public function onBeforeEndPanels(&$row)
    {
        if ($row->dpe_energy === null) {
            $row->dpe_energy = '-1';
        }

        if ($row->dpe_ges === null) {
            $row->dpe_ges = '-1';
        }

        $energyLabel = JText::_('PLG_JEA_DPE_ENERGY_CONSUMPTION');
        $energyDesc  = $energyLabel .'::'. JText::_('PLG_JEA_DPE_ENERGY_CONSUMPTION_DESC');
        $gesLabel = JText::_('PLG_JEA_DPE_EMISSIONS_GES');
        $gesDesc  = $gesLabel .'::'. JText::_('PLG_JEA_DPE_EMISSIONS_GES_DESC');

        $html ='
        <fieldset class="panelform">
          <ul class="adminformlist">
            <li>
              <label for="dpe_energy" class="hasTip" title="' . $energyDesc.'">' . $energyLabel .' : </label>
              <input type="text" name="dpe_energy" id="dpe_energy" value="'.$row->dpe_energy.'" class="numberbox" size="5" />
            </li>
            <li>
              <label for="dpe_ges" class="hasTip" title="' . $gesDesc.'">' . $gesLabel .' : </label>
              <input type="text" name="dpe_ges" id="dpe_ges" value="'.$row->dpe_ges.'" class="numberbox" size="5" />
            </li>
           </ul>
         </fieldset>';

        echo JHtml::_('sliders.panel', JText::_('PLG_JEA_DPE'), 'dpe-pane');
        echo $html;
    }


    /**
     * onAfterShowDescription method (called in the default_item.php tpl)
     *
     * @param stdClass $row
     */
    public function onAfterShowDescription(&$row)
    {
        if ($row->dpe_energy < 0 && $row->dpe_ges < 0) {
            return;
        }

        echo '<h3 class="jea_dpe">'. JText::_('PLG_JEA_DPE') .'</h3>'. PHP_EOL;

        echo '<div class="jea_dpe">'. PHP_EOL;
        echo getenv('GDFONTPATH');


        if ($row->dpe_energy >= 0) {
            try {
                $img = $this->__generateEnergyImage($row->dpe_energy);
                echo '<img src="' . $img . '" alt="'.JText::_('PLG_JEA_DPE_ENERGY_CONSUMPTION').'" style="margin-right: 50px;" />';
            } catch (Exception $e) {
                echo '<strong style="color:red">'. $e->getMessage() .'</strong>';
            }
        }

        if ($row->dpe_ges >= 0) {
            try {
                $img = $this->__generateGESImage($row->dpe_ges);
                echo '<img src="' . $img . '" alt="'.JText::_('PLG_JEA_DPE_EMISSIONS_GES').'" />';
            } catch (Exception $e) {
                echo '<strong style="color:red">'. $e->getMessage() .'</strong>';
            }
        }

        echo '</div>'. PHP_EOL;
    }

    
    /**
     * getDpeImage method
     *
     * @param stdClass $row
     *
     * @return array(dpe_energy Image Url , dpe_ges Image Url)
     */
    public function getDpeImage(&$row)
    {
    	if ($row->dpe_energy < 0 ) {
    		$row->dpe_energy = 0;
    	}
    	if ($row->dpe_ges < 0 ) {
    		$row->dpe_ges = 0;
    	}
    	if ($row->dpe_energy >= 0) {
    		try {
    			$img = $this->__generateEnergyImage($row->dpe_energy);
    			$energy = $img;
    		} catch (Exception $e) {
    			$energy = '';
    		}
    	}
    	 
    	if ($row->dpe_ges >= 0) {
    		try {
    			$img = $this->__generateGESImage($row->dpe_ges);
    			$ges = $img;
    		} catch (Exception $e) {
    			$ges = '';
    		}
    	}
    	 
    	return array($energy,$ges);
    	 
    }
    
    private function __generateEnergyImage($energy=0)
    {
        $lang = JFactory::getLanguage();
        $tag = $lang->getTag();

        $imagePath = JPATH_ROOT . '/images/com_jea/dpe/energy-'.$tag .'-'. $energy.'.png';
        $uri = JFactory::getUri();
        $imageURL = $uri->root(true) . '/images/com_jea/dpe/energy-'.$tag.'-'.$energy.'.png';

        if (!file_exists($imagePath)) {
            $levels = array(50,90,150,230,330,450);
            $labels = array(
                'measure'     => JText::_('PLG_JEA_DPE_ENERGY_MEASURE'),
                'top-left'    => JText::_('PLG_JEA_DPE_ENERGY_TOP_LEFT_LABEL'),
                'bottom-left' => JText::_('PLG_JEA_DPE_ENERGY_BOTTOM_LEFT_LABEL')
            );
            $this->__generateGDImage($energy, $imagePath, 'dpe-energy.png', $levels, $labels);
        }

        return $imageURL;
    }

    private function __generateGESImage($ges=0)
    {
        $lang = JFactory::getLanguage();
        $tag = $lang->getTag();

        $imagePath = JPATH_ROOT . '/images/com_jea/dpe/ges-'.$tag .'-'.$ges.'.png';
        $uri = JFactory::getUri();
        $imageURL = $uri->root(true) . '/images/com_jea/dpe/ges-'.$tag.'-'.$ges.'.png';

        if (!file_exists($imagePath)) {
            $levels = array(5,10,20,35,55,80);
            $labels = array(
                'measure'     => JText::_('PLG_JEA_DPE_GES_MEASURE'),
                'top-left'    => JText::_('PLG_JEA_DPE_GES_TOP_LEFT_LABEL'),
                'bottom-left' => JText::_('PLG_JEA_DPE_GES_BOTTOM_LEFT_LABEL')
            );
            $this->__generateGDImage($ges, $imagePath, 'dpe-ges.png', $levels, $labels);
        }

        return $imageURL;
    }

    private function __generateGDImage($dpeValue, $imagePath, $imageModel, $levels, $labels=array())
    {
        $currentLevel = 0;
        $imgWidth = 300;
        $imgHeiht = 260;
        $fontFile = JPATH_ROOT.'/plugins/jea/dpe/fonts/DejaVuSans.ttf';
        $fontBoldFile = JPATH_ROOT.'/plugins/jea/dpe/fonts/DejaVuSans-Bold.ttf';

        foreach ($levels as $level => $value) {
            if ($dpeValue <= $value) {
                $currentLevel = $level;
                break;
            }
        }

        if ($currentLevel == 0 && $dpeValue > $levels[count($levels)-1]) {
            $currentLevel = 6;
        }

        $img = @imagecreatetruecolor($imgWidth, $imgHeiht);

        if (!$img) {
            throw new Exception('Cannot create a GD image stream');
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $grey = imagecolorallocate($img, 200, 200, 200);
        $grey2 = imagecolorallocate($img, 40, 40, 40);
        imagefill($img, 0, 0, $white);

        $arrowImg = @imagecreatefrompng(JPATH_ROOT.'/plugins/jea/dpe/images/arrow.png');
        $imgModel   = @imagecreatefrompng(JPATH_ROOT.'/plugins/jea/dpe/images/'.$imageModel);

        // Where the img model start from Y
        $destY = ceil(($imgHeiht - imagesy($imgModel)) / 2);

        $dpeY = $destY;

        if ($currentLevel == 6) {
            $dpeY += imagesy($imgModel) - 15;
        } else {
            $dpeY += $currentLevel * 33; // 30 px height per level + 3px margin
            // Adjust now y between the levels limits
            $start = 0;
            $end = $levels[$currentLevel];
            if (isset($levels[$currentLevel-1])) {
                $start = $levels[$currentLevel-1] + 1;
            }

            $dpeY += floor(($dpeValue - $start) * 30 / ($end - $start));
        }

        // Draw horizontal line
        imageline($img, 0, $dpeY, $imgWidth, $dpeY, $grey);

        // Draw vertical line
        imageline($img, 220, 0, 220, $imgHeiht, $grey2);

        // Copy the image model
        imagecopy($img, $imgModel, 0, $destY, 0, 0, imagesx($imgModel), imagesy($imgModel));
        $destX = $imgWidth - imagesx($arrowImg);
        $destY = $dpeY - (imagesy($arrowImg) / 2);
        imagecopy($img, $arrowImg, $destX, $destY, 0, 0, imagesx($arrowImg), imagesy($arrowImg));

        // Add the value
        imagettftext($img, 11, 0 ,$destX+18 ,$destY+20 ,$white ,$fontBoldFile ,$dpeValue);

        // Add the measure
        if (isset($labels['measure'])) {
            $box = imagettfbbox(7, 0, $fontFile, $labels['measure']);
            $x = $box[4] - $box[0];
            imagettftext($img, 7, 0, $imgWidth-$x ,$destY+41 ,$grey2 ,$fontFile , $labels['measure']);
        }

        // Add text to top left
        if (isset($labels['top-left'])) {
            imagettftext($img, 8, 0 ,0 ,9 ,$grey2 ,$fontFile , $labels['top-left']);
        }

        // Add text to top right 
        imagettftext($img, 8, 0 ,230 ,9 ,$grey2 ,$fontFile , JText::_('PLG_JEA_DPE_TOP_RIGHT_LABEL'));

        // Add text to bottom left
        if (isset($labels['bottom-left'])) {
            imagettftext($img, 8, 0 ,0 , $imgHeiht-3 ,$grey2 ,$fontFile , $labels['bottom-left']);
        }

        $ret = @imagepng($img, $imagePath);

        imagedestroy($img);
        imagedestroy($arrowImg);
        imagedestroy($imgModel);

        if (!$ret) {
            throw new Exception('Cannot save image : '. $imagePath);
        }
    }

}
