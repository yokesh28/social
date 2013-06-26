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
 * A Queriable entityset. If no data is set then the queriable data
 * will wait until one of the iteration mehtod is called to load 
 * the data
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Entityset
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnDomainEntitysetDefault extends AnDomainEntityset
{
	/**
	 * Check if data has been loaded into the set
	 *  
	 * @var boolean
	 */
	protected $_loaded = false;
	
	/**
	 * The query that represents this entity set
	 * 
	 * @var AnDomainQuery
	 */
	protected $_set_query;
	
	/**
	 * Query Object
	 * 
	 * @var AnDomainQuery
	 */
	protected $_query;
	
	/**
	 * Constructor.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 */
	public function __construct(KConfig $config)
	{
		$this->_query = $config->query;
				
		parent::__construct($config);
		
		//if a data is passed then add the data		
		if ( $config->data ) {
			$this->_loaded = true;
			foreach($config->data as $entity) $this->insert($entity);
		}
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
		parent::_initialize($config);
	}

	/**
	 * Revert the query back to the original
	 * 
	 * @return AnDomainQuery
	 */
	public function reset()
	{
		$this->_set_query  = null;
		$this->_loaded     = false;
		$this->_object_set = new ArrayObject();
		
		return $this;
	}
	
	/**
	 * Return the entityset query. If $clone is passed it will return a clone instance of the entityset
	 * query is returned
	 * 
	 * @param boolean $clone If set to true then it will return a new clone instance of entityset
	 * 
	 * @return AnDomainQuery
	 */
	public function getQuery($clone = false)
	{
		if ( !isset($this->_set_query) || $clone ) 
		{
			if ( $this->_query instanceof AnDomainQuery )
			{
			    $query = clone $this->_query;
			}
			else 
			{
				$query = $this->_repository->getQuery();
				AnDomainQueryHelper::applyFilters($query, $this->_query);
			}
			
			//if clone is set, then return the qury object 
			if  ( $clone ) {
			    return $query;
			}
			//if not then set the entity query object    
			$this->_set_query = $query;
		}
		
		return $this->_set_query;
	}
	
	/**
	 * Return the total number of entities that match the entityset query
	 * 
	 * @return int
	 */
	public function getTotal()
	{
        $query = clone $this->getQuery();
        $query->order = null;
        return $query->fetchCount();
	}
	
	/**
	 * Returns the set limit
	 *
	 * @return int
	 */
	public function getLimit()
	{
	    return $this->getQuery()->limit;
	}
	
	/**
	 * Returns the set offset
	 *
	 * @return int
	 */
	public function getOffset()
	{
	    return $this->getQuery()->offset;
	}	
	
	/**
	 * If the missed method is implemented by the query object then delegate the call to the query object
	 * 
	 * @see KObject::__call()
	 */
    public function __call($method, array $arguments)
    {
        $parts = KInflector::explode($method);
        
        if ( $parts[0] == 'is' && isset($parts[1]) ) 
        {
            $behavior = lcfirst(substr($method, 2));
            return $this->_repository->hasBehavior($behavior);
        }

        //forward a call to the query
    	if ( method_exists($this->getQuery(), $method) || !$this->_repository->entityMethodExists($method) ) 
    	{
    		$result = call_object_method($this->getQuery(), $method, $arguments);
    		if ( $result instanceof AnDomainQuery )
    			$result = $this;
    	}
    	else 
    	{    	        	   
    	    $result = parent::__call($method, $arguments);
    	}
    	return $result;
    }
    
	/**
	 * Get an aggregated set of values from the entityset
	 * 
	 * @param  string $column 
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function __get($column)
	{			
		$this->_load();
		return parent::__get($column);
	}
	
	/**
	 * Set a property for each individual entity
	 * 
	 * @param  string $column 
	 * @param  mixed  $value
	 * @return AnDomainEntitysetDefault
	 */
	public function __set($column, $value)
	{
		$this->_load();
		return parent::__set($column, $value);	
	}
	
	/**
	 * Count Data
	 * 
	 * @return int
	 */
    public function count()
    {
    	$this->_load();		
    	return parent::count();
    }
    
 	/**
     * Rewind the Iterator to the first element
     *
     * @return	void
     */
	public function rewind() 
	{
		$this->_load();
		return parent::rewind();
	} 
	
	/**
     * Checks if current position is valid
     *
     * @return	boolean
     */
	public function valid() 
	{
		$this->_load();
		return parent::valid();
	} 
	
	/**
     * Return the key of the current element
     *
     * @return	scalar
     */
	public function key() 
	{
		$this->_load();
		return parent::key();
	} 
	
	/**
     * Return the current element
     *
     * @return	mixed
     */
	public function current() 
	{
		$this->_load();
		return parent::current();
	} 
	
	/**
     * Move forward to next element
     *
     * @return	void
     */
	public function next() 
	{
		$this->_load();
		return parent::next();
	}	
	
	/**
	 * Load the entities before getting an entity of offset
	 * @return 
	 * @param $offset Object
	 */
	public function offsetGet($offset)
	{
        $this->_load();
        return parent::offsetGet($offset);
	}	
	
	
	/**
	 * Returns the iterator
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{	
		$this->_load();
		return parent::getIterator();
	}  
	
	/**
	 * Destroy a collection of entities
	 * 
	 * @return void
	 */
	public function destroy()
	{
		$this->delete();
		return $this->_repository->getSpace()->commit();
	}
	
	/**
	 * Deletes all the entities
	 * 
	 * @return void
	 */
	public function delete()
	{
		return parent::__call('delete');
	}	

	/**
     * Return an associative array of the data.
     *
     * @return array
     */
    public function toArray()
    {
		$this->_load();
		return parent::toArray();
    }
    		
	/**
	 * Loads the data into the object set using the query if not already loaded
	 * 
	 * @return void
	 */
	protected function _load()
	{
		if ( $this->_loaded )
			return;
			
		if ( isset($this->_object_set) && $this->_object_set->count() > 0 ) {
			$this->_loaded = true;
			return;
		}
		
		$this->_loaded = true;
		
		$repository = $this->_repository;
		
		//get an array list instead of another entityset
		$data		= $repository->fetch($this->getQuery(), AnDomain::FETCH_ENTITY_LIST);
		if ( !$data ) 
			$data = array();
		$this->_object_set = new ArrayObject($data);		
	}
}