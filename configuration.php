<?php
class JConfig {
/* Site Settings */
var $offline = '0';
var $offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';
var $sitename = 'social networking';
var $siteurl  = 'http://localhost/social/';
var $editor = 'tinymce';
var $list_limit = '20';
var $legacy = '0';
/* Debug Settings */
var $debug = '0';
var $debug_lang = '0';
/* Database Settings */
var $dbtype = 'mysqli';
var $host = 'localhost';
var $user = 'root';
var $password = 'password';
var $db = 'social';
var $dbprefix = 'lj_';
/* Server Settings */
var $live_site = '';
var $secret = 'ClPfknoAueVuJX3x';
var $gzip = '0';
var $error_reporting = '-1';
var $helpurl = 'http://www.anahitapolis.com/help/resources';
var $xmlrpc_server = '0';
var $ftp_host = '127.0.0.1';
var $ftp_port = '21';
var $ftp_user = '';
var $ftp_pass = '';
var $ftp_root = '';
var $ftp_enable = '0';
var $force_ssl = '0';
/* Locale Settings */
var $offset = '0';
var $offset_user = '0';
/* Mail Settings */
var $mailer = 'mail';
var $mailfrom = 'yokesh.ganesan@gmail.com';
var $fromname = 'social networking';
var $sendmail = '/usr/sbin/sendmail';
var $smtpauth = '0';
var $smtpsecure = 'none';
var $smtpport = '25';
var $smtpuser = '';
var $smtppass = '';
var $smtphost = 'localhost';
/* Cache Settings */
var $caching = '0';
var $cachetime = '15';
var $cache_handler = 'file';
/* Meta Settings */
var $MetaDesc = 'Anahita® Social Engine - The open source social networking platform and framework.';
var $MetaKeys = '';
var $MetaTitle = '1';
var $MetaAuthor = '1';
/* SEO Settings */
var $sef           = '0';
var $sef_rewrite   = '0';
var $sef_suffix    = '0';
/* Feed Settings */
var $feed_limit   = 10;
var $feed_email   = 'author';
var $log_path = '/var/www/social/logs';
var $tmp_path = '/var/www/social/tmp';
/* Session Setting */
var $lifetime = '1440';
var $session_handler = 'database';
}
?>