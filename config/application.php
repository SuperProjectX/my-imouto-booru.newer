<?php
require dirname(__FILE__) . '/default_config.php';

class MoeBooru extends Rails_Application
{
    private $_moe_config;
    
    public function moe_config()
    {
        return $this->_moe_config;
    }
    
    protected function _init()
    {
        $this->_moe_config = new MoeBooru_Config();
    }
    
    protected function _initial_config()
    {
        return [
            'app' => [
                'encoding' => 'utf-8',
                
                'load_files' => [
                    '/lib/app_functions.php',
                    '/lib/Moebooru/Resizer.php',
                    '/lib/dtext.php'
                ],
                
                'rails_admin_url' => 'sysadmin',
                'rails_admin_ips' => [
                    '127.0.0.1'
                ],
                
                'environment' => 'development'
            ],
            
            'activerecord' => [
                'connection'=> 'mysql:host=localhost;dbname=myimouto;',
                'username'  => 'root',
                'password'  => 'xamPP',
                
                'table_schema_from_files' => true,
                
                'load_models' => [
                    'Post',
                    'User',
                    'Pool',
                    'PoolPost',
                    'ForumPost',
                    'Comment'
                ]
            ],
            
            'actionview' => [
                'layout' => 'default'
            ]
        ];
    }
}