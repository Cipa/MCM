<?php
if(!IN_MANAGER_MODE){
	exit();
}

$do = (isset($_GET['do']))?(intval($_GET['do'])):(0);
$moduleId = (isset($_GET['id']))?(intval($_GET['id'])):(0);

global $modx;

$tplVars['output.moduleName'] = $moduleName = $modx->db->getValue($modx->db->select("name", $modx->getFullTableName('site_modules'), "id=" . $moduleId));
if(!$moduleName){
	exit();
}

include_once(MODX_BASE_PATH.'assets/modules/mailchimp/MCAPI.class.php');
include_once(MODX_BASE_PATH.'assets/modules/mailchimp/mailchimp.class.php');
include_once(MODX_BASE_PATH.'assets/modules/mailchimp/helpers.php');

$tplVars['output.modulePath'] = $modulePath = 'assets/modules/'.$moduleFolder.'/';
$moduleTemplatesPath = 'assets/modules/'.$moduleFolder.'/templates/';
$newsletterTemplatesPath = 'assets/modules/'.$moduleFolder.'/newsletter-templates/';


$tplVars['do.main'] = 0;
$tplVars['do.preview'] = 1;
$tplVars['do.previewFrame'] = 2;
$tplVars['do.sendTest'] = 3;
$tplVars['do.delete'] = 4;
$tplVars['do.send'] = 5;

$tplVars['output.managerTheme'] = $modx->config['manager_theme'];
$tplVars['output.siteName'] = $modx->config['site_name'];
$tplVars['output.rootPageUrl'] = $rootPageUrl = rootPageURL();

$tplVars['output.css&js'] = '
	<link rel="stylesheet" type="text/css" href="media/style/'.$modx->config['manager_theme'].'/style.css" />
	<link rel="stylesheet" type="text/css" href="../'.$modulePath.'css/style.css" />
	<script type="text/javascript" src="../'.$modulePath.'js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="../'.$modulePath.'js/script.js"></script>
';


$newsletterTypes = array(
	'notification' => 'Notification (message + link to the page)',
	'content' => 'Just the content (will try to make all the paths absolute)',
	'full' => 'Full document/html (make sure the content and the template are email campaign friendly, will try to make all the paths absolute and generate inline css)'
);

$mc = new MailChimp($apiKey);

switch ($do) {
	case 0: //!main
		
		
		if(isset($_SESSION['mcCampaignId'])){
			
			$campaign = $mc->campaign($_SESSION['mcCampaignId']);
			
			if($campaign){

				$cListId = $campaign['data'][0]['list_id'];
				$cTitle = $campaign['data'][0]['title'];
				$cType = isset($_SESSION['mcCampaignType'])?$_SESSION['mcCampaignType']:'';
				$cDocument = isset($_SESSION['mcCampaignDocument'])?$_SESSION['mcCampaignDocument']:'';
				
				$cGroups = isset($campaign['data'][0]['segment_opts']['conditions'][0]['value'])?$campaign['data'][0]['segment_opts']['conditions'][0]['value']:NULL;
				
				$tplVars['output.campaignName'] = "'$cTitle'";

			}else{
			
				//remove campaign
				$mc->deleteCampaign($_SESSION['mcCampaignId']);
				
				//unset session		
				unset($_SESSION['mcCampaignId']);
				unset($_SESSION['mcCampaignType']);
				unset($_SESSION['mcCampaignGroups']);
				unset($_SESSION['mcCampaignDocument']);
			
				$tplVars['message.error'] = $mc->errorMessage . ' Please start over.';
			}

		}else{
			
			//nothing to do. campaign not initialized
			
		}
		
		if(isset($campaign)){
			$tplVars['output.deleteButton'] = '<li id="Button2"><a href="'.$rootPageUrl.'&do='.$tplVars['do.delete'].'"><img src="media/style/'.$tplVars['output.managerTheme'].'/images/icons/delete.png"> Delete</a></li>';
		
		}

		
		//get available lists
		$mcLists = $mc->lists();
		
		$listsCheckboxes = "";
		$groupsCheckboxes = "";
		if(count($mcLists)){
			foreach($mcLists as $key => $l){
				
				
				$checked = '';
				if(isset($cListId) && $cListId == $key){
					$checked = 'checked="checked"';
				}
				
				$listsCheckboxes .= '<input type="radio" '.$checked.' name="list" value="'.$key.'"> ' . $l['name'] . ' ('.$l['member_count'].' members)<br/>';
				
				//cache groups
				$mcGroups = $mc->groups($key);
				foreach($mcGroups as $key => $g){
					$disabled="";
					if(!$g['subscribers']){
						$disabled = 'disabled="disabled"';
					}
					
					$checked = "";
					if(isset($cGroups) && $cGroups && in_array($key, $cGroups)){
						$checked = 'checked="checked"';
					}
					
					$groupsCheckboxes .= '<input type="checkbox" '.$disabled.' name="groups[]" '.$checked.' value="'.$key.'"> ' . $g['name'] . ' ('.$g['subscribers'].' subscribers)<br/>';
				}

			}
			
			$tplVars['output.lists'] = $listsCheckboxes;
			
			if($groupsCheckboxes){
				$tplVars['output.groups'] = '<div id="'.$key.'" class="list-groups">'.$groupsCheckboxes.'</div>';
			}else{
				$tplVars['output.groups'] = '<p><b>No groups are configured in mailchimp. We can do without.</b></p>';
			}
			
			
		}else{
			$tplVars['output.lists'] = '<p><b>No list are configured in mailchimp</b></p>';
		}
		
		if($templateIds){//comma separated list
			$result = $modx->db->query('
				SELECT id, pagetitle 
				FROM '.$modx->getFullTableName('site_content').'
				WHERE template IN ('.$templateIds.') AND published = 1 AND deleted = 0
				ORDER BY createdon DESC
			');
			$documentsRadio = '';
			while($row = $modx->db->getRow($result)) {
				
				$checked = '';
				if(isset($cDocument) && $cDocument == $row['id']){
					$checked = 'checked="checked"';
				}
				$documentsRadio .= '<input type="radio" '.$checked.' name="document" value="'.$row['id'].'"> ' . $row['pagetitle'] .'<br/>';
			}
			$tplVars['output.documents'] = $documentsRadio;
		}else{
			$tplVars['output.documents'] = '<p><b>You must configure at least one template id</b></p>';
		}
		
		$tplVars['output.types'] = "";
		foreach($newsletterTypes as $key=>$t){
			$checked = '';
			if(isset($cType) && $cType == $key){
				$checked = 'checked="checked"';
			}
			$tplVars['output.types'] .= '<input type="radio" '.$checked.' name="type" value="'.$key.'"/> '.$t.'<br/>';
		}
		
		$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "main.tpl";
		echo parseTemplate($tpl, $tplVars, $rootPageUrl);
		
		break;
		
	case 1: //!preview
		
		$mcLists = $mc->lists();
		
		if(isset($_POST['type'])){//detect submit
			
			$list = isset($_POST['list']) ? $_POST['list'] : FALSE;
			$type = isset($_POST['type']) ? $_POST['type'] : FALSE;
			$document = isset($_POST['document']) ? intval($_POST['document']) : FALSE;
			$groups = isset($_POST['groups']) ? $_POST['groups'] : NULL; //groups need to be sent as comma delimeted list or null
			
			//detect session campaign
			if(isset($_SESSION['mcCampaignId'])){
				
				//!campaign update - validate campaign and update
				$campaign = $mc->campaign($_SESSION['mcCampaignId']);

				if($campaign){
					//valid campaign
					
					$header = file_get_contents($modx->config['base_path'] . $newsletterTemplatesPath . 'header.tpl');
					$footer = file_get_contents($modx->config['base_path'] . $newsletterTemplatesPath . 'footer.tpl');
					
					$fields = array(
						'list_id' => $list,
						'subject' => $mc->docTitle($document),
						'title' => $mc->docTitle($document),
						'content' => array('html'=> $mc->content($document, $type, $header, $footer))
					);
					
					if($groups){
						$fields['groups'] = $groups;
					}
					
					//update
					$retval = $mc->updateCampaign($_SESSION['mcCampaignId'], $fields);
					$campaign = $mc->campaign($_SESSION['mcCampaignId']);

					if($retval){
						
						$_SESSION['mcCampaignType'] = $type;
						$_SESSION['mcCampaignGroups'] = $groups;
						$_SESSION['mcCampaignDocument'] = $document;
						
						//get campaign
						$tplVars['message.success'] = "Campaign updated!";
						
						$tplVars['output.campaignTitle'] = $mc->docTitle($document);
						
						//display settings
						$tplVars['output.queryString'] = '';
				
						$tplVars['output.queryString'] .= '&list='.$list;
						$tplVars['output.sendingToListHtml'] = 'Sending to list: ' . $mcLists[$list]['name'];
						
						$mcGroups = $mc->groups($list);
						
						if($groups){
						
							//get groups from campaign
							
							$tplVars['output.sendingToGroupsHtml'] = 'Sending to groups: <ul>';
							foreach($groups as $g){

								$tplVars['output.sendingToGroupsHtml'] .=  '<li>'.$mcGroups[$g]['name'].'</li>';

							}
							
							$tplVars['output.sendingToGroupsHtml'] .= '</ul>';
							
						}else{
							
							$tplVars['output.sendingToGroupsHtml'] = 'Sending to groups: All';
							
						}
						
						
						if($type == 'notification'){
						
							$tplVars['output.queryString'] .= '&type='.$type;
							$tplVars['output.sendingTypeHtml'] = 'Sending as: ' . $type;
							
							$doc = $modx->getDocument($document, 'pagetitle');
							
							$tplVars['output.queryString'] .= '&document='.$document;
							$tplVars['output.documentTitle'] = $doc['pagetitle'];
						}

						
					}else{
						
						//remove campaign
						$mc->deleteCampaign($_SESSION['mcCampaignId']);
						//unset session
						unset($_SESSION['mcCampaignId']);
						unset($_SESSION['mcCampaignType']);
						unset($_SESSION['mcCampaignGroups']);
						unset($_SESSION['mcCampaignDocument']);
						
						$tplVars['message.error'] = $mc->errorMessage;
					}

				}else{
					
					$tplVars['message.error'] = "Invalid campaign. Please start over.";
					
				}

			}else{// no session campaign
				
				//!create campaign
				$emailType = 'regular';
				
				$opts['list_id'] = $list;
				$opts['subject'] = $mc->docTitle($document);
				$opts['from_email'] = $mc->emailSender();
				$opts['from_name'] = $mc->siteName();
				$opts['auto_footer'] = FALSE;
				
				if($type == 'notification'){
					$opts['inline_css'] = TRUE;
				}else{
					$opts['inline_css'] = FALSE;
				}
				
				$header = file_get_contents($modx->config['base_path'] . $newsletterTemplatesPath . 'header.tpl');
				$footer = file_get_contents($modx->config['base_path'] . $newsletterTemplatesPath . 'footer.tpl');
				
				$content = array('html'=> $mc->content($document, $type, $header, $footer));
				
				$retval = $mc->createCampaign($emailType, $opts, $content, $groups);

				if($retval){
					
					//campaign created
					$_SESSION['mcCampaignId'] = $retval;
					$_SESSION['mcCampaignType'] = $type;
					$_SESSION['mcCampaignGroups'] = $groups;
					$_SESSION['mcCampaignDocument'] = $document;
					
					//get campaign
					//$campaign = $mc->campaign($retval);

					$tplVars['message.success'] = "Campaign updated!";
					
					$tplVars['output.campaignTitle'] = $mc->docTitle($document);
					
					//display settings
					$tplVars['output.queryString'] = '';
			
					$tplVars['output.queryString'] .= '&list='.$list;
					$tplVars['output.sendingToListHtml'] = 'Sending to list: ' . $mcLists[$list]['name'];
					
					$mcGroups = $mc->groups($list);

					if($groups){
						
						$tplVars['output.queryString'] .= '&groups='.implode('|', $groups);
						
						$tplVars['output.sendingToGroupsHtml'] = 'Sending to groups: <ul>';
						foreach($groups as $g){
							$tplVars['output.sendingToGroupsHtml'] .=  '<li>'.$mcGroups[$g]['name'].'</li>';
						}
						
						$tplVars['output.sendingToGroupsHtml'] .= '</ul>';
						
					}else{
						
						$tplVars['output.sendingToGroupsHtml'] = 'Sending to groups: All';
						
					}
					
					
					if($type == 'notification'){
					
						$tplVars['output.queryString'] .= '&type='.$type;
						$tplVars['output.sendingTypeHtml'] = 'Sending as: ' . $type;
						
						$doc = $modx->getDocument($document, 'pagetitle');
						
						$tplVars['output.queryString'] .= '&document='.$document;
						$tplVars['output.documentTitle'] = $doc['pagetitle'];
					}

					
					$tplVars['message.success'] = "Campaign created!";
					
				}else{
					$tplVars['message.error'] = $mc->errorMessage;
				}
				
			}

			$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "preview.tpl";
			echo parseTemplate($tpl, $tplVars, $rootPageUrl);
			
		}else{
			
			$tplVars['message.error'] = 'Invalid Settings. Please try again.';
			
			$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "preview.tpl";
			echo parseTemplate($tpl, $tplVars, $rootPageUrl);
			
		}
		
		break;
		
		
	case 2: //!generate preview
			
			
		if(isset($_SESSION['mcCampaignId'])){
		
			$campaign = $mc->campaign($_SESSION['mcCampaignId']);

			$content = $mc->campaignContent($_SESSION['mcCampaignId']);
			
			$tplVars['output.content'] = $content['html'];
			
		}else{
			
			$tplVars['message.error'] = 'Error: Something is missing';

		}
		
		$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "preview-frame.tpl";
		echo parseTemplate($tpl, $tplVars, $rootPageUrl);

		break;
		
	case 3: //!send test
		
		$emails = $_POST['emails'];
		
		$response = array();
		
		if(areValidEmails($emails)){
			
			$result = $mc->sendTest($_SESSION['mcCampaignId'], explode(',', $emails));
			if($result){
				$response['success'] = 'Test sent';
			}else{
				$response['error'] = $mc->errorMessage;
			}

		}else{
			$response['error'] = 'Invalid email(s)';
		}

		$tplVars['output.content'] = json_encode($response);
		
		$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "ajax.tpl";
		echo parseTemplate($tpl, $tplVars, $rootPageUrl);
		
		break;
		
	case 4: //!delete
		
		//remove campaign
		$result = $mc->deleteCampaign($_SESSION['mcCampaignId']);
		//unset session
		unset($_SESSION['mcCampaignId']);
		unset($_SESSION['mcCampaignType']);
		unset($_SESSION['mcCampaignGroups']);
		unset($_SESSION['mcCampaignDocument']);
		
		if($result){
			$tplVars['message.success'] = 'Campaign deleted.';
		}else{
			$tplVars['message.error'] = $mc->errorMessage;
		}
		
		$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "deleted.tpl";
		echo parseTemplate($tpl, $tplVars, $rootPageUrl);
		
		break;
		
	case 5: //!send
		//send
		$result = $mc->send($_SESSION['mcCampaignId']);

		//unset session
		unset($_SESSION['mcCampaignId']);
		unset($_SESSION['mcCampaignType']);
		unset($_SESSION['mcCampaignGroups']);
		unset($_SESSION['mcCampaignDocument']);
		
		if($result){
			$tplVars['message.success'] = 'Campaign sent. For more details about this campaign login in mailchimp';
		}else{
			$tplVars['message.error'] = $mc->errorMessage;
		}
		
		$tpl = $modx->config['base_path'] . $moduleTemplatesPath . "sent.tpl";
		echo parseTemplate($tpl, $tplVars, $rootPageUrl);
		
		break;
}
