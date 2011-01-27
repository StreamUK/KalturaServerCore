<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Kaltura - Test Me Console</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<link rel="stylesheet" type="text/css" href="css/code.example.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.tooltip.css" />
	<script type="text/javascript" src="js/jquery-1.3.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.bgiframe.pack.js"></script>
	<script type="text/javascript" src="js/jquery.dimensions.pack.js"></script>
	<script type="text/javascript" src="js/jquery.tooltip.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.7.1.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.json.min.js"></script>
	<script type="text/javascript" src="js/code.example.js"></script>
	<script type="text/javascript" src="js/main.js?r35598"></script>
</head>
<body>
	<?php 
		require_once("../../bootstrap.php");
		ActKeyUtils::checkCurrent();
		KalturaLog::setContext("TESTME");
		
		$config = new Zend_Config_Ini("../../config/testme.ini");
		$indexConfig = $config->get('testme');
		
		$include = $indexConfig->get("include");
		$exclude = $indexConfig->get("exclude");
		
		$clientGenerator = new DummyForDocsClientGenerator();
		$clientGenerator->setIncludeOrExcludeList($include, $exclude);
		$clientGenerator->load();
		
		$list = array();
		$services = $clientGenerator->getServices();
		foreach($services as $serviceName => $serviceReflector)
		{
			if($serviceReflector->isDeprecated() || $serviceReflector->isServerOnly())
				unset($services[$serviceName]);
		}
	?>
<ul id="kmcSubMenu">
 	<li class="active">
     <a href="#">Test Console</a>
    </li>
    <li>
     <a href="../testmeDoc/index.php">API Documentation</a>
    </li>
   </ul>	
   <div class="testme">
	<div class="left">
		<div class="left-content">
			<!--p>
				<a href="../testmeDoc/index.php" target="_blank">Kaltura API V3 Documentation</a>
			</p-->
			<div class="param">
				<label for="history">History: </label>
				<select name="history"></select>
			</div>
			
			<div class="param">
				<label for="ks">KS (string):</label><input type="text" class="" name="ks" size="30"/> <input id="chk-ks" type="checkbox" checked="checked"/>
			</div>
			
			<div class="param">
				<label for="ks">No Cache (bool):</label><input type="text" class="" name="nocache" size="30"/> <input type="checkbox"/>
			</div>

			<div class="param">
				<label for="service">Select service:</label>
				<select name="service">
					<?php foreach($services as $serviceReflector): ?>
					<option value="<?php echo $serviceReflector->getServiceId();?>"><?php echo $serviceReflector->getServiceName(); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="param">
				<label for="action">Select action:</label>
				<select name="action">
					<option>Select a service...</option>
				</select>
				<img id="actionHelp" src="images/help.png" class="help" title="" />
			</div>

			<div>
				<div id="action-params">
				</div>
				<div id="objects-containter"></div>
			</div>
			<div>
				<button id="send" type="button">Send</button>
			</div>
			
			<form action="" method="post" target="result">
			</form>
		</div>
	</div>
	<div class="right">
		<iframe id="result" name="result" src=""></iframe>
	</div>
	</div>
	<ul id="codeSubMenu">
 		<li class="code-menu code-menu-php active">
 			<a href="#" onclick="switchToPHP()">PHP</a>
 		</li>
 		<li class="code-menu code-menu-java">
 			<a href="#" onclick="switchToJava()">Java</a>
 		</li>
 		<li class="code-menu">
			<a class="code-menu-toggle" href="#" onclick="toggleCode()" id="codeToggle">Hide Code Example</a>
		</li>
	</ul>	
	<div class="code" id="codeExample">
   		<div id="example"></div>
	</div>
</body>
</html>
