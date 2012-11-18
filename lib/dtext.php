<?php
abstract class DText
{
    static public function parse_inline($str)
    {
        $str = htmlspecialchars($str);
        
        if (preg_match_all('/\[\[.+?\]\]/m', $str, $wiki_ms)) {
            $wiki_ms = array_shift($wiki_ms);
            foreach ($wiki_ms as $wiki_m) {
                $tag = substr($wiki_m, 2, -2);
                if (preg_match('/^(.+?)\|(.+)$/', $tag, $m)) {
                    $tag = $m[1];
                    $name = $m[2];
                    $str = str_replace($wiki_m, '<a href="/wiki/show?title=' . urlencode(htmlspecialchars_decode(str_replace(' ', '_', $tag))) . '">' . $name . '</a>', $str);
                } else
                    $str = str_replace($wiki_m, '<a href="/wiki/show?title=' . urlencode(htmlspecialchars_decode(str_replace(' ', '_', $tag))) . '">' . $tag . '</a>', $str);
            }
        }
        
        $split = preg_split('/\{\{.+?\}\}/m', $str);
        if (count($split) > 1) {
            foreach ($split as $tag) {
                $tag = substr($tag, 2, -3);
                return '<a href="/post/index?tags=' + urlencode(htmlspecialchars_decode($tag)) + '">' + $tag + '</a>';
            }
        }
        
        $patterns = array(
            '/[Pp]ost #(\d+)/',
            '/[Ff]orum #(\d+)/',
            '/[Cc]omment #(\d+)/',
            '/[Pp]ool #(\d+)/',
            '/\n/m',
            '/\[b\](.+?)\[\/b\]/',
            '/\[i\](.+?)\[\/i\]/',
            '/\[spoilers?\](.+?)\[\/spoilers?\]/m',
            '/\[spoilers?(=(.+))\](.+?)\[\/spoilers?\]/m'
        );
        
        $replacements = array(
            '<a href="/post/show/\1">post #\1</a>',
            '<a href="/forum/show/\1">forum #\1</a>',
            '<a href="/comment/show/\1">comment #\1</a>',
            '<a href="/pool/show/\1">pool #\1</a>',
            '<br />',
            '<strong>\1</strong>',
            '<em>\1</em>',
            '<span href="#" class="spoiler" onclick="Comment.spoiler(this); return false;"><span class="spoilerwarning">spoiler</span></span><span class="spoilertext" style="display: none">\1</span>',
            '<span href="#" class="spoiler" onclick="Comment.spoiler(this); return false;"><span class="spoilerwarning">\2</span></span><span class="spoilertext" style="display: none">\3</span>'
        );
        
        
        $str = preg_replace($patterns, $replacements, $str);
        # http://regexlib.com/Search.aspx?k=URL&AspxAutoDetectCookieSupport=1
        $url = '(h?ttps?:\/\/[\w\-_]+(?:\.[\w\-_]+)+(?:[\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?)';
        
        if (preg_match_all('/'.$url.'|&lt;&lt;'.$url.'(?:\|(.+?))?&gt;&gt;|&quot;(.+?)&quot;\:'.$url.'/m', $str, $link_ms)) {
            foreach ($link_ms[0] as $k => $link) {
                if ($link_ms[1][$k]) {
                    $text = $link_ms[1][$k];
                    $link = rtrim($link, '.;,:\'"');
                } elseif ($link_ms[2][$k]) {
                    $link = $link_ms[2][$k];
                    if ($link_ms[3][$k])
                        $text = $link_ms[3][$k];
                    else
                        $text = $link_ms[2][$k];
                } else {
                    $text = $link_ms[4][$k];
                    $link = $link_ms[5][$k];
                }
                
                if (preg_match('/^ttp/', $link)) $link = 'h' . $link;
                $str = str_replace($link_ms[0][$k], '<a href="' . $link . '">' . $text . '</a>', $str);
            }
        }
        return $str;
    }
    
    static public function parse_list($str)
    {
        $html = "";
        $layout = array();
        $nest = 0;
        
        foreach(explode("\n", $str) as $line) {
            if (preg_match('/^\s*(\*+) (.+)/', $line, $m)) {
                $nest = strlen($m[1]);
                $content = self::parse_inline($m[2]);
            } else
                $content = parse_inline($line);

            if ($nest > count($layout)) {
                $html .= "<ul>";
                $layout[] = "ul";
            }

            while ($nest < count($layout)) {
                $elist = aray_pop($layout);
                if ($elist)
                    $html .= '</'.$elist.'>';
            }

            $html .= '<li>'.$content.'</li>';
        }

        while ($layout) {
            $elist = array_pop($layout);
            $html .= '</'.$elist.'>';
        }

        return $html;
    }

    static public function parse($str)
    {
        # Make sure quote tags are surrounded by newlines
        $str = preg_replace(
            array(
                '/\s*\[quote\]\s*/m',
                '/\s*\[\/quote\]\s*/m',
                '/(?:\r?\n){3,}/'
            ),
            array(
                "\n\n[quote]\n\n",
                "\n\n[/quote]\n\n",
                "\n\n"
            )
        , $str);
        $str = trim($str);
        $blocks = preg_split('/(?:\r?\n){2}/', $str);
        
        $html = array_map(function($block) {
            if (preg_match('/^(h[1-6])\.\s*(.+)$/', $block, $m)) {
                $tag = $m[1];
                $content = $m[2];
                return '<'.$tag.'>' . DText::parse_inline($content) . '</'.$tag.'>';
            } elseif (preg_match('/^\s*\*+ /', $block))
                return DText::parse_list($block);
            elseif ($block == '[quote]')
                return '<blockquote>';
            elseif ($block == '[/quote]')
                return '</blockquote>';
            else {
                return '<p>' . DText::parse_inline($block) . '</p>';
            }
        }, $blocks);
        
        return implode('', $html);
    }
}