<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    LibTheme
 * @subpackage Helper
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

require_once 'compiler.php';

/**
 * Less Compiler Template Helper
 * 
 * @category   Anahita
 * @package    LibTheme
 * @subpackage Helper
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibThemeTemplateHelperLess extends KTemplateHelperAbstract
{    
    /**
     * Compiles a less css file. The the compiler will create a css file output
     * 
     * @param array  $config Array of less compile configuration
     * 
     * @return void
     */    
    public function compile($config = array())
    {
        $config = new KConfig($config);
        
        $config->append(array(
            'parse_urls'        => true,            
            'compress' => true,
            'import' => array(),
            'force'  => false,
            'output' => null, //required
            'input'  => null, //required
        ));
        
        $less  = new lessc();
        $less->setPreserveComments(!$config->compress);
        if ( $config->compress ) {
            $less->setFormatter("compressed");   
        }
        $config['import'] = $config['import'];
        $less->setImportDir( $config['import'] );        
        $cache_file = JFactory::getConfig()->getValue('tmp_path').'/less-'.md5($config->input);

        if ( file_exists($cache_file) ) {
            $cache = unserialize(file_get_contents($cache_file));
        } else
            $cache = $config->input;
                
        $force = $config->force;
        
        //if output doesn't exsit then force compile
        if ( !is_readable($config->output) ) {
            $force = true;    
        }
        
        //check if any of the import folder have changed or
        //if yes then re-compile        
        if ( is_array($cache) )
        {
            foreach($config['import'] as $path) {
                if  ( filemtime($path) > $cache['updated'] ) {
                    $force = true;
                    break;
                }
            }
        }
        try {
        	$new_cache = $less->cachedCompile($cache, $force);
        }catch(Exception $e) {
        	print $e->getMessage();
        	return;
        }
        if (!is_array($cache) || $new_cache['updated'] > $cache['updated'] ) 
        {
            if ( $config->parse_urls ) {
                $new_cache['compiled'] = $this->_parseUrls($new_cache['compiled'], $config->import);
            }
            
            //store the cache
            file_put_contents($cache_file, serialize($new_cache));
            //store the compiled file
            
            file_put_contents($config->output, $new_cache['compiled']);
        }
    }
    
    /**
     * Parse URLs
     * 
     * @param string $text   The compiled css text
     * @param array  $paths  An array of paths to look for assets
     * 
     * @return string
     */
    protected function _parseUrls($text, array $paths)
    {
        $matches  = array();
        $replaces = array();
        
        $finder = $this->getService('anahita:file.pathfinder')
                ->addSearchDirs( array_reverse($paths) );
        $starting_path = $paths[0];
       
        if ( preg_match_all('/url\((.*?)\)/', $text, $matches) ) 
        {
            foreach($matches[1] as $match) 
            {
                $match = str_replace(array('"',"'"),'', $match);
                                
                if ( $path = $finder->getPath($match) ) 
                {
                    $path = AnHelperFile::getTravelPath($starting_path, $path);
                    $path = str_replace(DS,'/',$path);
                    $replaces[$match] = $path;
                }
            }
        }
        
        $text = str_replace(array_keys($replaces), array_values($replaces), $text);
        
        return $text;
    }
}