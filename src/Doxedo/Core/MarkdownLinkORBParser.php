<?php

namespace Doxedo\Core;

use dflydev\markdown\MarkdownExtraParser;

#
# Markdown LinkORB Parser Class
#

class MarkdownLinkORBParser extends MarkdownExtraParser {



	function MarkdownLinkORBParser() {
	#
	# Constructor function. Initialize the parser object.
	#
	/*
		# Add extra escapable characters before parent constructor 
		# initialize the table.
		$this->escape_chars .= ':|';
		
		# Insert extra document, block, and span transformations. 
		# Parent constructor will do the sorting.
		$this->document_gamut += array(
			"doFencedCodeBlocks" => 5,
			"stripFootnotes"     => 15,
			"stripAbbreviations" => 25,
			"appendFootnotes"    => 50,
			);
		$this->block_gamut += array(
			"doFencedCodeBlocks" => 5,
			"doTables"           => 15,
			"doDefLists"         => 45,
			);
		$this->span_gamut += array(
			"doFootnotes"        => 5,
			"doAbbreviations"    => 70,
			);
		*/
		parent::MarkdownExtraParser();
	}
	
	function transform($text) {
		$text = str_replace("\n!#", "\n#", $text);
		$text = str_replace("\n!!#", "\n##", $text);
		$text = str_replace("\n!!!#", "\n###", $text);
		$text = parent::transform($text);
		return $text;
	}
	
	function setup() {
	#
	# Setting up Extra-specific variables.
	#
		parent::setup();
		
	}
	
	function teardown() {
		
		parent::teardown();
	}
	
	
	
	
	
	function doHeaders($text) {
	#
	# Redefined to add id attribute support.
	#
		# Setext-style headers:
		#	  Header 1  {#header1}
		#	  ========
		#  
		#	  Header 2  {#header2}
		#	  --------
		#
		/*
		$text = preg_replace_callback(
			'{
				(^.+?)								# $1: Header text
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?	# $2: Id attribute
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx',
			array(&$this, '_doHeaders_callback_setext'), $text);
		*/
		# atx-style headers:
		#	# Header 1        {#header1}
		#	## Header 2       {#header2}
		#	## Header 2 with closing hashes ##  {#header3}
		#	...
		#	###### Header 6   {#header2}
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? # id attribute
				[ ]*
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	function _doHeaders_attr($attr) {
		if (empty($attr))  return "";
		return " id=\"$attr\"";
	}
	public $header_offset=0;
	function _doHeaders_callback_setext($matches) {
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$level += $this->header_offset;
		$attr  = $this->_doHeaders_attr($id =& $matches[2]);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);
		$level += $this->header_offset;
		$attr  = $this->_doHeaders_attr($id =& $matches[3]);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	

}

