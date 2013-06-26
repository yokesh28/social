<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Anahita_Engine
 * @subpackage Validator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Abstract Validator
 *
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Validator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
abstract class AnDomainValidatorAbstract extends KObject 
{       
    /**
     * Validations 
     * 
     * @var KConfig
     */
    protected $_validations;

    /**
     * Filters
     * 
     * @var array
     */
    protected $_filters;
    
    /**
     * Entity Description
     * 
     * @var AnDomainDescriptionAbstract
     */
    protected $_description;
    
    /**
     * Constructor.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     *
     * @return void
     */
    public function __construct(KConfig $config)
    {
        $this->_description = $config->description;
        
        parent::__construct($config);
        
        $this->_validations     = $config->validations;      
        $this->_filters         = array();
    }
    
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
        $description = $this->_description;
        
        $properties  = $description->getProperty();
        
        $validations = array();
        
        foreach($properties as $property)
        {                        
            if ( $property->isAttribute() && $property->getFormat() )
                $validations[$property->getName()]['format'] = $property->getFormat();
            
            if ( $property->isSerializable() && $property->isRequired() )
                $validations[$property->getName()]['required'] = true;                
            
            if ( $property->isSerializable() && $property->isUnique() )
                $validations[$property->getName()]['uniqueness'] = array('scope'=>array());
        }
        
        $config->append(array(            
            'validations' => $validations
        ));
        
        parent::_initialize($config);
    }    
    
    /**
     * Sanitize an entity property value against an array of passed validations
     *
     * @param AnDomainEntityAbstract $entity      Entity 
     * @param string                 $property    Property Name
     * @param array                  $value       Property Value   
     * @param array                  $validations An array of validations. If no validation are passed. Default validations are used
     * 
     * @return boolean
     */
    public function sanitizeData($entity, $property, $value, $validations = array())
    {       
        if ( is_string($property) ) 
            $property = $this->_description->getProperty($property);   
        
        if ( empty($validations) )        
            $validations = $this->getValidations($property);
        
        $validations = (array) KConfig::unbox($validations);
        
        foreach($validations as $validation => $options) 
        {
            if ( is_numeric($validation) ) 
            {
                $validation = $options;
                $options    = array();
            }
            
            $method = '_sanitize'.ucfirst($validation);
            
            if ( !method_exists($this, $method) )
                continue;            
                        
            $config = new KConfig(array(
                'property' => $property,
                'value'    => $value,
                'options'  => $options            
            ));
                      
            $value = $this->$method($config);
        }
        
        return $value;
    }    
            
    /**
     * Validates an entity property value against an array of passed validations
     *
     * @param AnDomainEntityAbstract $entity      Entity 
     * @param string                 $property    Property Name
     * @param array                  $value       Property Value   
     * @param array                  $validations An array of validations. If no validation are passed. Default validations are used
     * 
     * @return boolean
     */
    public function validateData($entity, $property, $value, $validations = array())
    {
        if ( is_string($property) ) 
            $property = $this->_description->getProperty($property);   
        
        if ( empty($validations) )        
            $validations = $this->getValidations($property);
        
        $validations = (array) KConfig::unbox($validations);
        
        foreach($validations as $validation => $options)
        {
             if ( is_numeric($validation) ) {
                 $validation = $options;
                 $options    = array();
             }
             
             $method = '_validate'.ucfirst($validation);
             
             if ( !method_exists($this, $method) )
                 continue;                                  
             
             $config = new KConfig(array(
                 'property' => $property,
                 'value'    => $value,
                 'entity'   => $entity,
                 'options'  => $options
             ));
             
             if ( $this->$method($config) === false )                  
                 return false;
        }
        
        return true;
    }
    
    /**
     * Adds a property validation. It overwrites an existing validation with the same name
     * 
     * @param string $property   The property to validate
     * @param string $validation The validation name
     * @param array  $options    Validation options
     * 
     * @return void
     */
    public function addValidation($property, $validation, $options = array())
    {
        $validations = $this->getValidations($property);        
        $validations[$validation] = $options;
        return $this;
    }
    
    /**
     * Return an array of validations for a property
     * 
     * @param AnDomainPropertyAbstract|string $property Property
     * 
     * @return KConfig
     */
    public function getValidations($property)
    {
        if ( is_string($property) ) 
        {
            $property = $this->_description->getProperty($property);   
        }
        
        $name = $property->getName();
        
        if ( !$this->_validations->$name ) {
            $this->_validations->$name = new KConfig();
        }
        
        return $this->_validations->$name;
    }
    
    /**
     * Return a filter object
     *
     * @param string $filter Filter name
     * 
     * @return KFilterChain
     */
    public function getFilter($filter)
    {
        if ( !$filter instanceof KFilterAbstract)
        {
            $filter = (string) $filter;
                            
            if ( !isset($this->_filters[$filter]) )
            {
                if ( is_string($filter) && strpos($filter,'.') === false ) 
                {
                    $identifier = clone $this->getIdentifier();                               
                    $identifier->path = array('filter');
                    $identifier->name = $filter;
                    register_default(array('identifier'=>$identifier,'prefix'=>$this));
                }
                else 
                    $identifier = $this->getIdentifier($filter);
                
                $this->_filters[$filter] = $this->getService($identifier);
            }
            
            $filter = $this->_filters[$filter];            
        }
        
        return $filter; 
    }
    
    /**
     * Sanitize length
     *
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _sanitizeLength(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $options  = KConfig::unbox($config->options);
        //if a number is just passed then treat it as max
        if ( !is_array($options) ) {
            $options = array('max'=>$options);
        }
        
        if ( $property->isAttribute() && $property->isScalar() && isset($options['max'])) {
            $value = LibBaseTemplateHelperText::truncate($value, array('length'=>$options['max'], 'consider_html'=>true, 'ending'=>''));
        }
        
        return $value;        
    }
    
    /**
     * Sanitizes format of a property using a KFilter
     *
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */   
    protected function _sanitizeFormat(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $filter   = $config->options;
                
        if ( !empty($value) && $property->isAttribute() && $property->isScalar() )
        {            
            $value = $this->getFilter($filter)->sanitize($value);
        }
                
        return $value;
    }
    
    /**
     * Validate format of a property using a KFilter
     * 
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _validateFormat(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $entity   = $config->entity;
        $filter   = $config->options;
      
        if ( !empty($value) && $property->isAttribute() && $property->isScalar() )
        {
            if ( $this->getFilter($filter)->validate($value) === false ) 
            {
                $entity->setError($property->getName().' must have the format of '.$filter);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate scope of a property
     * 
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _validateScope(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $entity   = $config->entity;
        $options  = KConfig::unbox($config->options);
                
        if ( !in_array($value, $options) ) {
            $entity->setError($property->getName().' must be one of the value of '.implode($options, ','));
            return false;
        }
        return true;
    }
    
    /**
     * Validate precense
     *
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _validateRequired(KConfig $config)
    {
        $entity   = $config->entity;
        $property = $config->property;
        $value    = KConfig::unbox($config['value']);
        $present  = true;
        
        if ( $entity->state() & AnDomain::STATE_DELETED )
            return true;
        
        //if the serial id is missing for a new entity, then don't validate
        if ( $entity->state() == AnDomain::STATE_NEW && $property === $entity->description()->getIdentityProperty() )
            return true;
        
        if ( $property->isAttribute() )
        {
            if ( $property->getType() == 'string')
            {
                //check if the value exists
                if ( KHelperString::strlen($value) <= 0 || ctype_space($value) ) {
                    $entity->setError(sprintf('%s %s can not be empty', $entity->getIdentifier()->name, $property->getName()));
                    return false;
                }
            }
            else 
            {
                $present = $value !== null;
            }
        }
        elseif ( $property->isRelationship() && $property->isManyToOne() )
        {
            $present = !is_null($value);
            //if not null and the entity state is not new then check if it's serilized values are acceptable
            //i.e. not null or having an id = 0
            //is to prevent having non null mock objects. i.e. viewer as a guest
            //or an empty entity
            if ( $present && $value->state() != AnDomain::STATE_NEW )
            {
                //check if the many to one object is null or not
                $values  = $property->serialize($value);
                foreach($values as $value)
                {
                    if ( !$value ) {
                        $present = false;
                        break;
                    }
                }
            }
        }
        
        if ( !$present ) {
            $entity->setError(sprintf('%s %s can not be empty', $entity->getIdentifier()->name, $property->getName()));
            return false;
        }
    }
     
    /**
     * Validate length
     * 
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _validateLength(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $entity   = $config->entity;
        $options  = KConfig::unbox($config->options);
        //if a number is just passed then treat it as max
        if ( !is_array($options) ) {
            $options = array('max'=>$options);
        }
                
        if ( $property->isAttribute() && $property->isScalar() )
        {            
            $options = KConfig::unbox($options);
            if ( is_array($options) ) 
            {           
                //check the min/max length
                if ( isset($options['max']) || isset($options['min']) )
                {
                    if ( isset($options['max']) )
                    {
                        $greater  = KHelperString::strlen($value) > (int)$options['max'];
                        if ( $greater )
                        {
                            $entity->setError(sprintf('%s %s can not be greater than %d characters', $this->getIdentifier()->name, $property->getName(), $options['max']));
                            return false;
                        }
                    }
                    if ( isset($options['min']) )
                    {
                        $lesser  = KHelperString::strlen($value) < (int)$options['min'];
                
                        if ( $lesser )
                        {
                            $entity->setError(sprintf('%s %s can not be less than %d characters', $this->getIdentifier()->name, $property->getName(), $options['min']));
                            return false;
                        }
                    }
                } 
           } 
           else 
           {
               if ( KHelperString::strlen($value) != (int) $options ) {
                   $entity->setError(sprintf('%s %s must be %d characters', $this->getIdentifier()->name, $property->getName(), $options));
                   return false;
               }
           }
        }
    }
    
    /**
     * Validate uniquess
     * 
     * @param KConfig $config Configuration. Contains keys property,value,entity 
     * 
     * @return boolean Return true if it's valid or false if it's not
     */
    protected function _validateUniqueness(KConfig $config)
    {
        $property = $config->property;
        $value    = $config->value;
        $entity   = $config->entity;
        $options  = $config->options;
                
        $conditions = array();
        
        $query		= $entity->getRepository()->getQuery();
        
        if ( $entity->persisted() )
            $query->where($entity->description()->getIdentityProperty()->getName(),'<>',$entity->getIdentityId());
        
        $conditions[$property->getName()] = $value;
        
        if ( isset($options['scope']) ) {
            $scope = (array)$options['scope'];
            foreach($scope as $key)
                $conditions[$key] = $entity->get($key);
        }
        
        $query->where($conditions);
        
        if ( $query->disableChain()->fetch() ) {
            $entity->setError('Uniquness validation failed for the '.$entity->getIdentifier()->name.'.'.$property->getName());
            return false;
        }
    }
}