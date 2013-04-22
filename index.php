<?php
/*****
 * index.php
 * 
 * (c) Tommi Tuura 2012
 * This file glues stuff together. 
 * 
 * This is free software. See LICENSE for details.
 ****/

/******************************************************
 * NOTE: When adding content, you are NOT supposed to 
 * Change anything in this file. All of the content is 
 * defined in site.xml and all the *.shtml files.
 * If you want to add new .less and .js files, then you 
 * must do it here. 
 *****************************************************/

echo '<!DOCTYPE html>';
require_once('site.php');

$xmlfiledata = file_get_contents('site.xml');

if (!$xmlfiledata) {
echo <<<NOXMLFILE
<html><head><title>Error.</title></head>
<body><div class="error">Could not read the site.xml file.</div></body></html>
NOXMLFILE;
die();
}

$xml = new SimpleXMLElement($xmlfiledata);

/* TODO: Here we SHOULD make more checks but right now 
   we'll just trust that if we got the file read, everything 
   will be fine. Also, better checking would require us to 
   actually write a XML Schema or DTD and we just won't bother 
   right now.
*/

$site = new Site($xml);

$pageSelection = getSelectionParameter($_GET);

?>
<html>
<head>
	<meta charset="UTF-8" />
	<title><?php print (string)$site->getTitle(); ?></title>
	<?php /* You can add your own less styles here, if you want. 
	         .less files must be defined before the less.js file. */ ?>
    <link rel="stylesheet/less" type="text/css" href="styles.less" />
	<script src="less.js" type="text/javascript"></script>
	<?php /* You can add your own javascripts here, if you want. */ ?>

</head>
<body>
<div class="container">
<div class="header">
	<?php print (string)$site->getHeader(); ?>
</div>
<div class="navbar">
	<?php print (string)$site->getNavbar($pageSelection); ?>
</div>
<div class="content">
	<?php print (string)$site->getContent($pageSelection);  ?>
</div>
<div class="lastmodified">
	Last modification of this page: <?php print (string)date("d. m. Y H:s", $site->getLastModificationDate($pageSelection)); ?>
</div>

</div>
</body>
</html>
