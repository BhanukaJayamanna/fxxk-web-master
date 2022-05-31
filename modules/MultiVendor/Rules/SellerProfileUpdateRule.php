<?php

namespace Modules\MultiVendor\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use App\Traits\GenerateSlug;

class SellerProfileUpdateRule implements Rule
{
    use GenerateSlug;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $slug = $this->productSlug($value);
        $seller_id = getParentSellerId();
        $user = User::where('slug', $slug)->where('id', '!=', $seller_id)->first();
        if($user){
            return false;
        }
        return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Shop Name Already Taken.';
    }
}
