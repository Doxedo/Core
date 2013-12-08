<?php

namespace Doxedo\Core\Target;

use Doxedo\Core\Language;
use Doxedo\Core\Scope;
use Doxedo\Core\Target\TargetInterface;
use Mustache_Engine;

abstract class AbstractTarget implements TargetInterface
{
    private $name;
    private $scope;
    private $language;
    private $renderer;

    public function __construct(Language $language)
    {
        $this->language = $language;
        $this->scope = new Scope();
        $this->scope->setParent($language->getScope());
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function getHtml()
    {
        $html = '';
        foreach($this->getLanguage()->getProject()->getTopics() as $topic) {
            //echo "TOPIC: " . $topic->getFilename() . "\n";
            $html .= $topic->toHtml($this->getScope(), true);
        }
        return $html;
    }

    protected function wrapTemplate($html) {
        $skinpath = $this->getLanguage()->getProject()->getBasePath() .  '/' . $this->getScope()->get('skinpath');

        $template = file_get_contents($skinpath . "singlepage.mustache");
        $m = new Mustache_Engine();
        $data = array_merge($this->getScope()->getAll(), array('html'=>$html));
        $html = $m->render($template, $data);

        return $html;
    }

    private function toPdf()
    {
        $html2pdf=new LinkORB_HTML2PDF();
        $html2pdf->skinpath = $this->skinpath;
        /*
        $this->QuickTemplate(sth . "toc.xsl", self::$buildpath . "toc.xsl");
        $this->QuickTemplate(self::$tplpath . "cover.tpl", self::$buildpath . "cover.html");
        $this->QuickTemplate(self::$tplpath . "header.tpl", self::$buildpath ."header.html");
        $this->QuickTemplate(self::$tplpath . "footer.tpl", self::$buildpath . "footer.html");
            
        $options = "";
        $options .= " --header-html " . self::$buildpath . "header.html";
        $options .= " --footer-html " . self::$buildpath . "footer.html";
        $options .= " -B 20 -T 20 -L 20 -R 20 --dpi 1200 --image-quality 100 ";
        $options .= " cover " . self::$buildpath . "cover.html";
        $options .= " toc --xsl-style-sheet " . self::$buildpath . "toc.xsl";
        */
        
        $outputfilename=(string)$this->xml->name ."." .  $this->language . ".pdf";
        echo "Saving " . $this->buildpath . $outputfilename . "\n";
        $html2pdf->css=file_get_contents($this->skinpath . "style.css");
        $html2pdf->html=file_get_contents($this->buildpath . "index.html");
        $html2pdf->addsimplelayout = false;
        $html2pdf->addstationary = false;
        $html2pdf->buildpath = $this->buildpath;
        foreach ($this->metavar as $key=>$value) {
            $html2pdf->SetMetaVar($key, $value);
        }
        $html2pdf->CreatePDF($this->buildpath . $outputfilename);
        
        //                    exec($cmd);
    }

}