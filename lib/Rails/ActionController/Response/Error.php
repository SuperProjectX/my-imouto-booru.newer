<?php
/**
 * This class also logs the errors.
 */
class ActionController_Response_Error extends ActionController_Response_Base
{
    private
        $_e,
        
        $_buffer = '',
        
        $_report;
    
    public function __construct(Exception $e, array $params)
    {
        $this->_params = $params;
        $this->_e = $e;
    }
    
    public function _render_view()
    {
        $buffer = '';
        $this->_create_report();
        
        if (Rails::application()->config('app', 'action_on_error') == Rails_Application::CONFIG_ERROR_SHOW_STACKTRACE) {
            $buffer  = $this->_header();
            $buffer .= $this->_report;
            $buffer .= $this->_footer();
        } else {
            $file = RAILS_ROOT . '/public/' . $this->_params['status'] . '.html';
            if (is_file($file)) {
                $buffer = file_get_contents($file);
            }
        }
        $this->_buffer = $buffer;
        $this->_log_report();
    }
    
    public function _print_view()
    {
        return $this->_buffer;
    }
    
    private function _header()
    {
$h = <<<HEREDOC
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Exception caught</title>
  <style>
    body { background-color: #fff; color: #333; }

    body, p, ol, ul, td {
      font-family: helvetica, verdana, arial, sans-serif;
      font-size:   13px;
      line-height: 18px;
    }

    pre {
      background-color: #eee;
      padding: 10px;
      font-size: 11px;
      overflow: auto;
    }
    
    pre.error_info {
        max-height:400px;
    }

    a { color: #000; }
    a:visited { color: #666; }
    a:hover { color: #fff; background-color:#000; }
  </style>
</head>
<body>
HEREDOC;
        return $h;
    }
    
    private function _footer()
    {
        return "</body>\n</html>";
    }
    
    private function _create_report()
    {
        $e = $this->_e;
        $params = $this->_params;
        $html = '';
        
        # Build HTML here.
        if ($e instanceof Rails_Exception && $e->title())
            $error_name = $e->title();
        else
            $error_name = get_class($e) . ' raised';
        
        $html = '';
        $html .= '<h1>'.$error_name.'</h1>'."\n";
        
        if ($e instanceof ActiveRecord_Exception)
            $html .= '<pre class="error_info">'.$e->get_message();
        else
            $html .= '<pre class="error_info">'.$e->getMessage();
        
        if (!empty($this->_params['skip_all_info'])) {
            $html .= "</pre>\n";
        } else {
            $base_dir = RAILS_ROOT . DIRECTORY_SEPARATOR;
            
            $file = $line = null;
            
            if (Rails::raised_exception()) {
                $tr = $e->getTrace();
                array_shift($tr);
                
                $trace = '';
                $i = 0;
                $base_dir_len = strlen($base_dir);
                foreach ($tr as $t) {
                    $trace .= '#' . $i . ' ';
                    if (isset($t['file'])) {
                        $t_file = substr($t['file'], $base_dir_len);
                        
                        if (!$i) {
                            $file = $t_file;
                            $line = $t['line'];
                        }
                        $trace .= $t_file . '('.$t['line'].'): ';
                    } else
                        $trace .= '[internal function]: ';
                     
                    if (!empty($t['class']))
                        $trace .= $t['class'] . $t['type'];
                    
                    $args = array();
                    if (isset($t['args'])) {
                        foreach ($t['args'] as $arg) {
                            !is_scalar($arg) && $arg = ucfirst(gettype($arg));
                            $args[] = strlen($arg) > 15 ? substr($arg, 0, 15) . '...' : $arg;
                        }
                    }
                    $args = implode(', ', $args);
                    
                    $trace .= $t['function'] . '(' . $args . ')';
                    $trace .= "\n";
                    $i++;
                }
                $trace .= "#$i {main}";
            } else {
                // $trace = str_replace("\n", "<br />\n", $e->getTraceAsString());
                $trace = $e->getTraceAsString();
                $trace = str_replace($base_dir, '', $trace);
                $file  = substr($e->getFile(), strlen($base_dir));
                $line  = $e->getLine();
            }
            
            if ($file && $line)
                $html .= "\n" . $file.' ('.$line.")</pre>";
            else
                $html .= '</pre>';
            $html .= "\n";
            $html .= $this->_removable_lines();
            $html .= '<pre>' . $trace . '</pre>';
        }
        
        $this->_report = $html;
    }
    
    private function _log_report()
    {
        if (empty($this->_params['php_error']) || Rails::application()->config('app', 'log_php_errors')) {
            $log = strip_tags(str_replace($this->_removable_lines(), '', $this->_report));
            Rails::log_error($log, false);
        }
    }
    
    # The following lines are removed in _log_report().
    private function _removable_lines()
    {
        $lines  = 'RAILS_ROOT: ' . RAILS_ROOT . "\n";
        $lines .= '<h3>Trace</h3>'."\n";
        return $lines;
    }
}