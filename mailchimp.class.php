<?php

class MailChimp {

	var $version = "0.1";
	
	var $errorMessage;
    
    var $errorCode;

    var $modx;
    
    var $apiKey;
	
	function MailChimp($apiKey) {
        $this->mcapi = new MCAPI($apiKey);
        
        $this->apiKey = $apiKey;
        
        global $modx;
        $this->modx = $modx;

    }
    
    /**
    * Get campaign
    */
	function campaign($id){
		
		$mcapi = $this->mcapi;
		
		$filters = array('campaign_id' => $id);
		$campaign = $mcapi->campaigns($filters);
		
		if($mcapi->errorCode){
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			return false;
		}else{
			return $campaign;
		}
		
	}
	
	//returns campaign id
	function createCampaign($type, $opts, $content, $groups=NULL){
		
		$mcapi = $this->mcapi;
		
		$segment_opts = NULL;
		if($groups){

			$cGroups = $this->groups($opts['list_id']);

			$groupList = array();
			foreach($groups as $g){
				array_push($groupList, $cGroups[$g]['name']);
			}

			//works with one group segment
			$groupings = $mcapi->listInterestGroupings($opts['list_id']);
			$groupingId = $groupings[0]['id'];
			
			$conditions = array();
			$conditions[] = array('field'=>'interests-'.$groupingId, 'op'=>'one', 'value'=> implode(',', $groupList));
			$segment_opts = array('match'=>'any', 'conditions'=>$conditions);
		}
		
		$result = $mcapi->campaignCreate($type, $opts, $content, $segment_opts);

		if($mcapi->errorCode){
			
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			
		}
		
		if($mcapi->errorCode){
			return false;
		}else{
			return $result;
		}
			
	}
	
	/**
    * Update campaign - $fields is an array of available fields
    */
	function updateCampaign($id, $fields){
		
		$mcapi = $this->mcapi;
	
		foreach($fields as $key => $f){
			
			if($key == 'groups'){
				
				
				$groupings = $mcapi->listInterestGroupings($fields['list_id']);
				$groupingId = $groupings[0]['id'];
				
				$segment_opts = array();
				
				$segment_opts['match'] = 'any';
				$segment_opts['conditions'] = array();
				$segment_opts['conditions'][0]['op'] = array();
				$segment_opts['conditions'][0]['op'] = 'one';
				$segment_opts['conditions'][0]['value'] = $f;
				$segment_opts['conditions'][0]['field'] = 'interests-'.$groupingId;

				$result = $mcapi->campaignUpdate($id, 'segment_opts', $segment_opts);
			
				if($mcapi->errorCode){
					
					$this->errorMessage = $mcapi->errorMessage;
					$this->errorCode = $mcapi->errorCode;
					//exit if error detected
					return false;
				}

			}else{
				
				
				$result = $mcapi->campaignUpdate($id, $key, $f);
			
				if($mcapi->errorCode){
					
					$this->errorMessage = $mcapi->errorMessage;
					$this->errorCode = $mcapi->errorCode;
					//exit if error detected
					return false;
				}
				
				
			}
			
			
		}
		
		return true;
			
	}
	
	/**
    * delete campaign - $fields is an array of available fields
    */
	function deleteCampaign($id){
		
		$mcapi = $this->mcapi;
		return $mcapi->campaignDelete($id);	
	}
	
	function campaignContent($id){
		
		$mcapi = $this->mcapi;
		
		return $mcapi->campaignContent($id);	
		
	}
	
	/**
     * Get the available subscribers lists
     */
	function lists(){
		
		$mcapi = $this->mcapi;
		$result = $mcapi->lists();
		
		$lists = array();
		
		if($mcapi->errorCode){
			
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			
		}else{
			
			foreach ($result['data'] as $list){
				
				$temp = array();
				
				$temp['name'] = $list['name'];
				$temp['webId'] = $list['web_id'];
				$temp['member_count'] = $list['stats']['member_count'];

				$lists[$list['id']] = $temp;
			}
		}
		
		if($mcapi->errorCode){
			return false;
		}else{
			return $lists;
		}
		
	}
	
	function groups($id){
		
		$mcapi = $this->mcapi;
		$result = $mcapi->listInterestGroupings($id);

		$lists = array();
		
		if($mcapi->errorCode){
			
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			
		}else{
			
			foreach ($result[0]['groups'] as $g){
				
				$temp = array();
				
				$temp['name'] = $g['name'];
				$temp['subscribers'] = $g['subscribers'];

				$lists[$g['bit']] = $temp;
			}
		}
		
		if($mcapi->errorCode){
			return false;
		}else{
			return $lists;
		}
		
	}
	
	/**
    * get id for the first grouping. This system only uses the forst one
    */
	function getGroupingId($id) {
	
		$mcapi = $this->mcapi;
		
		$result =  $mcapi->listInterestGroupings($id);
		
		return $result[0]['id'];
    }
	
	
	//returns boolean
	function send($cid){
		
		$mcapi = $this->mcapi;
		$result = $mcapi->campaignSendNow($cid);
		
		if($mcapi->errorCode){
			
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			return false;
		}else{
			return $result;
		}
			
	}
	

	//returns boolean
	function sendTest($cid, $test_emails){
		
		$mcapi = $this->mcapi;
		$result = $mcapi->campaignSendTest($cid, $test_emails, 'html');
		
		if($mcapi->errorCode){
			
			$this->errorMessage = $mcapi->errorMessage;
			$this->errorCode = $mcapi->errorCode;
			
		}
		
		if($mcapi->errorCode){
			return false;
		}else{
			return $result;
		}
			
	}

	function content($id, $type, $header, $footer){
		
		$content = 'Not available';
		
		if($type == 'full'){
			
			$content = $this->makeFullPaths(file_get_contents($this->docLink($id)));
			
		}
		
		if($type == 'content'){
			
			$content = '
				<style type="text/css">
					body{
						font-family: sans-serif;
						color: #505050;
					}
				</style>
				
				'.$header.$this->docContent($id) . $footer;
		}
		
		if($type == 'notification'){
		
			$content = '
				<style type="text/css">
					body{
						font-family: sans-serif;
						color: #505050;
					}
				</style>
				'.$header.'
				<h3>'.$this->docTitle($id).'</h3>
				<h4>New content is available. <a href="'.$this->docLink($id).'" target="_blank">Click here</a> to see it</h3>
								
			' . $footer;
		}
		
		return $content;
		
	}

	function docTitle($id){

		$doc = $this->modx->getDocument($id, 'pagetitle');
		return $doc['pagetitle'];
		
	}
	
	function docLink($id){
		
		return $this->modx->makeUrl($id, '', '', 'full');
		
	}
	
	function docContent($id){
		
		$doc = $this->modx->getDocument($id, 'content');
		return $this->makeFullPaths($this->modx->rewriteUrls($doc['content']));
		
	}
	
	function siteName(){
		
		return $this->modx->config['site_name'];
		
	}
	
	function emailSender(){
		
		return $this->modx->config['emailsender'];
		
	}
	
	
	//helper function to create full paths
	function makeFullPaths($content){
		
		$siteUrl = $this->modx->config['site_url'];
		
		$replace = array();
		$replaceWith = array();
		
		//match links
		preg_match_all('(href="(.*?)")',$content,$matches);
		
		foreach($matches[1] as $key=>$m){
		    
		    $hasHttp = strpos($m, 'http');
		    $hasMailto = strpos($m, 'mailto');
		    
		    if($hasHttp === false && $hasMailto === false ){
		        $new = $siteUrl . $m;
		        if(!in_array($new, $replaceWith)){
			        array_push($replace, $m);
			        array_push($replaceWith, $new);
		        }
		    }
		
		}
		
		//match src
		preg_match_all('(src="(.*?)")',$content,$matches);
		
		foreach($matches[1] as $key=>$m){
		    
		    $hasHttp = strpos($m, 'http');
		    
		    if($hasHttp === false){
		        $new = $siteUrl . $m;
		        if(!in_array($new, $replaceWith)){
			        array_push($replace, $m);
			        array_push($replaceWith, $new);
		        }
		    }
		
		}
		
		$content = str_replace($replace, $replaceWith, $content);
		
		return $content;
	
	}


}
?>