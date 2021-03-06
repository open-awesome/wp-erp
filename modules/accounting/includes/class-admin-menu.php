<?php
namespace WeDevs\ERP\Accounting;

/**
 * Admin Menu
 */
class Admin_Menu {

    /**
     * Kick-in the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function admin_menu() {
        
        $dashboard      = 'erp_ac_view_dashboard';
        $customer       = 'erp_ac_view_customer';
        $vendor         = 'erp_ac_view_vendor';
        $sale           = 'erp_ac_view_sale';
        $expense        = 'erp_ac_view_expense';
        $account_charts = 'erp_ac_view_account_lists';
        $bank           = 'erp_ac_view_bank_accounts';
        $journal        = 'erp_ac_view_journal';
        $reports        = 'erp_ac_view_reports';
       
        add_menu_page( __( 'Accounting', 'accounting' ), __( 'Accounting', 'accounting' ), $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ), 'dashicons-chart-pie', null );
        
        $dashboard      = add_submenu_page( 'erp-accounting', __( 'Dashboard', 'accounting' ), __( 'Dashboard', 'accounting' ), $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ) );
        $customer       = add_submenu_page( 'erp-accounting', __( 'Customers', 'accounting' ), __( 'Customers', 'accounting' ), $customer, 'erp-accounting-customers', array( $this, 'page_customers' ) );
        $vendor         = add_submenu_page( 'erp-accounting', __( 'Vendors', 'accounting' ), __( 'Vendors', 'accounting' ), $vendor, 'erp-accounting-vendors', array( $this, 'page_vendors' ) );
        $sale           = add_submenu_page( 'erp-accounting', __( 'Sales', 'accounting' ), __( 'Sales', 'accounting' ), $sale, 'erp-accounting-sales', array( $this, 'page_sales' ) );
        $expense        = add_submenu_page( 'erp-accounting', __( 'Expenses', 'accounting' ), __( 'Expenses', 'accounting' ), $expense, 'erp-accounting-expense', array( $this, 'page_expenses' ) );
        $account_charts = add_submenu_page( 'erp-accounting', __( 'Chart of Accounts', 'accounting' ), __( 'Chart of Accounts', 'accounting' ), $account_charts, 'erp-accounting-charts', array( $this, 'page_chart_of_accounting' ) );
        $bank           = add_submenu_page( 'erp-accounting', __( 'Bank Accounts', 'accounting' ), __( 'Bank Accounts', 'accounting' ), $bank, 'erp-accounting-bank', array( $this, 'page_bank' ) );
        $journal        = add_submenu_page( 'erp-accounting', __( 'Journal Entry', 'accounting' ), __( 'Journal Entry', 'accounting' ), $journal, 'erp-accounting-journal', array( $this, 'page_journal_entry' ) );
        $reports        = add_submenu_page( 'erp-accounting', __( 'Reports', 'accounting' ), __( 'Reports', 'accounting' ), $reports, 'erp-accounting-reports', array( $this, 'page_reports' ) );
        

        add_action( 'admin_print_styles-' . $dashboard, array( $this, 'chart_script' ) );
        add_action( 'admin_print_styles-' . $customer, array( $this, 'chart_script' ) );
        add_action( 'admin_print_styles-' . $vendor, array( $this, 'chart_script' ) );
        add_action( 'admin_print_styles-' . $bank, array( $this, 'chart_script' ) );
        add_action( 'admin_print_styles-' . $sale, array( $this, 'sales_chart_script' ) );
        add_action( 'admin_print_styles-' . $expense, array( $this, 'expense_chart_script' ) );
    }

    function sales_chart_script() {
        $this->chart_script();
        wp_localize_script( 'wp-erp-ac-js', 'erp_ac_tax', [ 'rate' => erp_ac_get_tax_info() ] );
    }

    function expense_chart_script() {
        $this->chart_script();
        wp_localize_script( 'wp-erp-ac-js', 'erp_ac_tax', [ 'rate' => erp_ac_get_tax_info() ] );   
    }

    function chart_script() {

        wp_enqueue_script( 'plupload-handlers' );
        wp_enqueue_script( 'erp-file-upload' );
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'erp-flotchart' );
        wp_enqueue_script( 'erp-flotchart-resize' );
        wp_enqueue_script( 'erp-flotchart-pie' );
        wp_enqueue_script( 'erp-flotchart-time' );
        wp_enqueue_script( 'erp-flotchart-tooltip' );
        wp_enqueue_script( 'erp-flotchart-orerbars' );
        wp_enqueue_script( 'erp-flotchart-axislables' );
        wp_enqueue_script( 'erp-flotchart-navigate' );
        wp_enqueue_script('erp-flotchart-selection');
        wp_enqueue_style('erp-tiptip');
    }

    public function dashboard_page() {
        include dirname( __FILE__ ) . '/views/dashboard.php';
    }

    public function page_sales() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $type     = isset( $_GET['type'] ) ? $_GET['type'] : 'pv';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'new':

                if ( $type == 'invoice' && ( erp_ac_create_sales_invoice() || erp_ac_publish_sales_invoice() ) ) {
                    $template = dirname( __FILE__ ) . '/views/sales/invoice-new.php';
                } else if ( $type == 'payment' && ( erp_ac_create_sales_payment() || erp_ac_publish_sales_payment() ) ) {
                   $template = dirname( __FILE__ ) . '/views/sales/payment-new.php';
                }

                break;

            case 'view':
                $transaction = Model\Transaction::find( $id );

                if ( $transaction->form_type == 'invoice' ) {
                    $template = dirname( __FILE__ ) . '/views/sales/invoice-single.php';
                } else {
                    $template = dirname( __FILE__ ) . '/views/sales/payment-single.php';
                }

                break;

            default:
                $template = dirname( __FILE__ ) . '/views/sales/transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<h1>You do not have sufficient permissions to access this page.</h1>';
        }
    }

    public function page_expenses() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $type     = isset( $_GET['type'] ) ? $_GET['type'] : 'pv';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'new':

                if ( $type == 'payment_voucher' && ( erp_ac_create_expenses_voucher() || erp_ac_publish_expenses_voucher() ) ) {

                    $template = dirname( __FILE__ ) . '/views/expense/payment-voucher.php';

                } elseif ( $type == 'vendor_credit' && ( erp_ac_create_expenses_credit() || erp_ac_publish_expenses_credit() ) ) {

                    $template = dirname( __FILE__ ) . '/views/expense/vendor-credit.php';

                }

                break;

            case 'view':
                $transaction = Model\Transaction::find( $id );

                if ( $transaction->form_type == 'payment_voucher' ) {
                    $template    = dirname( __FILE__ ) . '/views/expense/payment-voucher-single.php';
                } else {
                    $template    = dirname( __FILE__ ) . '/views/expense/vendor-credit-single.php';
                }

                break;

            default:
                $template = dirname( __FILE__ ) . '/views/expense/transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<h1>You do not have sufficient permissions to access this page.</h1>';
        }
    }

    public function page_chart_of_accounting() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_view_single_account() ) {
                    $ledger = Model\Ledger::find( $id );

                    $template = dirname( __FILE__ ) . '/views/accounts/single.php';
                }
                break;

            case 'edit':
                if ( erp_ac_edit_account() ) {
                    $template = dirname( __FILE__ ) . '/views/accounts/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_account() ) {
                    $template = dirname( __FILE__ ) . '/views/accounts/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/chart-of-accounts.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<h1>You do not have sufficient permissions to access this page.</h1>';
        }
    }

    public function page_bank() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : '';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {
            case 'view':
                $transaction = Model\Transaction::find( $id );
                $template = dirname( __FILE__ ) . '/views/bank/invoice.php';
                break;
            default:
                $template = dirname( __FILE__ ) . '/views/bank/bank.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    public function page_reports() {
        $type   = isset( $_GET['type'] ) ? $_GET['type'] : '';
        $pagenum          = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $limit            = 2;
        $offset           = ( $pagenum - 1 ) * $limit;


        switch ( $type ) {
            case 'trial-balance':
                $template = dirname( __FILE__ ) . '/views/reports/trial-balance.php';
                break;

            case 'sales-tax':
                if ( isset( $_GET['action'] ) && intval( $_GET['id'] ) ) {
                    $tax_id = $_GET['id'];
                    $taxs = erp_ac_normarlize_tax_from_transaction( [ 'tax_id' => [$_GET['id']], 'offset'  => $offset, 'number' => $limit] );
                    $taxs = $taxs['individuals'][$_GET['id']];
                    $count= erp_ac_get_sales_tax_report_count( ['tax_id' => [$_GET['id']] ] );
                    $taxinfo = erp_ac_get_tax_info();
                    
                    $template = dirname( __FILE__ ) . '/views/reports/tax/single-sales-tax.php';
                } else {
                    $taxs = erp_ac_normarlize_tax_from_transaction();
                    $taxs = $taxs['units'];
                    $template = dirname( __FILE__ ) . '/views/reports/tax/sales-tax.php';
                }
                break;

            case 'income-statement':
                $template = dirname( __FILE__ ) . '/views/reports/income-statement/statement.php';
                break;

            case 'balance-sheet':
                $template = dirname( __FILE__ ) . '/views/reports/balance-sheet/balance-sheet.php';
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/reports.php';
                break;
        }

        $template = apply_filters( 'erp_ac_reporting_pages', $template, $type );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    public function page_journal_entry() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : '';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ( $action ) {
            case 'view':
                $transaction = Model\Transaction::find( $id );
                $template = dirname( __FILE__ ) . '/views/journal/single.php';
                break;

            case 'new':
                if ( erp_ac_create_journal() ) {
                    $template = dirname( __FILE__ ) . '/views/journal/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/journal/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<h1>You do not have sufficient permissions to access this page.</h1>';
        }
    }

    /**
     * Handles the plugin page
     *
     * @return void
     */
    public function page_customers() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_current_user_can_view_single_customer() ) {
                    $customer = new \WeDevs\ERP\People( $id );
                    $template = dirname( __FILE__ ) . '/views/customer/single.php';                    
                }
                break;

            case 'edit':
                $customer = new \WeDevs\ERP\People( $id );
                if ( erp_ac_current_user_can_edit_customer( $customer->created_by ) ) {
                    $template = dirname( __FILE__ ) . '/views/customer/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_customer() ) {
                    $template = dirname( __FILE__ ) . '/views/customer/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/customer/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<h1>You do not have sufficient permissions to access this page.</h1>';
        }
    }

    /**
     * Handles the plugin page
     *
     * @return void
     */
    public function page_vendors() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_current_user_can_view_single_vendor() ) {
                    $vendor = new \WeDevs\ERP\People( $id );
                    $template = dirname( __FILE__ ) . '/views/vendor/single.php';                    
                }
                break;

            case 'edit':
                $vendor = new \WeDevs\ERP\People( $id );
                if ( erp_ac_current_user_can_edit_vendor( $vendor->created_by ) ) {
                    $template = dirname( __FILE__ ) . '/views/vendor/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_vendor() ) {
                    $template = dirname( __FILE__ ) . '/views/vendor/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/vendor/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}