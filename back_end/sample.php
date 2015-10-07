<?php
require_once "jssdk.php";
$jssdk = new JSSDK("YOURWEBID", "YOURSECRET");
$signPackage = $jssdk->getAccessToken();
echo "success";
?> 