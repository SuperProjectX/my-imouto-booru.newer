<?php
class NoteController extends ApplicationController
{
    // layout 'default', 'only' => [:index, :history, :search]
    // helper :post
    protected function _filters()
    {
        return [
            'before' => [
                ['post_member_only', 'only' => ['destroy', 'update', 'revert']]
            ]
        ];
    }
    
    public function search()
    {
         if ($this->params()->query) {
            $query = '%' . implode('%', array_filter(explode(' ', $this->params()->query))) . '%';
            $this->notes = Note::paginate(['order' => "id asc", 'per_page' => 25, 'conditions' => ["body LIKE ?", $query], 'page' => $this->page_number()]);
            $this->_respond_to_list("notes");
        } else
            $this->notes = new ActiveRecord_Collection();
    }

    public function index()
    {
        $this->_set_title('Notes');
        
        if ($this->params()->post_id) {
            $this->posts = Post::paginate(['order' => "last_noted_at DESC", 'conditions' => ["id = ?", $this->params()->post_id], 'per_page' => 100, 'page' => $this->page_number()]);
        } else {
            $this->posts = Post::paginate(['order' => "last_noted_at DESC", 'conditions' => "last_noted_at IS NOT NULL", 'per_page' => 16, 'page' => $this->page_number()]);
        }
        # iTODO:
        $this->_respond_to([
            'html',
            'xml' => function() {
                // {render :xml => @posts.map {|x| x.notes}.flatten.to_xml(:root => "notes")}
            },
            'json' => function() {
                 // {render :json => @posts.map {|x| x.notes}.flatten.to_json}
            }
        ]);
    }

    // public function history()
    // {
        // $this->_set_title('Note History');
        
        // if ($this->params()->id) {
            // $this->notes = NoteVersion.paginate('page' => page_number, 'per_page' => 25, 'order' => "id DESC", 'conditions' => ["note_id = ?", $(int)this->params()->id])
        // } elseif ($this->params()->post_id) {
            // $this->notes = NoteVersion.paginate('page' => page_number, 'per_page' => 50, 'order' => "id DESC", 'conditions' => ["post_id = ?", $(int)this->params()->post_id])
        // } elseif ($this->params()->user_id) {
            // $this->notes = NoteVersion.paginate('page' => page_number, 'per_page' => 50, 'order' => "id DESC", 'conditions' => ["user_id = ?", $(int)this->params()->user_id])
        // } else {
            // $this->notes = NoteVersion.paginate('page' => page_number, 'per_page' => 25, 'order' => "id DESC")
        // }

        // respond_to_list("notes")
    // }

    // public function revert()
    // {
        // $note = Note::find($this->params()->id);

        // if ($note->is_locked) {
            // $this->_respond_to_error("Post is locked", ['#history', 'id' => $note->id], 'status' => 422);
            // return;
        // }

        // $note->revert_to($this->params()->version):
        // $note->ip_addr = $this->request()->remote_ip():
        // $note->user_id = current_user()->id:

        // if ($note->save()) {
            // $this->_respond_to_success("Note reverted", ['#history', 'id' => $note->id]);
        // } else {
            // $this->_render_error($note);
        // }
    // }

    public function update()
    {
        if (isset($this->params()->note['post_id'])) {
            $note = new Note(['post_id' => $this->params()->note['post_id']]);
        } else {
            $note = Note::find($this->params()->id);
        }

         if ($note->is_locked) {
            $this->_respond_to_error("Post is locked", array('post#show', 'id' => $note->post_id), ['status' => 422]);
            return;
        }

        $note->add_attributes($this->params()->note);
        $note->user_id = current_user()->id;
        $note->ip_addr = $this->request()->remote_ip();
        # iTODO:
        if ($note->save()) {
            $this->_respond_to_success("Note updated", '#index', ['api' => ['new_id' => $note->id, 'old_id' => (int)$this->params()->id, 'formatted_body' => $note->formatted_body()]]);
            // ActionController::Base.helpers.sanitize(note.formatted_body)]]);
        } else {
            $this->_respond_to_error($note, ['post#show', 'id' => $note->post_id]);
        }
    }
}