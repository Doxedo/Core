<?php 

namespace Doxedo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexHelpCommand extends Command 
{
	private static $mdfile	 = '';
	private $uihelp			 = array();
	private $dbhelp			 = array();
	private $section		 = array();
	private $topic 			 = array();
	static $language		 = array();
	private static $uihelpfilename  = 'uihelp.s';
	private static $dbhelpfilename  = 'dbhelp.s';
    
    protected function configure()
    {
        $this
            ->setName('doxedo:indexhelp')
            ->setDescription(
                  'Generate the help file.'
              )
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'file name'
            );
    }
	
	function execute(InputInterface $input, OutputInterface $output)
    {
		$filename = $input->getArgument('filename');
		$this->loadMap($filename);
		
		foreach($this->topic as $topic) {
			$filename	 = $topic['file'];
			$languagesrc = $topic['src'];
			foreach($languagesrc as $l => $src) {
				$prefix	  = $filename . '.' . $l;
				$sections = explode("\n::", $src);
				foreach($sections as $section) {
					if(strpos($section, 'ID') !== 0)
						continue;
					$sectionArray = array();
					$lines = explode("\n", $section);//print_r($lines);exit;
					$i	   = 1;
					$match = count($lines);
					$title = '';
					$value = '';
					while($i < $match) {
						$line = trim($lines[$i]);
						if(strpos($line, '#') === 0) {
							$title = trim($line, '#');
						}elseif($line) {
							$value .= $line . "<br />";
						}
						$i++;
					}
					$sectionArray[]['title'] = $title;
					$sectionArray[]['value'] = $value;
					$sectioninfo = explode(',', trim($lines[0], ": "));
	
					foreach($sectioninfo as $info) {
						$qa		  = explode('=', $info);
						$keyvalue = trim($qa[1]);
						$keyname  = strtolower(trim($qa[0]));
						switch($keyname) {
							case 'id':
								if(strpos($qa[1], '.') === 0)
									$sectionid = $prefix . $qa[1];
								else
									$sectionid = $prefix. '.' . $qa[1];
								break;
			            	case 'uihelp':
			            		$actions = explode('.', $keyvalue);
			            		$page 	 = $actions[0];
			            		$action	 = $actions[1];
			            		$this->uihelp[$l][$page][$action][$sectionid][] = $title;
								break;
							case 'dbhelp':
								$actions = explode('.', $keyvalue);
			            		$table 	 = $actions[0];
								$field	 = $actions[1];
								$this->dbhelp[$l][$table][$field][$sectionid][] = $title;
						        break;
						}
					}
					$this->section[$sectionid]['title'] = $title;
					$this->section[$sectionid]['value'] = $value;
				}
				
			}
			$output->write($filename . ' succeed!');
		}

		$uihelp = serialize($this->uihelp);
		$dbhelp = serialize($this->dbhelp);
		$section = serialize($this->section);
		$uihelpfile = $uihelp . "\n" . $section;
		$dbhelpfile = $dbhelp . "\n" . $section;
		file_put_contents(self::setPath() . self::$uihelpfilename, $uihelpfile);
		file_put_contents(self::setPath(). self::$dbhelpfilename, $dbhelpfile);
		exit(0);
	}
    
    static function setpath()
    {
		$pathroot = LinkORB_Core::GetPath('root') . "build/";
		return $pathroot;
	}
	
	function loadMap($filename)
    {
		if (!file_exists($filename)) {
			Console::Error('Map file does not exist: ' . $filename);
			return false;
		}
		$this->xml = @simplexml_load_file($filename);
		if (!$this->xml) {
			Console::Error('Incorrectly formatted XML in ' . $filename);
			return false;
		}
		return $this->ParseMap($this->xml);
	}
	
	function parseMap($xml)
    {
		foreach($xml->language as $language) {
			self::$language[] = (string)$language['name'];
		}

		$languagepath = self::$mdfile . 'l10n/';
		$extension = '.md';
		$i 		   = 0;
		foreach($xml->topic as $node) {
			$href	  = (string)$node['href'];
			$filename = $href . $extension;
			if(!file_exists($filename)) {
				exit('File not found:' . $filename."\n");
			}
			// Check the language.
			foreach(self::$language as $l) {
				if($l == 'src') {
					$l = 'en-US';
					$languagefile = $filename;
				}else {
					$languagefile = $languagepath . $filename . '.' . $l . $extension;
				}

				if(file_exists($languagefile)) {
					$this->topic[$i]['src'][$l]  = file_get_contents($languagefile);
					$this->topic[$i]['file']	 = $href;
				}
			}
			$i++;
		}
        //print_r($this->topic);exit;
		return true;
	}
	
	static function get_UIhelpfile()
    {
		$filepath = self::Setpath();
		return $filepath . self::$uihelpfilename;
	}
	
	static function get_dbhelpfile()
    {
		$filepath = self::Setpath();
		return $filepath . self::$dbhelpfilename;
	}
}
?>
