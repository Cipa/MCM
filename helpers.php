<?php


function parseTemplate($tpl, $tplVars, $rootPageUrl) {
	if($tpl) {
		
		$tpl = file_get_contents($tpl);
		
		foreach ($tplVars as $key => $value) {
			
			//detect do action -> current url + do action id
			$isDo = stripos($key, 'o.');
			
			if ($isDo !== false) {
				$value = $rootPageUrl . '&do=' . $value;
			}
			
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl); 
		}
		
		$tpl = preg_replace('/(\[\+.*?\+\])/' ,'', $tpl);
		
		return $tpl;
	}
	
	return "";
}

function rootPageURL() {
	
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	
	//allow only 1 do
	$aDo = explode('&', $_SERVER["REQUEST_URI"]);
	//remove do
	$_SERVER["REQUEST_URI"] = $aDo['0'] . '&' . $aDo['1'];
	
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["HTTP_X_REWRITE_URL"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["HTTP_X_REWRITE_URL"];
	}
	
	return $pageURL;
	
}


function makeFullPaths($content, $siteUrl){
	
	$replace = array();
	$replaceWith = array();
	
	//match links
	preg_match_all('(href="(.*?)")',$content,$matches);
	
	foreach($matches[1] as $key=>$m){
	    
	    $hasHttp = strpos($m, 'http');
	    $hasMailto = strpos($m, 'mailto');
	    
	    if($hasHttp === false && $hasMailto === false ){
	        $new = $siteUrl . $m;
	        array_push($replace, $m);
	        array_push($replaceWith, $new);
	    }
	
	}
	
	//match src
	preg_match_all('(src="(.*?)")',$content,$matches);
	
	foreach($matches[1] as $key=>$m){
	    
	    $hasHttp = strpos($m, 'http');
	    
	    if($hasHttp === false){
	        $new = $siteUrl . $m;
	        array_push($replace, $m);
	        array_push($replaceWith, $new);
	    }
	
	}
	
	$output = str_replace($replace, $replaceWith, $output);
	
	return $content;

}

function isValidEmail($email){
	
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    	return true;
    }
	return true;
}

function areValidEmails($emails){
	
	$aEmails = explode(',',$emails);
	$isValid = true;
	foreach($aEmails as $e){
		if(!isValidEmail($e)){
			return false;
		}
	}
	
	return $isValid;
}