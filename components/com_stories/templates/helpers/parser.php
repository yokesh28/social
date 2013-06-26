<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Template_Helper
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Story Parser Template Helper
 *
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Template_Helper
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComStoriesTemplateHelperParser extends KTemplateHelperAbstract
{	
	/**
	 * Parse Template
	 * 
	 * @var ComBaseTemplateDefault
	 */
	protected $_template;
	
	/**
	 * Constructor.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 */
	public function __construct(KConfig $config)
	{
		parent::__construct($config);
				
		$identfier         = clone $this->getIdentifier();
		$identfier->path   = array('template');
		$identfier->name   = 'parser';
		register_default(array('identifier'=>$identfier, 'default'=>'ComBaseTemplateDefault'));		
		$this->_template  = $this->getService($identfier);
		
		foreach($config->filters as $filter) {
		    $this->_template->addFilter($filter);
		}
	
		$this->_template->getFilter('alias')->append( KConfig::unbox($config->alias) );

		JFactory::getLanguage()->load('com_stories');
		
		$this->_template->addPath(KConfig::unbox($config->paths), true);
		
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
		$config->append(array(
			'paths'     => array(dirname(__FILE__).'/../stories'),
			'filters'   => array('alias','shorttag'),
		    'alias'     => array(
                    '@escape('           => 'htmlspecialchars(',
		            '@route('            => 'LibBaseHelperUrl::getRoute(',
		            '@name(' 		 	 => '$this->renderHelper(\'com://site/stories.template.helper.story.actorName\',',
		            '@possessive(' 		 => '$this->renderHelper(\'com://site/stories.template.helper.story.possessiveNoune\',$story,',
		            '@link(' 			 => '$this->renderHelper(\'com://site/stories.template.helper.story.link\','
		    )
		));
		
		parent::_initialize($config);
	}
		
	/**
	 * Render a story. If a $actor is passed then we are rendering the stories related to an actor. (A profile stories
	 * as opposed to 
	 * 
	 * @param  ComStoriesDomainEntityStory      $story Story       
	 * @param  ComActorsDomainEntityActor $actor Actor
	 * 
	 * @return array
	 */
	public function parse($story, $actor = null)
	{
	    $options = array();
        
        JFactory::getLanguage()->load($story->component);
        
        static $commands;
        
        $commands = $commands ? clone $commands : new LibBaseTemplateObjectContainer();
        
        $commands->reset();
        
        $data = array(
            'commands'  => $commands,
            'actor'     => $actor,                              
            'helper'    => $this,
            'story'     => $story,
            'subject'   => $story->subject,
            'target'    => $story->target,
            'object'    => $story->object,
            'comment'   => $story->comment,
            'type'      => $story->getIdentifier()->name
        );
        
        $path = JPATH_ROOT.'/components/'.$story->component.'/templates/stories/'.$story->name.'.php';          
        
        $data = $this->_parseData( $this->_render($story, $path, $data) );
        
        $data['commands'] = $commands;
        
        return $data;
	}
	
	/**
	 * Renders a story
	 *
	 * @param array $paths
	 * @param array $data
	 */
	protected function _render($story, $paths, $data)
	{
		settype($paths, 'array');
		
		foreach($paths as $path) 
		{
			if ( $this->_template->findFile($path) ) {
				return $this->_template->loadFile($path, $data)->render();
			}
		}
		
		try {
			return $this->_template->loadTemplate($story->name, $data)->render();
		}catch(Exception $e) {
			print '<small>file missing :'.$path.'</small>';			
		}
	}		
	
	/**
	 * Parse the title,body from data
	 *
	 * @param  string $data
	 * @return array
	 */
	protected function _parseData($data)
	{
		$output  = array('title'=>'','body'=>'');
		$matches = array();		
		
		if ( preg_match_all('#<data name="([^"]+)">(.*?)<\/data>#si', $data, $matches) )
		{
			$attributes = $matches[1];
			$contents   = $matches[2];
			foreach($attributes as $i=>$attribute)
				$output[$attribute] = $contents[$i];
		}
				
		return $output;	
	}
	
	/**
	 * Return the parse template
	 *
	 * @return ComBaseTemplateDefault
	 */
	public function getTemplate()
	{
	    return $this->_template;
	}
}