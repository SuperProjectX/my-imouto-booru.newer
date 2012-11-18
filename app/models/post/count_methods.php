<?php
trait PostCountMethods
{
    static public function fast_count($tags = null)
    {
        # A small sanitation
        $tags = preg_replace('/ +/', ' ', trim($tags));
        
        # No cache. This query is too slow, if no tags, just return row_count().
        if (!$tags) {
            return self::row_count();
        }
        
        // cache_version = Rails.cache.read("$cache_version").to_i
        # Use base64 encoding of tags query for memcache key
        // tags_base64 = Base64.urlsafe_encode64(tags)
        // key = "post-count/v=#{cache_version}/#{tags_base64}"

        // count = Rails.cache.fetch(key) {
        // Post.count_by_sql(Post.generate_sql(tags, :count => true))
        // }.to_i
        // list($sql, $params) = Post::generate_sql($tags, array('count' => true));
        $params = Post::generate_sql($tags, array('count' => true));
        return Post::count_by_sql($params);
    }

    static public function row_count()
    {
        $sql = "SELECT COUNT(*) FROM posts WHERE status <> 'deleted' AND NOT is_held AND status <> 'pending' AND is_shown_in_index";
        return current(self::connection()->query($sql)->fetch());
    }
    
    public function increment_count()
    {
        self::execute_sql("UPDATE table_data SET row_count = row_count + 1 WHERE name = 'posts'");
    }

    public function decrement_count()
    {
        self::execute_sql("UPDATE table_data SET row_count = row_count - 1 WHERE name = 'posts'");
    }
}