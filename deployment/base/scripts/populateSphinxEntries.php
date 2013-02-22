<?php
chdir(dirname(__FILE__));

require_once('../../bootstrap.php');


$c = new Criteria();
if($argc > 1 && is_numeric($argv[1]))
	$c->add(entryPeer::UPDATED_AT, $argv[1], Criteria::GREATER_EQUAL);
if($argc > 2 && is_numeric($argv[2]))
	$c->add(entryPeer::PARTNER_ID, $argv[2], Criteria::EQUAL);
if($argc > 3 && is_numeric($argv[3]))
	$c->add(entryPeer::INT_ID, $argv[3], Criteria::GREATER_EQUAL);
		
$c->addAscendingOrderByColumn(entryPeer::UPDATED_AT);
$c->addAscendingOrderByColumn(entryPeer::ID);
$c->setLimit(10000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);
//$sphinxCon = DbManager::getSphinxConnection();

$entries = entryPeer::doSelect($c, $con);
$sphinx = new kSphinxSearchManager();
while(count($entries))
{
	foreach($entries as $entry)
	{
		KalturaLog::log('entry id ' . $entry->getId() . ' int id[' . $entry->getIntId() . '] crc id[' . $sphinx->getSphinxId($entry) . '] updated at ['. $entry->getUpdatedAt(null) .']');
		
		try {
			$ret = $sphinx->saveToSphinx($entry, true);
		}
		catch(Exception $e){
			KalturaLog::err($e->getMessage());
			exit -1;
		}
	}
	
	$c->setOffset($c->getOffset() + count($entries));
	kMemoryManager::clearMemory();
	$entries = entryPeer::doSelect($c, $con);
}

KalturaLog::log('Done. Cureent time: ' . time());
