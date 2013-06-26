<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Anahita_Loader
 * @subpackage Adapter
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Template Loader
 *
 * @category   Anahita
 * @package    Anahita_Loader
 * @subpackage Adapter
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnLoaderAdapterTemplate extends KLoaderAdapterAbstract
{	
	/** 
	 * The adapter type
	 * 
	 * @var string
	 */
	protected $_type = 'tmpl';
	
	/**
	 * The class prefix
	 * 
	 * @var string
	 */
	protected $_prefix = 'Tmpl';
			
    /**
	 * Get the path based on a class name
	 *
	 * @param  string		  	The class name 
	 * @return string|false		Returns the path on success FALSE on failure
	 */
	public function findPath($classname, $basepath = null)
	{
		$path = false; 
		
		if (strpos($classname, $this->_prefix) === 0) 
		{			
			$word  = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $classname));
			$parts = explode('_', $word);
			
			if (array_shift($parts) == 'tmpl') 
			{
				$name = array_shift($parts);
			
				$file 	   = array_pop($parts);
				
				if(count($parts)) 
				{
					foreach($parts as $key => $value) {
						$parts[$key] = KInflector::pluralize($value);												
					}
					
					$path = implode('/', $parts);
					$path = $path.'/'.$file;
				} 
				else $path = $file;
							
				$path = $this->_basepath.'/templates/'.$name.'/'.$path.'.php';
			}
		}
		
		return $path;
	}
}
?>