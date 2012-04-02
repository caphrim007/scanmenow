<?php

/**
* This report is a port of the apc.php script to
* the report system.
*
* It uses code taken directly from the apc.php
* script but which has thereafter been cleaned
* up to conform with coding standards and
* report design standards.
*
* @author Tim Rupp <caphrim007@gmail.com>
* @author Ralf Becker <beckerr@php.net>
* @author Rasmus Lerdorf <rasmus@php.net>
* @author Ilia Alshanetsky <ilia@prohost.org>
*/
class Reports_ApcCacheController extends Reports_Abstract {
	const IDENT = __CLASS__;
	const GRAPH_SIZE = 200;
	const DATE_FORMAT = 'Y/m/d H:i:s';

	public function indexAction() {
		$apcInstalled = false;
		$memFreeInt = 0;
		$memFreePercent = 0;
		$apcVersion = null;

		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$phpVersion = phpversion();

		$serverName = $_SERVER['SERVER_NAME'];
		$serverSoftware = $_SERVER['SERVER_SOFTWARE'];

		$apcCache = new Metrx_ApcCache();
		$cache = $apcCache->read();

		if (function_exists('apc_cache_info')) {
			$rawCache = apc_cache_info();
			$mem = apc_sma_info();
			if($mem['num_seg'] > 1 || $mem['num_seg'] == 1 && count($mem['block_lists'][0]) > 1) {
				$mem_note = "Memory Usage<br /><font size=-2>(multiple slices indicate fragments)</font>";
			} else {
				$mem_note = "Memory Usage";
			}

			$apcInstalled = true;
			$apcVersion = phpversion('apc');

			$this->view->assign(array(
				'memFreeInt' => $this->_bSize($cache['memFreeInt']),
				'memFreePercent' => $cache['memFreePercent'],
				'memUsedInt' => $this->_bSize($cache['memUsedInt']),
				'memUsedPercent' => $cache['memUsedPercent'],
				'hitInt' => $cache['hitInt'],
				'hitPercent' => $cache['hitPercent'],
				'missInt' => $cache['missInt'],
				'missPercent' => $cache['missPercent'],
				'memNote' => $mem_note,
				'fragmentation' => $cache['fragmentation'],
				'startTime' => date(self::DATE_FORMAT, $rawCache['start_time']),
				'uptime' => $this->_duration($rawCache['start_time']),
				'apcVersion' => $apcVersion,
				'cache' => $cache
			));
		}

		$this->view->assign(array(
			'phpVersion' => $phpVersion,
			'serverName' => $serverName,
			'serverSoftware' => $serverSoftware,
			'apcInstalled' => $apcInstalled
		));
	}

	protected function _duration($ts) {
		$str = '';
		$time = time();

		$years = (int)((($time - $ts)/(7*86400))/52.177457);
		$rem = (int)(($time-$ts)-($years * 52.177457 * 7 * 86400));
		$weeks = (int)(($rem)/(7*86400));
		$days = (int)(($rem)/86400) - $weeks*7;
		$hours = (int)(($rem)/3600) - $days*24 - $weeks*7*24;
		$mins = (int)(($rem)/60) - $hours*60 - $days*24*60 - $weeks*7*24*60;

		if($years == 1) {
			$str .= "$years year, ";
		}

		if($years > 1) {
			$str .= "$years years, ";
		}

		if($weeks==1) $str .= "$weeks week, ";
		if($weeks>1) $str .= "$weeks weeks, ";
		if($days==1) $str .= "$days day,";
		if($days>1) $str .= "$days days,";
		if($hours == 1) $str .= " $hours hour and";
		if($hours>1) $str .= " $hours hours and";
		if($mins == 1) $str .= " 1 minute";
		else $str .= " $mins minutes";
		return $str;
	}

	protected function _fillArc($im, $centerX, $centerY, $diameter, $start, $end, $color1, $color2, $text = '', $placeindex = 0) {
		$r = $diameter / 2;
		$w = deg2rad((360 + $start + ($end - $start) / 2) % 360);

		if (function_exists("imagefilledarc")) {
			// exists only if GD 2.0.1 is avaliable
			imagefilledarc($im, $centerX + 1, $centerY + 1, $diameter, $diameter, $start, $end, $color1, IMG_ARC_PIE);
			imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2, IMG_ARC_PIE);
			imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color1, IMG_ARC_NOFILL|IMG_ARC_EDGED);
		} else {
			imagearc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start + 1)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end - 1))   * $r, $centerY + sin(deg2rad($end))   * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end))   * $r, $centerY + sin(deg2rad($end))   * $r, $color2);
			imagefill($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $color2);
		}

		if ($text) {
			if ($placeindex > 0) {
				imageline($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2,$diameter, $placeindex * 12,$color1);
				imagestring($im, 4, $diameter, $placeindex * 12, $text, $color1);
				
			} else {
				imagestring($im, 4, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $text, $color1);
			}
		}
	} 

	protected function _textArc($im, $centerX, $centerY, $diameter, $start, $end, $color1, $text, $placeindex = 0) {
		$r = $diameter / 2;
		$w = deg2rad((360 + $start + ($end - $start) / 2) % 360);

		if ($placeindex > 0) {
			imageline($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2,$diameter, $placeindex * 12, $color1);
			imagestring($im, 4, $diameter, $placeindex * 12, $text, $color1);
				
		} else {
			imagestring($im, 4, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $text, $color1);
		}
	} 
	
	protected function _fillBox($im, $x, $y, $w, $h, $color1, $color2, $text = '', $placeindex = '') {
		$x1 = $x + $w - 1;
		$y1 = $y + $h - 1;

		imagerectangle($im, $x, $y1, $x1+1, $y+1, $color1);
		if($y1>$y) imagefilledrectangle($im, $x, $y, $x1, $y1, $color2);
		else imagefilledrectangle($im, $x, $y1, $x1, $y, $color2);
		imagerectangle($im, $x, $y1, $x1, $y, $color1);
		if ($text) {
			if ($placeindex>0) {
			
				if ($placeindex<16)
				{
					$px=5;
					$py=$placeindex*12+6;
					imagefilledrectangle($im, $px+90, $py+3, $px+90-4, $py-3, $color2);
					imageline($im,$x,$y+$h/2,$px+90,$py,$color2);
					imagestring($im,2,$px,$py-6,$text,$color1);	
					
				} else {
					if ($placeindex<31) {
						$px=$x+40*2;
						$py=($placeindex-15)*12+6;
					} else {
						$px=$x+40*2+100*intval(($placeindex-15)/15);
						$py=($placeindex%15)*12+6;
					}
					imagefilledrectangle($im, $px, $py+3, $px-4, $py-3, $color2);
					imageline($im,$x+$w,$y+$h/2,$px,$py,$color2);
					imagestring($im,2,$px+2,$py-6,$text,$color1);	
				}
			} else {
				imagestring($im,4,$x+5,$y1-16,$text,$color1);
			}
		}
	}

	public function memoryUsageChartAction() {
		$mem = apc_sma_info();

		$size = self::GRAPH_SIZE;
		$image = imagecreate($size+50, $size+10);

		$col_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		$col_red   = imagecolorallocate($image, 0xD0, 0x60,  0x30);
		$col_green = imagecolorallocate($image, 0x60, 0xF0, 0x60);
		$col_black = imagecolorallocate($image,   0,   0,   0);
		imagecolortransparent($image,$col_white);

		$s=$mem['num_seg']*$mem['seg_size'];
		$a=$mem['avail_mem'];
		$x=$y=$size/2;
		$fuzz = 0.000001;

		// This block of code creates the pie chart.  It is a lot more complex than you
		// would expect because we try to visualize any memory fragmentation as well.
		$angle_from = 0;
		$string_placement=array();
		for($i=0; $i<$mem['num_seg']; $i++) {	
			$ptr = 0;
			$free = $mem['block_lists'][$i];
			uasort($free, array($this, '_blockSort'));
			foreach($free as $block) {
				if($block['offset']!=$ptr) {
					$angle_to = $angle_from+($block['offset']-$ptr)/$s;
					if(($angle_to+$fuzz)>1) $angle_to = 1;
					if( ($angle_to*360) - ($angle_from*360) >= 1) {
						$this->_fillArc($image,$x,$y,$size,$angle_from*360,$angle_to*360,$col_black,$col_red);
						if (($angle_to-$angle_from)>0.05) {
							array_push($string_placement, array($angle_from,$angle_to));
						}
					}
					$angle_from = $angle_to;
				}
				$angle_to = $angle_from+($block['size'])/$s;
				if(($angle_to+$fuzz)>1) $angle_to = 1;
				if( ($angle_to*360) - ($angle_from*360) >= 1) {
					$this->_fillArc($image,$x,$y,$size,$angle_from*360,$angle_to*360,$col_black,$col_green);
					if (($angle_to-$angle_from)>0.05) {
						array_push($string_placement, array($angle_from,$angle_to));
					}
				}
				$angle_from = $angle_to;
				$ptr = $block['offset']+$block['size'];
			}
			if ($ptr < $mem['seg_size']) { // memory at the end 
				$angle_to = $angle_from + ($mem['seg_size'] - $ptr)/$s;
				if(($angle_to+$fuzz)>1) $angle_to = 1;
				$this->_fillArc($image,$x,$y,$size,$angle_from*360,$angle_to*360,$col_black,$col_red);
				if (($angle_to-$angle_from)>0.05) {
					array_push($string_placement, array($angle_from,$angle_to));
				}
			}
		}
		foreach ($string_placement as $angle) {
			$this->_textArc($image,$x,$y,$size,$angle[0]*360,$angle[1]*360,$col_black,$this->_bSize($s*($angle[1]-$angle[0])));
		}

		header("Content-type: image/png");
		imagepng($image);
		exit;
	}
		
	public function hitsAndMissesChartAction() {
		$cache = apc_cache_info();
		$size = self::GRAPH_SIZE;
		$image = imagecreate($size+50, $size+10);

		$col_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		$col_red   = imagecolorallocate($image, 0xD0, 0x60,  0x30);
		$col_green = imagecolorallocate($image, 0x60, 0xF0, 0x60);
		$col_black = imagecolorallocate($image,   0,   0,   0);
		imagecolortransparent($image,$col_white);

		$s = $cache['num_hits'] + $cache['num_misses'];
		$a = $cache['num_hits'];
		
		$this->_fillBox($image, 30,$size,50,-$a*($size-21)/$s,$col_black,$col_green,sprintf("%.1f%%",$cache['num_hits']*100/$s));
		$this->_fillBox($image,130,$size,50,-max(4,($s-$a)*($size-21)/$s),$col_black,$col_red,sprintf("%.1f%%",$cache['num_misses']*100/$s));

		header("Content-type: image/png");
		imagepng($image);
		exit;
	}
		
	public function memoryFragmentationChartAction() {
		$mem = apc_sma_info();

		$size = self::GRAPH_SIZE;
		$image = imagecreate(2*$size+150, $size+10);

		$col_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		$col_red   = imagecolorallocate($image, 0xD0, 0x60,  0x30);
		$col_green = imagecolorallocate($image, 0x60, 0xF0, 0x60);
		$col_black = imagecolorallocate($image,   0,   0,   0);
		imagecolortransparent($image,$col_white);

		$s=$mem['num_seg']*$mem['seg_size'];
		$a=$mem['avail_mem'];
		$x=130;
		$y=1;
		$j=1;

		// This block of code creates the bar chart.  It is a lot more complex than you
		// would expect because we try to visualize any memory fragmentation as well.
		for($i=0; $i<$mem['num_seg']; $i++) {	
			$ptr = 0;
			$free = $mem['block_lists'][$i];
			uasort($free, array($this, '_blockSort'));
			foreach($free as $block) {
				if($block['offset']!=$ptr) {       // Used block
					$h=(self::GRAPH_SIZE-5)*($block['offset']-$ptr)/$s;
					if ($h>0) {
                                                $j++;
						if($j<75) $this->_fillBox($image,$x,$y,50,$h,$col_black,$col_red,$this->_bSize($block['offset']-$ptr),$j);
                                                else $this->_fillBox($image,$x,$y,50,$h,$col_black,$col_red);
                                        }
					$y+=$h;
				}
				$h=(self::GRAPH_SIZE-5)*($block['size'])/$s;
				if ($h>0) {
                                        $j++;
					if($j<75) $this->_fillBox($image,$x,$y,50,$h,$col_black,$col_green,$this->_bSize($block['size']),$j);
					else $this->_fillBox($image,$x,$y,50,$h,$col_black,$col_green);
                                }
				$y+=$h;
				$ptr = $block['offset']+$block['size'];
			}
			if ($ptr < $mem['seg_size']) { // memory at the end 
				$h = (self::GRAPH_SIZE-5) * ($mem['seg_size'] - $ptr) / $s;
				if ($h > 0) {
					$this->_fillBox($image,$x,$y,50,$h,$col_black,$col_red,$this->_bSize($mem['seg_size']-$ptr),$j++);
				}
			}
		}

		header("Content-type: image/png");
		imagepng($image);
		exit;
	}
		
	/**
	* pretty printer for byte values
	*/
	protected function _bSize($s) {
		if (empty($s)) {
			$s = 0;
		}

		foreach (array('','K','M','G') as $i => $k) {
			if ($s < 1024) {
				break;
			}
			$s /= 1024;
		}

		return sprintf("%5.1f %sBytes",$s,$k);
	}

	protected function _blockSort($array1, $array2) {
		if ($array1['offset'] > $array2['offset']) {
			return 1;
		} else {
			return -1;
		}
	}
}

?>
