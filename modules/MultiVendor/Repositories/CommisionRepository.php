<?php
namespace Modules\MultiVendor\Repositories;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\MultiVendor\Entities\SellerCommssionType;

class CommisionRepository
{
    public function getAll()
    {
        return SellerCommssionType::latest()->get();
    }

    public function getAllActive()
    {
        return SellerCommssionType::where('status',1)->get();
    }

    public function findByID($id)
    {
        return SellerCommssionType::findOrFail($id);
    }

    public function create($data)
    {
        //
    }

    public function update($data, $id)
    {
        return SellerCommssionType::findOrFail($id)->update([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'status' => $data['status'],
            'description' => $data['description'],
        ]);
    }
}
