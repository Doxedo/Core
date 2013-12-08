<?php

namespace Doxedo\Core;

class Utils
{
    
    static function tiki2Markdown($text) {
        # Standardize line endings:
        #   DOS to Unix and Mac to Unix
        $text = preg_replace('{\r\n?}', "\n", $text);


        // Fix quotes
        $text = str_replace("“", "\"", $text);
        $text = str_replace("”", "\"", $text);
        $text = str_replace("’", "'", $text);
        $text = str_replace("‘", "'", $text);
        $text = str_replace("…", "...", $text);

        // strip out {maketoc}
        $text=str_replace("{maketoc}", "", $text);


        // Handle [code] blocks

        $pattern = '/(?s)\{CODE(.*?){CODE\}/';
        preg_match_all($pattern, $text, &$matches, PREG_SET_ORDER, 0);

        foreach ($matches as $m) {
            $container=$m[0];
            $content=$m[1];
            $end=strpos($content,"}");
            $code=substr($content,$end + 1);
            $lines=explode("\n", $code);
            $newcode="";
            foreach($lines as $line) {
                $newcode .= "\t" . $line . "\n";
            }
            $newcode=html_entity_decode($newcode);
            $text=str_replace($container, "\n<i>Code:</i>\n\n" . $newcode . "\n\n", $text);
                
            echo "FOUND CODE BLOCK!\n" . $content;
        }


        // Auto set hyperlinks

        /*
         $m = preg_match_all('/http:\/\/[a-z0-9A-Z.]+(?(?=[\/])(.*))/', $text, $match);

         if ($m) {
         $links=$match[0];
         for ($j=0;$j<$m;$j++) {
         $text=str_replace($links[$j],'<a href="'.$links[$j].'">'.$links[$j].'</a>',$text);
         }
         }
         */


        // Ordered lists '# '  to '1. '
        $text=str_replace("\n#", "\n0. ", $text);
        $text=str_replace("\n0.  ", "\n0. ", $text); // Remove double spaces
        $text=str_replace("\n0.  ", "\n0. ", $text); // Remove double spaces


        // Unordered lists '- '  to '- ' should work as usual


        // Switch !#, !!# to #, ## headers
        $text=str_replace("\n!!!!!#", "\n###", $text);
        $text=str_replace("\n!!!!#", "\n###", $text);
        $text=str_replace("\n!!!#", "\n###", $text);
        $text=str_replace("\n!!#", "\n##", $text);
        $text=str_replace("\n!#", "\n#", $text);

        $text=str_replace("\n!!!!!", "\n###", $text);
        $text=str_replace("\n!!!!", "\n###", $text);
        $text=str_replace("\n!!!", "\n###", $text);
        $text=str_replace("\n!!", "\n##", $text);
        $text=str_replace("\n!", "\n#", $text);


        return $text;

    }

}