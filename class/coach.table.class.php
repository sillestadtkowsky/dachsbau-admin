<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/template.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
    require_once ABSPATH . 'wp-admin/includes/screen.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class COACH_List_Table extends WP_List_Table {

    public function prepare_items() {
        $data         = $this->wp_list_table_data();
        $per_page     = 8;
        $current_page = $this->get_pagenum();
        $total_items  = count( $data );
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
            )
        );

        // $this->items           = $data;
        $this->items           = array_slice(
            $data,
            ( ( $current_page - 1 ) * $per_page ),
            $per_page
        );
        $columns               = $this->get_columns();
        $hidden                = $this->get_hidden_columns();
        $this->_column_headers = array( $columns, $hidden );
    }

    public function wp_list_table_data() {
        $data = array(
            array(
                'id'    => 1,
                'name'  => 'Dilan',
                'email' => 'deverleighj@pen.io',
            ),
            array(
                'id'    => 2,
                'name'  => 'Tanner',
                'email' => 'tfleischeri@a8.net',
            ),
            array(
                'id'    => 3,
                'name'  => 'Darrell',
                'email' => 'dspurh@pen.io',
            ),
            array(
                'id'    => 4,
                'name'  => 'Dudley',
                'email' => 'droistoneg@umn.edu',
            ),
            array(
                'id'    => 5,
                'name'  => 'Merrili',
                'email' => 'mnutbeanf@tmall.com',
            ),
            array(
                'id'    => 6,
                'name'  => 'Brana',
                'email' => 'bcasonee@craigslist.org',
            ),
            array(
                'id'    => 7,
                'name'  => 'Susannah',
                'email' => 'sgolsbyd@netvibes.com',
            ),
            array(
                'id'    => 8,
                'name'  => 'Darcey',
                'email' => 'dpithiec@cmu.edu',
            ),
            array(
                'id'    => 9,
                'name'  => 'Sofie',
                'email' => 'sbroskeb@ca.gov',
            ),
            array(
                'id'    => 10,
                'name'  => 'Joscelin',
                'email' => 'jwestripa@php.net',
            ),
            array(
                'id'    => 11,
                'name'  => 'Kalila',
                'email' => 'kmacavaddy9@xing.com',
            ),
            array(
                'id'    => 12,
                'name'  => 'Marie-jeanne',
                'email' => 'mcocci8@1und1.de',
            ),
            array(
                'id'    => 13,
                'name'  => 'Darnell',
                'email' => 'dlamborne7@linkedin.com',
            ),
            array(
                'id'    => 14,
                'name'  => 'Hugibert',
                'email' => 'hhelgass6@icio.us',
            ),
            array(
                'id'    => 15,
                'name'  => 'Anitra',
                'email' => 'alongforth5@cmu.edu',
            ),
            array(
                'id'    => 16,
                'name'  => 'Reinaldos',
                'email' => 'rburchett4@simplemachines.org',
            ),
            array(
                'id'    => 17,
                'name'  => 'Arlan',
                'email' => 'adelph3@homestead.com',
            ),
            array(
                'id'    => 18,
                'name'  => 'Harwell',
                'email' => 'hturbefield2@sciencedaily.com',
            ),
            array(
                'id'    => 19,
                'name'  => 'Mikey',
                'email' => 'mmoakes1@reuters.com',
            ),
            array(
                'id'    => 20,
                'name'  => 'Mira',
                'email' => 'mmaciocia0@newsvine.com',
            ),
        );

        return $data;
    }

    public function get_hidden_columns() {
        return array( 'id' );
    }

    public function get_columns() {
        $columns = array(
            'id'     => 'ID',
            'name'   => 'Name',
            'email'  => 'Email',
        );

        return $columns;
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'name':
            case 'email':
                return $item[ $column_name ];
            default:
                return 'N/A';
        }
    }
}

function display_bxft_table() {
    $bxft_table = new COACH_List_Table();
    $bxft_table->prepare_items();
    ?>
    <div class="wrap">
        <?php $bxft_table->display(); ?>
    </div>
    <?php
}

display_bxft_table();