<?php
trait PostVoteMethods
{
    public $voted_by = array();
    
    public static function static_recalculate_score($id = null)
    {
            $conds = $cond_params = array();

            $sql = "UPDATE posts AS p SET score = " .
                "(SELECT COALESCE(SUM(GREATEST(?, LEAST(?, score))), 0) FROM post_votes v WHERE v.post_id = p.id) ";
            $cond_params[] = CONFIG()->vote_sum_min;
            $cond_params[] = CONFIG()->vote_sum_max;

            if ($id) {
                $conds[] = "WHERE p.id = ?";
                $cond_params[] = $id;
            }

            $sql = implode(" ", array($sql, $conds));
            array_unshift($cond_params, $sql);
            self::execute_sql($cond_params);
    }

    public function recalculate_score()
    {
        $this->save();
        self::execute_sql(['UPDATE posts SET score = (SELECT COUNT(*) FROM post_votes WHERE post_id = :post_id AND score > 0) WHERE id = :post_id', 'post_id' => $this->id]);
        $this->reload();
    }

    public function vote($score, $user, array $options = array())
    {
        $score < CONFIG()->vote_record_min && $score = CONFIG()->vote_record_min;
        $score > CONFIG()->vote_record_max && $score = CONFIG()->vote_record_max;
        
        if ($user->is_anonymous)
            return false;
        
        $vote = PostVote::find_by_user_id_and_post_id(array($user->id, $this->id));
        
        if (!$vote) {
            $vote = PostVote::create(array('post_id' => $this->id, 'user_id' => $user->id, 'score' => $score));
        }
        
        $vote->update_attributes(array('score' => $score));
        
        $this->recalculate_score();
        
        return true;
    }
}