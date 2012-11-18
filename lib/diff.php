<?php
class DanbooruDiff
{
    const TAG_DEL = '<del>';
    const TAG_INS = '<ins>';
    const TAG_DEL_CLOSE = '</del>';
    const TAG_INS_CLOSE = '</ins>';
    const TAG_NEWLINE = "↲\n";
    const TAG_BREAK = "<br>\n";
    
    # Produce a formatted page that shows the difference between two versions of a page.
    static public function diff($old, $new)
    {
        # dark age utf8 regexp of 1.8
        $pattern = '(?:<.+?>)|(?:[0-9_A-Za-z\x80-\xff]+[\x09\x20]?)|(?:[ \t]+)|(?:\r?\n)|(?:.+?)';

        // $thisarr = preg_match($pattern, $old);
        // $otharr = $new.scan(pattern)

        // cbo = Diff::LCS::ContextDiffCallbacks.new
        // diffs = thisarr.diff(otharr, cbo)

        // escape_html = lambda {|str| str.gsub(/&/,'&amp;').gsub(/</,'&lt;').gsub(/>/,'&gt;')}

        // output = thisarr;
        // output.each { |q| q.replace(escape_html[q]) }

        // diffs.reverse_each do |hunk|
            // newchange = hunk.max{|a,b| a.old_position <=> b.old_position}
            // newstart = newchange.old_position
            // oldstart = hunk.min{|a,b| a.old_position <=> b.old_position}.old_position

            // if newchange.action == '+'
                // output.insert(newstart, TAG_INS_CLOSE)
            // end

            // hunk.reverse_each do |chg|
                // case chg.action
                // when '-'
                    // oldstart = chg.old_position
                    // output[chg.old_position] = TAG_NEWLINE if chg.old_element.match(/^\r?\n$/)
                // when '+'
                    // if chg.new_element.match(/^\r?\n$/)
                        // output.insert(chg.old_position, TAG_NEWLINE)
                    // else
                        // output.insert(chg.old_position, "#{escape_html[chg.new_element]}")
                    // end
                // end
            // end

            // if newchange.action == '+'
                // output.insert(newstart, TAG_INS)
            // end

            // if hunk[0].action == '-'
                // output.insert((newstart == oldstart || newchange.action != '+') ? newstart+1 : newstart, TAG_DEL_CLOSE)
                // output.insert(oldstart, TAG_DEL)
            // end
        // end

        // output.join.gsub(/\r?\n/, TAG_BREAK)
    }
}