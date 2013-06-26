<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Domain_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Expirable Behavior 
 * 
 * It provides a timeframe for an entity with start date and end date
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Domain_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibBaseDomainBehaviorExpirable extends AnDomainBehaviorAbstract
{			
	/**
	 * Initializes the default configuration for the object
	 *
	 * Called from {@link __construct()} as a first step of object instantiation.
	 *
	 * @param KConfig $config An optional KConfig object with configuration options.
	 *
	 * @return void
	 */
	protected function _initialize(KConfig $config)
	{
		$config->append(array(
			'attributes' => array(
				'startDate' => array('required'=>true, 'type'=>'date', 'default'=>'date'),
				'endDate'	=> array('required'=>true, 'type'=>'date')
			)
		));
		
		parent::_initialize($config);
	}
	
	/**
	 * Sets the end date of an expirable
	 *
	 * @param  AnDomainAttributeDate|KDate|array $date The end date
	 * 
	 * @return void
	 */
	public function setEndDate($date)
	{
		$date = AnDomainAttributeDate::getInstance()->setDate($date);
		$this->set('endDate', $date);
		return $this;
	}
	
	/**
	 * Sets the start date of an expirable
	 *
	 * @param  AnDomainAttributeDate|KDate|array $date The start date
	 * 
	 * @return void
	 */
	public function setStartDate($date)
	{
		$date = AnDomainAttributeDate::getInstance()->setDate($date);
		$this->set('startDate', $date);
		return $this;
	}	
	
}

?>