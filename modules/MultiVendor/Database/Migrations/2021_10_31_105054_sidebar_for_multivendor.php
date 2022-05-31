<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SidebarManager\Entities\Sidebar;

class SidebarForMultivendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('sidebars')){


            $exsist = DB::table('sidebars')->where('sidebar_id', 168)->first();
            if(!$exsist){

                $mywallets = Sidebar::where('module_id', 7)->pluck('id')->toArray();
                Sidebar::destroy($mywallets);

                $sql = [
                    // seller dashboard
                    ['sidebar_id' => 168, 'module_id' => 34, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Dashboard', 'route' => 'seller.dashboard', 'type' => 1,'position' => 1], //Main menu

                    //frontend cms
                    ['sidebar_id' => 5, 'module_id' => 2, 'module' => 'MultiVendor', 'parent_id' => 2, 'name' => 'Merchant Content', 'route' => 'frontendcms.merchant-content.index', 'type' => 2],

                    //seller manage
                    ['sidebar_id' => 25, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Manage Seller', 'route' => 'manage_seller', 'type' => 1,'position' => 4],
                    ['sidebar_id' => 26, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => 25, 'name' => 'Seller List', 'route' => 'admin.merchants_list', 'type' => 2],
                    ['sidebar_id' => 27, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => 25, 'name' => 'Commission Setup', 'route' => 'admin.seller_commission_index', 'type' => 2],
                    ['sidebar_id' => 6, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => 25, 'name' => 'Pricing Plan', 'route' => 'admin.pricing.index', 'type' => 2],
                    ['sidebar_id' => 28, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => 25, 'name' => 'Subssription Payments', 'route' => 'admin.subscription_payment_list', 'type' => 2],
                    ['sidebar_id' => 172, 'module_id' => 6, 'module' => 'MultiVendor', 'parent_id' => 25, 'name' => 'Manage Seller Configuration', 'route' => 'admin.seller_configuration', 'type' => 2],

                    // seller order manage
                    ['sidebar_id' => 134, 'module_id' => 30, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Seller Order Manage', 'route' => 'seller_order_manage', 'type' => 1,'position' => 30],
                    ['sidebar_id' => 135, 'module_id' => 30, 'module' => 'MultiVendor', 'parent_id' => 134, 'name' => 'My Orders', 'route' => 'order_manage.my_sales_index', 'type' => 2],

                    //wallet manage
                    ['sidebar_id' => 34, 'module_id' => 8, 'module' => 'MultiVendor', 'parent_id' => 32, 'name' => 'Withdraw Request', 'route' => 'wallet.withdraw_requests.get_data', 'type' => 2],

                    //my wallet
                    ['sidebar_id' => 29, 'module_id' => 7, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'My Wallet', 'route' => 'my_wallet', 'type' => 1,'position' => 10],
                    ['sidebar_id' => 30, 'module_id' => 7, 'module' => 'MultiVendor', 'parent_id' => 29, 'name' => 'Transactions', 'route' => 'my-wallet.index', 'type' => 2],
                    ['sidebar_id' => 31, 'module_id' => 7, 'module' => 'MultiVendor', 'parent_id' => 29, 'name' => 'Withdraw', 'route' => 'my-wallet.withdraw_index', 'type' => 2],

                    // seller staff manage
                    ['sidebar_id' => 132, 'module_id' => 28, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Seller Staff Manage', 'route' => 'seller_staff_manage', 'type' => 1,'position' => 28],

                    //seller subscription payment
                    ['sidebar_id' => 133, 'module_id' => 29, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Subscription Payment', 'route' => 'seller_subscription_payment', 'type' => 1,'position' => 29],

                    //products
                    ['sidebar_id' => 56, 'module_id' => 12, 'module' => 'MultiVendor', 'parent_id' => 48, 'name' => 'Inhouse Product List', 'route' => 'admin.my-product.index', 'type' => 2],

                    //reviews
                    ['sidebar_id' => 61, 'module_id' => 13, 'module' => 'MultiVendor', 'parent_id' => 58, 'name' => 'My Product Reviews', 'route' => 'seller.product-reviews.index', 'type' => 2],
                    ['sidebar_id' => 62, 'module_id' => 13, 'module' => 'MultiVendor', 'parent_id' => 58, 'name' => 'My Reviews', 'route' => 'my-reviews.index', 'type' => 2],

                    //seller products
                    ['sidebar_id' => 120, 'module_id' => 27, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Seller Product', 'route' => 'seller_product_module', 'type' => 1,'position' => 27],
                    ['sidebar_id' => 131, 'module_id' => 27, 'module' => 'MultiVendor', 'parent_id' => 120, 'name' => 'Seller Product List', 'route' => 'seller.product.index', 'type' => 2],

                    //order manages
                    ['sidebar_id' => 64, 'module_id' => 14, 'module' => 'MultiVendor', 'parent_id' => 63, 'name' => 'My Orders', 'route' => 'my_orders', 'type' => 2],

                    //refund manage
                    ['sidebar_id' => 72, 'module_id' => 15, 'module' => 'MultiVendor', 'parent_id' => 69, 'name' => 'My Refund List', 'route' => 'refund.my_refund_list', 'type' => 2],

                    // Support Ticket
                    ['sidebar_id' => 106, 'module_id' => 21, 'module' => 'MultiVendor', 'parent_id' => 100, 'name' => 'My Tickets', 'route' => 'seller.support-ticket.index', 'type' => 2],

                    // admin report
                    ['sidebar_id' => 137, 'module_id' => 31, 'module' => 'MultiVendor', 'parent_id' => 136, 'name' => 'Seller Wise report', 'route' => 'report.seller_wise_sales', 'type' => 2],
                    ['sidebar_id' => 144, 'module_id' => 31, 'module' => 'MultiVendor', 'parent_id' => 136, 'name' => 'Top Sellers', 'route' => 'report.top_seller', 'type' => 2],

                    // seller report
                    ['sidebar_id' => 151, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => null, 'name' => 'Seller Report', 'route' => 'seller_report', 'type' => 1,'position' => 32],
                    ['sidebar_id' => 152, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => 151, 'name' => 'Products', 'route' => 'seller_report.product', 'type' => 2],
                    ['sidebar_id' => 153, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => 151, 'name' => 'Top Customers', 'route' => 'seller_report.top_customer', 'type' => 2],
                    ['sidebar_id' => 154, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => 151, 'name' => 'Top Selling Items', 'route' => 'seller_report.top_selling_item', 'type' => 2],
                    ['sidebar_id' => 155, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => 151, 'name' => 'Orders', 'route' => 'seller_report.order', 'type' => 2],
                    ['sidebar_id' => 156, 'module_id' => 32, 'module' => 'MultiVendor', 'parent_id' => 151, 'name' => 'Seller Reviews', 'route' => 'seller_report.review', 'type' => 2]

                ];

                $users =  User::whereHas('role', function($query){
                    $query->where('type', 'superadmin')->orWhere('type', 'admin')->orWhere('type', 'staff')->orWhere('type', 'seller');
                })->pluck('id');

                foreach ($users as $key=> $user)
                {
                    $user_array[$key] = ['user_id' => $user];
                    foreach ($sql as $row)
                    {
                        $final_row = array_merge($user_array[$key],$row);
                        Sidebar::insert($final_row);
                    }
                }

            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
