<?php

/** 
 * LICENSE: Anahita is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 * @category   Anahita
 * @package    Com_Notifications
 * @subpackage Domains
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Setting delegate interface
 *
 * @category   Anahita
 * @package    Com_Notifications
 * @subpackage Domains
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
interface ComNotificationsDomainDelegateSettingInterface 
{
    /**
     * Constant for mailing a notifiation.
     */
    const NOTIFY_WITH_EMAIL = 1;
    const NOTIFY            = 2;
    
    /**
    * Checks with whether to notify a person or not
    *
    * @param ComPeopleDomainEntityPerson        	  $person       Person that notification being sent to
    * @param ComNotificationsDomainEntityNotification $notification The notification object
    * @param ComNotificationsDomainEntitySetting      $setting	    The setting object, it maybe NULL
    * 
    * @return int
    */
    public function shouldNotify($person, $notification, $setting);
}