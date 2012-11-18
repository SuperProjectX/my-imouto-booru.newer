<?php
trait PostCacheMethods
{
    public function expire_cache()
    {
        # Have to call this twice in order to expire tags that may have been removed
        // Moebooru::CacheHelper.expire('tags' => old_cached_tags) if old_cached_tags
        // Moebooru::CacheHelper.expire('tags' => cached_tags)
    }
}