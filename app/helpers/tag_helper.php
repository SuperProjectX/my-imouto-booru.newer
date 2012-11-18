<?php
class TagHelper extends ActionView_Helper
{
    public function tag_link($name, array $options = array())
    {
        !$name && $name = 'UNKNOWN';
        $prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $obsolete = isset($options['obsolete']) ? $options['obsolete'] : array();

        $tag_type = Tag::type_name($name);
        $obsolete_tag = (array($name) != $obsolete) ? '' : ' obsolete';
        $html = $prefix ? '' : $this->content_tag('span', $prefix, array('class' => $obsolete_tag));
        $html .= $this->content_tag('span',
                                    $this->link_to($name, array('post#index', 'tags' => $name)),
                                    array('class' => "tag-type-".$tag_type.$obsolete_tag));
        return $html;
    }
    
    public function tag_links($tags, array $options = array())
    {
        if (!$tags)
            return '';
        
        $prefix = isset($options['prefix']) ? $options['prefix'] : "";

        $html = "";
        
        if (is_string(current($tags))) {
            if (is_string(key($tags)))
                $tags = array_keys($tags);
            $tags = Tag::find_all(array('conditions' => array("name in (?)", $tags), 'select' => "name, post_count, id"));
            $tags = $tags->reduce(array(), function($all, $x) {$all[] = array($x->name, $x->post_count, $x->id); return $all;});
            usort($tags, function($a, $b) {if ($a > $b) return $a; return $b;});
        } elseif (is_array(current($tags))) {
            # $x is expected to have name as first value and post_count as second.
            $tags = array_map(function($x){return array(current($x), next($x), null);}, $tags);
        } elseif (current($tags) instanceof Tag) {
            $tags = array_map(function($x){return array($x->name, $x->post_count, $x->id);}, $tags->all());
        }
        
        foreach ($tags as $t) {
            $name  = array_shift($t);
            $count = array_shift($t);
            $id    = array_shift($t);
            !$name && $name = 'UNKNOWN';
            
            $tag_type = Tag::type_name($name);
            
            $html .= '<li class="tag-type-' . $tag_type . '">';
            
            if (CONFIG()->enable_artists && $tag_type == 'artist')
                $html .= '<a href="/artist/show?name=' . $this->u($name) . '">?</a> ';
            else
                $html .= '<a href="/wiki/show?title=' . $this->u($name) . '">?</a> ';
            
            if (current_user()->is_privileged_or_higher()) {
                $html .= '<a href="/post?tags=' . $this->u($name) . '+' . $this->u($this->params()->tags) . '" class="no-browser-link">+</a> ';
                $html .= '<a href="/post?tags=-' . $this->u($name) . '+' .$this->u($this->params()->tags) . '" class="no-browser-link">&ndash;</a> ';
            }
            
            if (!empty($options['with_hover_highlight'])) {
                $mouseover = ' onmouseover=\'Post.highlight_posts_with_tag("' . str_replace("'", "&#145;", $name) . '")\'';
                $mouseout  = " onmouseout='Post.highlight_posts_with_tag(null)'";
            } else
                $mouseover = $mouseout = '';
            
            $html .= '<a href="/post?tags=' . $this->u($name) . '"' . $mouseover . $mouseout . '>' . (str_replace("_", " ", $name)) . '</a> ';
            $html .= '<span class="post-count">' . $count . '</span> ';
            $html .= '</li>';
        }
        
        return $html;
    }
    
    public function cloud_view($tags, $divisor = 6)
    {
        $html = "";
        
        foreach ($tags as $tag) {
            $size = log($tag['post_count']) / $divisor;
            $size < 0.8 && $size = 0.8;
            $html .= '<a href="/post/index?tags='.$this->u($tag['name']).'" style="font-size: '.$size.'em;" title="'.$tag['post_count'].' '.($tag['post_count'] == 1 ? 'post' : 'posts').'">'.$this->h($tag['name']).'</a> ';
        }

        return $html;
    }
}