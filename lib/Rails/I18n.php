<?php
class Rails_I18n
{
    /**
     * Locale name.
     */
    private $_locale;
    
    private $_available_locales;
    
    /**
     * Array with translations.
     */
    private $_tr = array();
    
    public function __construct($locale = null)
    {
        if ($locale !== null)
            $this->locale($locale);
        else {
            $this->_locale = $this->_config('default');
        }
        $this->_load_locale();
    }
    
    public function locale($val = null)
    {
        if ($val !== null) {
            if (!is_string($val))
                Rails::raise('InvalidArgumentException', 'Locale value must be a string, %s passed', array(gettype($val)));
            elseif ($this->_locale == $val)
                return;
            $this->_locale = $val;
            $this->_load_locale();
        } else
            return $this->_locale;
    }
    
    public function t($params)
    {
        if (is_array($params))
            $name = array_shift($params);
        elseif (is_string($params)) {
            $name = $params;
            $params = array();
        } else
            Rails::raise('InvalidArgumentException', 'Argument must be either an array or string, %s passed', gettype($params));
        
        if (is_null($tr = $this->_get_translation($this->_locale, $name))
            && ($this->_locale != $this->_config('default') ? is_null($tr = $this->_get_translation($this->_config('default'), $name)) : true))
            $tr = '<span class="translation_missing">#' . $name . '</span>';
        
        if (is_int(strpos($tr, '%{'))) {
            foreach ($params as $k => $param)
                $tr = str_replace('%{'.$k.'}', $param, $tr);
        }
        if ($params) {
            call_user_func_array('sprintf', array_merge(array($tr), $params));
        }
        
        return $tr;
    }
    
    public function available_locales()
    {
        if (!is_array($this->_available_locales)) {
            $this->_get_available_locales();
        }
        return $this->_available_locales;
    }
    
    private function _get_translation($lang, $name)
    {
        $tr = null;
        
        if (is_int(strpos($name, '.'))) {
            $tr = $this->_tr[$lang];
            foreach (explode('.', $name) as $idx) {
                if (isset($tr[$idx])) {
                    $tr = $tr[$idx];
                } else {
                    break;
                }
            }
            if (!is_string($tr))
                $tr = null;
        } else {
            if (isset($this->_tr[$lang][$name]))
                $tr = $this->_tr[$lang][$name];
        }
        
        return $tr;
    }
    
    private function _get_available_locales()
    {
        $dh = opendir($this->_config('path'));
        
        $this->_available_locales = array();
        
        while (!is_bool($file = readdir($dh))) {
            if ($file == '.' || $file == '..')
                continue;
            $locale = pathinfo($file, PATHINFO_FILENAME);
            $this->_available_locales[] = $locale;
        }
        closedir($dh);
    }
    
    private function _load_locale($locale = null)
    {
        !func_num_args() && $locale = $this->_locale;
        
        // $exts = $this->_config('ext');
        $exts = array('php');
        !is_array($exts) && $exts = array($exts);
        
        $file = $this->_config('path') . '/' . $locale . '.';
        
        foreach ($exts as $ext) {
            $f = $file.$ext;
            if (is_file($f)) {
                $this->_tr = array_merge($this->_tr, require $f);
                break;
            }
        }
    }
    
    private function _config($name = null)
    {
        $config = Rails::application()->config('i18n');
        return func_num_args() ? $config[$name] : $config;
    }
}