<?php
class AdminController extends ApplicationController
{
    use AdminController_Trait;
    
    final public function index()
    {
        
    }
    
    final public function gen_table_data()
    {
        $connection = Rails::application()->config('activerecord', 'connection');
        if (!preg_match('/dbname=(\w+)/', $connection, $m)) {
            Rails::raise('Exception', "Couldn't determine database name");
        }
        
        $dbname = $m[1];
        
        $tables = ActiveRecord::select_values(sprintf('SHOW TABLES FROM `%s`', $dbname));
        
        if (!$tables) {
            $this->_to_index('Warning: Couldn\'t retrieve table information.');
            return;
        }

        $db_tables_path = RAILS_ROOT . '/db';
        !is_dir($db_tables_path) && mkdir($db_tables_path);
        $db_tables_path .= '/tables';
        !is_dir($db_tables_path) && mkdir($db_tables_path);

        foreach (glob($db_tables_path . '/*') as $file)
            unlink($file);

        foreach ($tables as $table) {
            $data = ActiveRecord::select("DESCRIBE ".$table);
            
            $table_data = $table_indexes = $pri = $uni = array();
            
            foreach ($data as $d) {
                $table_data[$d['Field']] = array(
                    'type'    => $d['Type']
                );
            }
            
            $idxs = ActiveRecord::select("SHOW INDEX FROM ".$table);
            
            if ($idxs) {
                foreach ($idxs as $idx) {
                    if ($idx['Key_name'] == 'PRIMARY') {
                        $pri[] = $idx['Column_name'];
                    } elseif ($idx['Non_unique'] === '0') {
                        $uni[] = $idx['Column_name'];
                    }
                }
            }
            
            if ($pri)
                $table_indexes['PRI'] = $pri;
            elseif ($uni)
                $table_indexes['UNI'] = $uni;
            
            $contents = '<?php
$this->_columns = ' . var_export($table_data, true) . ';
$this->_indexes = ' . var_export($table_indexes, true) . ';';
            
            file_put_contents($db_tables_path . '/' . $table . '.php', $contents);
        }

        // Keys: null, UNI, MUL, PRI

        $this->_to_index('Database tables file updated');
    }
    
    final protected function _to_index($notice)
    {
        $url = '/' . Rails::application()->config('app', 'rails_admin_url');
        parent::_redirect_to(array($url, 'notice' => $notice));
    }
    
    protected function _redirect_to($redirect_params, array $params = array())
    {
        $redirect_params[0] = '/' . Rails::application()->config('app', 'rails_admin_url') . '/' . $redirect_params[0];
        parent::_redirect_to($redirect_params, $params);
    }
}