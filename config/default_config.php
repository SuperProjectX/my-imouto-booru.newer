<?php
/**
 * Instead of modifying the this file, custom
 * config can be set in the config.php file,
 * which will replace these default values.
 */
class MoeBooru_Config
{
    public $app_name    = 'my.imouto';
    public $server_host = 'localhost:3000';
    public $url_base    = 'http://localhost:3000';
    
    # The version of this MyImouto
    public $version = '0.1.9';
    
    # This is a salt used to make dictionary attacks on account passwords harder.
    public $user_password_salt = 'choujin-steiner';
    
    # Set to true to allow new account signups.
    public $enable_signups = true;
    
    # Newly created users start at this level. Set this to 30 if you want everyone
    # to start out as a privileged member.
    public $starting_level = 30;
    
    # What method to use to store images.
    # local_flat: Store every image in one directory.
    # local_hierarchy: Store every image in a hierarchical directory, based on the post's MD5 hash. On some file systems this may be faster.
    # local_flat_with_amazon_s3_backup: Store every image in a flat directory, but also save to an Amazon S3 account for backup.
    # amazon_s3: Save files to an Amazon S3 account.
    # remote_hierarchy: Some images will be stored on separate image servers using a hierarchical directory.
    public $image_store = 'local_hierarchy';
    
    # Set to true to enable downloading whole pools as ZIPs.
    public $pool_zips = false;
    
    # Enables image samples for large images.
    public $image_samples = true;
    
    # The maximum dimensions and JPEG quality of sample images.    This is applied
    # before sample_max/sample_min below.    If sample_width is nil, neither of these
    # will be applied and only sample_min/sample_max below will determine the sample
    # size.
    public $sample_width = null;
    public $sample_height = 1000; # Set to null if you never want to scale an image to fit on the screen vertically
    public $sample_quality = 92;
    
    # The greater dimension of sample images will be clamped to sample_min, and the smaller
    # to sample_min.    2000x1400 will clamp a landscape image to 2000x1400, or a portrait
    # image to 1400x2000.
    public $sample_max = 1500;
    public $sample_min = 1200;
    
    # The maximum dimensions of inline images for the forums and wiki.
    public $inline_sample_width = 800;
    public $inline_sample_height = 600;
    
    # Resample the image only if the image is larger than sample_ratio * sample_dimensions.
    # This is ignored for PNGs, so a JPEG sample is always created.
    public $sample_ratio = 1;
    
    # A prefix to prepend to sample files
    public $sample_filename_prefix = '';
    
    # Enables creating JPEGs for PNGs.
    public $jpeg_enable = true;
    
    # Scale JPEGs to fit in these dimensions.
    public $jpeg_width = 3500;
    public $jpeg_height = 3500;
    
    # Resample the image only if the image is larger than jpeg_ratio * jpeg_dimensions.    If
    # not, PNGs can still have a JPEG generated, but no resampling will be done.
    #
    # Moebooru is getting confusing. For now, the max value will be used as JPEG quality.
    public $jpeg_ratio = 1.25;
    public $jpeg_quality = array('min' => 94, 'max' => 97, 'filesize' => 4194304 /*1024*1024*4*/);
    
    # If enabled, URLs will be of the form:
    # http://host/image/00112233445566778899aabbccddeeff/12345 tag tag2 tag3.jpg
    #
    # This allows images to be saved with a useful filename, and hides the MD5 hierarchy (if
    # any).    This does not break old links; links to the old URLs are still valid.    This
    # requires URL rewriting (not redirection!) in your webserver.
    public $use_pretty_image_urls = true;
    
    # If use_pretty_image_urls is true, sets a prefix to prepend to all filenames.    This
    # is only present in the generated URL, and is useful to allow your downloaded files
    # to be distinguished from other sites; for example, "moe 12345 tags.jpg" vs.
    # "kc 54321 tags.jpg".
    public $download_filename_prefix = "myimouto";
    
    # Files over this size will always generate a sample, even if already within
    # the above dimensions.
    public $sample_always_generate_size = 524288; // 512*1024
    
    # After a post receives this many posts, new comments will no longer bump the post in comment/index.
    public $comment_threshold = 9999;
    
    # Members cannot post more than X posts in a day.
    public $member_post_limit = 16;
    
    # This sets the minimum and maximum value a user can record as a vote.
    public $vote_record_min = 0;
    public $vote_record_max = 3;
    
    # This allows posts to have parent-child relationships. However, this requires manually updating the post counts stored in table_data by periodically running the script/maintenance script.
    public $enable_parent_posts = true;
    
    # Show only the first page of post/index to visitors.
    public $show_only_first_page = false;
    
    # Defines the various user levels.
    public $user_levels = array (
        "Unactivated" => 0,
        "Blocked"     => 10,
        "Member"      => 20,
        "Privileged"  => 30,
        "Contributor" => 33,
        "Janitor"     => 35,
        "Mod"         => 40,
        "Admin"       => 50
    );
    
    # Defines the various tag types. You can also define shortcuts.
    public $tag_types = array(
        "General"   => 0,
        "general"   => 0,
        "Artist"    => 1,
        "artist"    => 1,
        "art"       => 1,
        "Copyright" => 3,
        "copyright" => 3,
        "copy"      => 3,
        "Character" => 4,
        "character" => 4,
        "char"      => 4,
        "Circle"    => 5,
        "circle"    => 5,
        "cir"       => 5,
        "Faults"    => 6,
        "faults"    => 6,
        "fault"     => 6,
        "flt"       => 6
    );
    
    # Tag type IDs to not list in recent tag summaries, such as on the side of post/index:
    public $exclude_from_tag_sidebar = array(0, 6);
    
    # Determine who can see a post.
    function can_see_post(User $user, Post $post) {
        # By default, no posts are hidden.
        return true;
        
        # Some examples:
        #
        # Hide post if user isn't privileged and post is not safe:
        # if($post->rating == 'e' && $user->is('>=20')) return true;
        # 
        # Hide post if user isn't a mod and post has the loli tag:
        # if($post->has_tag('loli') && $user->is('>=40')) return true;
    }
    
    # Determines who can see ads.
    function can_see_ads($user) {
        return $user->is_member_or_lower();
    }
    
    # Defines the default blacklists for new users.
    public $default_blacklists = array (
        "rating:q",
        "rating:e"
    );
    
    # Enable the artists interface.
    public $enable_artists = true;
    
    # Users cannot search for more than X regular tags at a time.
    public $tag_query_limit = 6;
    
    # Set this to true to hand off time consuming tasks (downloading files, resizing images, any sort of heavy calculation) to a separate process.
    # Do Not edit this, must always be false.
    public $enable_asynchronous_tasks = false;
    
    public $avatar_max_width = 125;
    public $avatar_max_height = 125;
    
    # The number of posts a privileged_or_lower can have pending at one time.    Any
    # further posts will be rejected.
    public $max_pending_images = null;
    
    # If set, posts by privileged_or_lower accounts below this size will be set to
    # pending.
    public $min_mpixels = null;
    
    # If true, pending posts act like hidden posts: they're hidden from the index unless
    # pending:all is used, and posts are bumped to the front of the index when they're
    # approved.
    public $hide_pending_posts = true;
    
    public $local_image_service = "localhost";
    
    public $dupe_check_on_upload = false;
    
    # Members cannot post more than X comments in an hour.
    public $member_comment_limit = 20;
    
    # (Next 2 arrays will be filled when including config/languages.php)
    public $language_names = array();
    
    # Languages that we're aware of.    This is what we show in "Secondary languages", to let users
    # select which languages they understand and that shouldn't be translated.
    public $known_languages = array();
    
    # Languages that we support translating to.    We'll translate each comment into all of these
    # languages.    Set this to array() to disable translation.
    public $translate_languages = array(); // array('en', 'ja', 'zh-CN', 'zh-TW', 'es'):
    
    public $available_locales = array('de', 'en', 'es', 'ja', 'ru', 'cn');
    
    public $admin_contact = 'http://pop-works.blogspot.com';
    
    /**
     * *******************************
     * MyImouto-specific configuration
     * *******************************
     */
    
    # Creates a fake sample_url for posts without a sample, so they can be zoomed-in in browse mode.
    # This is specifically useful if you're not creating image samples.
    public $fake_sample_url = true;
    
    # Allowed mime-types.
    # Don't edit this.
    public $allowed_mime_types = array(
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'application/x-shockwave-flash' => 'swf'
    );
    
    # Automatically add "gif" tag to GIF files.
    public $add_gif_tag_to_gif = true;
    
    # Automatically add "flash" tag to SWF files.
    public $add_flash_tag_to_swf = true;
    
    # Shows a checkbox in /post/delete to completely
    # delete a post in one pass.
    public $allow_destroy_completely = true;
    
    # Check the "destroy completely" checkbox by default.
    public $default_to_destroy_completely = false;
    
    # Default reason to delete posts (for quicker deletion).
    # Leave blank to force typing a reason.
    public $default_post_delete_reason = 'Default reason';
    
    # Enables manual tag deletion.
    public $enable_tag_deletion = true;
    
    # Enables manual tax fix count.
    public $enable_tag_fix_count = true;
    
    # Default rating for upload (e, q or s).
    public $default_rating_upload = 's';
    
    # Default rating for import (e, q or s).
    public $default_rating_import = 's';
    
    # Show homepage or redirect to /post otherwise.
    public $skip_homepage = false;
    
    # Show moe imoutos (post count) in homepage.
    public $show_homepage_imoutos = true;
    
    # Parse moe imouto filenames on post creation
    # These only work if filename is like "moe|yande.re 123 tag_1 tag_2".
    # You can modify how the filenames are parsed in the
    # app/models/post/filename_parsing_methods.php file, the
    # _parse_filename_tags and _parse_filename_source functions.
    
    # Take tags from filename.
    public $tags_from_filename = true;
    
    # Automatically create source for images.
    public $source_from_filename = true;
    
    # For /post tag left-sidebar, show tags of posts that
    # were posted N days ago.
    # Default: '1 day'.
    # This value will be passed to strtotime(). Check out
    # http://php.net/manual/en/function.strtotime.php
    # for more info.
    # The leading minus sign (-) will be added automatically
    # therefore must be omitted.
    public $post_index_tags_limit = '1 day';
    
    # Enable resizing image in /post/show by double-clicking on it.
    public $dblclick_resize_image = true;
    
    # Enable news bar on the top of the page.
    public $show_news_bar = true;
    
    public function __construct()
    {
        $custom_config = include RAILS_ROOT . '/config/config.php';
        foreach ($custom_config as $conf => $value) {
            $this->$conf = $value;
        }
    }
    
    public function __get($prop)
    {
        return null;
    }
}

function CONFIG()
{
    return Rails::application()->moe_config();
}