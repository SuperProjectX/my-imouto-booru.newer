<?php
# Admin
$this->match('admin(/index)', 'admin#index');
$this->match('admin/edit_user');
$this->match('admin/reset_password');

# Advertisements
/*
resources :advertisements do
    collection do
        post :update_multiple
    end
    member do
        get :redirect
    end
end
*/

# Artist
$this->match('artist(/index)(.:format)', 'artist#index');
$this->match('artist/create(.:format)');
$this->match('artist/destroy(.:format)(/:id)', 'artist#destroy');
$this->match('artist/preview');
$this->match('artist/show(/:id)', 'artist#show');
$this->match('artist/update(.:format)(/:id)', 'artist#update');

# Banned
$this->match('banned(/index)', 'banned#index');

# Batch
$this->match('batch(/index)', 'batch#index');
$this->match('batch/create');
$this->post('batch/enqueue');
$this->post('batch/update');

# Blocks
$this->post('blocks/block_ip');
$this->post('blocks/unblock_ip');

# Comment
$this->match('comment(/index)', 'comment#index');
$this->match('comment/edit(/:id)', 'comment#edit');
$this->match('comment/moderate');
$this->match('comment/search');
$this->match('comment/show(.:format)(/:id)', 'comment#show');
$this->match('comment/destroy(.:format)(/:id)', 'comment#destroy', ['via' => ['post', 'delete']]);
$this->match('comment/update(/:id)', 'comment#update', ['via' => ['post', 'put']]);
$this->post('comment/create(.:format)');
$this->post('comment/mark_as_spam(/:id)', 'comment#mark_as_spam');

# Dmail
$this->match('dmail(/inbox)', 'dmail#inbox');
$this->match('dmail/compose');
$this->match('dmail/preview');
$this->match('dmail/show(/:id)', 'dmail#show');
$this->match('dmail/show_previous_messages');
$this->post('dmail/create');
$this->get('dmail/mark_all_read', 'dmail#confirm_mark_all_read');
$this->post('dmail/mark_all_read');

# Favorite
$this->match('favorite/list_users(.:format)');

# Forum
$this->match('forum(/index)(.:format)', 'forum#index');
$this->match('forum/preview');
$this->match('forum/new', 'forum#blank', ['as' => 'new']); // $this->match('forum/new');
$this->match('forum/add');
$this->match('forum/edit(/:id)', 'forum#edit');
$this->match('forum/show(/:id)', 'forum#show');
$this->match('forum/search');
$this->match('forum/mark_all_read');
$this->match('forum/lock', ['via' => ['post', 'put']]);
$this->match('forum/stick(/:id)', 'forum#stick', ['via' => ['post', 'put']]);
$this->match('forum/unlock(/:id)', 'forum#unlock', ['via' => ['post', 'put']]);
$this->match('forum/unstick(/:id)', 'forum#unstick', ['via' => ['post', 'put']]);
$this->match('forum/update(/:id)', 'forum#update', ['via' => ['post', 'put']]);
$this->match('forum/destroy(/:id)', 'forum#destroy', ['via' => ['post', 'delete']]);
$this->post('forum/create');

# Help
$this->match('help(/index)', 'help#index');
$this->match('help/:action', 'help#:action');

# History
$this->match('history(/index)', 'history#index');
$this->post('history/undo');

# Inline
$this->match('inline(/index)', 'inline#index');
$this->match('inline/add_image(/:id)', 'inline#add_image');
$this->match('inline/create');
$this->match('inline/crop(/:id)', 'inline#crop');
$this->match('inline/edit(/:id)', 'inline#edit');
$this->match('inline/copy(/:id)', 'inline#copy', ['via' => ['post', 'put']]);
$this->match('inline/update(/:id)', 'inline#update', ['via' => ['post', 'put']]);
$this->match('inline/delete(/:id)', 'inline#delete', ['via' => ['post', 'delete']]);
$this->match('inline/delete_image(/:id)', 'inline#delete_image', ['via' => ['post', 'delete']]);

# JobTask
$this->match('job_task(/index)', 'job_task#index');
$this->match('job_task/destroy(/:id)', 'job_task#destroy');
$this->match('job_task/restart(/:id)', 'job_task#restart');
$this->match('job_task/show(/:id)', 'job_task#show');

# Note
$this->match('note(/index)(.:format)', 'note#index');
$this->match('note/history(.:format)(/:id)', 'note#history');
$this->match('note/search(.:format)');
$this->match('note/revert(.:format)(/:id)', 'note#revert', ['via' => ['post', 'put']]);
$this->match('note/update(.:format)(/:id)', 'note#update', ['via' => ['post', 'put']]);

# Pool
$this->match('pool(/index)(.:format)', 'pool#index');
$this->match('pool/add_post(.:format)', 'pool#add_post');
$this->match('pool/copy(/:id)', 'pool#copy');
$this->match('pool/create(.:format)', 'pool#create');
$this->match('pool/destroy(.:format)(/:id)', 'pool#destroy');
$this->match('pool/import(/:id)', 'pool#import');
$this->match('pool/order(/:id)', 'pool#order');
$this->match('pool/remove_post(.:format)', 'pool#remove_post');
$this->match('pool/select');
$this->match('pool/show(.:format)(/:id)', 'pool#show');
$this->match('pool/transfer_metadata');
$this->match('pool/update(.:format)(/:id)', 'pool#update');
$this->match('pool/zip/:id/:filename', 'pool#zip', ['constraints' => ['filename' => '/.*/']]);

# Post
$this->match('post(/index)(.:format)', 'post#index');
$this->match('post/acknowledge_new_deleted_posts');
$this->match('post/activate');
$this->match('post/atom(.:format)', 'post#atom', ['format' => 'atom']);
$this->match('post/browse');
$this->match('post/delete(/:id)', 'post#delete');
$this->match('post/deleted_index');
$this->match('post/download');
$this->match('post/error');
$this->match('post/exception');
$this->match('post/histogram');
$this->match('post/moderate');
$this->match('post/piclens', ['format' => 'rss']);
$this->match('post/popular_by_day');
$this->match('post/popular_by_month');
$this->match('post/popular_by_week');
$this->match('post/popular_recent');
$this->match('post/random(/:id)', 'post#random');
$this->match('post/show(/:id)(/*tag_title)', 'post#show', ['constraints' => ['id' => '/\d+/'], 'format' => false]);
$this->match('post/similar(/:id)', 'post#similar');
$this->match('post/undelete(/:id)', 'post#undelete');
$this->match('post/update_batch');
$this->match('post/upload');
$this->match('post/upload_problem');
$this->match('post/view(/:id)', 'post#view');
$this->match('post/flag(/:id)', 'post#flag', ['via' => ['post', 'put']]);
$this->match('post/revert_tags(.:format)(/:id)', 'post#revert_tags', ['via' => ['post', 'put']]);
$this->match('post/update(.:format)(/:id)', 'post#update', ['via' => ['post', 'put']]);
$this->match('post/vote(.:format)(/:id)', 'post#vote', ['via' => ['post', 'put']]);
$this->match('post/destroy(.:format)(/:id)', 'post#destroy', ['via' => ['post', 'delete']]);
$this->post('post/create(.:format)', 'post#create');
$this->match('post/import'); # Pop.

$this->match('atom', 'post#atom', ['format' => 'atom']);
$this->match('download', 'post#download');
$this->match('histogram', 'post#histogram');

# PostTagHistory
$this->match('post_tag_history(/index)', 'post_tag_history#index');

# Report
$this->match('report/tag_updates');
$this->match('report/note_updates');
$this->match('report/wiki_updates');
$this->match('report/post_uploads');
$this->match('report/votes');
$this->match('report/set_dates');

# Static
$this->match('static/500');
$this->match('static/more');
$this->match('static/terms_of_service');
$this->match('/opensearch', 'static#opensearch');

# TagAlias
$this->match('tag_alias(/index)', 'tag_alias#index');
$this->match('tag_alias/update', ['via' => ['post', 'put']]);
$this->post('tag_alias/create');

# Tag
$this->match('tag(/index)(.:format)', 'tag#index');
$this->get('tag/autocomplete_name', ['as' => 'ac_tag_name']);
$this->match('tag/cloud');
$this->match('tag/edit(/:id)', 'tag#edit');
$this->match('tag/edit_preview');
$this->match('tag/mass_edit');
$this->match('tag/popular_by_day');
$this->match('tag/popular_by_month');
$this->match('tag/popular_by_week');
$this->match('tag/related(.:format)', 'tag#related');
$this->match('tag/show(/:id)', 'tag#show');
$this->match('tag/summary');
$this->match('tag/update(.:format)', 'tag#update');
$this->match('tag/fix_count'); # Pop.
$this->match('tag/delete'); # Pop.

# TagImplication
$this->match('tag_implication(/index)', 'tag_implication#index');
$this->match('tag_implication/update', ['via' => ['post', 'put']]);
$this->post('tag_implication/create');

# TagSubscription
$this->match('tag_subscription(/index)', 'tag_subscription#index');
$this->match('tag_subscription/create');
$this->match('tag_subscription/update');
$this->match('tag_subscription/destroy(/:id)', 'tag_subscription#destroy');

# User
$this->get('user/autocomplete_name', ['as' => 'ac_user_name']);
$this->match('user(/index)(.:format)', 'user#index');
$this->match('user/activate_user');
$this->match('user/block(/:id)', 'user#block');
$this->match('user/change_email');
$this->match('user/change_password');
$this->match('user/check');
$this->match('user/edit');
$this->match('user/error');
$this->match('user/home');
$this->match('user/invites');
$this->match('user/login');
$this->match('user/logout');
$this->match('user/remove_from_blacklist');
$this->match('user/resend_confirmation');
$this->match('user/reset_password');
$this->match('user/set_avatar(/:id)', 'user#set_avatar');
$this->match('user/show(/:id)', 'user#show');
$this->match('user/show_blocked_users');
$this->match('user/signup');
$this->match('user/unblock');
$this->match('user/authenticate', ['via' => ['post', 'put']]);
$this->match('user/modify_blacklist', ['via' => ['post', 'put']]);
$this->match('user/update', ['via' => ['post', 'put']]);
$this->post('user/create');
$this->post('user/remove_avatar/:id', 'user#remove_avatar');

# UserRecord
$this->match('user_record(/index)', 'user_record#index');
$this->match('user_record/create(/:id)', 'user_record#create');
$this->match('user_record/destroy(/:id)', 'user_record#destroy', ['via' => ['post', 'delete']]);

# Wiki
$this->match('wiki(/index)(.:format)', 'wiki#index');
$this->match('wiki/add');
$this->match('wiki/diff');
$this->match('wiki/edit');
$this->match('wiki/history(.:format)(/:id)', 'wiki#history');
$this->match('wiki/preview');
$this->match('wiki/recent_changes');
$this->match('wiki/rename');
$this->match('wiki/show(.:format)', 'wiki#show');
$this->match('wiki/lock(.:format)', 'wiki#lock', ['via' => ['post', 'put']]);
$this->match('wiki/revert(.:format)', 'wiki#revert', ['via' => ['post', 'put']]);
$this->match('wiki/unlock(.:format)', 'wiki#unlock', ['via' => ['post', 'put']]);
$this->match('wiki/update(.:format)', 'wiki#update', ['via' => ['post', 'put']]);
$this->match('wiki/destroy(.:format)', 'wiki#destroy', ['via' => ['post', 'delete']]);
$this->post('wiki/create(.:format)', 'wiki#create');

$this->root('static#index');