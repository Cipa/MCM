<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>[+output.moduleName+]</title>
	[+output.css&js+]
	<script type="text/javascript">
		
		var sendTestUrl = '[+do.sendTest+]';
		var sendTestData = '[+output.queryString+]';
		
	</script>
	
</head>
<body>
	<h1>[+output.moduleName+] Module</h1>
	
	<div id="actions">
		  <ul class="actionButtons">
		  	  <li id="Button1">
				<a href="[+do.send+]"><img src="media/style/[+output.managerTheme+]/images/icons/save.png"> Send</a>
			  </li>
			  <li id="Button2">
				<a href="[+do.delete+]" class="js-button-send"><img src="media/style/[+output.managerTheme+]/images/icons/delete.png"> Delete </a>
			  </li>
			  <li id="Button3">
				<a href="[+do.main+]"><img src="media/style/[+output.managerTheme+]/images/icons/b06.gif"> Setup</a>
			  </li>
		  </ul>
	</div>
	
	<div class="sectionHeader">Details for "[+output.campaignTitle+]"</div>
	
	<div class="sectionBody">
		<p><strong>[+message.error+]
	    [+message.success+]</strong></p>
	    
	    <p>[+output.sendingToListHtml+]</p>
	    <p>[+output.sendingToGroupsHtml+]</p>
	    <p>[+output.sendingTypeHtml+]</p>

	</div>
	
	<div class="sectionHeader">Preview</div>
    <div class="sectionBody">
    	
    	<form action="#">
	    	<input type="text" class="inputBox" name="emails" placeholder="Comma separated list of emails"/>
	    	<input type="submit" class="js-button-send-test" value="Send Test"/>
	    	<span class="js-message"></span>
    	</form>
    	
    	<br />
    	
    	<iframe class="preview-template" src="[+do.previewFrame+]"></iframe>
    </div>

</body>
</html>