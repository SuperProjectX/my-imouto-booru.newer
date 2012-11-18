<?php
$this->xml->instruct();
$this->xml->posts(['count' => $this->posts->total_entries(), 'offset' => ($this->posts->current_page() - 1) * $this->posts->per_page()], function() {
    foreach ($this->posts as $post) {
        $post->to_xml(['builder' => $this->xml, 'skip_instruct' => true]);
    }
});