<?php
class AvatarHelper extends ActionView_Helper
{
    # id is an identifier for the object referencing this avatar; it's passed down
    # to the javascripts to implement blacklisting "click again to open".
    public function avatar(User $user, $id, array $html_options = array())
    {
        static $shown_avatars = array();
        static $posts_to_send = array();

        #if not @shown_avatars[user] then
            $shown_avatars[$user->id] = true;
            $posts_to_send[] = $user->avatar_post;
            $img = $this->image_tag($user->avatar_url() . "?" . strtotime($user->avatar_timestamp),
                                        array_merge(array('class' => "avatar", 'width' => $user->avatar_width, 'height' => $user->avatar_height), $html_options));
            return $this->link_to($img,
                          array("post#show", 'id' => $user->avatar_post->id),
                          array('class' => "ca" . $user->avatar_post->id,
                          'onclick' => "Post.check_avatar_blacklist(".$user->avatar_post->id.", ".$id.")"));
        #end
    }
    
    public function avatar_init(Post $post = null)
    {
        static $posts = array();
        
        if ($post) {
            $posts[(string)$post->id] = $post;
        } else {
            if (!$posts)
                return '';
            $ret = '';
            foreach ($posts as $post)
                $ret .= 'Post.register('.$post->to_json().')';
            $ret .= 'Post.init_blacklisted()';
            return $ret;
        }
    }
}