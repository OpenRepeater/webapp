<?php
#####################################################################################################
# Sound Devices Class
#####################################################################################################

class SoundDevices {

    public $device_list;
    public $device_in_count = 0;
    public $device_out_count = 0;


	public function __construct() {
		$this->device_list = [];
		$this->device_in_count = 0;
		$this->device_out_count = 0;
	}


	###############################################
	# Main Device Processor
	###############################################

	public function get_device_list($output = 'array') {		
		// Get ALSA Version from System
		exec("sudo orp_helper audio version", $version);
		
		// Capture Input Devices from System
		ob_start();
		passthru("sudo orp_helper audio inputs");
		$arecord_results = ob_get_clean();
		
		// Capture Output Devices from System
		ob_start();
		passthru("sudo orp_helper audio outputs");
		$aplay_results = ob_get_clean();
		
		// Build Advance Details Results
		$audio_details = '<h4>Audio Input Devices</h4>';
		$audio_details .= '<pre>'.$arecord_results.'</pre>';
		$audio_details .= '<h4>Audio Output Devices</h4>';
		$audio_details .= '<pre>'.$aplay_results.'</pre>';
		$audio_details .= '<p>'.$version[0].'</p>';
		
		
		// Sanitize arecored & aplay results, make a single string with no line feeds
		$arecord_results = trim(preg_replace('/\s+/', ' ', $arecord_results));
		$aplay_results = trim(preg_replace('/\s+/', ' ', $aplay_results));
		
		// Break results into group strings
		preg_match_all("/card\s[0-9].*?.subdevice\s#[0-9]/",$arecord_results,$input_dev_grps);
		preg_match_all("/card\s[0-9].*?.subdevice\s#[0-9]/",$aplay_results,$output_dev_grps);
		$input_dev_grps = $input_dev_grps[0]; // Remove multidimensional array generated by preg_match_all
		$output_dev_grps = $output_dev_grps[0]; // Remove multidimensional array generated by preg_match_all
		
		// Loop through inputs and add to device array
		foreach($input_dev_grps as $in_val) {
			preg_match('/card\s(.*?):/', $in_val, $in_cardnum);
			preg_match('/card\s.*?\[(.*?)\]/', $in_val, $in_label);
			preg_match('/card\s.*?\[.*?\].*?\[(.*?)\]/', $in_val, $in_type);
		
			$channels = [passthru ("sudo orp_helper audio channels $in_cardnum[1]")];
			// Write Left & Right Channels to Array
			$this->device_list[] = array('card' => $in_cardnum[1], 'label' => $in_label[1], 'type' => $in_type[1], 'direction' => "IN", 'channel' => 0, 'channel_label' => "ch:$channels");
			$this->device_list[] = array('card' => $in_cardnum[1], 'label' => $in_label[1], 'type' => $in_type[1], 'direction' => "IN", 'channel' => 1, 'channel_label' => "Right");
			$this->device_in_count = $this->device_in_count + 2; // this is because left and right are currently hard coded
		
		
			// Write Mono Input Channel to Array
		/*	$this->device_list[] = array('card' => $in_cardnum[1], 'label' => $in_label[1], 'type' => $in_type[1], 'direction' => "IN", 'channel' => 0, 'channel_label' => "Mono");
			$this->device_in_count = $this->device_in_count + 1; // this is because left and right are currently hard coded
		*/
		}
		
		// Loop through ouputs and add to device array
		foreach($output_dev_grps as $out_val) {
			preg_match('/card\s(.*?):/', $out_val, $out_cardnum);
			preg_match('/card\s.*?\[(.*?)\]/', $out_val, $out_label);
			preg_match('/card\s.*?\[.*?\].*?\[(.*?)\]/', $out_val, $out_type);
		
			// Write Left & Right Channels to Array
			$this->device_list[] = array('card' => $out_cardnum[1], 'label' => $out_label[1], 'type' => $out_type[1], 'direction' => "OUT", 'channel' => 0, 'channel_label' => "Left");
			$this->device_list[] = array('card' => $out_cardnum[1], 'label' => $out_label[1], 'type' => $out_type[1], 'direction' => "OUT", 'channel' => 1, 'channel_label' => "Right");
			$this->device_out_count = $this->device_out_count + 2; // this is because left and right are currently hard coded
		}

		if ($output == 'details') {
			return $audio_details;
		} else {
			return $this->device_list;
		}
	}



	###############################################
	# Return Counts
	###############################################

	public function get_device_in_count() {
		return $this->device_in_count;
	}


	public function get_device_out_count() {
		return $this->device_out_count;
	}

}