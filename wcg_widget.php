<?php
/*
Plugin Name: World Community Grid (WCG) Widget
Plugin URI: http://www.freakcommander.de/1587/computer/wordpress/world-community-grid-widget/
Description: Allows the user to show WCG badges and statistics in sidebar.
Author: crille
Version: 3.0
Author URI: http://www.freakcommander.de/
License: GPL 2.0, @see http://www.gnu.org/licenses/gpl-2.0.html
*/

class WP_Widget_Wcg extends WP_Widget {
	
	// calculate paths
	private $pluginPath;
	private $data_dir;
	private $data_url;
	private $static_dir;
	private $static_url;
	
	//calculate path to files
	private $xml_userfile;
	private $template_file;
		
		
	function WP_Widget_Wcg() 
	{
		
		if (function_exists('load_plugin_textdomain')) {
			if ( !defined('WP_PLUGIN_DIR') ) {
				load_plugin_textdomain('wcg_widget', str_replace( ABSPATH, '', dirname(__FILE__) ) . '');
			} else {
				load_plugin_textdomain('wcg_widget', false, dirname(plugin_basename(__FILE__)) . '');
			}
		}

		
		
		$widget_ops = array('classname' => 'widget_wcg', 'description' => __('Allows the user to show WCG badges and statistics in sidebar', 'wcg_widget') );
		$control_ops = array('width' => 450);
		$this->WP_Widget('wcg', 'World Community Grid Widget', $widget_ops, $control_ops);
		
		
		// calculate paths
		$this->pluginPath = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		$this->data_dir = $this->pluginPath.'data/';
		$this->data_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'data/';
		$this->static_dir = $this->pluginPath.'static/';
		$this->static_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'static/';
		
		//calculate path to files
		$this->xml_userfile = $this->data_dir.'wcg_user.xml';
		$this->template_file = $this->static_dir.'templates.xml';
	}

 
	function widget($args, $instance) 
	{
		// Ausgabefunktion
		extract($args, EXTR_SKIP);
		
		function SekundenZuFormatString($sekunden)
		{
			//Erstelle String mit folgendem Aufbau y:d:h:m:s
			//0:000:00:00

			//Jahr: 60*60*24*365 = 31536000 Sekunden
			$jahre = floor($sekunden / 31536000);
			$sekunden = $sekunden % 31536000;

			//Tag: 60*60*24 = 86400 Sekunden
			$tage = floor($sekunden / 86400);
			$tage = str_pad($tage, 3, "0", STR_PAD_LEFT);
			$sekunden = $sekunden % 86400;

			//Stunde: 60*60 = 3600 Sekunden
			$stunden = floor($sekunden / 3600);
			$stunden = str_pad($stunden, 2, "0", STR_PAD_LEFT);
			$sekunden = $sekunden % 3600;

			//Minute: 60 Sekunden
			$minuten = floor($sekunden / 60);
			$minuten = str_pad($minuten, 2, "0", STR_PAD_LEFT);
			$sekunden = $sekunden % 60;

			$sekunden = str_pad($sekunden, 2, "0", STR_PAD_LEFT);

			return $jahre.':'.$tage.':'.$stunden.':'.$minuten.':'.$sekunden;
		}	
		
		
		// get widget values
    	$title = $instance['title'];
    	$wcg_name = $instance['wcg_name'];
		$wcg_code = $instance['wcg_code'];
		$above_html = $instance['above_html'];
		$badge_html = $instance['badge_html'];
		$below_html = $instance['below_html'];
		
		
		//ERROR-Handling
		$error_message = "";
		if (ini_get('allow_url_fopen') != 1)
		{
			$error_message .= __('allow_url_fopen is off in your php.ini.', 'wcg_widget');
		}
		if (!class_exists('SimpleXMLElement'))
		{
			$error_message .= __('Your PHP has no SimpleXML extension.', 'wcg_widget');
		}
		
		if (!file_exists($this->data_dir))
		{
			$error_message .= sprintf(__('The directory %s is not available.', 'wcg_widget'), $this->data_dir);
		}
		else
		{
			if (!is_writable($this->data_dir))
			{
				$error_message .= sprintf(__('The directory %s is not writable.', 'wcg_widget'), $this->data_dir);
			}
			if (strpos($_SERVER["SERVER_SOFTWARE"], 'Win') === false) //setting windows directory executable?!?
			{
				if (!is_executable($this->data_dir))
				{
					$error_message .= sprintf(__('The directory %s is not executable.', 'wcg_widget'), $this->data_dir);
				}
			}
			if (file_exists($this->xml_userfile))
			{
				if (!is_writable($this->xml_userfile))
				{
					$error_message .= sprintf(__('The file %s is not writable.', 'wcg_widget'), $this->xml_userfile);
				}
			}
		}
		if (!file_exists($this->static_dir))
		{
			$error_message .= sprintf(__('The directory %s is not available.', 'wcg_widget'), $this->static_dir);
		}
		else
		{
			if (!file_exists($this->template_file))
			{
				$error_message .= sprintf(__('The file %s is not available.', 'wcg_widget'), $this->template_file);
			}
		}
		
		//if wcg_data.xml is too old load a new one
		//update wcg_data.xml file
		if (empty($error_message) AND //kein vorhergehender Error
			(date(strtotime('-1 days')) > filemtime($this->xml_userfile) OR //file was not updated last 
			filesize($this->xml_userfile) == 0)) 
		{
			$remote_file = 'http://www.worldcommunitygrid.org/verifyMember.do?name='.urlencode($wcg_name).'&code='.urlencode($wcg_code);
			$xml_string = file_get_contents($remote_file);
			if ($xml_string !== FALSE)
			{
				$xml_try = @simplexml_load_string($xml_string);
				if ($xml_try !== FALSE)
				{
					if ($xml_try->getName() == "Error" OR $xml_try->getName() == "unavailable")
					{
						$error_message .= __('WCG error', 'wcg_widget').': '.$xml_try;
					}
					else
					{
						if (!copy($remote_file, $this->xml_userfile))
						{
							$error_message .= sprintf(__('Unable to copy %s to %s.', 'wcg_widget'), $remote_file, $this->xml_userfile);
						}
					}
				}
				else
				{
					if (!file_exists($this->xml_userfile) OR filesize($this->xml_userfile) == 0)
					{
						$error_message .= sprintf(__('The wcg file %s cannot be parsed by SimpleXML.'),$remote_file);
					}
				}
			}
			else
			{
				if (!file_exists($this->xml_userfile) OR filesize($this->xml_userfile) == 0)
				{
					$error_message .= sprintf(__('The wcg file %s is not available.', 'wcg_widget'), $remote_file);
				}
			}
		}
		
		//Load userfields
		$xml = simplexml_load_file($this->xml_userfile);
		if ($xml !== false)
		{
			if (isset($xml->MemberStatsByProjects))
			{
				unset($ufield);
				foreach ($xml->MemberStatsByProjects->children() as $project)
				{
					$shortname = $project->ProjectShortName;
					if (isset($instance["$shortname"]))
					{
						foreach ($instance["$shortname"] as $userfield)
						{
							$ufield["$shortname"][] = htmlspecialchars($userfield, ENT_QUOTES);
						}
					}
				}
			}
		}
		
		if (empty($error_message))
		{
			
			$MemberName = $xml->MemberStats->MemberStat->Name;
			$MemberID = $xml->MemberStats->MemberStat->MemberId;
			$RegisterDate = $xml->MemberStats->MemberStat->RegisterDate;
			$LastResult = $xml->MemberStats->MemberStat->LastResult;
			$NumDevices = $xml->MemberStats->MemberStat->NumDevices;
			
			$TotalRunTime = SekundenZuFormatString($xml->MemberStats->MemberStat->StatisticsTotals->RunTime);
			$TotalRunTimeRank = $xml->MemberStats->MemberStat->StatisticsTotals->RunTimeRank;
			$TotalPoints = $xml->MemberStats->MemberStat->StatisticsTotals->Points;
			$TotalPointsRank = $xml->MemberStats->MemberStat->StatisticsTotals->PointsRank;
			$TotalResults = $xml->MemberStats->MemberStat->StatisticsTotals->Results;
			$TotalResultsRank = $xml->MemberStats->MemberStat->StatisticsTotals->ResultsRank;
			
			$AverageRunTimePerDay = SekundenZuFormatString($xml->MemberStats->MemberStat->StatisticsAverages->RunTimePerDay);
			$AverageRunTimePerResult = SekundenZuFormatString($xml->MemberStats->MemberStat->StatisticsAverages->RunTimePerResult);
			$AveragePointsPerHourRunTime = round(floatval($xml->MemberStats->MemberStat->StatisticsAverages->PointsPerHourRunTime),2);
			$AveragePointsPerDay = round(floatval($xml->MemberStats->MemberStat->StatisticsAverages->PointsPerDay),2);
			$AveragePointsPerResult = round(floatval($xml->MemberStats->MemberStat->StatisticsAverages->PointsPerResult),2);
			$AverageResultsPerDay = round(floatval($xml->MemberStats->MemberStat->StatisticsAverages->ResultsPerDay),2);
			
			//team tags
			if (isset($xml->TeamHistory->Team))
			{
				$i = 0;
				foreach ($xml->TeamHistory->children() as $teamitem)
				{
					$teamName = $teamitem->Name;
					$teamId = $teamitem->TeamId;
					
					$TeamJoinDate = $teamitem->JoinDate;
					
					if (isset($teamitem->RetireDate))
					{
						$TeamRetireDate = $teamitem->RetireDate;
					}
					else
					{
						$TeamRetireDate = FALSE;
					}
					$teamRunTime = SekundenZuFormatString($teamitem->StatisticsTotals->RunTime);
					$teamPoints = $teamitem->StatisticsTotals->Points;
					$teamResults = $teamitem->StatisticsTotals->Results;
					
					if ($i == 0 AND $TeamRetireDate === FALSE)
					{
						//if it is the current team allow tags like [TeamName]
						$search_abovebelow_html_team = array('[TeamName]', '[TeamId]', '[TeamRunTime]', '[TeamPoints]', '[TeamResults]');
						$replace_abovebelow_html_team = array($teamName, $teamId, $teamRunTime, $teamPoints, $teamResults);
						$above_html = str_replace($search_abovebelow_html_team, $replace_abovebelow_html_team, $above_html);
						$below_html = str_replace($search_abovebelow_html_team, $replace_abovebelow_html_team, $below_html);
						
						$muster_team = '/\[(TeamJoinDate) ([A-Za-z\-\.\:\\\ ]+)\]/em';
						$ersetzen_team =  "date(\"\\2\", strtotime(\$\\1))";
						$above_html = preg_replace($muster_team, $ersetzen_team, $above_html);
						$below_html = preg_replace($muster_team, $ersetzen_team, $below_html);
					}
					
					//all teams in team history have tags like [TeamName|1] etc
					$search_abovebelow_html_team = array('[TeamName|'.$i.']', '[TeamId|'.$i.']', '[TeamRunTime|'.$i.']', '[TeamPoints|'.$i.']', '[TeamResults|'.$i.']');
					$replace_abovebelow_html_team = array($teamName, $teamId, $teamRunTime, $teamPoints, $teamResults);
					$above_html = str_replace($search_abovebelow_html_team, $replace_abovebelow_html_team, $above_html);
					$below_html = str_replace($search_abovebelow_html_team, $replace_abovebelow_html_team, $below_html);
					
					if ($TeamRetireDate === FALSE)
					{
						$muster_team = '/\[(TeamJoinDate)\|'.$i.' ([A-Za-z\-\.\:\\\ ]+)\]/em';
					}
					else
					{
						$muster_team = '/\[(TeamRetireDate|TeamJoinDate)\|'.$i.' ([A-Za-z\-\.\:\\\ ]+)\]/em';
					}
					$ersetzen_team =  "date(\"\\2\", strtotime(\$\\1))";
					$above_html = preg_replace($muster_team, $ersetzen_team, $above_html);
					$below_html = preg_replace($muster_team, $ersetzen_team, $below_html);
					
					
					$i++;
				}
			}
			
			$search_abovebelow_html = array('[MemberName]', '[MemberID]', '[NumDevices]', '[TotalRunTime]', '[TotalRunTimeRank]', 
						'[TotalPoints]', '[TotalPointsRank]', '[TotalResults]', '[TotalResultsRank]', '[AverageRunTimePerDay]', '[AveragePointsPerHourRunTime]',
						'[AverageRunTimePerResult]', '[AveragePointsPerDay]', '[AveragePointsPerResult]', '[AverageResultsPerDay]',
						'[TeamName]', '[TeamId]', '[TeamJoinDate]', '[TeamRetireDate]', '[TeamRunTime]', '[TeamPoints]', '[TeamResults]'); 
			$replace_abovebelow_html = array($MemberName, $MemberID, $NumDevices, $TotalRunTime, $TotalRunTimeRank, 
						$TotalPoints, $TotalPointsRank, $TotalResults, $TotalResultsRank, $AverageRunTimePerDay, $AveragePointsPerHourRunTime,
						$AverageRunTimePerResult, $AveragePointsPerDay, $AveragePointsPerResult, $AverageResultsPerDay,
						); 
			
			
			$above_html = str_replace($search_abovebelow_html, $replace_abovebelow_html, $above_html);
			$below_html = str_replace($search_abovebelow_html, $replace_abovebelow_html, $below_html);
			
			$muster = '/\[(LastResult|RegisterDate) ([A-Za-z\-\.\:\\\ ]+)\]/em';
			$ersetzen =  "date(\"\\2\", strtotime(str_replace('T',' ',\$xml->MemberStats->MemberStat->\\1)))";
			
			$above_html = preg_replace($muster, $ersetzen, $above_html);
			$below_html = preg_replace($muster, $ersetzen, $below_html);
			
			$search_badge_html = array('[ProjectName]', '[ProjectShortName]', '[BadgeDescription]', '[BadgePictureUrl]', '[ProjectResearchUrl]', '[ProjectRunTime]', 
							'[ProjectPoints]', '[ProjectResults]');
			$badge_html_out = '';
			if (isset($xml->MemberStats->MemberStat->Badges))
			{
				foreach ($xml->MemberStats->MemberStat->Badges->children() as $badges)
				{	
					$ProjectName = $badges->ProjectName;
					$BadgeDescription = $badges->Resource->Description;
					//Badge Picture
					/////////////////////////////////////////////
					$RemoteBadgeFile = $badges->Resource->Url;
					//Get FileName
					$path_parts = pathinfo($RemoteBadgeFile);
					$BadgeFileName = $path_parts['filename'].'.'.$path_parts['extension'];
					//Local File Name
					$BadgePictureFile = $this->data_dir.$BadgeFileName;
					//Local File URL
					$BadgePictureUrl = $this->data_url.$BadgeFileName;
					if (!file_exists($BadgePictureFile))
					{
						if (!copy($RemoteBadgeFile, $BadgePictureFile))
						{
							$error_message .= sprintf(__('Unable to copy %s to %s.', 'wcg_widget'), $RemoteBadgeFile, $BadgePictureFile);
						}
						chmod($BadgePictureFile, 0666);
					}
					/////////////////////////////////////////////
					$ProjectShortName = substr(strrchr($BadgePictureUrl,"/"),1,-6);
					if (in_array($ProjectShortName, array('beta')))
					{
						$ProjectResearchUrl = 'http://www.worldcommunitygrid.org/';
					}
					else
					{
						$ProjectResearchUrl = 'http://www.worldcommunitygrid.org/research/'.$ProjectShortName.'/overview.do';
					}
					
					foreach ($xml->MemberStatsByProjects->children() as $project)
					{
						if ($ProjectShortName == $project->ProjectShortName)
						{
							$ProjectRunTime = SekundenZuFormatString($project->RunTime);
							$ProjectPoints = $project->Points;
							$ProjectResults = $project->Results;
							break 1;
						}
					}
					
					$number_uf = count($ufield["$ProjectShortName"]);
					unset($search_userfield_html);
					unset($replace_userfield_html);
					for($i=0;$i<$number_uf;$i++)
					{
						$search_userfield_html[] = '[UserField|'.($i+1).']';
						$replace_userfield_html[] = $ufield["$ProjectShortName"][$i];
						
					}
					$badge_html_uf = str_replace($search_userfield_html, $replace_userfield_html, $badge_html);
					
					$replace_badge_html = array($ProjectName, $ProjectShortName, $BadgeDescription, $BadgePictureUrl, $ProjectResearchUrl, $ProjectRunTime, $ProjectPoints, $ProjectResults);
					$badge_html_out .= str_replace($search_badge_html, $replace_badge_html, $badge_html_uf);
					
				}
			}
		}
		
		
		
    	// the widget's form
		echo $before_widget . $before_title . $title . $after_title;
		echo '<div>';
        
		if (!empty($error_message))
		{
			echo __('Error', 'wcg_widget').': '.$error_message;
		}
		else
		{
			echo $above_html.$badge_html_out.$below_html;
		}
		if (date(strtotime('-14 days')) > filemtime($this->xml_userfile))
		{
			echo sprintf(__('Warning: %s was not updated in the last 14 days.', 'wcg_widget'), $this->xml_userfile);
		}
		echo '</div>';
    	echo $after_widget;
		
		clearstatcache();
	}
 
	function update($new_instance, $old_instance) 
	{	
		
		//update values
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);		
		$instance['wcg_name'] = strip_tags($new_instance['wcg_name']);
		$instance['wcg_code'] = strip_tags($new_instance['wcg_code']);
		$instance['templateid'] = strip_tags($new_instance['templateid']);
		
		if (strval($instance['templateid']) === '0')
		{
			//if html/code fields are used
			$instance['above_html'] = $new_instance['above_html'];
			$instance['badge_html'] = $new_instance['badge_html'];
			$instance['below_html'] = $new_instance['below_html'];
		}
		else
		{
			//if a template is used
			$xml_save = simplexml_load_file($this->template_file);
			foreach ($xml_save->children() as $template_save)
			{
				if (strval($instance['templateid']) == strval($template_save->Id))
				{
					$instance['above_html'] = "".$template_save->AboveHtml;
					$instance['badge_html'] = "".$template_save->BadgeHtml;
					$instance['below_html'] = "".$template_save->BelowHtml;
				}
			}
		}
		
		// Leere userfields löschen: arr_empty[1]..[#Userfields]
		// Wenn true, dann löschen $instance[%][1]
		
		$xml = simplexml_load_file($this->xml_userfile);
		if ($xml !== false)
		{
			if (isset($xml->MemberStatsByProjects))
			{
				$arr_empty = array();
				
				foreach ($xml->MemberStatsByProjects->children() as $project)
				{
					$shortname = $project->ProjectShortName;
					unset($instance["$shortname"]);
					$i=0;
					foreach ($new_instance["$shortname"] as $values)
					{
						$instance["$shortname"][] = strip_tags($values);
						if (trim(strip_tags($values)) !== "")
						{
							$arr_empty[$i] = false;
						} elseif(!isset($arr_empty[$i]))
						{
							$arr_empty[$i] = true;
						}
						$i++;
					}
				}
				
				foreach ($arr_empty as $key => $value)
				{
					if ($value)
					{
						foreach ($xml->MemberStatsByProjects->children() as $project)
						{
							$shortname = $project->ProjectShortName;
							unset($instance["$shortname"]["$key"]);
						}
					}
				}
			}
		}
		
		return $instance;
	}
 
	function form($instance) 
	{
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, 
		array( 	'wcg_name'=>'',
				'wcg_code'=>'',
				'templateid'=>'0',
				'title'=>__('World Community Grid', 'wcg_widget'), 
				'above_html'=>__('[MemberName] calculated [TotalResults] results & got the following badges:<br />', 'wcg_widget'),
				'badge_html'=>__('<a href="[ProjectResearchUrl]" title="[BadgeDescription]"><img src="[BadgePictureUrl]" alt="[BadgeDescription]"/></a>', 'wcg_widget'),
				'below_html'=>__('<br />WCG - technology solving problems - <a href="http://www.worldcommunitygrid.org/reg/viewRegister.do">register</a> & help.', 'wcg_widget') 
			));
		
		
		//load values
		$title = htmlspecialchars($instance['title'], ENT_QUOTES);
		$wcg_name = htmlspecialchars($instance['wcg_name'], ENT_QUOTES);
		$wcg_code = htmlspecialchars($instance['wcg_code'], ENT_QUOTES);
		$templateid = htmlspecialchars($instance['templateid'], ENT_QUOTES);
		$above_html = htmlspecialchars($instance['above_html'], ENT_QUOTES);
		$badge_html = htmlspecialchars($instance['badge_html'], ENT_QUOTES);
		$below_html = htmlspecialchars($instance['below_html'], ENT_QUOTES);
		
		$xml = simplexml_load_file($this->xml_userfile);
		if ($xml !== false)
		{
			if (isset($xml->MemberStatsByProjects))
			{
				unset($ufield);
				foreach ($xml->MemberStatsByProjects->children() as $project)
				{
					$shortname = $project->ProjectShortName;
					if (isset($instance["$shortname"]))
					{
						foreach ($instance["$shortname"] as $userfield)
						{
							$ufield["$shortname"][] = htmlspecialchars($userfield, ENT_QUOTES);
						}
					}
				}
			}
		}
		
		
		//Widget admin panel
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('title').'">' . __('Title', 'wcg_widget').':'.
		'<br /><input style="width: 400px;" id="'.$this->get_field_name('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('wcg_name').'">' .  __('WCG Member Name', 'wcg_widget').':'.
		'<br /><input style="width: 400px;" id="'.$this->get_field_name('wcg_name').'" name="'.$this->get_field_name('wcg_name').'" type="text" value="'.$wcg_name.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('wcg_code').'">' .  __('WCG Verification Code', 'wcg_widget').': ('.__('see <a href="https://secure.worldcommunitygrid.org/ms/viewMyProfile.do">your profile</a>)', 'wcg_widget') .
		'<br /><input style="width: 400px;" id="'.$this->get_field_name('wcg_code').'" name="'.$this->get_field_name('wcg_code').'" type="text" value="'.$wcg_code.'" /></label></p>';?>
		
		<?php
		//template div
		$xml = simplexml_load_file($this->template_file);
		if ($xml !== false)
		{
			//(<a href="??">view/rate/upload more templates here</a>)
			echo '<p style="text-align:right;border-bottom:1px dashed black;"><label for="'.$this->get_field_name('templateid').'">' .  __('Choose a template', 'wcg_widget').':<br />';
			$check = 'checked="checked"';
			foreach ($xml->children() as $template)
			{
				echo "<p style=\"border-bottom:1px dashed black;text-align:right;\">";
				echo "<input type=\"radio\" name=\"".$this->get_field_name('templateid')."\" id=\"wcg_widget-templateid".$template->Id."\" value=\"".$template->Id."\" style=\"margin-right:10px;\" ";
				if ($templateid == strval($template->Id))
				{
					echo $check;
				}
				echo " />";
				echo "<img src=\"".$this->static_url.$template->PreviewFileName."\" style=\"vertical-align:middle;\"/>";
				echo "</p>";
			}
			//no template entry
			echo "<p style=\"text-align:right;\"><input type=\"radio\" name=\"".$this->get_field_name('templateid')."\" id=\"wcg_widget-templateid0\" value=\"0\" ";
			if ($templateid === '0')
			{
				echo $check;
			}
			echo " /> ".__("No Template", 'wcg_widget').": <span style=\"font-size:smaller;font-style:italic; \">".__("use the HTML/Code input fields", 'wcg_widget')."</span><br />";
			echo '<label></p>';
		}

		if ($templateid === '0')
		{
			echo '<div id="code">';
		}
		else
		{
			echo '<div id="code" style="display:none;">';
		}
		?>
		<script type="text/javascript">
			function template_upload(cur_form)
			{
				var altAction = cur_form.action;
				cur_form.action = 'http://www.freakcommander.de/wcg-widget/receive_template.php';
				cur_form.target = '_blank';
				cur_form.submit();
				cur_form.action = altAction;
			}
		</script>
		<?php
		echo '<p style="text-align:right;"><input type="button" onClick="template_upload(this.form)" value="'.__('Upload your template for other users', 'wcg_widget').'" /></p>';
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('above_html').'">' .  __('What HTML should precede the badge items', 'wcg_widget').':'.
		' <textarea style="width: 400px;" rows="3" id="'.$this->get_field_name('above_html').'" name="'.$this->get_field_name('above_html').'">'.$above_html.'</textarea></label></p>';
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('badge_html').'">' .  __('HTML of badge items', 'wcg_widget').':'.
		' <textarea style="width: 400px;" rows="3" id="'.$this->get_field_name('badge_html').'" name="'.$this->get_field_name('badge_html').'">'.$badge_html.'</textarea></label></p>';
		echo '<p style="text-align:right;"><label for="'.$this->get_field_name('below_html').'">' .  __('What HTML should follow the badge items', 'wcg_widget').':' .
		' <textarea style="width: 400px;" rows="3" id="'.$this->get_field_name('below_html').'" name="'.$this->get_field_name('below_html').'">'.$below_html.'</textarea></label></p>';
		
		$show_userfield = count($ufield["$shortname"]);
		$xml = simplexml_load_file($this->xml_userfile);
		if ($xml !== false)
		{
			if (isset($xml->MemberStatsByProjects))
			{
				$jquery_insert = '<fieldset style="border:1px grey dashed;padding:5px;margin-top:5px;">';
				$jquery_insert .= '<legend style="font-size:16px;">##. '.__('userfield for badges','wcg_widget').':</legend>';
				foreach ($xml->MemberStatsByProjects->children() as $project)
				{
					$shortname = $project->ProjectShortName;
					$jquery_insert .= '<p style="text-align:right;font-size:9px;"><label for="'.$this->get_field_name($shortname).'">' .$project->ProjectName.
				'<input style="width: 180px;" id="'.$this->get_field_name($shortname).'" name="'.$this->get_field_name($shortname).'[]" type="text" value="" /></label></p>';
				}
				$jquery_insert .= '</fieldset>';
				
				for ($i=0;$i<$show_userfield;$i++)
				{
					echo '<fieldset style="border:1px grey dashed;padding:5px;margin-top:5px;">';
					echo '<legend style="font-size:16px;">'.($i+1).'. '.__('userfield for badges','wcg_widget').':</legend>';
					foreach ($xml->MemberStatsByProjects->children() as $project)
					{
						$shortname = $project->ProjectShortName;
						echo '<p style="text-align:right;font-size:9px;"><label for="'.$this->get_field_name($shortname).'">' .$project->ProjectName.
				'<input style="width: 180px;" id="'.$this->get_field_name($shortname).'" name="'.$this->get_field_name($shortname).'[]" type="text" value="'.$ufield["$shortname"][$i].'" /></label></p>';
					}
					echo '</fieldset>';
				}
				
				echo '<p id="show_uf" style="text-align:right;"><input type="button" id="show_uf_button" onClick="showUF()" value="'.__('Show (more) userfields', 'wcg_widget').'" /></p>';
			}
			
		}
		?>
		<script type="text/javascript">
		var counter = <?php echo ($i); ?>;
		function showUF(){
			counter++;
			jQuery(document).ready(function($){
				$('<?php echo $jquery_insert; ?>'.replace('##', counter)).insertBefore("p[id='show_uf']");
			});
		};
		</script><?php
		
		echo '</div><input type="hidden" id="'.$this->get_field_id('submit').'" name="'.$this->get_field_id('submit').'" value="1" />';
	}
}

function widget_init()
{
	if ( !is_blog_installed() )
		return;
	
	register_widget('WP_Widget_Wcg');
	
	do_action('widgets_init');
}

function widget_admin_header()
{ 
?>
	
	<script type="text/javascript">
		
		jQuery(document).ready(function($){
			$("input[id^='wcg_widget-templateid']").live("click",function () {
				if ($(this).val() == '0')
				{
					$("div[id='code']").show("slow");
				}
				else
				{
					$("div[id='code']").hide("slow");
				}
			});
		});
		</script>
	

	<?php
}

add_action('admin_head-widgets.php','widget_admin_header');
add_action('init', 'widget_init', 1);	
?>