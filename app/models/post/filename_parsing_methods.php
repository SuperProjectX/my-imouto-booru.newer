<?php
trait PostFilenameParsingMethods
{
    private function _get_tags_from_filename()
    {
        if ($tags = $this->_parse_filename_tags()) {
            if ($this->tags)
                $tags = implode(' ', array_unique(array_filter(array_merge(explode(' ', $this->tags), explode(' ', $tags)))));
            $this->tags = $tags;
        }
    }
    
    private function _get_source_from_filename()
    {
        if ($source = $this->_parse_filename_source($this->tempfile_name))
            $this->source = $source;
    }
    
    /**
     * Function to get tags based on filename.
     * Must return a string.
     */
    private function _parse_filename_tags()
    {
        if (!preg_match("/^(?:yande\.re|moe) \d+ (.*)$/", $this->tempfile_name, $m))
            return;
        
        foreach (explode(' ', $m[1]) as $tag)
            $tags[] = $tag;
        
        $tags = implode(' ', array_filter(array_unique($tags)));
        return $tags;
    }

    /**
     * Function to get source based on filename.
     * Must return a string.
     */
    private function _parse_filename_source()
    {
        if (preg_match("/^(?:moe|yande\.re) (\d+) /", $this->tempfile_name, $m))
            return 'https://yande.re/post/show/'.$m[1];
    } 
}