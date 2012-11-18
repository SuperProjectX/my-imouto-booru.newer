<?php
class ApplicationHelper extends ActionView_Helper
{
    private $_top_menu_items = [];
    
    public function html_title()
    {
        $base_title = CONFIG()->app_name;
        if ($this->content_for('title'))
            return $this->yield('title'); // . " | " . $base_title;
        else
            return $base_title;
    }
    
    public function tag_header($tags = null)
    {
        if (!$tags)
            return;
        $tags = array_filter(explode(' ', $tags));
        foreach($tags as $k => $tag)
            $tags[$k] = $this->link_to(str_replace('_', ' ', $tag), array('/post', 'tags' => $tag));
        return '/'.implode('+', $tags);
    }

    public function make_main_item($label, $url_options, $options = [])
    {
        $item = $this->_make_menu_item($label, $url_options, $options);
        $this->_top_menu_items[] = $item;
        return json_encode($item);
    }

    public function make_sub_item($label, $url_options, $options = [])
    {
        $item = $this->_make_menu_item($label, $url_options, $options);
        return json_encode($item);
    }
    
    public function top_menu_items()
    {
        return $this->_top_menu_items;
    }
    
    # Return true if the user can access the given level, or if creating an
    # account would.  This is only actually used for actions that require
    # privileged or higher; it's assumed that starting_level is no lower
    # than member.
    public function can_access($level)
    {
        $needed_level = User::get_user_level($level);
        $starting_level = CONFIG()->starting_level;
        $user_level = current_user()->level;
        if ($user_level >= $needed_level || $starting_level >= $needed_level)
            return true;
        return false;
     }
    
    # Return true if the starting level is high enough to execute
    # this action.    This is used by User.js.
    public function need_signup($level)
    {
        $needed_level = User::get_user_level($level);
        $starting_level = CONFIG()->starting_level;
        return $starting_level >= $needed_level;
    }
    
    public function get_help_action_for_controller($controller)
    {
        $singular = array("forum", "wiki");
        $help_action = $controller;
        if (in_array($help_action, $singular))
            return $help_action;
        else
            return $help_action . 's';
    }
    
    public function navigation_links($post)
    {
        $html = array();
        
        if ($post instanceof Post) {
            $html[] = $this->tag("link", array('rel' => "prev", 'title' => "Previous Post", 'href' => $this->url_for(array('post#show', 'id' => $post->id - 1))));
            $html[] = $this->tag("link", array('rel' => "next", 'title' => "Next Post", 'href' => $this->url_for(array('post#show', 'id' => $post->id + 1))));
        } elseif ($post instanceof ActiveRecord_Collection) {
            $posts = $post;
            
            $url_for = $this->request()->controller() . '#' . $this->request()->action();
            
            if ($posts->previous_page()) {
                $html[] = $this->tag("link", array('href' => $this->url_for(array_merge(array($url_for), $this->params()->to_array(), array('page' => 1))), 'rel' => "first", 'title' => "First Page"));
                $html[] = $this->tag("link", array('href' => $this->url_for(array_merge(array($url_for), $this->params()->to_array(), array('page' => $posts->previous_page()))), 'rel' => "prev", 'title' => "Previous Page"));
            }

            if ($posts->next_page()) {
                $html[] = $this->tag("link", array('href' => $this->url_for(array_merge(array($url_for), $this->params()->to_array(), array('page' => $posts->next_page()))), 'rel' => "next", 'title' => "Next Page"));
                $html[] = $this->tag("link", array('href' => $this->url_for(array_merge(array($url_for), $this->params()->to_array(), array('page' => $posts->total_pages()))), 'rel' => "last", 'title' => "Last Page"));
            }
        }
        
        return implode("\n", $html);
    }
    
    public function format_text($text, array $options = [])
    {
        return DText::parse($text);
    }

    public function format_inline($inline, $num, $id, $preview_html = null)
    {
        if (!$inline->inline_images)
            return "";

        $url = $inline->inline_images->first->preview_url();
        if (!$preview_html)
            $preview_html = '<img src="'.$url.'">';
        
        $id_text = "inline-$id-$num";
        $block = '
            <div class="inline-image" id="'.$id_text.'">
                <div class="inline-thumb" style="display: inline;">
                '.$preview_html.'
                </div>
                <div class="expanded-image" style="display: none;">
                    <div class="expanded-image-ui"></div>
                    <span class="main-inline-image"></span>
                </div>
            </div>
        ';
        $inline_id = "inline-$id-$num";
        $script = 'InlineImage.register("'.$inline_id.'", '.to_json($inline).');';
        return array($block, $script, $inline_id);
    }
    
    public function format_inlines($text, $id)
    {
        $num = 0;
        $list = array();
        
        // preg_match('/image #(\d+)/i', $text, $m);
        // foreach ($m as $t) {
            // $i = Inline::find($m[1]);
            // if ($i) {
                // list($block, $script) = format_inline($i, $num, $id);
                // $list[] = $script;
                // $num++;
                // return $block;
            // } else
                // return $t;
        // }

        if ($num > 0 )
            $text .= '<script language="javascript">' . implode("\n", $list) . '</script>';

        return $text;
    }

    public function compact_time($datetime)
    {
        $datetime = new DateTime($datetime);
        
        if ($datetime->format('Y') == gmd('Y')) {
            if ($datetime->format('M d, Y') == gmd('M d, Y'))
                $format = 'H:i';
            else
                $format = 'M d';
        } else {
            $format = 'M d, Y';
        }
        return $datetime->format($format);
    }
    
    /**
     * Test:
     * To change attribute ['level' => 'member'] for ['class' => "need-signup"]
     */
    public function form_tag($action_url = null, $attrs = [], Closure $block = null)
    {
        /* Took from parent { */
        if (func_num_args() == 1 && $action_url instanceof Closure) {
            $block = $action_url;
            $action_url = null;
        } elseif ($attrs instanceof Closure) {
            $block = $attrs;
            $attrs = [];
        }
        /* } */
        
        if (isset($attrs['level']) && $attrs['level'] == 'member') {
            $class = "need-signup";
            if (isset($attrs['class']))
                $attrs['class'] .= ' ' . $class;
            else
                $attrs['class'] = $class;
            unset($attrs['level']);
        }
        
        return parent::form_tag($action_url, $attrs, $block);
    }
    
    private function _make_menu_item($label, $url_options, array $options)
    {
        !is_array($url_options) && $url_options = array($url_options);
        
        $url_for = current($url_options);
        $token = new Rails_UrlToken($url_for);
        $token_controller = $token->controller();
        
        $item = array(
            'label'       => $label,
            'dest'        => $this->url_for($url_options),
            'class_names' => isset($options['class_names']) ? $options['class_names'] : array()
        );
        isset($options['html_id']) && $item['html_id'] = $options['html_id'];
        isset($options['name']) && $item['name'] = $options['name'];
        
        if (isset($options['level']) && $this->need_signup($options['level']))
            $item['login'] = true;

        if ($token_controller == Rails::application()->dispatcher()->parameters()->controller)
            $item['class_names'][] = "current-menu";

        return $item;
    }
}