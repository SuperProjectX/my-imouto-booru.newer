<?php
# These are methods dealing with getting the image and generating the thumbnail.
# It works in conjunction with the image_store methods. Since these methods have
# to be called in a specific order, they've been bundled into one module.
trait PostFileMethods
{
    /**
     * For /post/import
     */
    static public function get_import_files($dir)
    {
        # [0] files; [1] invalid_files; [2] invalid_folders;
        $data = array(array(), array(), array());
        
        if ($fh = opendir($dir)) {
            while (false !== ($file = readdir($fh))) {
                if ($file == '.' || $file == '..')
                    continue;
                
                if (is_int(strpos($file, '?'))) {
                    $e = addslashes(str_replace(RAILS_ROOT.'/public/data/import/', '', utf8_encode($dir.$file)));
                    if (preg_match('/\.\w+$/', $e))
                        $data[1][] = $e;
                    else
                        $data[2][] = $e;
                    continue;
                }
                
                if (is_dir($dir.$file)) {
                    list($files, $invalid_files, $invalid_folders) = Post::get_import_files($dir.$file.'/');
                    $data[0] = array_merge($data[0], $files);
                    $data[1] = array_merge($data[1], $invalid_files);
                    $data[2] = array_merge($data[2], $invalid_folders);
                } else
                    $data[0][] = addslashes(str_replace(RAILS_ROOT.'/public/data/import/', '', utf8_encode($dir.$file)));
            }
            closedir($fh);
        }
        return $data;
    }
    // include Moebooru::TempfilePrefix

    public function strip_exif()
    {
         // if (file_ext.downcase == 'jpg' then) {
            // # FIXME: awesome way to strip EXIF.
            // #                This will silently fail on systems without jhead in their PATH
            // #                and may cause confusion for some bored ones.
            // system('jhead', '-purejpg', tempfile_path)
        // }
        // return true
    }

    protected function _ensure_tempfile_exists()
    {
        if (empty($_FILES['post']['name']['file']) || $_FILES['post']['error']['file'] === UPLOAD_ERR_OK)
            return;
        $this->errors()->add('file', "not found, try uploading again");
        return false;
    }

    protected function _validate_content_type()
    {
        if (!array_key_exists($this->mime_type, CONFIG()->allowed_mime_types)) {
            $this->errors()->add('file', 'is an invalid content type: ' . $this->mime_type);
            return false;
        }
        
        $this->file_ext = CONFIG()->allowed_mime_types[$this->mime_type];
    }
    
    public function pretty_file_name($options = array())
    {
        # Include the post number and tags.    Don't include too many tags for posts that have too
        # many of them.
        empty($options['type']) && $options['type'] = 'image';
        $tags = null;
        # If the filename is too long, it might fail to save or lose the extension when saving.
        # Cut it down as needed.    Most tags on moe with lots of tags have lots of characters,
        # and those tags are the least important (compared to tags like artists, circles, "fixme",
        # etc).
        #
        # Prioritize tags:
        # - remove artist and circle tags last; these are the most important
        # - general tags can either be important ("fixme") or useless ("red hair")
        # - remove character tags first; 

     
        if ($options['type'] == 'sample') {
            $tags = "sample";
        } else
         $tags = Tag::compact_tags($this->tags, 150);
        
        # Filter characters.
        $tags = str_replace(array('/', '?'), array('_', ''), $tags);

        $methodame = "{$this->id} $tags";
        if (CONFIG()->download_filename_prefix)
            $methodame = CONFIG()->download_filename_prefix . " " . $methodame;
        
        return $methodame;
    }
    
    public function file_name()
    {
        return $this->md5 . "." . $this->file_ext;
    }
    
    # PHP does this automatically
    // public function delete_tempfile()
    // {
        // FileUtils.rm_f(tempfile_path)
        // FileUtils.rm_f(tempfile_preview_path)
        // FileUtils.rm_f(tempfile_sample_path)
        // FileUtils.rm_f(tempfile_jpeg_path)
    // }

    // public function tempfile_path()
    // {
        // "#{tempfile_prefix}.upload"
    // }

    public function fake_sample_url()
    {
        if (CONFIG()->use_pretty_image_urls) {
            $paramsath = "/data/image/".$this->md5."/".$this->pretty_file_name(array('type' => 'sample')).'.'.$this->file_ext;
        } else
            $paramsath = "/data/image/" . CONFIG()->sample_filename_prefix . $this->md5 . '.' . $this->file_ext;
        
        return CONFIG()->url_base . $paramsath;
    }
    
    public function tempfile_preview_path()
    {
        return RAILS_ROOT . "/public/data/{$this->md5}-preview.jpg";
    }

    public function tempfile_sample_path()
    {
        return RAILS_ROOT . "/public/data/{$this->md5}-sample.jpg";
    }
    
    public function tempfile_jpeg_path()
    {
        return RAILS_ROOT . "/public/data/".$this->md5."-jpeg.jpg";
    }

     # Generate MD5 and CRC32 hashes for the file.    Do this before generating samples, so if this
    # is a duplicate we'll notice before we spend time resizing the image.
    public function regenerate_hash()
    {
        $paramsath = !empty($this->tempfile_path) ? $this->tempfile_path : $this->file_path();
        
        if (!file_exists($paramsath)) {
            
            $this->errors()->add('file', "not found");
            return false;
        }
        
        $this->md5 = md5_file($paramsath);
        # TODO
        // $this->crc32 = ...............
        return true;
    }

    public function regenerate_jpeg_hash()
    {
        if (!$this->has_jpeg())
            return false;

        // crc32_accum = 0
        // File.open(jpeg_path, 'rb') { |fp|
            // buf = ""
            // while fp.read(1024*64, buf) do
                // crc32_accum = Zlib.crc32(buf, crc32_accum)
            // end
        // }
        // return; false if self.jpeg_crc32 == crc32_accum

        // self.jpeg_crc32 = crc32_accum
        return true;
    }

    public function generate_hash()
    {
        if (!$this->regenerate_hash())
            return false;
        
        if (Post::exists(array("md5 = ?", $this->md5))) {
            $this->errors()->add('md5', "already exists");
            return false;
        } else
            return true;
    }

    # Generate the specified image type.    If options[:force_regen] is set, generate the file even
    # IF it already exists
    
    public function regenerate_images($type, array $options = array())
    {
        if (!$this->image())
            return true;

         // if (type == :sample then) {
            // return; false if !generate_sample(options[:force_regen])
            // temp_path = tempfile_sample_path
            // dest_path = sample_path
        // } elseif (type == :jpeg then) {
            // return; false if !generate_jpeg(options[:force_regen])
            // temp_path = tempfile_jpeg_path
            // dest_path = jpeg_path
        // } elseif (type == :preview then) {
            // return; false if !generate_preview
            // temp_path = tempfile_preview_path
            // dest_path = preview_path
        // } else {
            // raise Exception, "unknown type: %s" % type
        // }

        // # Only move in the changed files on success.    When we return; false, the caller won't
        // # save us to the database; we need to only move the new files in if we're going to be
        // # saved.    This is normally handled by move_file.
         // if (File.exists?(temp_path)) {
            // FileUtils.mkdir_p(File.dirname(dest_path), 'mode' => 0775)
            // FileUtils.mv(temp_path, dest_path)
            // FileUtils.chmod(0775, dest_path)
        // }

        return true;
    }

    # Automatically download from the source if it's a URL.
    public function download_source()
    {
        if (!preg_match('/^https?:\/\//', $this->source) || !empty($this->file_ext))
            return;

        // begin
            // Danbooru.http_get_streaming(source) do |response|
                // File.open(tempfile_path, "wb") do |out|
                    // response.read_body do |block|
                        // out.write(block)
                    // end
                // end
            // end

            // if (self.source.to_s =~ /^http/ and self.source.to_s !~ /pixiv\.net/ then) {
                // #self.source = "Image board"
                // self.source = ""
            // }

            // return; true
        // rescue SocketError, URI::Error, Timeout::Error, SystemCallError => x
            // delete_tempfile
            // errors.add "source", "couldn't be opened: #{x}"
            // return; false
        // end
    }

    public function determine_content_type()
    {
        if (!file_exists($this->tempfile_path)) {
            $this->errors()->add_to_base("No file received");
            return false;
        }
        
        $this->tempfile_ext = pathinfo($this->tempfile_name, PATHINFO_EXTENSION);
        $this->tempfile_name = pathinfo($this->tempfile_name, PATHINFO_FILENAME);
        
        if (class_exists('finfo', false)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $this->mime_type = $finfo->file($this->tempfile_path);
        } else {
            $this->tempfile_ext == 'jpeg' && $this->tempfile_ext = 'jpg';
            $this->mime_type = array_search($this->tempfile_ext, CONFIG()->allowed_mime_types);
        }
        
        is_bool($this->mime_type) && $this->mime_type = 'Unkown mime-type';
    }

    // # Assigns a CGI file to the post. This writes the file to disk and generates a unique file name.
    // public function file=(f)
    // {
        // return; if f.nil? || count(f) == 0

        // if (f.tempfile.path) {
            // # Large files are stored in the temp directory, so instead of
            // # reading/rewriting through Ruby, just rely on system calls to
            // # copy the file to danbooru's directory.
            // FileUtils.cp(f.tempfile.path, tempfile_path)
        // } else {
            // File.open(tempfile_path, 'wb') {|nf| nf.write(f.read)}
        // }

        // self.received_file = true
    // }

    protected function set_image_dimensions()
    {
        if ($this->image() or $this->flash()) {
            list($this->width, $this->height) = getimagesize($this->tempfile_path);
        }
        $this->file_size = filesize($this->tempfile_path);
    }

    # If the image resolution is too low and the user is privileged or below, force the
    # image to pending.    If the user has too many pending posts, raise an error.
    #
    # We have to do this here, so on creation it's done after set_image_dimensions so
    # we know the size.    If we do it in another module the order of operations is unclear.
    protected function image_is_too_small()
    {
        if (!CONFIG()->min_mpixels) return false;
        if (empty($this->width)) return false;
        if ($this->width * $this->height >= CONFIG()->min_mpixels) return false;
        return true;
    }

    protected function set_image_status()
    {
        if(!$this->image_is_too_small())
            return true;
        
        if ($this->user->level >= 33)
            return;

        $this->status = "pending";
        $this->status_reason = "low-res";
        return true;
    }

    # If this post is pending, and the user has too many pending posts, reject the upload.
    # This must be done after set_image_status.
    public function check_pending_count()
    {
        if (!CONFIG()->max_pending_images) return;
        if ($this->status != "pending") return;
        if ($this->user->level >= 33) return;

        $paramsending_posts = Post::count(array('conditions' => array("user_id = ? AND status = 'pending'", $this->user_id)));
        if ($paramsending_posts < CONFIG()->max_pending_images) return;

        $this->errors()->add_to_base("You have too many posts pending moderation");
        return false;
    }

    # Returns true if the post is an image format that GD can handle.
    public function image()
    {
        return in_array($this->file_ext, array('jpg', 'jpeg', 'gif', 'png'));
    }

    # Returns true if the post is a Flash movie.
    public function flash()
    {
        return $this->file_ext == "swf";
    }
    
    public function gif()
    {
        return $this->file_ext == 'gif';
    }

    // public function find_ext(file_path)
    // {
        // ext = File.extname(file_path)
        // if (ext.blank?) {
            // return; "txt"
        // } else {
            // ext = ext[1..-1].downcase
            // ext = "jpg" if ext == "jpeg"
            // return; ext
        // }
    // }
    

    
    // public function content_type_to_file_ext(content_type)
    // {
        // case content_type.chomp
        // when "image/jpeg"
            // return; "jpg"

        // when "image/gif"
            // return; "gif"

        // when "image/png"
            // return; "png"

        // when "application/x-shockwave-flash"
            // return; "swf"

        // } else {
            // nil
        // end
    // }

    public function raw_preview_dimensions()
    {
        if ($this->image()) {
            $dim = Moebooru_Resizer::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 300, 'height' => 300));
            $dim = array($dim['width'], $dim['height']);
        } else
            $dim = array(300, 300);
        
        // if (!$paramsrop)
            return $dim;
        // elseif ($paramsrop == 'w')
            // return $dim[0];
        // elseif ($paramsrop == 'h')
            // return $dim[1];
    }

    public function preview_dimensions()
    {
        if ($this->image()) {
            $dim = Moebooru_Resizer::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 150, 'height' => 150));
            $dim = array($dim['width'], $dim['height']);
        } else
            $dim = array(150, 150);
        
        // if (!$paramsrop)
            return $dim;
        // elseif ($paramsrop == 'w')
            // return $dim[0];
        // elseif ($paramsrop == 'h')
            // return $dim[1];
    }

    public function generate_sample($force_regen = false)
    {
        if ($this->gif() || !$this->image()) return true;
        elseif (!CONFIG()->image_samples) return true;
        elseif (!$this->width && !$this->height) return true;
        elseif ($this->file_ext == "gif") return true;

        # Always create samples for PNGs.
        $ratio = $this->file_ext == 'png' ? 1 : CONFIG()->sample_ratio;

        $size = array('width' => $this->width, 'height' => $this->height);
        if (CONFIG()->sample_width)
            $size = Moebooru_Resizer::reduce_to($size, array('width' => CONFIG()->sample_width, 'height' => CONFIG()->sample_height), $ratio);
        
        $size = Moebooru_Resizer::reduce_to($size, array('width' => CONFIG()->sample_max, 'height' => CONFIG()->sample_min), $ratio, false, true);
        
        # We can generate the sample image during upload or offline.    Use tempfile_path
        #- if it exists, otherwise use file_path.
        $paramsath = $this->tempfile_path;
        // $paramsath = file_path unless File.exists?(path)
        if (!file_exists($paramsath)) {
            $this->errors()->add('file', "not found");
            return false;
        }

        # If we're not reducing the resolution for the sample image, only reencode if the
        # source image is above the reencode threshold.    Anything smaller won't be reduced
        # enough by the reencode to bother, so don't reencode it and save disk space.
        if ($size['width'] == $this->width && $size['height'] == $this->height && filesize($paramsath) < CONFIG()->sample_always_generate_size) {
            $this->sample_width = null;
            $this->sample_height = null;
            return true;
        }
        
        # If we already have a sample image, and the parameters havn't changed,
        # don't regenerate it.
        if ($this->has_sample() && !$force_regen && ($size['width'] == $this->sample_width && $size['height'] == $this->sample_height))
            return true;
        
        try {
            Moebooru_Resizer::resize($this->file_ext, $paramsath, $this->tempfile_sample_path(), $size, CONFIG()->sample_quality);
        } catch (Exception $e) {
            $this->errors()->add('sample', 'couldn\'t be created: '. $e->getMessage());
            return false;
        }
        
        $this->sample_width = $size['width'];
        $this->sample_height = $size['height'];
        $this->sample_size = filesize($this->tempfile_sample_path());
        
        # TODO: enable crc32 for samples.
        $crc32_accum = 0;

        return true;
    }

    protected function generate_preview()
    {
        if (!$this->image() || (!$this->width && !$this->height))
            return true;
        
        $size = Moebooru_Resizer::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 300, 'height' => 300));

        # Generate the preview from the new sample if we have one to save CPU, otherwise from the image.
        if (file_exists($this->tempfile_sample_path()))
            list($paramsath, $ext) = array($this->tempfile_sample_path(), "jpg");
        elseif (file_exists($this->sample_path()))
            list($paramsath, $ext) = array($this->sample_path(), "jpg");
        elseif (file_exists($this->tempfile_path))
            list($paramsath, $ext) = array($this->tempfile_path, $this->file_ext);
        elseif (file_exists($this->file_path()))
            list($paramsath, $ext) = array($this->file_path(), $this->file_ext);
        else
            return false;
        
        try {
            Moebooru_Resizer::resize($ext, $paramsath, $this->tempfile_preview_path(), $size, 85);
        } catch (Exception $e) {
            $this->errors()->add("preview", "couldn't be generated (".$e->getMessage().")");
            return false;
        }
        
        $this->actual_preview_width = $this->raw_preview_dimensions()[0];
        $this->actual_preview_height = $this->raw_preview_dimensions()[1];
        $this->preview_width = $this->preview_dimensions()[0];
        $this->preview_height = $this->preview_dimensions()[1];
        
        return true;
    }

    # If the JPEG version needs to be generated (or regenerated), output it to tempfile_jpeg_path.    On
    # error, return; false; on success or no-op, return; true.
    protected function generate_jpeg($force_regen = false)
    {
        if ($this->gif() || !$this->image()) return true;
        elseif (!CONFIG()->jpeg_enable) return true;
        elseif (!$this->width && !$this->height) return true;
        
        # Only generate JPEGs for PNGs.    Don't do it for files that are already JPEGs; we'll just add
        # artifacts and/or make the file bigger.    Don't do it for GIFs; they're usually animated.
        if ($this->file_ext != "png") return true;

        # We can generate the image during upload or offline.    Use tempfile_path
        #- if it exists, otherwise use file_path.
        $paramsath = $this->tempfile_path;
        // path = file_path unless File.exists?(path)
        // unless File.exists?(path)
            // record_errors.add(:file, "not found")
            // return false
        // end
        
        # If we already have the image, don't regenerate it.
        if (!$force_regen && ctype_digit((string)$this->jpeg_width))
            return true;
        
        $size = Moebooru_Resizer::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => CONFIG()->jpeg_width, 'height' => CONFIG()->jpeg_height), CONFIG()->jpeg_ratio);
        try {
            Moebooru_Resizer::resize($this->file_ext, $paramsath, $this->tempfile_jpeg_path(), $size, CONFIG()->jpeg_quality['max']);
        } catch (Moebooru_Resizer_Error $e) {
            $this->errors()->add("jpeg", "couldn't be created: {$e->getMessage()}");
            return false;
        }
        
        $this->jpeg_width = $size['width'];
        $this->jpeg_height = $size['height'];
        $this->jpeg_size = filesize($this->tempfile_jpeg_path());
        
        # iTODO: enable crc32 for jpg.
        $crc32_accum = 0;

        return true;
    }

    # Returns true if the post has a sample image.
    public function has_sample()
    {
        return !empty($this->sample_size);
    }

    # Returns true if the post has a sample image, and we're going to use it.
    public function use_sample($user = null)
    {
        if (!$user)
            $user = current_user();
        
        if ($user && !$user->show_samples)
            return false;
        else
            return CONFIG()->image_samples && $this->has_sample();
    }

    public function get_file_image($user = null)
    {
        return array(
            'url'    => $this->file_url,
            'ext'    => $this->file_ext,
            'size'   => $this->file_size,
            'width'  => $this->width,
            'height' => $this->height
        );
    }
    
    public function get_file_jpeg($user = null)
    {
        if ($this->status == "deleted" or !$this->use_jpeg($user))
            return $this->get_file_image($user);

        return array(
            'url'    => $this->jpeg_url(),
            'size'   => $this->jpeg_size,
            'ext'    => "jpg",
            'width'  => $this->jpeg_width,
            'height' => $this->jpeg_height
        );
    }
    
    public function get_file_sample($user = null)
    {
        if ($this->status == "deleted" or !$this->use_sample($user))
            return $this->get_file_jpeg($user);
        
        return array(
            'url'    => $this->sample_url(),
            'size'   => $this->sample_size,
            'ext'    => "jpg",
            'width'  => $this->sample_width,
            'height' => $this->sample_height
        );
    }

    public function sample_url()
    {
        if ($this->_fake_samples_for_browse()) {
            return $this->fake_sample_url();
        }
    
        if (!$this->has_sample())
            return $this->jpeg_url();
        
        if (CONFIG()->use_pretty_image_urls)
            $paramsath = "/sample/{$this->md5}/".$this->pretty_file_name(array('type' => 'sample')).'.jpg';
        else
            $paramsath = "/data/sample/" . CONFIG()->sample_filename_prefix . $this->md5.'.jpg';

        return CONFIG()->url_base . $paramsath;
    }

    public function get_sample_width($user = null)
    {
        $this->get_file_sample($user)['width'];
    }
    
    public function get_sample_height($user = null)
    {
        $this->get_file_sample($user)['height'];
    }
    
    public function has_jpeg()
    {
        return $this->jpeg_size;
    }
    
    public function use_jpeg($user = null)
    {
        return CONFIG()->jpeg_enable && $this->has_jpeg();
    }
    
    public function jpeg_url()
    {
        if (!$this->has_jpeg())
            return $this->file_url;
        
        if (CONFIG()->use_pretty_image_urls)
            $paramsath = "/jpeg/{$this->md5}/".$this->pretty_file_name(array('type' => 'jpeg')).'.jpg';
        else
            $paramsath = "/data/jpeg/{$this->md5}.jpg";
        
        return CONFIG()->url_base . $paramsath;
    }
}