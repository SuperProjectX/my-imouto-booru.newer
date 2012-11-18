<?php
abstract class ActionView_Helper extends ActionView
{
    use ActionView_Helper_Form, ActionView_Helper_Date, ActionView_Helper_FormTag,
        ActionView_Helper_Header, ActionView_Helper_Html, ActionView_Helper_Number,
        ActionView_Helper_Tag, ActionView_Helper_Text, ActionView_Helper_WillPaginate,
        ActionView_Helper_JavaScript;
    
    /**
     * ActionView_Base children for methods that
     * require it when passing Closures, like form().
     */
    private $_view;
    
    public function __call($method, $params)
    {
        if ($helper = ActionView::find_helper_for($method)) {
            return call_user_func_array(array($helper, $method), $params);
        } else {
            Rails::raise('ActionView_Base_Exception', "Called to undefined method/helper '%s'.", $method);
        }
    }
    
    public function set_view(ActionView_Base $view)
    {
        $this->_view = $view;
    }
    
    public function view()
    {
        return $this->_view;
    }
    
    public function name()
    {
        return $this->_name;
    }
    
    public function url_for($params)
    {
        return ActionController::url_for($params);
    }
    
    public function params()
    {
        return Rails::application()->dispatcher()->parameters();
    }
    
    public function request()
    {
        return Rails::application()->dispatcher()->request();
    }
    
    public function h($str, $flags = null, $charset = null)
    {
        $flags === null && $flags = ENT_COMPAT;
        !$charset && $charset = Rails::application()->config('app', 'encoding');
        return htmlspecialchars($str, $flags, $charset);
    }
    
    public function u($str)
    {
        return urlencode($str);
    }
    
    public function hex_encode($str)
    {
        $r = '';
        $e = strlen($str);
        $c = 0;
        $h = '';
        while ($c < $e) {
            $h = dechex(ord(substr($str, $c++, 1)));
            while (strlen($h) < 3)
                $h = '%' . $h;
            $r .= $h;
        }
        return $r;
    }
    
    public function wrap_js($body)
    {
        return '<script type="text/javascript">' . $body . '</script>';
    }
    
    # TODO: move this method somewhere else, it doesn't belong here.
    protected function _parse_url_params($url_params)
    {
        if (is_array($url_params) || (strpos($url_params, 'http') !== 0 && strpos($url_params, '/') !== 0)) {
            if (!is_array($url_params))
                $url_params = array($url_params);
            $url_to = ActionController::url_for($url_params);
        } else
            $url_to = $url_params;
        return $url_to;
    }
}