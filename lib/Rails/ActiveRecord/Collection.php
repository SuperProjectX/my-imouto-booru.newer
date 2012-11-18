<?php
class ActiveRecord_Collection implements ArrayAccess, Iterator
{
    private $_page;
    
    private $_per_page;
    
    private $_pages;
    
    private $_offset;
    
    private $_rows;
    
    /* ArrayAccess { */
    private $_members = array();
    
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_members[] = $value;
        } else {
            $this->_members[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->_members[$offset]);
    }
    
    public function offsetUnset($offset)
    {
        unset($this->_members[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->_members[$offset]) ? $this->_members[$offset] : null;
    }
    /* } Iterator {*/
    private $_position = 0;
    
    public function rewind()
    {
        reset($this->_members);
        $this->_position = key($this->_members);
    }

    public function current()
    {
        return $this->_members[$this->_position];
    }

    public function key()
    {
        return key($this->_members);
    }

    public function next()
    {
        next($this->_members);
        $this->_position = key($this->_members);
    }

    public function valid()
    {
        return array_key_exists($this->_position, $this->_members);
    }
    /* } */
    
    public function __construct(array $members = array(), array $data = null)
    {
        $this->_members = $members;
        $this->_set_extra_params($data);
    }
    
    public function merge($coll)
    {
        if ($coll instanceof ActiveRecord_Collection)
            $coll = $coll->all();
        $this->_members = array_merge($this->_members, $coll);
        return $this;
    }
    
    public function all()
    {
        return $this->_members;
    }
    
    /**
     * Each (experimental)
     *
     * If string is passed, it'll be taken as method name to be called.
     * Eg. $posts->each('destroy'); - All posts will be destroyed.
     * In this case, $params for the method may be passed.
     *
     * A Closure may also be passed.
     */
    public function each($function, array $params = array())
    {
        if (is_string($function)) {
            foreach ($this->all() as $m)
                call_user_func_array(array($m, $function), $params);
        } elseif ($function instanceof Closure) {
            foreach ($this->all() as $m) {
                $function($m);
            }
        } else {
            Rails::raise('InvalidArgumentException',
                         'Argument must be an either a string or a Closure, %s passed.',
                         gettype($model));
        }
    }
    
    public function reduce($var, Closure $block)
    {
        foreach ($this->all() as $m) {
            $var = $block($var, $m);
        }
        return $var;
    }
    
    public function unshift($model)
    {
        if ($model instanceof ActiveRecord_Base)
            $model = array($model);
        elseif (!$model instanceof ActiveRecord_Collection) {
            Rails::raise('InvalidArgumentException',
                         'Argument must be an instance of either ActiveRecord_Base or ActiveRecord_Collection, %s passed.',
                         gettype($model));
        }
        
        foreach ($model as $m)
            array_unshift($this->_members, $m);
        
        return $this;
    }
    
    /**
     * Searches objects for a property with a value and returns object.
     */
    public function search($prop, $value)
    {
        foreach ($this->all() as $obj) {
            if ($obj->$prop == $value)
                return $obj;
        }
        return false;
    }
    
    # Returns a Collection with the models that matches the options.
    # Eg: $posts->select(array('is_active' => true, 'user_id' => 4));
    # If Closure passed as $opts, the model that returns == true on the function
    # will be added.
    public function select($opts)
    {
        $objs = array();
        
        if (is_array($opts)) {
            foreach ($this as $obj) {
                foreach ($opts as $prop => $cond) {
                    if (!$obj->$prop || $obj->$prop != $cond)
                        continue;
                    $objs[] = $obj;
                }
            }
        } elseif ($opts instanceof Closure) {
            foreach ($this->all() as $obj) {
                $opts($obj) && $objs[] = $obj;
            }
        }
        
        return new self($objs);
    }
    
    public function remove($attrs)
    {
        !is_array($attrs) && $attrs = array('id' => $attrs);
        
        foreach ($this->all() as $k => $m) {
            foreach ($attrs as $a => $v) {
                if ($m->attribute($a) != $v)
                    continue 2;
            }
            unset($this->_members[$k]);
        }
        
        return $this;
    }
    
    public function max($criteria)
    {
        if (!$this->_members)
            return false;
        
        $current = key($this->_members);
        if (count($this->_members) < 2)
            return $this->_members[$current];
        
        $max = $this->_members[$current];
        
        if ($criteria instanceof Closure) {
            $params = $this;
            foreach($params as $current) {
                if (!$next = next($params))
                    break;
                $max = $criteria($max, $next);
            }
        } else {
            
        }
        return $max;
    }
    
    public function blank()
    {
        return empty($this->_members);
    }
    
    public function any()
    {
        return (bool)$this->_members;
    }
    
    /**
     * TODO: xml shouldn't be created here.
     */
    public function to_xml()
    {
        if ($this->blank())
            return;
        
        $t = get_class($this->current());
        $t = $t::t();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<' . $t . '>';
        
        foreach ($this->all() as $obj) {
            $xml .= $obj->to_xml(array('skip_instruct' => true));
        }
        
        $xml .= '</' . $t . '>';
        
        return $xml;
    }
    
    /**
     * Returns an array of the attributes in the models.
     * $attrs could be a string of a single attribute we want, and
     * an indexed array will be returned.
     * If $attrs is an array of many attributes, an associative array will be returned.
     */
    public function attributes($attrs)
    {
        $models_attrs = array();
        
        if (is_string($attrs)) {
            foreach ($this as $m)
                $models_attrs[] = $m->$attrs;
        } else {
            foreach ($this->all() as $m) {
                $model_attrs = [];
                foreach ($attrs as $attr)
                    $model_attrs[$attr] = $m->$attr;
                $models_attrs[] = $model_attrs;
            }
        }
        
        return $models_attrs;
    }
    
    public function size()
    {
        return count($this->_members);
    }
    
    # Removes dupe models based on id or other attribute.
    public function unique($attr = 'id')
    {
        $checked = array();
        foreach ($this->all() as $k => $obj) {
            if (in_array($obj->$attr, $checked))
                unset($this->_members[$k]);
            else
                $checked[] = $obj->$attr;
        }
        return $this;
    }
    
    # array_slices the collection.
    public function slice($offset, $length = null)
    {
        $clone = clone $this;
        $clone->_members = array_slice($clone->_members, $offset, $length);
        return $clone;
    }
    
    public function page()
    {
        return $this->_page;
    }
    
    # Alias of page();
    public function current_page()
    {
        return $this->_page;
    }
    
    public function per_page()
    {
        return $this->_per_page;
    }
    
    public function offset()
    {
        return $this->_offset;
    }
    
    public function pages()
    {
        if (!is_int($this->_rows) || !is_int($this->_per_page) || $this->_rows < 1 || $this->_per_page < 1)
            return false;
        return ceil($this->_rows / $this->_per_page);
    }
    
    public function total_pages()
    {
        return $this->_pages;
    }
    
    public function rows()
    {
        return $this->_rows;
    }
    
    # Alias of rows()
    public function total_entries()
    {
        return $this->_rows;
    }
    
    public function previous_page()
    {
        return $this->_page - 1;
    }
    
    public function next_page()
    {
        return $this->_page + 1;
    }
    
    public function delete_if(Closure $conditions)
    {
        foreach ($this->all() as $k => $m) {
            if ($conditions($m))
                unset($this[$k]);
        }
    }
    
    public function replace(ActiveRecord_Collection $replacement)
    {
        $this->_members = $replacement->all();
    }
    
    private function _set_extra_params($params)
    {
        if ($params) {
            $params = array_intersect_key($params, array_fill_keys(array('page', 'per_page', 'offset', 'rows'), null));
            foreach($params as $k => $v) {
                $k = '_' . $k;
                $this->$k = (int)$v;
            }
        }
    }
}