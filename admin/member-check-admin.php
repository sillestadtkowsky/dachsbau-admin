<?php

/*
* ###############################
* ADD Admin Menu
* ###############################
*/
function member_checker_creator()
{
  add_menu_page('Mitglieder', 'Mitglieder-Admin', 'manage_options', 'member-checker-menu', 'member_checker_home', 'dashicons-list-view', 5);
}
add_action('admin_menu', 'member_checker_creator');

/* 
* ####################
* ADD Admin Home
* ####################
*/
function member_checker_home()
{
  ?>
<div class="wrap">
  <h1>Mitgliederübersicht</h1>
</div>

<?php
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  $myListTable = new MembersTable();
  echo '<div class="wrap">';

  $requestPage = sanitize_text_field($_REQUEST["page"]);
  $html = '';
  $html .=  '<form id="events-filter" method="get"><input type="hidden" name="page" value="' . sanitize_text_field($requestPage) . '" />';
  $myListTable->prepare_items(); 
  echo '<form method="post">
   <input type="hidden" name="page" value="wp_list_table_class" />';
  $myListTable->search_box('Finden', 'search');
  echo '</form><h3>Mitgliederliste</h3>';
  
  $myListTable->display(); 
  $html .= '</form></div></div>'; 

  echo $html;
}