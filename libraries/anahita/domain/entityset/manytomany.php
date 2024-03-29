<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Entityset
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * One to many aggregated entityset
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Entityset
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnDomainEntitysetManytomany extends AnDomainEntitysetOnetomany
{	
	/**
	 * Child Repository
	 * 
	 * @var Repository
	 */
	protected $_child;

	/**
	 * Target Repository
	 * 
	 * @var Repository
	 */
	protected $_target_property;	
	
	/**
	 * Constructor.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 */
	public function __construct(KConfig $config)
	{
		$this->_child = $config->child;
		
		$this->_target_property = $config->target_property;
		
		parent::__construct($config);
	}
	
	/**
	 * Find an entity within this collection
	 * 
	 * @param  AnDomainEntityAbstract|array $entity
	 * @return AnDomainEntityAbstract 
	 */
	public function find($entity)
	{
		$conditions = array();
		
		if ( is_array($entity) ) {
			foreach($entity as $key => $value) {
				$conditions[$this->_target_property.'.'.$key] = $value;
			}
		}		
		else $conditions[$this->_target_property] = $entity;
		
		$conditions[$this->_property] 		 = $this->_root;
		
		return $this->_child->find($conditions);
	}
		
	/**
	 * Return an entity of the aggregated type and set the initial 
	 * property
	 * 
	 * @param  array $data
	 * @param  array $config Extra configuation for instantiating the object
	 * @return AnDomainEntityAbstract
	 */
	public function findOrCreate($data = array(), $config = array())
	{
		$target = $this->getRepository()->find($data);
		
		if ( !$target )
			$target = $this->create($data, $config);
		else	
			$this->insert($target, $config);
		
		return $target;
	}
	
	/**
	 * Return an entity of the aggregated type and set the initial 
	 * property
	 * 
	 * @param  array $data
	 * @param  array $config Extra configuation for instantiating the object
	 * @return AnDomainEntityAbstract
	 */
	public function create($data = array(), $config = array())
	{
		$config = new KConfig($config);
		
		$config->append(array(
			'data' 			=> $data ,
			'relationship'	=> array()
		));
		
		$entity = $this->getRepository()->getEntity($config);
		
		$this->insert($entity, $config->relationship);
		
		return $entity;
	}	

	/**
	 * Insert an entity to the aggregation. If multiple target is passed then
	 * add the all of them to the collection. It prevents adding the same
	 * entity into the its existing collection
	 * 
	 * @param   AnDomainEntityAbstract|array $target
	 * @param 	array $config
	 * @return  AnDomainEntityAbstract
	 */
	public function insert($target, $config = array())
	{
		if ( AnHelperArray::isIterable($target) ) 
		{
			$targets    = AnHelperArray::unique($target);
			$relations  = new AnObjectSet();
			foreach($target as $target)
				$relations->insert($this->insert($target, $config));
			
			return $relations;
		}
		
		$data   = array(
				$this->_property 		=> $this->_root,
				$this->_target_property	=> $target
			);
		
		//shouldn't be able to add the same entity into  the same collection		
		$relation = $this->_child->findOrCreate($data, $config);
				 
		return $relation;
	}

	/**
	 * Removes an object relation from the aggregation
	 * 
	 * @see KObjectSet::extract()
	 */
    public function extract($target)
    {
    	$relation = $this->_child->find(array(
				$this->_property 		=> $this->_root,
				$this->_target_property	=> $target    	
    		));    		
    	
    	if ( $relation ) {
    		$relation->delete();	
    	}
    	
    	return $relation;
    }
    
    /**
     * Overwrite the entity default destroy behavior. Deletes all the relations that connects the aggregate root to the
     * target entities. Dose not delete the targets !
     * 
     * @return void
     */
    public function destroy()
    {
        foreach($this as $target) {
            $this->extract($target);
        }
        
        return $this->_repository->getSpace()->commit();   
    }
        
    /**
     * Overwrite the entity default delete behavior. Deletes all the relations that connects the aggregate root to the
     * target entities. Dose not delete the targets !
     * 
     * @return void
     */
    public function delete()
    {
        foreach($this as $target) {
            $this->extract($target);
        }
    }
}