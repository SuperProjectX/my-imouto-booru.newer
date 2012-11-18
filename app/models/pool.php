<?php
class Pool_AccessDeniedError extends Exception
{}

class Pool_PostAlreadyExistsError extends Exception
{}

class Pool extends ActiveRecord_Base
{
    # iTODO:
    // m.set_callback 'undo', 'after', 'update_pool_links'
    // m.versioned 'name'
    // m.versioned 'description', 'default' => ""
    // m.versioned 'is_public', 'default' => true
    // m.versioned 'is_active', 'default' => true
    
    /* PostMethods { */
    static public function get_pool_posts_from_posts(array $posts)
    {
        if (!$post_ids = array_map(function($post){return $post->id;}, $posts))
            return array();
        # CHANGED: WHERE pp.active ...
        $sql = sprintf("SELECT pp.* FROM pools_posts pp WHERE pp.post_id IN (%s)", implode(',', $post_ids));
        return PoolPost::find_by_sql($sql)->all();
    }

    static public function get_pools_from_pool_posts(array $pool_posts)
    {
        if (!$pool_ids = array_unique(array_map(function($pp){return $pp->pool_id;}, $pool_posts)))
            return array();

        $sql = sprintf("SELECT p.* FROM pools p WHERE p.id IN (%s)", implode(',', $pool_ids));
        
        if ($pools = Pool::find_by_sql($sql))
            return $pools->all();
        else
            return [];
    }

    public function can_be_updated_by($user)
    {
        return $this->is_public || $user->has_permission($this);
    }

    public function add_post($post_id, $options = array())
    {
        if (isset($options['user']) && !$this->can_be_updated_by($options['user']))
            throw new Pool_AccessDeniedError();
        
        $seq = isset($options['sequence']) ? $options['sequence'] : $this->next_sequence();
        
        $pool_post = $this->all_pool_posts ? $this->all_pool_posts->search('post_id', $post_id) : null;
        
        if ($pool_post) {
            # If :ignore_already_exists, we won't raise PostAlreadyExistsError; this allows
            # he sequence to be changed if the post already exists.
            if ($pool_post->active && empty($options['ignore_already_exists'])) {
                throw new Pool_PostAlreadyExistsError();
            }
            $pool_post->active = true;
            $pool_post->sequence = $seq;
            $pool_post->save();
        } else {
            
            PoolPost::create(array('pool_id' => $this->id, 'post_id' => $post_id, 'sequence' => $seq));
            // new PoolPost('create', array('pool_id' => $this->id, 'post_id' => $post_id, 'sequence' => $seq));
        }
        
        if (empty($options['skip_update_pool_links'])) {
            $this->reload();
            $this->update_pool_links();
        }
    }

    public function remove_post($post_id, $options = array())
    {
        if (!empty($options['user']) && !$this->can_be_updated_by($options['user']))
            throw new Exception('Access Denied');
        
        if ($this->all_pool_posts) {
            if (!$pool_post = $this->all_pool_posts->search('post_id', $post_id))
            return;
            
            $pool_post->delete();
        }
        
        
        $this->reload(); # saving pool_post modified us
        $this->update_pool_links();
    }

    public function recalculate_post_count()
    {
        $this->post_count = $this->pool_posts->size();
    }

    public function transfer_post_to_parent($post_id, $parent_id)
    {
        $pool_post = $this->pool_posts->find_first(array('conditions' => array("post_id = ?", $post_id)));
        $parent_pool_post = $this->pool_posts->find_first(array('conditions' => array("post_id = ?", $parent_id)));
        // return if not parent_pool_post.nil?
        if ($parent_pool_post)
            return;

        $sequence = $pool_post->sequence;
        $this->remove_post($post_id);
        $this->add_post($parent_id, array('sequence' => $sequence));
    }

    public function get_sample() 
    {
        # By preference, pick the first post (by sequence) in the pool that isn't hidden from
        # the index.
        $pool_post = PoolPost::find_all(array(
                            'order' => "posts.is_shown_in_index DESC, pools_posts.sequence, pools_posts.post_id",
                            'joins' => "JOIN posts ON posts.id = pools_posts.post_id",
                            'conditions' => array("pool_id = ? AND posts.status = 'active'", $this->id)));
        
        foreach ($pool_post as $pp) {
            if ($pp->post->can_be_seen_by(current_user())) {
                return $pp->post;
            }
        }
    }

    public function can_change_is_public($user) 
    {
        return $user->has_permission($this);
    }

    public function can_change($user, $attribute)
    {
        if (!$user->is_member_or_higher())
            return false;
        return $this->is_public || $user->has_permission($this);
    }

    public function update_pool_links() 
    {
        if (!$this->pool_posts)
            return;
        
        # iTODO: an assoc can be called like "pool_posts(true)"
        # to force reload.
        # Add support for this maybe?
        # $this->_load_association('pool_posts');
        $pp = $this->pool_posts; //(true) # force reload
        
        $count = $pp->size();
        
        foreach ($pp as $i => $v) {
            $v->next_post_id = ($i == $count - 1) ? null : isset($pp[$i + 1]) ? $pp[$i + 1]->post_id : null;
            $v->prev_post_id = $i == 0 ? null : isset($pp[$i - 1]) ? $pp[$i - 1]->post_id : null;
            $pp[$i]->save();
        }
    }

    public function next_sequence() 
    {
        $seq = 0;
        
        foreach ($this->pool_posts as $pp) {
            $seq = max(array($seq, $pp->sequence));
        }
        
        return $seq + 1;
    }
    
    # iTODO:
    public function expire_cache()
    {
        // Rails::cache()->expire();
    }
   
    /* } ApiMethods { */
    
    public function api_attributes()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'user_id'     => $this->user_id,
            'is_public'   => $this->is_public,
            'post_count'  => $this->post_count,
            'description' => $this->description,
        ];
    }

    public function as_json(array $params = [])
    {
        return json_encode($this->api_attributes());
    }

    # iTODO:
    // public function to_xml(array $options = [])
    // {
        // empty($options['indent']) && $options['indent'] = 2;
        // empty($options['indent']) && $options['indent'] = 2; // ???
        // $xml = isset($options['builder']) ? $options['builder'] : new Rails_Builder_XmlMarkup(['indent' => $options['indent']]);
        // # $xml = options['builder'] ||= Builder::XmlMarkup.new('indent' => options['indent']);
        // $xml->pool($api_attributes, function() {
            // $xml->description($this->description);
            // yield options['builder'] if $this->block_given()
        // })
    // }
    
    /* } NameMethods { */
    
    static public function find_by_name($name)
    {
        if (ctype_digit((string)$name)) {
            return self::find_by_id($name);
        } else {
            return self::find_first(['conditions' => ["lower(name) = lower(?)", $name]]);
        }
    }

    public function normalize_name()
    {
        $this->name = str_replace(' ', "_", $this->name);
    }

    public function pretty_name()
    {
        return str_replace('_', ' ', $this->name);
    }
    
    /* } ZipMethods { */
    
    public function get_zip_filename(array $options = [])
    {
        $filename = str_replace('?', "", $this->pretty_name());
        if (!empty($options['jpeg']))
            $filename .= " (JPG)";
        return $filename . ".zip";
    }

    # Return true if any posts in this pool have a generated JPEG version.
    public function has_jpeg_zip(array $options = [])
    {
        foreach ($this->pool_posts as $pool_post) {
            $post = $pool_post->post;
            if ($post->has_jpeg())
                return true;
        }
        return false;
    }

    # Estimate the size of the ZIP.
    public function get_zip_size(array $options = [])
    {
        $sum = 0;
        foreach ($this->pool_posts as $pool_post) {
            $post = $pool_post->post;
            if ($post->status == 'deleted')
                continue;
            $sum += !empty($options['jpeg']) && $post->has_jpeg() ? $post->jpeg_size : $post->file_size;
        }

        return $sum;
    }

    // #nginx version
    // public function get_zip_data(options= [])
    // {
        // return "" if pool_posts.empty?;

        // $jpeg = options['jpeg'] || false;

        // $buf = [];

        // # Pad sequence numbers in filenames to the longest sequence number.    Ignore any text
        // # after the sequence for padding; for example, if we have 1, 5, 10a and 12,pad
        // # to 2 digits.

        // # Always pad to at least 3 digits.
        // $max_sequence_digits = 3;
        // pool_posts.each do |pool_post|
            // $filtered_sequence = pool_post.sequence.gsub(/^([0-9]+(-[0-9]+)?)?.*/, '\1') # 45a -> 45;
            // filtered_sequence.split(/-/).each { |p|
                // $max_sequence_digits = [p.length, max_sequence_digits].max;
            // }
        // end

        // $filename_count = [];
        // pool_posts.each do |pool_post|
            // $post = pool_post.post;
            // next if post.status == 'deleted'

            // # Strip Rails::root/public off the file path, so the paths are relative to document-root.
            // if (jpeg && post.has_jpeg?) {
                // $path = post.jpeg_path;
                // $file_ext = "jpg";
            // } else {
                // $path = post.file_path;
                // $file_ext = post.file_ext;
            // }
            // $path = path[Rails::root.join('public').to_s.length .. path.length];

            // # For padding filenames, break numbers apart on hyphens and pad each part.    For
            // # example, if max_sequence_digits is 3, and we have "88-89", pad it to "088-089".
            // $filename = pool_post.sequence.gsub(/^([0-9]+(-[0-9]+)*)(.*)$/) { |m|;
                // if ($1 != "") {
                    // $suffix = $3;
                    // $numbers = $1.split(/-/).map { |p|;
                        // "%0*i" % [max_sequence_digits, p.to_i]
                    // }.join("-")
                    // "%s%s" % [numbers, suffix]
                // } else {
                    // "%s" % [$3]
                // }
            // }

            // #filename = "%0*i" % [max_sequence_digits, pool_post.sequence]

            // # Avoid duplicate filenames.
            // filename_count[filename] ||= 0
            // filename_count[filename] = filename_count[filename] + 1
            // if (filename_count[filename] > 1) {
                // filename << " (%i)" % [filename_count[filename]]
            // }
            // filename << ".%s" % [file_ext]

            // #buf << "#{filename}\n"
            // #buf << "#{path}\n"
            // if (jpeg && post.has_jpeg?) {
                // $file_size = post.jpeg_size;
                // $crc32 = post.jpeg_crc32;
            // } else {
                // $file_size = post.file_size;
                // $crc32 = post.crc32;
            // }
            // $crc32 = crc32 ? "%x" % crc32.to_i : '-';
            // buf += [['filename' => filename, 'path' => path, 'file_size' => file_size, 'crc32' => crc32 }]
        // end

        // return buf;
   // ]

    // # Generate a mod_zipfile control file for this pool.
    // public function get_zip_control_file(options= [])
    // {
        // return "" if pool_posts.empty?;

        // $jpeg = options['jpeg'] || false;

        // $buf = "";

        // # Pad sequence numbers in filenames to the longest sequence number.    Ignore any text
        // # after the sequence for padding; for example, if we have 1, 5, 10a and 12,pad
        // # to 2 digits.

        // # Always pad to at least 3 digits.
        // $max_sequence_digits = 3;
        // pool_posts.each do |pool_post|
            // $filtered_sequence = pool_post.sequence.gsub(/^([0-9]+(-[0-9]+)?)?.*/, '\1') # 45a -> 45;
            // filtered_sequence.split(/-/).each { |p|
                // $max_sequence_digits = [p.length, max_sequence_digits].max;
            // }
        // end

        // $filename_count = [];
        // pool_posts.each do |pool_post|
            // $post = pool_post.post;
            // next if post.status == 'deleted'

            // # Strip Rails::root/public off the file path, so the paths are relative to document-root.
            // if (jpeg && post.has_jpeg?) {
                // $path = post.jpeg_path;
                // $file_ext = "jpg";
            // } else {
                // $path = post.file_path;
                // $file_ext = post.file_ext;
            // }
            // $path = path[Rails::root.join('public').to_s.length .. path.length];

            // # For padding filenames, break numbers apart on hyphens and pad each part.    For
            // # example, if max_sequence_digits is 3, and we have "88-89", pad it to "088-089".
            // $filename = pool_post.sequence.gsub(/^([0-9]+(-[0-9]+)*)(.*)$/) { |m|;
                // if ($1 != "") {
                    // $suffix = $3;
                    // $numbers = $1.split(/-/).map { |p|;
                        // "%0*i" % [max_sequence_digits, p.to_i]
                    // }.join("-")
                    // "%s%s" % [numbers, suffix]
                // } else {
                    // "%s" % [$3]
                // }
            // }

            // #filename = "%0*i" % [max_sequence_digits, pool_post.sequence]

            // # Avoid duplicate filenames.
            // filename_count[filename] ||= 0
            // filename_count[filename] = filename_count[filename] + 1
            // if (filename_count[filename] > 1) {
                // filename << " (%i)" % [filename_count[filename]]
            // }
            // filename << ".%s" % [file_ext]

            // buf << "#{filename}\n"
            // buf << "#{path}\n"
            // if (jpeg && post.has_jpeg?) {
                // buf << "#{post.jpeg_size}\n"
                // buf << "#{post.jpeg_crc32}\n"
            // } else {
                // buf << "#{post.file_size}\n"
                // buf << "#{post.crc32}\n"
            // }
        // end

        // return buf;
    // }

    /* } */
    protected function _associations()
    {
        return [
            'belongs_to' => [
                'user',
            ],
            'has_many' => [
                'pool_posts' => ['class_name' => "PoolPost", 'order' => 'CAST(sequence AS UNSIGNED), post_id'], //'conditions' => "pools_posts.active"],
                'all_pool_posts' => ['class_name' => "PoolPost", 'order' => 'CAST(sequence AS UNSIGNED), post_id']
            ]
        ];
    }
    
    protected function _validations()
    {
        return [
            'name' => [
                'presence' => true,
                'uniqueness' => true
            ]
        ];
    }
    
    protected function _callbacks()
    {
        return [
            'after_save' => ['expire_cache'],
            'before_validation' => ['normalize_name']
        ];
    }
}