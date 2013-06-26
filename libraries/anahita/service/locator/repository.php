<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Anahita_Service
 * @subpackage Locator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Repositroy locator is KService locator to return repository objects of any entities.  
 * 
 * The format of the identifier must be repos:[//application/]<Component Name>.<Entity Name>. This will translate
 * to KService::get(com:[//application/]<Component Name>.domain.entity.<Entity Name>
 *
 * @category   Anahita
 * @package    Anahita_Service
 * @subpackage Locator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnServiceLocatorRepository extends KServiceLocatorAbstract implements KServiceInstantiatable
{
    /**
     * Return 
     *
     * @param KConfigInterface 	$config    An optional KConfig object with configuration options
     * @param KServiceInterface	$container A KServiceInterface object
     *
     * @return KServiceInstantiatable
     */
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        if (!$container->has($config->service_identifier))
        {
            $identifier = self::_identifier($config->service_identifier);
            $instance   = AnDomain::getRepository($identifier, $config->toArray());
            $container->set($config->service_identifier, $instance);
        }
    
        return $container->get($config->service_identifier);
    }
        
    /**
     * The type
     *
     * @var string
     */
    protected $_type = 'repos';
    
    /**
     * Get the classname based on an identifier
     *
     * @param 	mixed  		 An identifier object - koowa:[path].name
     * @return string|false  Return object on success, returns FALSE on failure
     */
    public function findClass(KServiceIdentifier $identifier)
    {  
        return __CLASS__;
    }   

    /**
     * Get the path based on an identifier
     *
     * @param  object  	An identifier object - koowa:[path].name
     * @return string	Returns the path
     */
    public function findPath(KServiceIdentifier $identifier)
    {
        return self::_identifier($identifier)->filepath;
    }  

    /**
     * Converts a repos locator identifier repos:[//application/]<Component>.<Name> to a 
     * component identifier
     *
     * @param KServiceIdentifier $identifier
     * 
     * @return KServiceIdentifier
     */
    protected function _identifier(KServiceIdentifier $identifier)
    {
        $identifier = clone $identifier;
        $identifier->type = 'com';
        $identifier->path = array('domain','entity');
        return $identifier;        
    }
}