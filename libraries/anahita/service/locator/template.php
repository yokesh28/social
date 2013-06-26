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
 * Template Locator. If a module is not found, it first look at the default classes 
 *
 * @category   Anahita
 * @package    Anahita_Service
 * @subpackage Locator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnServiceLocatorTemplate extends KServiceLocatorAbstract
{
	/** 
	 * The type
	 * 
	 * @var string
	 */
	protected $_type = 'tmpl';
	
	/**
	 * Get the classname based on an identifier
	 *
	 * @param 	mixed  		 An identifier object - koowa:[path].name
	 * @return string|false  Return object on success, returns FALSE on failure
	 */
	public function findClass(KServiceIdentifier $identifier)
	{
	    $classname = 'Tmpl'.ucfirst($identifier->package).KInflector::implode($identifier->path).ucfirst($identifier->name);
	    
	    if (!$this->getService('koowa:loader')->loadClass($classname, $identifier->basepath))
	    {
	        $classname = AnServiceClass::findDefaultClass($identifier);
            
	        if ( !$classname )
	        {
                $path      = KInflector::implode($identifier->path);
                $classes[] = 'TmplBase'.$path.ucfirst($identifier->name);
                $classes[] = 'TmplBase'.$path.ucfirst($identifier->name).'Default';
                $classes[] = 'LibTheme'.$path.ucfirst($identifier->name);
                $classes[] = 'LibTheme'.$path.ucfirst($identifier->name).'Default';                
                $classes[] = 'LibBase'.$path.ucfirst($identifier->name);
                $classes[] = 'LibBase'.$path.ucfirst($identifier->name).'Default';
	            $classes[] = 'K'.$path.ucfirst($identifier->name);
                $classes[] = 'K'.$path.ucfirst($identifier->name).'Default';            
                  
	            foreach($classes as $class)
	            {
	                if ( $this->getService('koowa:loader')->loadClass($class,  $identifier->basepath)) {
	                    $classname = $class;
	                    break;
	                }
	            }

	            if ( $classname ) {
	                AnServiceClass::setDefaultClass($identifier, $classname);
	            }
	        }
	    }
		
		return $classname;
	}
	
    /**
     * Get the path based on an identifier
     *
     * @param  object   An identifier object - com:[//application/]component.view.[.path].name
     * @return string   Returns the path
     */
    public function findPath(KServiceIdentifier $identifier)
    {
        $path  = '';
        $parts = $identifier->path;
                
        $theme = strtolower($identifier->package);
            
        if(!empty($identifier->name))
        {
            if(count($parts)) 
            {
                if ( $parts[0] != 'html' )
                {
                    foreach($parts as $key => $value) {
                       $parts[$key] = KInflector::pluralize($value);
                    }
                } 
                
                $path = implode('/', $parts).'/'.strtolower($identifier->name);
            } 
            else $path  = strtolower($identifier->name);    
        }
             
        $path = $identifier->basepath.'/templates/'.$theme.'/'.$path.'.php';
           
        return $path;
    }
}