<?php
ActiveRecord::load_model('WikiPageVersion');
require_once RAILS_ROOT . '/lib/versioning.php';

class WikiPage extends ActiveRecord_Base
{
    use ActsAsVersioned;
  
    static public function generate_sql(array $options = array())
    {
        $joins = [];
        $conds = [];
        $params = [];

        if (isset($options['title'])) {
            $conds[] = "wiki_pages.title = ?";
            $params[] = $options['title'];
        }

        if (isset($options['user_id'])) {
            $conds[] = "wiki_pages.user_id = ?";
            $params[] = $options['user_id'];
        }

        $joins = implode(" ", $joins);
        $conds = [implode(" AND ", $conds), $params];

        return [$joins, $conds];
    }

    static public function find_page($title, $version = null)
    {
        if (!$title)
            return;

        $page = self::find_by_title($title);
        if ($version && $page)
            $page->revert_to($version);
        
        return $page;
    }

    static public function find_by_title($title)
    {
        return self::find_first(['conditions' => ["lower(title) = lower(?)", str_replace(' ', '_', $title)]]);
    }

    public function normalize_title()
    {
        $this->title = strtolower(str_replace(" ", "_", $this->title));
    }

    public function last_version()
    {
        return (int)$this->version == ($this->_next_version() - 1);
    }

    public function first_version()
    {
        return $this->version == 1;
    }

    public function author()
    {
        return $this->user->name;
    }

    public function pretty_title()
    {
        return str_replace('_', ' ', $this->title);
    }

    public function diff($version)
    {
        if (!$otherpage = self::find_page($this->title, $version))
            return;
        
        $body = explode("\r\n", $this->body);
        $otherbody = explode("\r\n", $otherpage->body);
        
        $diff = new Horde_Text_Diff('auto', array($body, $otherbody));
        $renderer = new Horde_Text_Diff_Renderer_unified();
        
        $result = $renderer->render($diff);
        $result = explode("\n", $result);
        array_shift($result);
        $result = implode('<br />', $result);
        
        return $result;
    }

    # FIXME: history shouldn't be changed on lock/unlock.
    #        We should instead check last post status when editing
    #        instead of doing mass update.
    public function lock()
    {
        $this->is_locked = true;
        
        // transaction do
        self::execute_sql("UPDATE wiki_pages SET is_locked = TRUE WHERE id = ?", $this->id);
        self::execute_sql("UPDATE wiki_page_versions SET is_locked = TRUE WHERE wiki_page_id = ?", $this->id);
        // end
    }

    public function unlock()
    {
        $this->is_locked = false;
        
        // transaction do
        self::execute_sql("UPDATE wiki_pages SET is_locked = FALSE WHERE id = ?", $this->id);
        self::execute_sql("UPDATE wiki_page_versions SET is_locked = FALSE WHERE wiki_page_id = ?", $this->id);
        // end
    }

    public function rename($new_title)
    {
        // if (self::exists(['wiki_pages WHERE title = ? AND id != ?', $new_title, $this->id]))
            // return false;
        
        self::execute_sql("UPDATE wiki_pages SET title = ? WHERE id = ?", $new_title, $this->id);
        self::execute_sql("UPDATE wiki_page_versions SET title = ? WHERE wiki_page_id = ?", $new_title, $this->id);
        
        return true;
    }
    
    public function to_xml(array $options = [])
    {
        return parent::to_xml(array_merge($options, ['root' => 'wiki_page']), ['id' => $this->id, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at, 'title' => $this->title, 'body' => $this->body, 'updater_id' => $this->user_id, 'locked' => $this->is_locked, 'version' => $this->version]);
    }
    
    public function as_json()
    {
        return ['id' => $this->id, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at, 'title' => $this->title, 'body' => $this->body, 'updater_id' => $this->user_id, 'locked' => $this->is_locked, 'version' => $this->version];
    }

    protected function _ensure_changed()
    {
        if (!$this->title_changed() || !$this->body_changed())
            $this->errors()->add('base', 'no_change');
    }
    
    protected function _callbacks()
    {
        return array_merge_recursive([
            'before_save' => ['normalize_title'],
            'before_validation_on_update' => ['ensure_changed']
        ], $this->_versioning_callbacks());
    }
    
    protected function _associations()
    {
        return [
            'belongs_to' => ['user']
        ];
    }
    
    protected function _validations()
    {
        return [
            'title' => [
                'uniqueness' => true,
                'presence'   => true
            ],
            'body' => [
                'presence' => true,
            ]
        ];
    }
    
    protected function _acts_as_versioned()
    {
        return ['table_name' => "wiki_page_versions", 'foreign_key' => "wiki_page_id", 'order' => "updated_at DESC"];
    }
}