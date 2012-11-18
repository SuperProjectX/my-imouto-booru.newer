<?php
class ActionView_Partial extends ActionView_Template
{
    public function render_content()
    {
        ob_start();
        $this->_init_render();
        return ob_get_clean();
    }
}