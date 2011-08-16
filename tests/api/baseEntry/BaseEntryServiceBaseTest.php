<?php

/**
 * baseEntry service base test case.
 */
abstract class BaseEntryServiceBaseTest extends KalturaApiTestCase
{
	/**
	 * Tests baseEntry->add action
	 * @param KalturaBaseEntry $entry 
	 * @param KalturaEntryType $type 
	 * @param KalturaBaseEntry $reference 
	 * @return int
	 * @dataProvider provideData
	 */
	public function testAdd(KalturaBaseEntry $entry, KalturaEntryType $type = -1, KalturaBaseEntry $reference)
	{
		$resultObject = $this->client->baseEntry->add($entry, $type);
		$this->assertType('KalturaBaseEntry', $resultObject);
		$this->assertNotNull($resultObject->id);
		$this->validateAdd($entry, $type, $reference);
		return $resultObject->id;
	}

	/**
	 * Validates testAdd results
	 */
	protected function validateAdd(KalturaBaseEntry $entry, KalturaEntryType $type = -1, KalturaBaseEntry $reference)
	{
	}

	/**
	 * Tests baseEntry->get action
	 * @param string $entryId Entry id
	 * @param int $version Desired version of the data
	 * @param KalturaBaseEntry $reference 
	 * @return int
	 * @dataProvider provideData
	 */
	public function testGet($entryId, $version = -1, KalturaBaseEntry $reference)
	{
		$resultObject = $this->client->baseEntry->get($entryId, $version);
		$this->assertType('KalturaBaseEntry', $resultObject);
		$this->assertNotNull($resultObject->id);
		$this->validateGet($entryId, $version, $reference);
		return $resultObject->id;
	}

	/**
	 * Validates testGet results
	 */
	protected function validateGet($entryId, $version = -1, KalturaBaseEntry $reference)
	{
	}

	/**
	 * Tests baseEntry->update action
	 * @param string $entryId Entry id to update
	 * @param KalturaBaseEntry $baseEntry Base entry metadata to update
	 * @param KalturaBaseEntry $reference 
	 * @return int
	 * @dataProvider provideData
	 */
	public function testUpdate($entryId, KalturaBaseEntry $baseEntry, KalturaBaseEntry $reference)
	{
		$resultObject = $this->client->baseEntry->update($entryId, $baseEntry);
		$this->assertType('KalturaBaseEntry', $resultObject);
		$this->assertNotNull($resultObject->id);
		$this->validateUpdate($entryId, $baseEntry, $reference);
		return $resultObject->id;
	}

	/**
	 * Validates testUpdate results
	 */
	protected function validateUpdate($entryId, KalturaBaseEntry $baseEntry, KalturaBaseEntry $reference)
	{
	}

	/**
	 * Tests baseEntry->delete action
	 * @param string $entryId Entry id to delete
	 * @dataProvider provideData
	 */
	public function testDelete($entryId)
	{
		$resultObject = $this->client->baseEntry->delete($entryId);
		$this->validateDelete($entryId);
	}

	/**
	 * Validates testDelete results
	 */
	protected function validateDelete($entryId)
	{
	}

	/**
	 * Tests baseEntry->listAction action
	 * @param KalturaBaseEntryFilter $filter Entry filter
	 * @param KalturaFilterPager $pager Pager
	 * @param KalturaBaseEntryListResponse $reference 
	 * @dataProvider provideData
	 */
	public function testListAction(KalturaBaseEntryFilter $filter = null, KalturaFilterPager $pager = null, KalturaBaseEntryListResponse $reference)
	{
		$resultObject = $this->client->baseEntry->listAction($filter, $pager);
		$this->assertType('KalturaBaseEntryListResponse', $resultObject);
		$this->validateListAction($filter, $pager, $reference);
	}

	/**
	 * Validates testListAction results
	 */
	protected function validateListAction(KalturaBaseEntryFilter $filter = null, KalturaFilterPager $pager = null, KalturaBaseEntryListResponse $reference)
	{
	}

	/**
	 * Called when all tests are done
	 * @param int $id
	 * @return int
	 */
	abstract public function testFinished($id);

}
