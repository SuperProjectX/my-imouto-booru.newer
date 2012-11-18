<h2>System menu</h2>
<ul>
  <li><?php echo $this->link_to('Generate database tables files', '#gen_table_data') ?></li>
  <li><?php echo $this->link_to('Scripts', '#scripts') ?></li>
</ul>

<?php echo $this->link_to('Back to '.Rails::application_name(), 'root') ?>