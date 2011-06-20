<?php

date_default_timezone_set('America/New_York');

require_once 'C:\web\kaltura\infra\nusoap\class.nusoap_base.php';
require_once 'C:\web\kaltura\infra\nusoap\class.soap_transport_http.php';
require_once 'C:\web\kaltura\infra\nusoap\class.xmlschema.php';
require_once 'C:\web\kaltura\infra\nusoap\class.wsdl.php';
require_once 'C:\web\kaltura\infra\nusoap\class.soap_parser.php';
require_once 'C:\web\kaltura\infra\nusoap\class.soapclient.php';
require_once 'C:\web\kaltura\infra\nusoap\SoapTypes.php';

require_once 'ComcastClient.php';
require_once 'ComcastMediaService.php';

require_once 'ComcastIDList.class.php';
require_once 'ComcastBusinessObject.class.php';
require_once 'ComcastStatusObject.class.php';
require_once 'ComcastContent.class.php';
require_once 'ComcastMedia.class.php';
require_once 'ComcastContentType.class.php';
require_once 'ComcastArrayOfFormat.class.php';
require_once 'ComcastFormat.class.php';
require_once 'ComcastLanguage.class.php';
require_once 'ComcastCustomDataElement.class.php';
require_once 'ComcastFieldValue.class.php';
require_once 'ComcastTextValue.class.php';
require_once 'ComcastMediaFile.class.php';
require_once 'ComcastMediaFileType.class.php';
require_once 'ComcastAddContentOptions.class.php';
require_once 'ComcastAddContentResults.class.php';

$userName = 'roman.kreichman@kaltura.com'; 
$password = 'Roman1234';

$mediaService = new ComcastMediaService($userName, $password);

$entryId = '0_n4qy9ni8';
$entryDuration = 26749;
$thumbnailURL = 'http://62.90.210.41:81/p/140/sp/14000/thumbnail/entry_id/0_n4qy9nh3/version/100000';
$entryTitle = 'test to comcast';
$entryDescription = 'The test to comcast desc';

$flavorAsset1_Bitrate = 734;
$flavorAsset1_Duration = 26749;
$flavorAsset1_URL = 'http://pa-www.kaltura.com/content/shared/tmp/tantan/0_n4qy9nh3_0_ebj1imzl_1.flv';
$flavorAsset1_Width = 624;
$flavorAsset1_Height = 352;

$flavorAsset2_Bitrate = 878;
$flavorAsset2_Duration = 26749;
$flavorAsset2_URL = 'http://pa-www.kaltura.com/content/shared/tmp/tantan/0_n4qy9nh3_0_syrsamqn_1.flv';
$flavorAsset2_Width = 640;
$flavorAsset2_Height = 352;

$media = new ComcastMedia();
	
//$media->ID; // important for updates
//$media->template = new ComcastArrayOfMediaField();

$media->album = 'Fox Sports';
$media->airdate = time();
$media->author = 'Fox Sports';
$media->availableDate = time();
$media->categories = array();
$media->categories[] = 'TV Full Episode';
$media->contentType = ComcastContentType::_VIDEO;
$media->copyright = '2004 FOX Interactive Television, LLC';
$media->expirationDate = time() + (60 * 60 * 24 * 30); // 30 days
$media->externalID = $entryId;
$media->formats = new ComcastArrayOfFormat();
$media->formats = ComcastFormat::_FLV;
$media->keywords = 'Fox Sports';
$media->language = ComcastLanguage::_ENGLISH;
$media->length = $entryDuration;
$media->rating = 'TV-14';
$media->thumbnailURL = $thumbnailURL; //72x92
$media->title = $entryTitle;
$media->description = $entryDescription;
$media->customData = array();

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Brand';
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'HBO';
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'providerExternalId'; //Required, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '90745c5d4e00'; //Required, any single text/numeric string, must be unique to each individual asset -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Video Content Type'; //Required, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Long Form'; //Required, value values are Long Form OR Short Form, Long Form is to be applied to TV Full Episodes or Movie Full Feature, all other categories must be mapped to Short Form -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Headline'; //Required for Short Form assets -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Season 1, Episode 5'; //Required for Short Form assets, a plain text field that should mirror the title field -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Link Text'; //Required for Comcast.net assets -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '<![CDATA[E! News Now]]>'; //Required for .net assets, a plain text field that descripbes the link href below -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Link Href'; //Required for Comcast.net assets -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'http://www.enews.com'; //Required for .net assets, should be the URL that your logo on the video channel directs to -->
$media->customData[] = $customDataElement;

//TV Series Matching ID -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Series ID'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '1975'; //Required for TV, The cPlatform, TMS, Baseline, IMDB or AMG series matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Series ID Type'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Required for TV, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Series Name'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'The Sopranos'; //Required for TV, a plain text field -->
$media->customData[] = $customDataElement;

//TV Season Matching ID -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Season Number'; //Optional for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '6'; //Optional for TV, any integer value -->
$media->customData[] = $customDataElement;

//TV Episode Matching ID -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Episode ID'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '34764'; //Required for TV, The cPlatform, TMS, Baseline, IMDB or AMG episode matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Episode ID Type'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Required for TV, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Episode Name'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Made In America'; //Required for TV, a plain text field -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Episode Number'; //Required for TV, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '6'; //Required for TV, any integer value -->
$media->customData[] = $customDataElement;

//Movie Matching ID -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Movie ID'; //Required for Movie, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '545454'; //Required for Movie, The cPlatform, TMS, Baseline, IMDB or AMG episode matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Movie ID Type'; //Required for Movie, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Required for Movie, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Movie Name'; //Required for Movie, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'The Sopranos Made Up Movie'; //Required for Movie, a plain text field -->
$media->customData[] = $customDataElement;

//Advertising -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'C3 Airdate'; //Optional, literal string when an asset is to follow C3 behavior -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '2009-06-22T20:00:00-04'; //Optional, the linear airdate and time of the C3 asset, also the beginning of the C3 window. -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'BlackoutStartTimes'; //Optional, literal string must be present when an asset follows C3 behavior -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '270540,744780,1285850'; //Optional, a comma separated list of milliseconds OR HH:MM:SS of when black-out times begin. -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'BlackoutEndTimes'; //Optional, literal string must be present when an asset follows C3 behavior -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '273470,747350,1288650'; //Optional, a comma separated list of milliseconds OR HH:MM:SS of when black-out times end. -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'ChapterStartTimes'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '270540,744780,1285850'; //Optional, a comma separated list of milliseconds OR HH:MM:SS of when ad insertions are expected. -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Credit Roll Start'; //Required, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '1277000'; //Required, a single value in milliseconds OR HH:MM:SS of when credits begin. -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Adult'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Adult'; //Optional, valid values are: Adult, nonAdult -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'HD Override'; //Optional, literal string - coordinate with CIM before using -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'none'; //Optional, valid values are: none, HD, SD -->
$media->customData[] = $customDataElement;

//People, up to 10 people can be associated with each media asset -->
$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 01 ID'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '72308'; //Optional, the cPlatform, TMS, Baseline, IMDB or AMG person matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 01 ID Type'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Optional, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 01 Name'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'James Gandolfini'; //Optional, a plain text field -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 02 ID'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '44429'; //Optional, the cPlatform, TMS, Baseline, IMDB or AMG person matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 02 ID Type'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Optional, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 02 Name'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Lorraine Bracco'; //Optional, a plain text field -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 03 ID'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = '39861'; //Optional, the cPlatform, TMS, Baseline, IMDB or AMG person matching ID -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 03 ID Type'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'cPlatform'; //Optional, the ID space used, valid values are: cPlatform, TMS, Baseline, IMDB, AMG -->
$media->customData[] = $customDataElement;

$customDataElement = new ComcastCustomDataElement();
$customDataElement->title = 'Associated Person Addition 03 Name'; //Optional, literal string -->
$customDataElement->value = new ComcastTextValue();
$customDataElement->value->text = 'Edie Falco'; //Optional, a plain text field -->
$media->customData[] = $customDataElement;

$mediaFiles = array();

$mediaFile = new ComcastMediaFile();
//$mediaFile->template = new ComcastArrayOfMediaFileField();
$mediaFile->allowRelease = true;
$mediaFile->bitrate = $flavorAsset1_Bitrate;
$mediaFile->contentType = ComcastContentType::_VIDEO;
$mediaFile->format = ComcastFormat::_FLV;
$mediaFile->length = $flavorAsset1_Duration;
$mediaFile->mediaFileType = ComcastMediaFileType::_INTERNAL;
$mediaFile->originalLocation = $flavorAsset1_URL;
$mediaFile->height = $flavorAsset1_Width;
$mediaFile->width = $flavorAsset1_Height;
$mediaFiles[] = $mediaFile;


$mediaFile = new ComcastMediaFile();
//$mediaFile->template = new ComcastArrayOfMediaFileField();
$mediaFile->allowRelease = true;
$mediaFile->bitrate = $flavorAsset2_Bitrate;
$mediaFile->contentType = ComcastContentType::_VIDEO;
$mediaFile->format = ComcastFormat::_FLV;
$mediaFile->length = $flavorAsset2_Duration;
$mediaFile->mediaFileType = ComcastMediaFileType::_INTERNAL;
$mediaFile->originalLocation = $flavorAsset2_URL;
$mediaFile->height = $flavorAsset2_Width;
$mediaFile->width = $flavorAsset2_Height;
$mediaFiles[] = $mediaFile;


$options = new ComcastAddContentOptions();
$options->generateThumbnail = false;
$options->publish = false;
$options->deleteSource = false;

try{
$results = $mediaService->addContent($media, $mediaFiles, $options);
var_dump($results);
}
catch(Exception $e){}
file_put_contents('err.log', $mediaService->getError());
file_put_contents('request.xml', $mediaService->request);
file_put_contents('response.xml', $mediaService->responseData);


