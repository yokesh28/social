<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller_Toolbar
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Comment Toolbar
 *
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller_Toolbar
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseControllerToolbarComment extends ComBaseControllerToolbarDefault
{
    /**
     * Called before list commands
     *
     * @return void
     */
    public function addListCommands()
    {
        $comment = $this->getController()->getItem();
        
		if ( $comment->authorize('vote'))
			 $this->addCommand('vote');
		
		if ( $comment->authorize('edit') ) 
		{
			$url = $comment->getURL().'&comment[layout]=form';
			
			if ( $this->getController()->editor ) {
				$url = $url.'&comment[editor]=1';
			}
			
			$this->addCommand('editcomment', JText::_('LIB-AN-ACTION-EDIT'))
			    ->getCommand('editcomment')
			    ->href($url)
				->setAttribute('data-trigger','Request')
				->setAttribute('data-request-options',"{replace:'!.an-comment'}");
		}
			
		if ( $comment->authorize('delete') ) 
		{
			$this->addCommand('deletecomment', JText::_('LIB-AN-ACTION-DELETE'))
			    ->getCommand('deletecomment')
			    ->href($comment->getURL().'&action=delete')
				->setAttribute('data-trigger','Remove');
		}
	}	
}