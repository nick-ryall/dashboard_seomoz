<?php

	require_once 'lib/class.seomozapi.php';
	
	class extension_dashboard_seomoz extends Extension {
		private $params = array();
		
		public static $seomoz_cache;
	
		
		public function about() {
			return array(
				'name'			=> 'Dashboard SEOmoz',
				'version'		=> '1.0',
				'release-date'	=> '2011-09-22',
				'author'		=> array(
					'name'			=> 'Nick Ryall',
					'website'		=> 'http://randb.com.au/',
					'email'			=> 'nick@randb.com.au'
				),
				'description'	=> 'Uses Nick Dunn\'s Dashboard Extension to SEOmoz API information in a custom panel'
	 		);
		}
		
		
		public function getSubscribedDelegates() {
		    return array(
		        array(
		        	'page'		=> '/backend/',
		        	'delegate'	=> 'InitaliseAdminPageHead',
		        	'callback'	=> 'append_assets'
		        ),
		        array(
		            'page'      => '/backend/',
		            'delegate'  => 'DashboardPanelRender',
		            'callback'  => 'render_panel'
		        ),
		        array(
		            'page'      => '/backend/',
		            'delegate'  => 'DashboardPanelOptions',
		            'callback'  => 'dashboard_panel_options'
		        ),
		        array(
		            'page'      => '/backend/',
		            'delegate'  => 'DashboardPanelTypes',
		            'callback'  => 'dashboard_panel_types'
		        ),
		    );
		}
		
		
		public function dashboard_panel_types($context) {
		    $context['types']['seomoz_panel'] = 'SEOmoz Panel';
		}
		
		
		public function dashboard_panel_options($context) {
		    // make sure it's your own panel type, as this delegate fires for all panel types!
		    if ($context['type'] != 'seomoz_panel') return;
		

		    $config = $context['existing_config'];

		    
		    $fieldset = new XMLElement('fieldset', NULL, array('class' => 'settings'));
		    $fieldset->appendChild(new XMLElement('legend', 'My SEOmoz Account'));
		    
		     $p = new XMLElement('p', 'If you have an SEOmoz.org account, you can log in and find your credentials on the `http://www.seomoz.org/api` page.	If you don\'t have a free SEOmoz.org account, sign up, and visit the API page to retrieve your API credentials.');
		     
		    $fieldset->appendChild($p);
		    
		    $domain = Widget::Label('Domain', Widget::Input('config[domain]', $config['domain']));
		    $fieldset->appendChild($domain);
		
		    $access_id = Widget::Label('Access ID', Widget::Input('config[access_id]', $config['access_id']));
		    $fieldset->appendChild($access_id);
		    
		    $secret_key = Widget::Label('Secret Key', Widget::Input('config[secret_key]', $config['secret_key']));
		    $fieldset->appendChild($secret_key);
		
		    $context['form'] = $fieldset;
		
		}
		
		
		public function render_panel($context) {
		    if ($context['type'] != 'seomoz_panel') return;
		    $config = $context['config'];
		    $context['panel']->appendChild(extension_dashboard_seomoz::display_results($config['access_id'], $config['secret_key'], $config['domain']));
		}

		public function display_results($access_id, $secret_key, $domain) {
	
			try {
				
				$seomozapi = new SEOMozAPI( $access_id, $secret_key );
				$urlmetrics = json_decode( $seomozapi->urlmetrics( $domain ) );
	
				$target_url = preg_replace('!http(s)?:\/\/!', '', $domain);
				
				$attribution = str_replace( '/', '%252F', rtrim($target_url,"/") );
				$attribution = "http://www.opensiteexplorer.org/links/?site=".$attribution;
				
				$wrapper = new XMLElement('div');
				
				$info = new XMLElement('div');
				$info->setAttribute('class', 'info');
				
				
				//mozRank
				$rank_header = new XMLElement('h4', 'Domain mozRank');
				$info->appendChild($rank_header);
				$rank_info = new XMLElement('p', 'Measure of the mozRank <a href="http://www.opensiteexplorer.org/About#faq_5" target="_blank">(?)</a> of the domain in the Linkscape index');
				$info->appendChild($rank_info);
				
				$dl_results = new XMLElement('dl');
				
				//10 Point Score
				$dt_score = new XMLElement('dt', '10-point score');
				$dd_score = new XMLElement('dd', '<a target="_blank" href="'.$attribution.'">'.$urlmetrics->fmrp.'</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				//Raw Score
				$dt_score = new XMLElement('dt', 'Raw score');
				$dd_score = new XMLElement('dd', '<a target="_blank" href="'.$attribution.'">'.$urlmetrics->fmrr.'</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				//Domain Authority
				$dt_score = new XMLElement('dt', 'Domain Authority <a href="http://apiwiki.seomoz.org/w/page/20902104/Domain-Authority/" target="_blank">(?)</a>');
				$dd_score = new XMLElement('dd', '<a target="_blank" href="' . $attribution . '" target="_blank">' .$urlmetrics->pda . '</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				$info->appendChild($dl_results);
				
				
				//External Links
				$external_links_header = new XMLElement('h4', 'External Links to Homepage');
				$info->appendChild($external_links_header);
				
				$dl_results = new XMLElement('dl');
				
				$dt_score = new XMLElement('dt', 'The number of external (from other subdomains), juice passing links <a href="http://apiwiki.seomoz.org/w/page/13991139/Juice-Passing" target="_blank">(?)</a> to the target URL in the Linkscape index');
				$dd_score = new XMLElement('dd', '<a target="_blank" href="' . $attribution . '" target="_blank">' .$urlmetrics->ueid . '</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				$info->appendChild($dl_results);
				

				//Links to homepage
				$homepage_links_header = new XMLElement('h4', 'Links to Homepage');
				$info->appendChild($homepage_links_header);
				$dl_results = new XMLElement('dl');
				
				$dt_score = new XMLElement('dt', 'The number of internal and external, juice and non-juice passing links <a href="http://apiwiki.seomoz.org/w/page/13991139/Juice-Passing" target="_blank">(?)</a> to the target URL in the Linkscape index');
				$dd_score = new XMLElement('dd', '<a href="' . $attribution . '" target="_blank">' .$urlmetrics->uid . '</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				$info->appendChild($dl_results);

				
				//Homepage mozRank
				$homepage_mozrank_header = new XMLElement('h4', 'Homepage mozRank');
				$info->appendChild($homepage_mozrank_header);
				$homepage_mozrank_info = new XMLElement('p', 'Measure of the mozRank <a href="http://www.opensiteexplorer.org/About#faq_5" target="_blank">(?)</a> of the homepage URL in the Linkscape index');
				$info->appendChild($homepage_mozrank_info);
				
				$dl_results = new XMLElement('dl');
				
				//10 Point Score
				$dt_score = new XMLElement('dt', '10-point score');
				$dd_score = new XMLElement('dd', '<a href="'.$attribution.'">'.$urlmetrics->umrp.'</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				
				//Raw Score
				$dt_score = new XMLElement('dt', 'Raw score');
				$dd_score = new XMLElement('dd', '<a href="'.$attribution.'">'.$urlmetrics->umrr.'</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);

				//Homepage Authority
				$dt_score = new XMLElement('dt', 'Homepage Authority <a href="http://apiwiki.seomoz.org/Page-Authority" target="_blank">(?)</a>');
				$dd_score = new XMLElement('dd', '<a href="' . $attribution . '" target="_blank">' .$urlmetrics->upa . '</a>');
				
				$dl_results->appendChild($dt_score);
				$dl_results->appendChild($dd_score);
				
				$info->appendChild($dl_results);
				

			    $wrapper->appendChild($info);
			    return $wrapper;
			    
			} catch (Exception $e) { 
			   
			   $info = new XMLElement('div');
			   $info->setAttribute('class', 'info');
			   $info_header = new XMLElement('h4', 'No data found. Check your account details.');
			   $info->appendChild($info_header);
			   $info_header->appendChild(new XMLElement('p', '<code>'.(string)$e->getMessage().'</code>'));
			   return $info;
			} 	
		}
		
		/*-------------------------------------------------------------------------
			Utitilites:
		-------------------------------------------------------------------------*/
		

		public function append_assets($context) {
			$page = $context['parent']->Page;
			$page->addStylesheetToHead(URL . '/extensions/dashboard_seomoz/assets/dashboard.seomoz.index.css', 'screen', 1000);
		}

	}	
?>