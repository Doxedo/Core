<?php

namespace Doxedo\Core\Topic;

use Doxedo\Core\Utils;
use RuntimeException;
use LinkORB_TranslationFile; // extract to it's own library?
use Doxedo\Core\Scope;
use Doxedo\Core\MarkdownLinkORBParser; // extract to it's own library? or use standard external library dependency
use Mustache_Engine;
use InvalidArgumentException;

class Markdown extends BaseTopic
{
    function loadFile($filename) {
        //$this->id=self::CleanTopicId($filename);
        $this->filename = $filename;
        $this->src = "";
        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: $filename");
        }
        $src = file_get_contents($filename);
        $lines = explode("\n", $src);
        $metamode = true;
        foreach($lines as $line) {
            if ($metamode) {
                $colon = strpos($line,":");
                if ($colon > 0) {
                    $key=strtolower(trim(substr($line, 0, $colon)));
                    $value=trim(substr($line,$colon+1));
                    //TODO: Support multi-line?
                    $this->scope->define($key, $value);
                } else {
                    $metamode = false;
                }
            }
            if (!$metamode) {
                $this->src .= $line . "\n";
            }
        }

        //$this->src = Utils::tiki2Markdown($this->src);
    }

    public function toHtml(Scope $scope, $recursive = false) {
        $src = $this->src;

        $m = new Mustache_Engine();
        $data = array_merge($scope->getAll(), $this->scope->getAll());
        $src = $m->render($src, $data);

        $parser = new MarkdownLinkORBParser();
        $parser->header_offset = $this->depth;
        # Transform text using parser.
        $html = $parser->transform($src);

        if ($recursive) {
            foreach($this->getTopics() as $topic) {
                $html .= $topic->toHtml($scope, $recursive);
            }
        }
        return $html;
    }
}
