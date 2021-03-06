<?php
/**
 * Script to test the output from Doctrine_Cli
 *
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));

require_once(dirname(__FILE__) . '/TestTask02.php');

$cli = new Doctrine_Cli(array('autoregister_custom_tasks' => false));
$cli->run($_SERVER['argv']);
