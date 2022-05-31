<?php
namespace Modules\MultiVendor\Repositories;

use Illuminate\Support\Facades\Auth;
use \App\Models\User;
use App\Traits\Notification;
use Modules\MultiVendor\Entities\SubSeller;
use Illuminate\Support\Facades\Hash;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\RolePermission\Entities\Role;
use Modules\SidebarManager\Entities\Sidebar;

class SubSellerRepository
{
    use Notification;
    public function getAll()
    {
        return SubSeller::with('seller', 'sub_seller')->where('seller_id', auth()->user()->id)->get();
    }

    public function findUserByID($id)
    {
        return User::findOrFail($id);
    }

    public function create($data)
    {
        $role = Role::where('type', 'seller')->orderBydesc('id')->first();
        $user =  User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'email_verified_at' => date("Y-m-d H:i:s"),
            'is_verified' => 1,
            'role_id' => $role->id,
            'username' => $data['email'],
            'verify_code' => sha1(time()),
            'password' => Hash::make($data['password']),
            'avatar' => isset($data['avatar'])?$data['avatar']:null,
            'currency_id' => app('general_setting')->currency,
            'lang_code' => app('general_setting')->language_code,
            'currency_code' => app('general_setting')->currency_code,
        ]);

        // User Notification Setting Create
        (new UserNotificationSetting())->createForRegisterUser($user->id);

        $this->adminNotificationUrl = '/seller/my-staff';
        $this->routeCheck = 'seller.sub_seller.index';
        $this->typeId = EmailTemplateType::where('type', 'sub_seller_create_email_template')->first()->id; //register email templete typeid
        $this->notificationSend("Sub Seller Created", $user->id);

        SubSeller::create([
            'seller_id' => auth()->user()->id,
            'user_id' => $user->id,
            'phone' => $data['phone'],
            'address' => $data['address'],
        ]);
        $sidebars = Sidebar::where('user_id',1)->orderBy('default_position','asc')->get();
        foreach ($sidebars as $sidebar){
            $data = $sidebar->toArray();
            $data['user_id'] = $user['id'];
            $data['position'] = $sidebar['default_position'];
            Sidebar::create($data);
        }
    }

    public function update($data, $id)
    {
        $user = User::where('id', $id)->whereHas('sub_seller', function($query){
            $seller_id = getParentSellerId();
            return $query->where('seller_id', $seller_id);
            
        })->first();

        if($user){
            $user->update([
                'password' => ($data['password'])?Hash::make($data['password']):$user->password,
                'avatar' => isset($data['avatar'])?$data['avatar']:$user->avatar,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'address' => $data['address']
            ]);
            $user->sub_seller->update([
                'phone' => $data['phone'],
                'address' => $data['address']
            ]);
        }
        return true;

    }

    public function delete($id)
    {
        $user = $this->findUserByID($id);
        $user->sub_seller->delete();
        $user->delete();
    }
}
