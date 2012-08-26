<?php

set_time_limit(0);

ini_set("memory_limit","700M");

chdir(dirname(__FILE__));

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../'));
require_once(ROOT_DIR . '/infra/bootstrap_base.php');
require_once(ROOT_DIR . '/infra/KAutoloader.php');

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "sphinx_search", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "metadata", "*"));
KAutoloader::setClassMapFilePath('../../../cache/classMap.cache');
KAutoloader::register();

date_default_timezone_set(kConf::get("date_default_timezone")); // America/New_York

error_reporting(E_ALL);

$dbConf = array (
  'datasources' => 
  array (
    'default' => 'propel',
  
    'propel' => 
    array (
      'adapter' => 'mysql',
      'connection' => 
      array (
      	'classname' => 'KalturaPDO',
        'phptype' => 'mysql',
        'database' => 'kaltura',
        'hostspec' => 'pa-backup1',
        'user' => 'kaltura',
        'password' => 'kaltura',
		'dsn' => 'mysql:host=pa-backup1;dbname=kaltura;user=kaltura;password=kaltura;',
      ),
    ),
    
  
    'propel2' => 
    array (
      'adapter' => 'mysql',
      'connection' => 
      array (
      	'classname' => 'KalturaPDO',
        'phptype' => 'mysql',
        'database' => 'kaltura',
        'hostspec' => 'pa-backup1',
        'user' => 'kaltura_read',
        'password' => 'kaltura_read',
		'dsn' => 'mysql:host=pa-backup1;dbname=kaltura;user=kaltura_read;password=kaltura_read;',
      ),
    ),
    
  
    'propel3' => 
    array (
      'adapter' => 'mysql',
      'connection' => 
      array (
      	'classname' => 'KalturaPDO',
        'phptype' => 'mysql',
        'database' => 'kaltura',
        'hostspec' => 'pa-backup1',
        'user' => 'kaltura_read',
        'password' => 'kaltura_read',
		'dsn' => 'mysql:host=pa-backup1;dbname=kaltura;user=kaltura_read;password=kaltura_read;',
      ),
    ),
  ),
  'log' => 
  array (
    'ident' => 'kaltura',
    'level' => '7',
  ),
);

KalturaLog::setLogger(new KalturaStdoutLogger());

//$dbConf = kConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();

$exclude = array(
	396894, 
	403598, 
	765735, 
	776915, 
	795308, 
	913844, 
	961914, 
	968615, 
	1045770, 
	1079049, 
	1079053, 
	1079055, 
	1079085, 
	1079097, 
	1079101, 
	1079106, 
	1079113, 
	1080762, 
	1080763, 
	1080766, 
	1080767, 
	1080768, 
	1080770, 
	1080898, 
	1080902, 
	1114125, 
	1114126, 
	1114127, 
	1114128, 
	1114138, 
	1114139, 
	1114140, 
	1114141, 
	28466202, 
);

$sphinxMgr = new kSphinxSearchManager();
$lastEntryFile = 'last_entry';
$lastTimeFile = 'last_time';

$lastEntry = 0;
$lastTime = 0;

if(file_exists($lastEntryFile))
	$lastEntry = file_get_contents($lastEntryFile);
if(file_exists($lastTimeFile))
	$lastTime = file_get_contents($lastTimeFile);

if(!$lastEntry)
	$lastEntry = 0;
if(!$lastTime)
	$lastTime = 0;


//if($argc > 1 && is_numeric($argv[1]))
//	$lastEntry = max($lastEntry, $argv[1]);
	

$insertsFile = fopen('inserts.sql', 'a');
$errorsFile = fopen('errors.sql', 'a');

if(!$insertsFile)
{
	echo "upable to open sql file [" . realpath($insertsFile) . "]";
	exit;
}

$isInsert = true;

myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;

//$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);

while(1)
{
	clearstatcache(); // must clear stat cache or the file be seen with zero size
	if (file_exists("stop"))
		break;

	$c = new Criteria();
	$c->Add(entryPeer::INT_ID, $lastEntry, Criteria::GREATER_THAN);	
	$c->addAscendingOrderByColumn(entryPeer::INT_ID);
	$c->setLimit(1000);

	//$entries = entryPeer::doSelect($c, $con);
	$entries = entryPeer::doSelect($c);

	if (!count($entries))
		break;

	$currentEntry = null;
	foreach($entries as $entry)
	{
		$currentEntry = $entry->getIntId();
		if(in_array($currentEntry, $exclude))
		{
			file_put_contents($lastEntryFile, $currentEntry);
			fputs($errorsFile, "Entry [" . $entry->getId() . "] field int_id is dullicated [$currentEntry]\n");
			continue;
		}
		
		KalturaLog::log('entry id ' . $entry->getId() . ' int id[' . $currentEntry . '] status['. $entry->getStatus().']');

		if ($entry->getStatus() == 3)
		{
			file_put_contents($lastEntryFile, $currentEntry);
			continue;
		}
		
		try{
//$sql ="$currentEntry";
			$sql = $sphinxMgr->getSphinxSaveSql($entry, $isInsert, true);	
			//$sql = $entry->getSphinxSaveSql($isInsert, true);
			
			fputs($insertsFile, "$sql;\n");
		}
		catch(Exception $e){
			fputs($errorsFile, $e->getMessage() . "\n");
		}
		file_put_contents($lastEntryFile, $currentEntry);
		
	}
	$lastEntry = $currentEntry;
	
	entryPeer::clearInstancePool();
	MetadataPeer::clearInstancePool();
	MetadataProfilePeer::clearInstancePool();
	MetadataProfileFieldPeer::clearInstancePool();

	if (file_exists("runone"))
		break;
}

fclose($insertsFile);
fclose($errorsFile);
KalturaLog::log('Done');
