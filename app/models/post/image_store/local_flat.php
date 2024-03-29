<?php
class Post_ImageStore_LocalFlat extends Post_ImageStore_Base
{
    public function file_path()
    {
        return RAILS_ROOT . "/public/data/" . $this->_post->file_name();
    }

    public function file_url()
    {
        if (CONFIG()->use_pretty_image_urls)
            return CONFIG()->url_base . "/image/".$this->_post->md5."/".urlencode($this->_post->pretty_file_name()).".".$this->_post->file_ext;
        else
            return CONFIG()->url_base . "/data/".$this->_post->file_name();
    }
    
    public function preview_path()
    {
        if ($this->_post->image())
            return RAILS_ROOT . "/public/data/preview/".$this->_post->md5.".jpg";
        else
            return RAILS_ROOT . "/public/download-preview.png";
    }

    public function sample_path()
    {
        return RAILS_ROOT . "/public/data/sample/" . CONFIG()->sample_filename_prefix . $this->_post->md5 . ".jpg";
    }

    public function preview_url()
    {
        if ($this->_post->status == "deleted")
            return CONFIG()->url_base . "/deleted-preview.png";
        elseif ($this->_post->image())
            return CONFIG()->url_base . "/data/preview/".$this->_post->md5.".jpg";
        else
            return CONFIG()->url_base . "/download-preview.png";
    }

    public function jpeg_path()
    {
        return RAILS_ROOT . "/public/data/jpeg/".$this->_file_hierarchy()."/".$this->_post->md5.".jpg";
    }

    public function store_jpeg_url()
    {
         if (CONFIG()->use_pretty_image_urls) {
            return CONFIG()->url_base . "/jpeg/".$this->_post->md5."/".urlencode($this->_post->pretty_file_name(array('type' => 'jpeg'))).".jpg";
        } else {
            return CONFIG()->url_base . "/data/jpeg/".$this->_post->md5.".jpg";
        }
    }

    public function store_sample_url()
    {
         if (CONFIG()->use_pretty_image_urls) {
            $path = "/sample/".$this->_post->md5."/".urlencode($this->_post->pretty_file_name(array('type' => 'sample'))).".jpg";
        } else {
            $path = "/data/sample/" . CONFIG()->sample_filename_prefix . $this->_post->md5.".jpg";
        }

        return CONFIG()->url_base . $path;
    }
}