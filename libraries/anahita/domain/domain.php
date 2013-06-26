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
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Anahita Domain
 *
 * Domain offers classes for domain driven programming. Domain Package implements 
 * Unit of Work, Data Mapper, Domain Query patterns 
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnDomain
{		
	/**
	 * Property Access Constants
	 */
	const ACCESS_PRIVATE   = 0;
	const ACCESS_PROTECTED = 1;
	const ACCESS_PUBLIC	   = 2;
	
	/**
	 * Entity States
	 */
	const STATE_CLEAN  		= 2;
	//pre-committ states
	const STATE_NEW			= 4;
	const STATE_MODIFIED 	= 8;
	const STATE_DELETED 	= 16;
	
	//internal states
	const STATE_COMMITABLE	= 28;	
	//internal states
	const STATE_INSERTED	= 32;
	const STATE_UPDATED		= 64;
	const STATE_DESTROYED	= 128;
	const STATE_COMMITTED   = 224;
		
	/**
	 * Delete Rules	 
	 */
	const DELETE_CASCADE	= 'cascade';
	const DELETE_DESTROY	= 'destroy';
	const DELETE_DENY		= 'deny';
	const DELETE_NULLIFY	= 'nullify';
	const DELETE_IGNORE	    = 'ignore';
	
	/**
	 * Fetch Mode
	 */
	const FETCH_DATA        = 1;
	const FETCH_VALUE	    = 2;
	const FETCH_ENTITY 		= 4;	
	const FETCH_VALUE_LIST	= 8;	
	const FETCH_DATA_LIST   = 16;	
	const FETCH_ENTITY_SET  = 32;
	const FETCH_ENTITY_LIST = 64;
	
	/**
	 * Fetch Check
	 */
	const FETCH_ITEM		= 7;
	const FETCH_LIST		= 112;

	/**
	 * Entity operations
	 */	
	const OPERATION_FETCH   = 1;
	const OPERATION_INSERT  = 2;
	const OPERATION_UPDATE  = 4;
	const OPERATION_DELETE  = 8;
    const OPERATION_DESTROY = 16;
	const OPERATION_COMMIT  = 32;	
	
	/**
	 * Entity Identifers must have application in their path. This method set the 
	 * application of an identifier if the path is missing
	 *
	 * @param string $identifier Entity Identifier
	 * 
	 * @return KServiceIdentifier
	 */	
	static public function getEntityIdentifier($identifier)
	{
	    $identifier = KService::getIdentifier($identifier);
	    
	    if ( !$identifier->basepath )
	    {
	        $adapters     = KService::get('koowa:loader')->getAdapters();
	        $basepath     = pick($adapters[$identifier->type]->getBasePath(), JPATH_BASE);
	        $applications = array_flip(KServiceIdentifier::getApplications());
	        if ( isset($applications[$basepath])  )
	        {
	            $identifier->application = $applications[$basepath];
	            $identifier->basepath    = $basepath;
	        }
	    }
	    
	    return $identifier;
	}
	
    /**
     * Helper mehtod to return a repository for an entity
     *
     * @param string $identifier Entity Identifier
     * @param array  $config     Configuration 
     * 
     * @return AnDomainRepositoryAbstract 
     */
	static public function getRepository($identifier, $config = array())
	{
	    $strIdentifier = (string) $identifier;
	    
	    if ( !KService::has($identifier) )
	    {
	        $identifier = self::getEntityIdentifier($identifier);
	    }
	      
	    if ( !KService::has($identifier) )
	    {
	        KService::set($strIdentifier, KService::get($identifier, $config));
	    }	    
	    
	    return KService::get($identifier)->getRepository();
	}
}

/**
 * Helper mehtod to return a repository for an entity
 *
 * @param string $entity Entity Identifier
 * @param array  $config Configuration 
 * 
 * @return AnDomainRepositoryAbstract
 *  
 *  @deprecated Use AnDomain::getRepository or KService::get('repos:[//application/]<Package Name>.<Entity Name>')
 */
function repos($repository, $config = array())
{
    deprecated("Use AnDomain::getRepository or KService::get('repos:[//application/]<Package Name>.<Entity Name>')");
    return KService::get($repository, $config)->getRepository();
}

?>