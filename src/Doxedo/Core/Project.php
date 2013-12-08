<?php

namespace Doxedo\Core;

use RuntimeException;
use Doxedo\Core\Topic\Markdown as MarkdownTopic;

class Project
{
    private $basepath; // base of the project filename
    private $languages = array();
    private $scope;
    private $topics;

    public function getScope()
    {
        return $this->scope;
    }
    
    public function getTopics()
    {
        return $this->topics;
    }
    public function addLanguage(Language $language)
    {
        $this->languages[] = $language;
    }
    
    public function getLanguages()
    {
        return $this->languages;
    }

    public function getBasePath() {
        return $this->basepath;
    }

    public function loadXmlFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Project file does not exist: " . $filename);
        }
        $xml = @simplexml_load_file($filename);
        if (!$xml) {
            throw new RuntimeException("Incorrectly formatted XML in " . $filename);
        }
        $this->basepath = dirname(realpath($filename));
        echo "project.basepath:" . $this->basepath . "\n";

        $this->scope = new Scope();

        // parse topics, recursively
        foreach($xml->topic as $node) {
            $this->parseXmlNode($node, 0);
        }

        // load project-level variables
        foreach($xml->variable as $variable) {
            $this->scope->define((string)$variable['key'], (string)$variable);
        }


        // load languages
        foreach($xml->language as $languageNode) {
            $language = new Language($this);
            $language->setName((string)$languageNode['name']);


            // load language-level variables
            foreach($languageNode->variable as $variable) {
                $language->getScope()->define((string)$variable['key'], (string)$variable);
            }

            // load targets per language
            foreach($languageNode->target as $targetNode) {
                $class = (string)$targetNode['class'];
                if ($class == '') {
                    $class = 'Doxedo\Core\Target\SinglePage';
                }
                $target = new $class($language);
                $target->setName((string)$targetNode['name']);

                // load target-level variables
                foreach($targetNode->variable as $variable) {
                    $target->getScope()->define((string)$variable['key'], (string)$variable);
                }
                $language->addTarget($target);
            }
            $this->addLanguage($language);
        }


    }

    private function parseXmlNode($node, $depth=0)
    {
        $href=(string)$node["href"];
        //TODO: foreach over all attributes to define variables into topic scope


        //echo str_repeat("   ", $depth);
        //echo "TOPIC: " . $href ."\n";
          
        //TODO: Switchcase by file extension?  
        $topic = new MarkdownTopic();

        $topic->setDepth($depth);
        $topic->loadFile($this->basepath . '/' . $href);

        $this->topics[] = $topic;
        foreach($node->topic as $node) {
            $this->parseXmlNode($node, $depth + 1);
        }
        return true;
    }
}