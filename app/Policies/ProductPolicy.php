<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(User $user , $ability)
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user)
    {
        return true ;
    }

    public function view(User $user , Product $product) :bool
    {
        if($product->status === 'active' && $product->vendor?->isVerified)
        {
            return true;
        }


        if($user->isVendor() && $user->vendor->id == $product->vendor_id)
        {
            return true;
        }
        return false;
    }

    public function create(User $user) :bool
    {
        return $user->isVendor() && $user->vendor !== null;
    }

    public function update(User $user , Product $product)
    {
        if($user->isVendor() && $user->vendor->id === $product->vendor_id)
        {
            return Response::allow();
        }

        return Response::deny('you can only update your own product');
    }

    public function delete(User $user, Product $product): Response
    {
        if ($user->isVendor() && $user->vendor?->id === $product->vendor_id) {
            return Response::allow();
        }

        return Response::deny('You can only delete your own products.');
    }


}
