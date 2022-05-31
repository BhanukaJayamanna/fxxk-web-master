<?php

namespace Modules\MultiVendor\Http\Controllers;

use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Support\Facades\Validator;
use Modules\FrontendCMS\Entities\Pricing;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\MultiVendor\Services\MerchantService;
use Modules\MultiVendor\Services\ProfileService;
use Yajra\DataTables\Facades\DataTables;
use Modules\Setup\Entities\Country;
use Modules\MultiVendor\Repositories\CommisionRepository;
use App\Traits\Notification;
use Modules\MultiVendor\Rules\SellerValidateRule;

class MerchantController extends Controller
{
    use Notification;
    protected $merchantService, $profileService;
    public function __construct(MerchantService $merchantService, ProfileService $profileService)
    {
        $this->middleware('maintenance_mode');
        $this->merchantService = $merchantService;
        $this->profileService = $profileService;
    }

    public function index()
    {
        return view('multivendor::merchants.index');
    }

    public function inactiveMerchants()
    {
        return view('multivendor::merchants.inactive_merchants');
    }

    public function secretLogin($id)
    {
        $present_id = auth()->user()->id;
        User::findOrFail($id)->update([
            'secret_login' => 1,
        ]);
        Auth::logout();
        session()->flush();
        session()->put('secret_logged_in_by_user',$present_id);
        Auth::loginUsingId($id);
        Toastr::success(__('auth.logged_in_successfully'), __('common.success'));
        return redirect()->route('seller.dashboard');
    }

    public function getData(Request $request){
        if($request->type == "deactive"){
            $seller = $this->merchantService->getInactive();
        }else{
            $seller = $this->merchantService->getActive();
        }

        return DataTables::of($seller)
            ->addIndexColumn()
            ->addColumn('name', function($seller){
                return view('multivendor::merchants.components._name_td',compact('seller'));
            })
            ->addColumn('email', function($seller){
                return @$seller->user->email;
            })
            ->addColumn('phone', function($seller){
                return @$seller->user->username ?? 'X';
            })
            ->addColumn('commission_type', function($seller){
                return view('multivendor::merchants.components._commission_rate_td',compact('seller'));
            })
            ->addColumn('is_trusted', function($seller){
                return view('multivendor::merchants.components._is_trusted_td',compact('seller'));
            })
            ->addColumn('gst', function($seller){
                return view('multivendor::merchants.components.gst_status',compact('seller'));
            })
            ->addColumn('shop_name', function($seller){
                return @$seller->seller_shop_display_name ?? 'X';
            })
            ->addColumn('wallet_balance', function($seller){
                return single_price(@$seller->user->SellerCurrentWalletAmounts);
            })
            ->addColumn('total_orders', function($seller){
                return count(@$seller->user->order_packages);
            })
            ->addColumn('action', function($seller){
                return view('multivendor::merchants.components._action_td',compact('seller'));
            })
            ->rawColumns(['commission_type','is_trusted','action'])
            ->toJson();
    }

    public function show($id)
    {
        $data['user'] = $this->merchantService->findUserByID($id);
        return view('multivendor::merchants.show_details', $data);
    }

    public function getOrders($id){
        $user = $this->merchantService->findUserByID($id);
        $order_package = $user->order_packages;
        return DataTables::of($order_package)
            ->addIndexColumn()
            ->addColumn('date', function($order_package){
                return date(app('general_setting')->dateFormat->format, strtotime($order_package->created_at));
            })
            ->addColumn('order_number', function($order_package){
                return @$order_package->order->order_number;
            })
            ->addColumn('email', function($order_package){
                return @$order_package->order->customer->email;

            })
            ->addColumn('no_of_product', function($order_package){
                return view('multivendor::merchants.components._show_no_of_product_td',compact('order_package'));
            })
            ->addColumn('total_amount',function($order_package){
                return single_price($order_package->products->sum('total_price') + $order_package->shipping_cost + $order_package->tax_amount);

            })
            ->addColumn('order_status', function($order_package){
                return view('multivendor::merchants.components._show_order_status_td',compact('order_package'));
            })
            ->addColumn('delivery_status',function($order_package){
                return @$order_package->delivery_process->name;
            })
            ->rawColumns(['no_of_product','order_status'])
            ->toJson();
    }

    public function getWalletHistory($id){
        $user = $this->merchantService->findUserByID($id);
        $transaction = $user->wallet_balances;
        return DataTables::of($transaction)
            ->addIndexColumn()
            ->addColumn('date', function($transaction){
                return date(app('general_setting')->dateFormat->format, strtotime($transaction->created_at));
            })
            ->addColumn('user',function($transaction){
                return  @$transaction->user->first_name . ' '. @$transaction->user->last_name;

            })
            ->addColumn('amount',function($transaction){
                return  single_price($transaction->amount);

            })
            ->addColumn('payment_method', function($transaction){
                return $transaction->GatewayName;

            })
            ->addColumn('approval', function($transaction){
                return view('multivendor::merchants.components._show_wallet_approval_td',compact('transaction'));
            })
            ->rawColumns(['approval'])
            ->toJson();
    }

    public function getProduct($id){
        $user = $this->merchantService->findUserByID($id);
        $product = $user->seller_products;
        return DataTables::of($product)
        ->addIndexColumn()
        ->addColumn('product_name', function($product){
            return view('multivendor::merchants.components._show_product_name_td',compact('product'));
        })
        ->addColumn('category', function($product){
            return @$product->product->category->name;
        })
        ->addColumn('brand', function($product){
            return @$product->product->brand->name;
        })
        ->addColumn('logo', function($product){
            return view('multivendor::merchants.components._show_product_logo_td',compact('product'));
        })
        ->addColumn('stock', function($product){
            return view('multivendor::merchants.components._show_product_stock_td',compact('product'));
        })
        ->addColumn('status', function($product){
            return view('multivendor::merchants.components._show_product_status_td',compact('product'));
        })
        ->addColumn('action',function($product){
            return view('multivendor::merchants.components._show_product_action_td',compact('product'));
        })
        ->rawColumns(['product_name','logo','stock','status','action'])
        ->toJson();
    }

    public function edit($id)
    {
        $data['seller'] = $this->profileService->getData($id);
        $data['countries'] = Country::all();
        $commissionRepo = new CommisionRepository();
        $data['commissions'] = $commissionRepo->getAllActive();
        $data['pricings'] = Pricing::where('status', 1)->get();
        return view('multivendor::profile.index', $data);
    }

    public function create()
    {
        $commisionRepo = new CommisionRepository();
        $data['commissions'] = $commisionRepo->getAllActive();
        return view('multivendor::merchants.create', $data);
    }

    public function store(Request $request)
    {
        $this->validator($request->all())->validate();
        DB::beginTransaction();
        
        try {

            $user = $this->merchantService->create($request->except("_token"));
            DB::commit();
            // User Notification Setting Create
            (new UserNotificationSetting())->createForRegisterUser($user->id);
            $this->adminNotificationUrl = '/admin/merchants';
            $this->routeCheck = 'admin.merchants_list';
            $this->typeId = EmailTemplateType::where('type', 'seller_create_email_template')->first()->id; //register email templete typeid
            $this->notificationSend("Seller Created", $user->id);

            LogActivity::successLog('New Merchant has been added.');
            Toastr::success(__('common.created_successfully'),__('common.success'));
            return redirect()->route('admin.merchants_list');

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            DB::rollBack();
            Toastr::error(__('common.operation_failed'));
            return back();

        }

    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'commission_id' => 'required',
            'commission_rate' => 'required',
            'shop_name' => ['required','max:100','unique:seller_accounts,seller_shop_display_name', new SellerValidateRule($data['shop_name'])]
        ],
        [
            'name.required' => "This Name Filed is required",
            'email.required' => "This Email is required",
            'email.email' => "This is not a valid email",
            'email.unique' => "Email has already taken",
            'password.required' => "This Password Filed is required",
            'subscription_type.required' => 'Please Select a Subscription Type'
        ]);
    }

    public function change_merchant_trusted_status($id)
    {
        try {
            $this->merchantService->changeTrustedStatus($id);
            LogActivity::successLog('Trusted Status has been changed.');
            Toastr::success(__('common.status_change_message'),__('common.success'));
            return redirect()->route('admin.merchants_list');

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function update_commission(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->merchantService->update_commission($request->except("_token"));
            DB::commit();
            LogActivity::successLog('Commision has been Updated to '. $request->rate . ' %');
            Toastr::success(__('common.updated_successfully'),__('common.success'));
            return redirect()->back();

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            DB::rollBack();
            Toastr::error(__('common.operation_failed'));
            return back();
        }

    }

    public function gst_status_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->merchantService->gstStatusUpdate($request->except("_token"));
            LogActivity::successLog('Gst status update successful.');
            DB::commit();
            return 1;

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            DB::rollBack();
            return 0;
        }

    }


    public function seller_configuration()
    {
        try {
            $sellerConfiguration = $this->merchantService->getsellerConfiguration();
            return view('multivendor::merchants.seller_configuration', compact('sellerConfiguration'));
        } catch (\Exception $e) {
            Toastr::error(__('common.operation_failed'));
            LogActivity::errorLog($e->getMessage());
            return back();
        }
    }

    public function seller_configuration_update(Request $request)
    {
        try {
            $this->merchantService->sellerConfigurationUpdate($request->except('_token'));
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            LogActivity::successLog("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
            return back();
        } catch (\Exception $e) {
            Toastr::error(__('common.operation_failed'));
            LogActivity::errorLog($e->getMessage());
            return back();
        }

    }

    public function update_status($userId)
    {
        try {
            $this->merchantService->update_status($userId);
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            LogActivity::successLog('seller updated.');
            return back();
        } catch (Exception $e) {
            Toastr::error(__('common.operation_failed'));
            LogActivity::errorLog($e->getMessage());
            return back();
        }
    }
}
