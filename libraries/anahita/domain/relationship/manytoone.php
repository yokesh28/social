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
 * @subpackage Relationship
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Many to one  or Belongs to Relationship
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Relationship
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnDomainRelationshipManytoone extends AnDomainRelationshipProperty implements AnDomainPropertySerializable
{	
	/**
	 * The name of the column(field) in the database that reprsents the 
	 * type for the polymorphic many to one relationships
	 * 
	 * @var AnDomainResourceColumn
	 */
	protected $_type_column  = null;
	
	/**
	 * The child column in the database that contains the reference value that uniquly
	 * identifies the parent object 
	 * 
	 * @var AnDomainResourceColumn
	 */
	protected $_child_column = null;

	/**
	 * Polymorphic
	 *
	 * @var boolean
	 */
	protected $_polymorphic  = false;
	
    /**
	 * Configurator
	 *
	 * @param KConfig $config Property Configuration 
	 * 
	 * @return void
	 */
	public function setConfig(KConfig $config)
	{
		parent::setConfig($config);
		
		$this->_type_column 	= $config->type_column;
			
		$this->_child_column	= $config->child_column;
		
		$this->_polymorphic		= $config->polymorphic === true;
		
		if ( !isset($this->_parent) )
			$this->_polymorphic = true;
	}

	/**
	 * Initializes the options for the object
	 *
	 * Called from {@link __construct()} as a first step of object instantiation.
	 *
	 * @param 	object 	An optional KConfig object with configuration options.
	 * @return 	void
	 */
	protected function _initialize(KConfig $config)
	{
	    //disable the chain for the belongs to relationship
	    $config->append(array(	       
            'query'        => array('disable_chain'=>true)
	    ));
	
	    parent::_initialize($config);
	}
		
	/**
	 * Serialize an entity 
	 * 
	 * @param AnDomainEntityAbstract $entity
	 * @return array
	 */	
	public function serialize($entity)
	{
		if ( !is_null($entity) && is_scalar($entity) ) {
			throw new KException($this->getName().' be an instnaceof '.$this->getParent());
		}
		
		$data = array();
		
		$data[(string)$this->_child_column] = $entity ? $entity->get($this->_parent_key) : null;
		
		if ( $this->_polymorphic ) 
		{
			if  ( $entity instanceof KMixinAbstract )
					$entity = $entity->getMixer();
			
			$data[(string)$this->_type_column] = $entity ? (string)$entity->description()->getInheritanceColumnValue()->getIdentifier() : null;
		}

		return $data;
	}	
	
	/**
	 * Return whether the relationship is polymorphic
	 * 
	 * @return boolean
	 */
	public function isPolymorphic()
	{
		return $this->_polymorphic;
	}
	
	/**
	 * Returns the child column
	 * 
	 * @return AnDomainResourceColumn
	 */
	public function getChildColumn()
	{
		return $this->_child_column;	
	}
	
	/**
	 * Returns the type column in a polymorphic relationship
	 * 
	 * @return AnDomainResourceColumn
	 */
	public function getTypeColumn()
	{
		return $this->_type_column;	
	}
	
	/**
	 * Set an invere relationship. ie. if x belongs to y, then y has at least one x
	 * 
	 * @param array $config An array of configuration
	 *  
	 * @return void
	 */
	public function setInverse($config = array())
	{
	    $config = new KConfig($config);
	    
		if ( !isset($this->_parent) ) {
			throw new KException('Can not have an inverse relationship with a polymorphic parent');
		}
		
		$config->append(array(		        		   
		     'cardinality'  => 'many'
        ));

		$config->append(array(
		     'name'  => (int)$config->cardinality == 1 ? $this->_child->name : KInflector::pluralize($this->_child->name),		        
		));
		
		$name        = $config->name;
		$cardinality = $config->cardinality;
		unset($config['name']);unset($config['cardinality']);
		
		$config['child']         = $this->_child;
		$config['child_key']     = $this->_name;
		$config['type']          = 'has';
		$config['cardinality']   = $cardinality;
		
		$this->getParentRepository()->getDescription()
			->setRelationship($name, KConfig::unbox($config));
	}
	
	/**
	 * Return an array of the table fields(column)
	 * 
	 * @return array 
	 */
	public function getColumns()
	{
		$columns = array();
		$columns[$this->_parent_key] = $this->_child_column;		
		if ( $this->_polymorphic ) {
			$columns['type'] = $this->_type_column;
		}
		return $columns;
	}
		
	/**
	 * Materialize a many-to-one relationship for the entity and the data from 
	 * the database
	 * 
	 * @param AnDomainEntityAbstract $instance The entity whose relationship it's materializing for
	 * @param array                  $data     The row data
	 *  
	 * @return AnDomainProxyEntity
	 */
	public function materialize(array $data, $instance)
	{	
		if ( empty($data) ) {
			return null;
		}
		
		$child_key =  $this->_child_column->key();
			
		if ( !array_key_exists($child_key, $data) ) {
			throw new AnDomainExceptionMapping($this->getName().' Mapping Failed');
		}

		//get parent value
		$parent_value = $data[$child_key];
		
		//get parent class identifier
		//if relationsip is polymorphic then get the type from
		//the data
		if ( $this->_polymorphic ) 
		{
			$key   	= $this->_type_column->key();
			$parent	= isset($data[$key]) ? $data[$key] : null;
		} 
		else $parent = $this->_parent;

		//if any of the parent and parent value is missing then
		//nullify the relationship
		if ( empty($parent) || empty($parent_value) )
			return null;
        
		$config = array();
		
		$config['relationship'] = $this;
		$config['value']        = $parent_value;
		$config['property']     = $this->_parent_key;
		$config['service_identifier']   = AnDomain::getRepository($parent)->getDescription()->getEntityIdentifier();
				
		return new AnDomainEntityProxy(new KConfig($config));		
	}		
}