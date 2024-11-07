<?php

//namespace WPOVEN\LIST\TABLE;

//use WP_List_Table;

class WPOven_SMTP_Suresend_List_Table extends WP_List_Table
{
    private $table_data;

    // Define table columns
    function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'time'          => __('Time', 'WPoven'),
            'recipient'         => __('Recipient', 'WPoven'),
            'subject'   => __('Subject', 'WPoven'),
            // 'attachments'   => __('attachments', 'WPoven'),
            'headers'   => __('Headers', 'WPoven'),
            'status'        => __('Status', 'WPoven'),
            'action'        => __('', 'WPoven')
        );
        return $columns;
    }

    // Output the content of the "Actions" column
    protected function column_action($item)
    {
        $view = sprintf(
            '<a href="#" title="View Log" data-modal-id="modal-%s" class="button open-modal-btn" data-action="view" data-id="%s">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 10C9.10457 10 10 9.10457 10 8C10 6.89543 9.10457 6 8 6C6.89543 6 6 6.89543 6 8C6 9.10457 6.89543 10 8 10Z" fill="#50575E" fill-opacity="0.8"/>
                    <path d="M15.47 7.83C14.882 6.30882 13.861 4.99331 12.5334 4.04604C11.2058 3.09878 9.62977 2.56129 8.00003 2.5C6.37029 2.56129 4.79423 3.09878 3.46663 4.04604C2.13904 4.99331 1.11811 6.30882 0.530031 7.83C0.490315 7.93985 0.490315 8.06015 0.530031 8.17C1.11811 9.69118 2.13904 11.0067 3.46663 11.954C4.79423 12.9012 6.37029 13.4387 8.00003 13.5C9.62977 13.4387 11.2058 12.9012 12.5334 11.954C13.861 11.0067 14.882 9.69118 15.47 8.17C15.5098 8.06015 15.5098 7.93985 15.47 7.83ZM8.00003 11.25C7.35724 11.25 6.72889 11.0594 6.19443 10.7023C5.65997 10.3452 5.24341 9.83758 4.99742 9.24372C4.75144 8.64986 4.68708 7.99639 4.81248 7.36596C4.93788 6.73552 5.24741 6.15642 5.70193 5.7019C6.15646 5.24738 6.73555 4.93785 7.36599 4.81245C7.99643 4.68705 8.64989 4.75141 9.24375 4.99739C9.83761 5.24338 10.3452 5.65994 10.7023 6.1944C11.0594 6.72886 11.25 7.35721 11.25 8C11.2487 8.86155 10.9059 9.68743 10.2967 10.2966C9.68746 10.9058 8.86158 11.2487 8.00003 11.25Z" fill="currentColor"/>
                </svg>
            </a>&nbsp;&nbsp;',
            $item['id'],
            $item['id']
        );
        $resend = sprintf(
            '<button type="submit" title="Resend Mail" id="resend" name="resend" class="button" value="%s">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.39998 3.3079C2.39998 3.06708 2.49832 2.83613 2.67338 2.66585C2.84844 2.49557 3.08586 2.3999 3.33343 2.3999C3.581 2.3999 3.81843 2.49557 3.99348 2.66585C4.16854 2.83613 4.26688 3.06708 4.26688 3.3079V4.08998C5.18891 3.28835 6.35776 2.8061 7.59112 2.71844C8.82447 2.63078 10.053 2.94265 11.085 3.6054C12.117 4.26815 12.8945 5.24452 13.2962 6.38219C13.698 7.51987 13.7013 8.75488 13.3057 9.8946C12.9101 11.0343 12.1379 12.0146 11.1095 12.6826C10.0811 13.3506 8.85425 13.6688 7.62044 13.5874C6.38663 13.5061 5.21519 13.0298 4.28885 12.2329C3.36251 11.436 2.73337 10.3632 2.49954 9.18202C2.39002 8.61906 2.8779 8.15054 3.4666 8.15054C3.90844 8.15054 4.25817 8.50284 4.35152 8.92294C4.52107 9.68189 4.93619 10.368 5.53598 10.8807C6.13577 11.3933 6.88867 11.7055 7.68423 11.7715C8.47978 11.8374 9.27613 11.6537 9.95646 11.2471C10.6368 10.8406 11.1653 10.2327 11.4644 9.51259C11.7636 8.7925 11.8176 7.99811 11.6186 7.24595C11.4196 6.49379 10.9781 5.82345 10.3588 5.33327C9.73944 4.84308 8.97493 4.55884 8.17737 4.52225C7.37982 4.48566 6.5912 4.69863 5.92719 5.12994H6.44495C6.69251 5.12994 6.92994 5.2256 7.105 5.39589C7.28006 5.56617 7.3784 5.79712 7.3784 6.03794C7.3784 6.27875 7.28006 6.5097 7.105 6.67999C6.92994 6.85027 6.69251 6.94593 6.44495 6.94593H3.33343C3.08586 6.94593 2.84844 6.85027 2.67338 6.67999C2.49832 6.5097 2.39998 6.27875 2.39998 6.03794V3.3079Z" fill="currentColor"/>
                </svg>
            </button>&nbsp;&nbsp;',
            $item['id']
        );
        $delete = sprintf(
            '<button type="submit" title="Delete Log" class="button" value="%s" id="delete" name="delete" style="color:red;">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4.66683H3.33333V13.3335C3.33333 13.6871 3.47381 14.0263 3.72386 14.2763C3.97391 14.5264 4.31304 14.6668 4.66667 14.6668H11.3333C11.687 14.6668 12.0261 14.5264 12.2761 14.2763C12.5262 14.0263 12.6667 13.6871 12.6667 13.3335V4.66683H4ZM6.66667 12.6668H5.33333V6.66683H6.66667V12.6668ZM10.6667 12.6668H9.33333V6.66683H10.6667V12.6668ZM11.0787 2.66683L10 1.3335H6L4.92133 2.66683H2V4.00016H14V2.66683H11.0787Z" fill="currentColor"/>
            </svg>
        </button>',
            $item['id']
        );
        $modal = $this->data_modal($item);
        $actions = array(
            '<div class="alignright">' . $view . $resend . $delete . $modal . '</div>'
        );

        return $this->row_actions($actions, true);
    }

    // Bind table with columns, data and all
    function prepare_items()
    {
        global $wpdb;
        $table_name = esc_sql($wpdb->prefix . 'wpoven_smtp_suresend_logs');

        $this->table_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name}"), ARRAY_A);

        // Check if action is set and sanitize the value
        if (isset($_GET['action'])) {
            $action = wp_unslash(sanitize_text_field($_GET['action']));
            if ($action === 'success' || $action === 'failed') {
                $this->table_data = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE status = %s ORDER BY time DESC",
                    $action
                ), ARRAY_A);
            }
        }

        if (isset($_POST['s']) && !empty($_POST['s'])) {
            $search_term = sanitize_text_field($_POST['s']);
            $search_columns = ['time', 'recipient', 'subject', 'status'];
        
            // Escape the search term and create wildcards for the LIKE condition
            $search_wildcards = '%' . $wpdb->esc_like($search_term) . '%';
        
            // Build query conditions manually and prepare placeholders
            $conditions = [];
        
            foreach ($search_columns as $column) {
                // Prepare each condition as "column LIKE %s"
                $conditions[] = $wpdb->prepare("$column LIKE %s", $search_wildcards);
            }
            $where_clause = implode(' OR ', $conditions);
            $this->table_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE $where_clause"), ARRAY_A);
        }



        if (isset($_POST['action']) == 'delete_all' || isset($_POST['delete'])) {
            if (isset($_POST['element']) && $_POST['action'] == 'delete_all') {
                $selectedLogIds = array_map('absint', $_POST['element']);
                foreach ($selectedLogIds as $logId) {
                    $wpdb->delete($table_name, array('id' => $logId), array('%d'));
                }
                echo '<div class="updated notice"><p>' . count($selectedLogIds) . '&nbsp;Rows deleted successfully!</p></div>';
            }
            if (isset($_POST['delete'])) {
                $id =  $_POST['delete'];
                $wpdb->delete($table_name, array('id' => $id), array('%d'));
                echo '<div class="updated notice"><p>1&nbsp;Rows deleted successfully!</p></div>';
            }
        }

        if (isset($_POST['resend'])) {
            $id =  $_POST['resend'];
            $single_row = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM %s WHERE id = %d", $table_name, $id)
            );
            $to = $single_row[0]->recipient;
            $subject = $single_row[0]->subject;
            $headers = $single_row[0]->headers;
            $message = $single_row[0]->message;
            $attachments = array();
            wp_mail($to, $subject, $message, $headers, $attachments);
        }

        $columns = $this->get_columns();
        $subsubsub = $this->views();
        $hidden = (is_array(get_user_meta(get_current_user_id(), 'aaa', true))) ? get_user_meta(get_current_user_id(), 'dff', true) : array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 12);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil($total_items / $per_page) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }

    //Get column default
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'time':
            case 'recipient':
            case 'subject':
                // case 'attachments':
            case 'headers':
                return $item[$column_name];
            case 'status':
                $color = 'red';
                if ($item['status'] == 'success') {
                    $color = 'green';
                }
                return '<span style="color:' . $color . '">' . esc_html($item['status']) . '</span>';
            case 'action':
            default:
                return $item[$column_name];
        }
    }
    // Get checkbox
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['id']
        );
    }

    // Sorting columns
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'time'  => array('time', true),
            'recipient' => array('recipient', true),
            'subject' => array('subject', true),
            'headers' => array('headers', true),
            'status'   => array('status', true)
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete_all'    => __('Delete'),
        );
        return $actions;
    }

    // Sorting function
    function usort_reorder($a, $b)
    {
        $time = (!empty($_GET['time'])) ? $_GET['time'] : 'time';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$time], $b[$time]);


        return ($order === 'asc') ? $result : -$result;
    }

    function views()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpoven_smtp_suresend_logs';

        $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            $row_count = 0;
        } else {
            $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }

        $success = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'success'");
        if (!$success) {
            $success = 0;
        }

        $failed = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'failed'");
        if (!$failed) {
            $failed = 0;
        }
        echo '<ul class="subsubsub">';
        echo sprintf(
            '<a type="button" style="color:blue;" href="?page=wpoven-smtp-suresend-smtp-logs">All&nbsp;(%s)</a>&nbsp;|&nbsp;',
            $row_count
        );
        echo sprintf(
            '<a style="color:green;" href="?page=wpoven-smtp-suresend-smtp-logs&action=success">Success&nbsp;(%s)</a>&nbsp;|&nbsp;',
            $success
        );
        echo sprintf(
            '<a style="color:red;" href="?page=wpoven-smtp-suresend-smtp-logs&action=failed">Failed&nbsp;(%s)</a>&nbsp;',
            $failed
        );
        echo '</ul>';
    }


    /**
     * HTML for review log modal.
     */
    function data_modal($item)
    {
        return '<div id="modal-' . $item['id'] . '" class="hidden disable-outside-clicks">
			<div class="modal modal-content" >
				<header>
					<div><h1>SMTP Logs</h1></div>
					<div style="color:red; border-color:red;" class="button close rounded alignright"><strong >Close</strong></div>
				</header>
				<div>
					<div class="wp-list-table">
						<div class="wp-list-row">
							<div class="wp-list-cell"><h4>Recipient</h4></div>
							<div class="wp-list-cell"><span>' . $item['recipient'] . '</span></div>
						</div>
						<div class="wp-list-row">
							<div class="wp-list-cell"><h4>Time</h4></div>
							<div class="wp-list-cell"><span>' . $item['time'] . '</span></div>
						</div>
						<div class="wp-list-row">
							<div class="wp-list-cell"><h4>Subject</h4></div>
							<div class="wp-list-cell">' . $item['subject'] . '</div>
						</div>
                        <div class="wp-list-row">
							<div class="wp-list-cell"><h4>Headers</h4></div>
							<div class="wp-list-cell">' . $item['headers'] . '</div>
						</div>
						<div class="wp-list-row">
							<div class="wp-list-cell"><h4>Status</h4></div>
							<div class="wp-list-cell">' . $item['status'] . '</div>
						</div>
					</div>
					<div class="wp-list-table">
						<div class="wp-list-row">
							<div class="wp-list-cell">
								<h4>Message</h4><iframe srcdoc="' . htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8') . '" style="width:100%; height:250px; border:none;"></iframe>
							</div>
						</div>
					</div>
					<div class="wp-list-table">
						<div class="wp-list-row">
							<div class="wp-list-cell">
								<h4>Debug Log</h4><iframe srcdoc="<pre>' . htmlspecialchars($item['smtplogs'], ENT_QUOTES, 'UTF-8') . '</pre>" style="width:100%; height:200px; border:none;"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
    }
}
