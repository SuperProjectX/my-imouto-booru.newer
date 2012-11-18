<?php
ActiveRecord::load_model(array('ArtistUrl', 'WikiPage'));

class Artist extends ActiveRecord_Base
{
    public function __toString()
    {
        return $this->name;
    }
    
    protected function _associations()
    {
        return [
            'has_many' => [
                'artist_urls' => ['class_name' => 'ArtistUrl']
            ],
            'belongs_to' => [
                'updater' => ['class_name' => 'User', 'foreign_key' => "updater_id"]
            ]
        ];
    }
    
    protected function _callbacks()
    {
        return [
            'after_save' => ['commit_urls', 'commit_notes', 'commit_aliases', 'commit_members'],
            'before_validation' => ['normalize']
        ];
    }
    
    protected function _validations()
    {
        return [
            'name' => ['uniqueness' => true]
        ];
    }
    
    /* UrlMethods { */
    static public function find_all_by_url($url)
    {
        $url = ArtistUrl::normalize($url);
        $artists = new ActiveRecord_Collection();

        while ($artists->blank() && strlen($url) > 10) {
            $u = str_replace('*', '%', $url) . '%';
            $artists[] = Artist::find_all(array('joins' => "JOIN artist_urls ON artist_urls.artist_id = artists.id", 'conditions' => array("artists.alias_id IS NULL AND artist_urls.normalized_url LIKE ?", $u), 'order' => "artists.name"));

            # Remove duplicates based on name
            $artists->unique('name');
            
            $url = dirname($url);
        }

        return $artists->slice(0, 20);
    }
    
    protected function commit_urls()
    {
        if ($this->urls) {
            $this->artist_urls->clear();

            foreach (explode("\r\n", $this->urls) as $url)
                ArtistUrl::create(array('url' => $url, 'artist_id' => $this->id));
        }
    }
    
    protected function _urls()
    {
        $urls = array();
        foreach($this->artist_urls as $x)
            $urls[] = $x->url;
        return implode("\n", $urls);
    }
    
    /* Note Methods */
    protected function _wiki_page()
    {
        return WikiPage::find_page($this->name);
    }

    public function notes_locked()
    {
        if ($this->wiki_page)
            return !empty($this->wiki_page->is_locked);
    }

    public function notes()
    {
        if ($this->wiki_page)
            return $this->wiki_page->body;
        else
            return '';
    }
    
    protected function on_notes_change($text)
    {
        $this->notes = $text;
    }
    
    protected function commit_notes()
    {
        if ($this->notes) {
            if (!$this->wiki_page)
                WikiPage::create(array('title' => $this->name, 'body' => $this->notes, 'ip_addr' => $this->updater_ip_addr, 'user_id' => $this->updater_id));
            elseif ($this->wiki_page->is_locked)
                $this->errors()->add('notes', "are locked");
            else
                $this->wiki_page->update_attributes(array('body' => $this->notes, 'ip_addr' => $this->updater_ip_addr, 'user_id' => $this->updater_id));
        }
    }
    
    /* Alias Methods */
    protected function commit_aliases()
    {
        self::execute_sql("UPDATE artists SET alias_id = NULL WHERE alias_id = ".$this->id);
        
        if ($this->alias_names) {
            foreach ($this->alias_names as $name) {
                $a = Artist::find_or_create_by_name($name)->update_attributes(array('alias_id' => $this->id, 'updater_id' => $this->updater_id));
            }
        }
    }
    
    protected function on_alias_names_change($names)
    {
        $this->alias_names = array();
        foreach (explode(',', $names) as $name)
            $this->alias_names[] = trim($name);
    }
    
    public function _alias_names()
    {
        return implode(', ', $this->aliases->attributes('name'));
    }

    public function aliases()
    {
        if ($this->new_record())
            return new ActiveRecord_Collection();
        else
            return Artist::find_all(array('conditions' => "alias_id = ".$this->id, 'order' => "name"));
    }

    protected function _alias_name()
    {
        $name = '';
        
        if ($this->alias_id) {
            try {
                $name = Artist::find($this->alias_id)->name;
            } catch(ActiveRecord_Exception_RecordNotFound $e) {
            }
        }
        
        return $name;
    }

    protected function on_alias_name_change($name)
    {
        if (!empty($name)) {
            if ($artist = Artist::find_or_create_by_name($name))
                $this->alias_id = $artist->id;
            else
                $this->alias_id = null;
        } else
            $this->alias_id = null;
        
        return $name;
    }
    
    /* Group Methods */
    protected function commit_members()
    {
        self::execute_sql("UPDATE artists SET group_id = NULL WHERE group_id = ".$this->id);

        if ($this->member_names) {
            foreach ($this->member_names as $name) {
                $a = Artist::find_or_create_by_name($name);
                $a->update_attributes(array('group_id' => $this->id, 'updater_id' => $this->updater_id));
            }
        }
    }
    
    protected function _group_name()
    {
        if ($this->group_id)
            return Artist::find($this->group_id)->name;
    }

    public function members()
    {
        if ($this->new_record())
            return new ActiveRecord_Collection();
        else
            return Artist::find_all(array('conditions' => "group_id = ".$this->id, 'order' => "name"));
    }
    
    public function member_names()
    {
        return implode(', ', $this->members->attributes('name'));
    }
    
    protected function on_member_names_change($names)
    {
        foreach (explode(',', $names) as $name)
            $this->member_names[] = trim($name);
    }

    protected function on_group_name_change($name)
    {
        if (empty($name))
            $this->group_id = null;
        else 
            $this->group_id = Artist::find_or_create_by_name($name)->id;
        return $name;
    }

    /* Api Methods */
    public function api_attributes()
    {
        return [
            'id'       => $this->id, 
            'name'     => $this->name, 
            'alias_id' => $this->alias_id, 
            'group_id' => $this->group_id,
            'urls'     => $this->artist_urls->attributes('url')
        ];
    }

    public function to_xml(array $options = array())
    {
        $options['root'] = "artist";
        $attrs = $this->api_attributes();
        $attrs['urls'] = implode(' ', $attrs['urls']);
        return parent::to_xml($options, $attrs);
    }

    public function as_json(array $args = array())
    {
        return json_encode($this->api_attributes());
    }
    
    public function normalize()
    {
        $this->name = str_replace(' ', '_', trim(strtolower($this->name)));
    }
    
    static public function generate_sql($name)
    {
        if (strpos($name, 'http') === 0)
            $conds = array('id IN (??)', self::find_all_by_url($name)->attributes('id'));
        else
            $conds = array("name LIKE ?", $name . "%");
        
        $sql = array('conditions' => $conds);
        
        return $sql;
    }
}