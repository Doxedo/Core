<?php

class L10n
{
    public function updateL10N() {
        //echo $this->html;

        // PHASE1: Convert span-level elements
        $depth = 1;
        $text = "";
        echo $this->filename;

        $src="<html>" . $this->html . "</html>";

        /* Drop stupid ms-word quotes */
        $src=str_replace("‘","'", $src);
        $src=str_replace("’","'", $src);
        $extractmode = true;


        $tf=new LinkORB_TranslationFile();
        $tf->name="markdown-topic:" . $this->filename;
        $tf->datatype="x-linkorb-markdown-topic";
        $tf->sourcelang="en_US";
        $tf->targetlang="en_US";
        $tf->tool="x-lt-ldoc-updatel10n";

        $l10npath=dirname($this->filename) . "/l10n/";
        if (!file_exists($l10npath)) mkdir($l10npath);

        // Load previous trans-unit's if available
        if (file_exists($l10npath . basename($this->filename)  . ".src.xlf")) {
            $tf->LoadXLIFF($l10npath . basename($this->filename) . ".src.xlf", true);
            // Set all transunit's to 'translate=false'
            foreach($tf->transunit as $tu) {
                $tu->translate="no";
                $tu->comment= array();
                $tu->filename= array();
            }
        }



        $xhtml=new XMLReader();
        $xhtml->setParserProperty ('SUBST_ENTITIES', 0);

        $xhtml->xml($src);
        $skiptext=false;
        $inpre=false;
        while ($xhtml->read() && $depth != 0) {
            if (in_array($xhtml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
                if (!$skiptext) {
                    //$text .= htmlentities($xhtml->value); // Leaving entities as-is now
                    $text .= $xhtml->value;
                }
            }

            // OPENING TAG
            if ($xhtml->nodeType == XMLReader::ELEMENT) {
                switch ($xhtml->name) {
                    case "code":
                        if (!$inpre) $text .= "`";
                        break;
                    case "pre":
                        $inpre=true;
                        $text .="<" . $xhtml->name . ">";
                        break;
                    case "em":
                        $text .= "*";
                        break;
                    case "a":
                        $text .= "[linktext](http://www.example.com)";
                        $skiptext=true;
                        break;
                    default:
                        $text .="<" . $xhtml->name . ">";
                        break;
                }
                $depth++;
            }

            // CLOSING TAG
            if ($xhtml->nodeType == XMLReader::END_ELEMENT) {
                switch ($xhtml->name) {
                    case "code":
                        if (!$inpre) $text .= "`";
                        break;
                    case "pre":
                        $inpre=false;
                        $text .="</" . $xhtml->name . ">";
                        break;
                         
                    case "em":
                        $text .= "*";
                        break;
                    case "a":
                        $text .= "";
                        $skiptext=false;
                        break;
                    default:
                        $text .="</" . $xhtml->name . ">";
                        break;
                }
                $depth--;
            }
        }

        // PHASE2: Convert block-level elements
        $depth = 1;
        $xhtml=new XMLReader();

        $text=str_replace("<br>","<br />", $text);
        $xhtml->xml($text);
        file_put_contents("/tmp/html.txt", $text); echo $text;
        $text = "";
        $firstliparagraph=false;
        $tagstack=array();
        $liststack=array();
        $inpre=false;
        $inblockquote=false;
        while ($xhtml->read() && $depth != 0) {
            if (in_array($xhtml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
                if (!$skiptext) {
                    $string=$xhtml->value;
                    $indent = str_repeat("\t", count($liststack));
                    if ($inpre) {
                        // Add indention, and insert text as-is (no translation etc)
                        $string = trim(str_replace("\n", "\n\t" . $indent, $string));
                        $text .= $string;
                    } else {
                         
                        // Remove linebreaks for all paragraphs, blockquotes, etc (all except pre)
                        $string = str_replace("\n", " " , $string);
                         
                        // Remove reduntant spaces
                        $string = str_replace("   ", " " , $string);
                        $string = str_replace("   ", " " , $string);
                        $string = str_replace("  ", " " , $string);
                        $string = trim($string);



                        if (trim($string, " \t\n")!="") {
                            if (!$firstliparagraph) $text .= $indent;
                            if ($inblockquote) {
                                $text .= "> ";
                            }
                            // SEGMENTOR
                            //$segment=explode(". ", $string);
                            $segment = preg_split("/([.!]+\s)/", $string . " ", null, PREG_SPLIT_DELIM_CAPTURE);
                            $si=0;

                            while ($si<count($segment)) {
                                $s=$segment[$si];
                                if ($si+1<count($segment)) {
                                    $si++;
                                    $s.=$segment[$si];
                                }
                                $firstliparagraph=false;
                                // Re-add closing punctuation
                                //if ($si<count($segment)-1) $s.= ".";
                                $s=trim($s);
                                if ($s) {
                                    $extractmode = true;
                                    if ($extractmode) {
                                         
                                        $tu=$tf->GetTranslationUnit(null, $s);
                                        $tu->addComment("Topic: " . basename($this->filename));
                                        $tu->addFilename("../" . basename($this->filename));
                                        //$text .= "[START|" . $tu->id . "|" . str_replace("|", "(PIPE)", $s) . "|END]";
                                        $text .= "[START|" . $tu->id . "|END]";

                                    } else {
                                        $text .= "@" . $s . "@";
                                    }
                                }
                                $si++;
                            }

                        }
                    }
                }
            }

            // OPENING TAG
            if ($xhtml->nodeType == XMLReader::ELEMENT) {
                array_push($tagstack, array("name" => $xhtml->name));
                //$text .="[" . $xhtml->name  . "(d:" . count($tagstack) ." l:" . count($liststack) . ")]";
                switch ($xhtml->name) {
                    case "h0":
                    case "h1":
                    case "h2":
                    case "h3":
                    case "h4":
                    case "h5":
                    case "h6":
                        $text .= "\n" . str_repeat("#", (int)$xhtml->name[1]) ." ";
                        break;
                    case "p":
                        break;
                    case "pre":
                        $inpre=true;
                        //$text .= "PRE";
                        $text .= "\t";
                        break;
                    case "blockquote":
                        $inblockquote=true;
                        //$text .= "BLOCKQUOTE";
                        break;
                    case "ul":
                    case "ol":
                        array_push($liststack, array("name" => $xhtml->name));
                        break;
                    case "li":
                        $firstliparagraph=true;
                        $text .= str_repeat("\t", count($liststack)-1);
                        if ($liststack[count($liststack)-1]['name']=="ol") {
                            $text .= "#\t";
                        } else {
                            $text .= "*\t";
                        }
                        break;
                    default:
                         
                        break;
                }
                 
                $depth++;
            }

            // CLOSING TAG
            if ($xhtml->nodeType == XMLReader::END_ELEMENT) {
                array_pop($tagstack);
                //$text .="[/" . $xhtml->name  . "]";
                switch ($xhtml->name) {
                    case "h0":
                    case "h1":
                    case "h2":
                    case "h3":
                    case "h4":
                    case "h5":
                    case "h6":
                        $text .= "\n\n";
                        break;
                    case "p":
                        $text .= "\n\n";
                        break;
                    case "pre":
                        $inpre=false;
                        $text .= "\n\n";
                        break;
                    case "blockquote":
                        $inblockquote=false;
                        break;
                    case "li":
                        $text .= "\n";
                        break;
                    case "ul":
                    case "ol":
                        array_pop($liststack);
                        //                      $text .= "\n";
                        break;
                    default:
                        break;
                }
                $depth--;
            }
        }
        // Strip redundant linebreaks
        $text=str_replace("\n\n\n\n", "\n\n", $text);
        $text=str_replace("\n\n\n", "\n\n", $text);
        file_put_contents($l10npath . basename($this->filename) . ".skl.md", $text);
        file_put_contents($l10npath . basename($this->filename) . ".src.xlf", $tf->ToXLIFF());
        //echo "\n\n=====================================!!!!!!!!!!!!!\n" . $text;


        $locale=array();
        $locale[]="nl-NL";
        $locale[]="zh-CN";
        $locale[]="fr-FR";
        $locale[]="es-ES";
        $locale[]="ru-RU";
        $locale[]="de-DE";
        $locale[]="jp-JP";
        $locale[]="ar-EG"; // arabic Egypt
        $locale[]="hi-IN"; // hindi India
        foreach($locale as $l) {
            $tf=new LinkORB_TranslationFile();
            $tf->name="markdown-topic:" . $this->filename;
            $tf->name="markdown-topic:" . $this->filename;
            $tf->datatype="x-linkorb-markdown-topic";
            $tf->sourcelang="en_US";
            $tf->targetlang=$l;
            $tf->tool="x-lt-ldoc-updatel10n";
                
            // Pretranslation
            $filename=$l10npath . basename($this->filename) . "." . $l . ".xlf";
            if (file_exists($filename)) {
                $tf->LoadXLIFF($filename, true, dirname($filename));

                foreach($tf->transunit as $tu) {
                    $tu->translate="no";
                    $tu->comment=array();
                    $tu->filename=array();
                        
                    if ($tu->target=="") $tf->transunit[$tu->id]->export=false;
                }
            }
                
            // Load new src trans-units on top
            $tf->LoadXLIFF($l10npath . basename($this->filename) . ".src.xlf", true, dirname($filename));
                
            // Save
            file_put_contents($filename, $tf->ToXLIFF());
        }


        // -------------
        foreach($locale as $l) {
            $o=file_get_contents($l10npath . basename($this->filename) . ".skl.md");
            $tf=new LinkORB_TranslationFile();
            $filename=$l10npath . basename($this->filename) . "." . $l . ".xlf";
            if (file_exists($filename)) {
                $tf->LoadXLIFF($filename, true, dirname($filename));

                foreach($tf->transunit as $tu) {
                    //$tag="[START|" . $tu->id . "|" . $tu->src . "|END]";
                    $tag="[START|" . $tu->id . "|END]";
                    $target=trim($tu->target);
                    if ($target=="") $target = "@" . $tu->src . "@";
                    //echo "$tag\n";
                    $o=str_replace($tag, $target, $o);
                }
            }
            file_put_contents($l10npath . basename($this->filename) . "." . $l . ".md", $o);
        }
        //exit ("END");
        //return $text;
    }
   
    function topicL10N($recursive = false) {
        if ($this->filename) $this->updateL10N($this->filename);

        foreach($this->topic as $subtopic) {
            $subtopic->topicL10N($recursive);
        }

        return;
    }
}