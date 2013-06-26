<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Edge represent a connection between two nodes. An edge can be used any context but it 
 * must be created through a social application
 *
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseDomainEntityEdge extends AnDomainEntityDefault
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
		    'abstract_identifier' => 'com:base.domain.entity.edge', //edge is an abstract entity, can not be stored in database
		    'inheritance_column'  => 'type',
			'resources'           => array(
				array('name'=>'anahita_edges', 'alias'=>$this->getIdentifier()->name)
			),
			'attributes' => array(
				'id' 				=> array('key'=>true)
			),
			'behaviors' => array(
				'modifiable'
			),		
			'relationships' => array(				
				'nodeA' 	 => array('required' =>true,  'polymorphic'=>true, 'parent'=>'com:base.domain.entity.node'),
				'nodeB' 	 => array('required' =>true,  'polymorphic'=>true, 'parent'=>'com:base.domain.entity.node')			
			)
		));
		
		return parent::_initialize($config);
	}
	
	/**
	 * Validates an entity 
	 *
	 * @param  KCommandContext $context
	 * @return void
	 */
	protected function _validateInsert(KCommandContext $context)
	{
		//@TODO temporary move it to a repository validators ??
		if ( $this->nodeA->id == $this->nodeB->id ) {
			return false;
		}
	}
}