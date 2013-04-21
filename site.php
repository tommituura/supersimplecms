<?php 
/***********************************
 * site.php
 *
 * (c) Tommi Tuura 2012
 * 
 * This file has a class definition 
 * for a Site class that holds most 
 * of our needed functionality.
 *  
 * Also, this file holds some 
 * (currently, only one)
 * helper functions to help our 
 * stuff here. 
 * 
 * This is free software. See 
 * LICENSE for more details.
 **********************************/

class Site {
	private $success = FALSE;
	private $htmltitle = null;
	private $siteheader = null;

	private $toc = null;


	/************************************
	 * Private helper functions	 
	 ************************************/

	private function handlePage($pageelement) {
		$page = array();
		$page['name'] = (string)$pageelement->attributes()->name;
		$page['id'] = (string)$pageelement->attributes()->id;
		$page['filename'] = (string)$pageelement->attributes()->filename;

		return $page;
	}

	private function handleSection($sectionelement) {
		$section = array();
		$section['name'] = (string)$sectionelement->attributes()->name;
		$section['pages'] = array();
		foreach($sectionelement->xpath('page') as $page) {
			array_push($section['pages'], $this->handlePage($page));
		}

		return $section;
	}


	/************************************
	 * Public functions, including 
	 * constructor
	 ************************************/

	/****************************************
	 * The constructor expects a simplexml 
	 * object that has been checked to be 
	 * something useful. Giving a null value 
	 * as a parameter makes a "dummy" site 
	 * object that will give error messages 
	 * as printout. 
	 ***************************************/	
	function __construct($simplexmlobject) {
		if (!$simplexmlobject) {
			return;
		}

		$this->toc = array();
		foreach ($simplexmlobject->xpath('//section') as $section) {
			// print_r($section);
			array_push($this->toc, $this->handleSection($section));
		}
		
		$this->htmltitle  = (string)$simplexmlobject->htmltitle;
		$this->siteheader = (string)$simplexmlobject->siteheader;

		//print_r($this->toc);
		$this->success = TRUE;
	}

	/****************************************
	 * This method takes a string and returns 
	 * an array about page, having keys 
	 * 'name', 'id', 'filename'. Values are 
	 * determined in site.xml.
	 * If no matching $selectedPage==id is 
	 * found, returns null.
	 ****************************************/
	function getPageById($selectedPage) {
		foreach ($this->toc as $section) {
			foreach($section['pages'] as $page) {
				if ($page['id'] == $selectedPage) {
					return $page;
				}
			}
		}
		return null;
	}

	/**********************************************
	 * This method generates a navbar from div elements, placing 
	 * a 'class="selected"' attribute to one of the divs, and 
	 * every div being either a page or section heading. Page headings 
	 * will also be links to the same page but with GET parameter 'page=<ID>' 
	 * where the page id corresponds to the page in question.
	 *********************************************/
	function getNavbar($selectedPage) {
		if (!$this->success) {
			return "Site object not initialized properly";
		} 
		
		$pageArray = $this->getPageById($selectedPage);
		if (!$pageArray) {
			$pageArray = $this->toc[0]['pages'][0];
		}
		/* Now, we can be sure we have some page in there... */
		$selectedPageId = $pageArray['id'];

		$navbarString ="";

		foreach($this->toc as $section) {
			$navbarString .= '<div class="navbar-sectiondiv">';
			$navbarString .= '<span class="navbar-sectionspan">' . $section['name'] . '</span>'. "\n";
			foreach($section['pages'] as $page) {
				if ($selectedPageId == $page['id']) {
					$navbarString .= '<div class="navbar-pagediv selected">';
				} else {
					$navbarString .= '<div class="navbar-pagediv">';
				}
				$navbarString .= '<span class="navbar-pagespan">'; 
				$navbarString .= '<a href="?page=' . $page['id'] .'">' . $page['name'] . '</a></span>';
				$navbarString .= '</div>'. "\n";
			}
			$navbarString .= '</div>' . "\n";
		}
		return $navbarString;
	}

	/****************************************************
	 * This method gets the contents of the file whose 
	 * id is given as a parameter. If unknown parameter is 
	 * given, it will default to the first page of first section.
	 *****************************************************/
	function getContent($selectedPage) {
		if (!$this->success) {
			return "Site object not initialized properly";
		} 
		
		$pagearray = $this->getPageById($selectedPage);
		$filename = "";
		if (!$pagearray) {
			$filename = $this->toc[0]['pages'][0]['filename'];
		} else {
			$filename = $pagearray['filename'];
		}
		$contents = file_get_contents($filename);
		if (!$contents) {
			return "Could not open file: " . $filename; 
		} else {
			$lastmodified = date("d M Y H:i", filemtime($filename));
			return $contents; // . "\n" . '<div class="lastmodified">Sivua muutettu viimeksi: ' . $lastmodified . '</div>' . "\n";
		}
	
	}
	/****************************************************
	 * This method gets the last modification date of 
	 * selected page's file. Like getContent, if unknown 
	 * parameter is given, it will default to the first page 
	 * of first section. 
	 ****************************************************/
	function getLastModificationDate($selectedPage) {
		if (!$this->success) {
			return -1;
		}
		$pagearray = $this->getPageById($selectedPage);
		$filename = "";
		if (!$pagearray) {
			$filename = $this->toc[0]['pages'][0]['filename'];
		} else {
			$filename = $pagearray['filename'];
		}
		return filemtime($filename);
	}
	function getHeader() {
		if (!$this->success) {
			return "Site object not initialized properly";
		} else {
			return $this->siteheader;
		}
	}

	function getTitle() {
		if (!$this->success) {
			return "Site object not initialized properly";
		} else {
			return $this->htmltitle;
		}

	}

} 

/* This function helps us fetch the GET parameter.  
 * It's simply to make fetching the "page" parameter easier 
 * from the array that's given to it, supposedly $_GET 
 * supervariable. It is here mostly to make index.php 
 * little less ugly.
 */

function getSelectionParameter($paramarray) {
	if (!is_array($paramarray) || !isset($paramarray['page'])) {
		return "";
	} else {
		return $paramarray['page'];
	}
}

?>

