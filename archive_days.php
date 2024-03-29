<?php
/**
 * Archive days
 *
 * Enables the Archive page to drill down to days, not just months
 *
 * @author Marcus Wong (wongm)
 * @package plugins
 */

$plugin_description = gettext("Includes template functions to allow the Archive page to drill down to days, not just months. Zenphoto core functionality already permits searcing for photos by day, as well as month.");
$plugin_author = "Marcus Wong (wongm)";
$plugin_version = '1.1.0'; 
$plugin_URL = "https://github.com/wongm/zenphoto-archive-days/"; 

zp_register_filter('checkPageValidity', 'archiveDays::checkLinks');

class archiveDays {
	
	static function checkLinks($count, $gallery_page, $page) {
    	switch (stripSuffix($gallery_page))
    	{
        	case 'archive':
        	    return isSingleMonthArchive();
    	}
	}
}



/**
 * Prints a compendum of months, with links to a second archive page that will the days that blowong to that month.
 *
 * @param string $class optional class
 * @param string $yearid optional class for "year"
 * @param string $monthid optional class for "month"
 * @param string $order set to 'desc' for the list to be in descending order
 */
function printAllMonths($class='archive', $yearid='year', $monthid='month', $order='asc') {
	if (!empty($class)){ $class = "class=\"$class\""; }
	if (!empty($yearid)){ $yearid = "class=\"$yearid\""; }
	if (!empty($monthid)){ $monthid = "class=\"$monthid\""; }
	$mr = getOption('mod_rewrite');
	$datecount = getAllDates($order);
	$lastyear = "";
	$nr = 0;
	
	echo "\n<ul $class>\n";
	
	foreach ($datecount as $key => $val) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
		}

		if ($lastyear != $year) {
			$lastyear = $year;
			if($nr != 1) {  echo "</ul>\n</li>\n";}
			echo "<li $yearid>$year\n<ul $monthid>\n";
		}
		
		if ($mr) {
			$archiveURL = SEO_WEBPATH . '/' . _ARCHIVE_ . '/?page=' . substr($key, 0, 7);
		} else {
			$archiveURL = SEO_WEBPATH . "/index.php?p=archive&page=" . substr($key, 0, 7);
		}
		
		// link to archive page for all days in this month
		echo "<li><a href=\"".html_encode($archiveURL)."\" rel=\"nofollow\">$month ($val photos)</a>\n";
		
		// link to search page for all photos of this month
		echo "<a href=\"".html_encode(SearchEngine::getSearchURL(null, substr($key, 0, 7), null, 0, null))."\" rel=\"nofollow\">(show all)</a></li>\n";
	}
	echo "</ul>\n</li>\n</ul>\n";
}

/**
 * Prints a compendum of dates and links to a search page that will show images taken on that date
 *
 * @param string $class optional class
 * @param string $yearid optional class for "year"
 * @param string $monthid optional class for "month"
 * @param string $order set to 'desc' for the list to be in descending order
 */
function printSingleMonthArchive($class='archive', $yearid='year', $monthid='month', $order='asc') {
	if (!empty($class)){ $class = "class=\"$class\""; }
	if (!empty($yearid)){ $yearid = "class=\"$yearid\""; }
	if (!empty($monthid)){ $monthid = "class=\"$monthid\""; }
	
	$month = $_GET['page'];
	$splitmonth = explode('-', $month);
	
	$datecount = getAllDaysInMonth($month, $order);
	$lastyear = "";
	echo "\n<ul $class>\n";
	$nr = 0;
	
	foreach ($datecount as $key => $val) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
			$day = substr($key, 8, 2);
		}

		if ($lastyear != $year) {
			$lastyear = $year;
			if($nr != 1) {  echo "</ul>\n";}
		}
		
		// link to search results
		echo "<li><a href=\"".html_encode(SearchEngine::getSearchURL(null, $key, null, 0, null))."\" rel=\"nofollow\">$month $day ($val photos)</a></li>\n";
	}
	echo "</ul>\n</li>\n</ul>\n";
}

/**
 * Are we listing all months in the Gallery, or the days in a specific month?
 * Based on the required URL parameter being set
 *
 * @return boolean
 */
function isSingleMonthArchive() {
	if (isset($_GET['page']))
	{
		$month = $_GET['page'];
		$splitmonth = explode('-', $month);
		
		if (sizeof($splitmonth) == 2 && is_numeric($splitmonth[0]) && is_numeric($splitmonth[1])) {
			return true;
		}
	}
	
	return false;
}

/**
 * Get the title of the month this archive page is for
 * Based on the required URL parameter being set
 *
 * @return string
 */
function getSingleMonthArchiveTitle() {
	if (isSingleMonthArchive()) {
		$month = $_GET['page'];
		$splitmonth = explode('-', $month);
		return strftime('%B %Y', mktime(1, 1, 1, $splitmonth[1], 1, $splitmonth[0]));
	}
}

/**
 * Prints the breadcrumb navigation for archive view.
 *
 * @param string $before Insert here the text to be printed before the links
 * @param string $between Insert here the text to be printed between the links
 * @param string $after Insert here the text to be printed after the links
 * @param mixed $truncate if not empty, the max lenght of the description.
 * @param string $elipsis the text to append to the truncated description
 */
function printArchiveBreadcrumb($before = '', $between=' | ', $after = ' | ', $truncate=NULL, $elipsis='...') {
	if (isSingleMonthArchive()) {
		if ($mr = getOption('mod_rewrite')) {
			$archiveURL = SEO_WEBPATH . '/' . _ARCHIVE_ . '/';
		} else {
			$archiveURL = SEO_WEBPATH . "/index.php?p=archive";
		}
		
		echo $before;
		echo "<a href=\"".html_encode($archiveURL)."\" rel=\"nofollow\">" . gettext("Archive View") . "</a>\n";
		echo $after;
	}
}
/**
 * Prints the title of this archive view.
 *
 */
function printArchiveTitle() {
	if (isSingleMonthArchive()) {
		echo getSingleMonthArchiveTitle();
	} else {
		echo gettext("Archive View");
	}
}

/**
 * Private helper function
 * Retrieves a list of all unique years & months from the images in the gallery
 *
 * @param string $order set to 'desc' for the list to be in descending order
 * @return array
 */
function getAllDaysInMonth($month, $order='desc') {
	global $_zp_db;
	$alldates = array();
	$cleandates = array();
	$sql = "SELECT `date` FROM ". $_zp_db->prefix('images') . " WHERE `date` <= LAST_DAY('$month-05 23:59:59') AND `date` >= '$month-01 00:00:00'";
	if (!zp_loggedin()) { $sql .= " AND `show` = 1"; }
	$result = $_zp_db->queryFullArray($sql);
	foreach($result as $row){
		$alldates[] = $row['date'];
	}
	foreach ($alldates as $adate) {
		if (!empty($adate)) {
			$cleandates[] = substr($adate, 0, 10);// eg: 2001-12-31
		}
	}
	
	$datecount = array_count_values($cleandates);
	if ($order == 'desc') {
		krsort($datecount);
	} else {
		ksort($datecount);
	}
	return $datecount;
}
?>