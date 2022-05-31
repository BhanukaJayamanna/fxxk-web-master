<?php

namespace Modules\MultiVendor\Repositories;


use Illuminate\Support\Facades\Auth;
use \App\Models\User;
use App\Models\MerchantDetail;
use App\Traits\GenerateSlug;
use App\Traits\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\GeneralSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use \Modules\MultiVendor\Entities\SellerAccount;
use \Modules\MultiVendor\Entities\SellerBankAccount;
use \Modules\MultiVendor\Entities\SellerBusinessInformation;
use \Modules\MultiVendor\Entities\SellerReturnAddress;
use \Modules\MultiVendor\Entities\SellerWarehouseAddress;
use Modules\MultiVendor\Entities\SellerSubcription;
use Modules\MultiVendor\Events\SellerCarrierCreateEvent;
use Modules\MultiVendor\Events\SellerPickupLocationCreated;
use Modules\MultiVendor\Events\SellerShippingConfigEvent;
use Modules\MultiVendor\Events\SellerShippingRateEvent;
use Modules\RolePermission\Entities\Role;
use Modules\SidebarManager\Entities\Sidebar;
use Str;

class MerchantRepository
{
    use Notification, GenerateSlug;
    public function getAll()
    {
        return SellerAccount::with('user', 'user.SellerAccount', 'user.SellerBankAccount')->latest();
    }


    public function getActive()
    {
        $activeUserIds = User::where('is_active', 1)->where('id', '>', 1)->pluck('id');
        return SellerAccount::with('commission_type')->whereIn('user_id', $activeUserIds);
    }

    public function getInactive()
    {
        $activeUserIds = User::where('is_active', 0)->where('id', '>', 1)->pluck('id');
        return SellerAccount::whereIn('user_id', $activeUserIds)->with('commission_type')->latest();
    }

    public function getAllSeller()
    {
        return SellerAccount::with('user', 'user.SellerAccount', 'user.SellerBankAccount')->latest()->get();
    }

    public function findUserByID($id)
    {
        return User::with('SellerAccount', 'SellerBankAccount', 'SellerAccount.commission_type', 'SellerWarehouseAddress', 'SellerBusinessInformation', 'SellerReturnAddress', 'order_packages', 'seller_products')->findOrFail($id);
    }

    public function create($data)
    {
        $role = Role::where('type', 'seller')->first();
        $user =  User::create([
            'first_name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => date("Y-m-d H:i:s"),
            'is_verified' => 1,
            'is_active' => 1,
            'role_id' => $role->id,
            'seller_status' => 'approve',
            'username' => $data['phone_number'],
            'verify_code' => sha1(time()),
            'password' => Hash::make($data['password']),
            'currency_id' => app('general_setting')->currency,
            'lang_code' => app('general_setting')->language_code,
            'currency_code' => app('general_setting')->currency_code,
        ]);

        $slug = $this->productSlug($data['shop_name']);
        $user->slug = $slug;
        $user->save();
        Event::dispatch(new SellerCarrierCreateEvent($user['id']));
        Event::dispatch(new SellerPickupLocationCreated($user['id']));
        Event::dispatch(new SellerShippingRateEvent($user['id']));
        Event::dispatch(new SellerShippingConfigEvent($user['id']));
        SellerAccount::create([
            'user_id' => $user['id'],
            'seller_id' => 'BDEXCJ' . rand(99999, 10000000),
            'seller_shop_display_name' => $data['shop_name'],
            'seller_commission_id' => (!empty($data['commission_id'])) ? $data['commission_id'] : 1,
            'commission_rate' => (!empty($data['commission_rate'])) ? $data['commission_rate'] : 0,
            'subscription_type' => (!empty($data['pricing_id'])) ? $data['pricing_id'] : null,
            'seller_phone' => $data['phone_number']
        ]);
        SellerBusinessInformation::create([
            'user_id' => $user['id'],
            'business_country' => app('general_setting')->default_country,
            'business_state' => app('general_setting')->default_state
        ]);
        SellerBankAccount::create([
            'user_id' => $user['id']
        ]);
        if (!empty($data['pricing_id'])) {
            SellerSubcription::create([
                'seller_id' => $user['id'],
                'pricing_id' => $data['pricing_id']
            ]);
        }

        SellerWarehouseAddress::create([
            'user_id' => $user['id'],
            'warehouse_country' => app('general_setting')->default_country,
            'warehouse_state' => app('general_setting')->default_state
        ]);
        SellerReturnAddress::create([
            'user_id' => $user['id'],
            'return_country' => app('general_setting')->default_country,
            'return_state' => app('general_setting')->default_state
        ]);

        $sidebars = Sidebar::where('user_id',1)->orderBy('default_position','asc')->get();
        foreach ($sidebars as $sidebar){
            $data = $sidebar->toArray();
            $data['user_id'] = $user['id'];
            $data['position'] = $sidebar['default_position'];
            Sidebar::create($data);
        }
        return $user;
    }

    public function update_commission($data)
    {
        SellerAccount::find($data['seller_account_id'])->update([
            'commission_rate' => $data['rate'],
        ]);
    }

    public function changeTrustedStatus($id)
    {
        $seller_account = SellerAccount::find($id);
        if ($seller_account->is_trusted == 1) {
            $seller_account->update(['is_trusted' => 0]);
        } else {
            $seller_account->update(['is_trusted' => 1]);
        }
    }

    public function gstStatusUpdate($data)
    {
        $seller_account = SellerBusinessInformation::where('user_id', $data['id'])->first();
        $seller_account->update([
            'claim_gst' => $data['status']
        ]);
    }

    public function customerToSellerConvert($data)
    {
        $user = User::find(auth()->user()->id);
        $user->update([
            'role_id' => 5
        ]);

        SellerAccount::create([
            'user_id' => auth()->user()->id,
            'seller_id' => 'BDEXCJ' . rand(99999, 10000000),
            'seller_shop_display_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'seller_commission_id' => (!empty($data['commission_id'])) ? $data['commission_id'] : 1,
            'commission_rate' => (!empty($data['commission_rate'])) ? $data['commission_rate'] : 0,
            'subscription_type' => (!empty($data['pricing_id'])) ? $data['pricing_id'] : null,
            'seller_phone' => null
        ]);
        SellerBusinessInformation::create([
            'user_id' => auth()->user()->id
        ]);
        SellerBankAccount::create([
            'user_id' => auth()->user()->id
        ]);
        if (!empty($data['pricing_id'])) {
            SellerSubcription::create([
                'seller_id' => auth()->user()->id,
                'pricing_id' => $data['pricing_id']
            ]);
        }

        SellerWarehouseAddress::create([
            'user_id' => auth()->user()->id
        ]);
        SellerReturnAddress::create([
            'user_id' => auth()->user()->id
        ]);
    }



    public function getSellerConfiguration()
    {
        return GeneralSetting::select('auto_approve_seller','multi_category','commission_by')->first();
    }

    public function sellerConfigurationUpdate($request)
    {
        $generatlSetting = GeneralSetting::first();
        $generatlSetting->auto_approve_seller = $request['status'];
        $generatlSetting->multi_category = $request['multi_category'];
        $generatlSetting->commission_by = $request['commission_by'];
        $generatlSetting->save();
    }

    public function update_status($userId)
    {
        $user = User::findOrFail($userId);
        $this->notificationUrl = "#";
        if ($user->is_active == 0) {
            $user->is_active = 1;
            // Send Notification
            $this->adminNotificationUrl = '/admin/merchants';
            $this->routeCheck = 'admin.merchants_list';
            $this->typeId = EmailTemplateType::where('type', 'seller_approve_email_template')->first()->id;
            $this->notificationSend("Seller approved", $user->id);
        } else {
            $user->is_active = 0;
            $this->adminNotificationUrl = '/admin/inactive-merchants';
            $this->routeCheck = 'admin.merchants_list';
            $this->typeId = EmailTemplateType::where('type', 'seller_suspended_email_template')->first()->id;
            $this->notificationSend("Seller suspended", $user->id);
        }
        $user->save();
    }
}
