<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Follow a vendor
     */
    public function follow(User $vendor): RedirectResponse
    {
        if (!$vendor->isVendor()) {
            return back()->with('error', 'You can only follow vendors.');
        }

        if (auth()->user()->id === $vendor->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        auth()->user()->follow($vendor);

        return back()->with('success', "You are now following {$vendor->name}!");
    }

    /**
     * Unfollow a vendor
     */
    public function unfollow(User $vendor): RedirectResponse
    {
        auth()->user()->unfollow($vendor);

        return back()->with('success', "You have unfollowed {$vendor->name}.");
    }

    /**
     * Show vendor's followers
     */
    public function followers(User $vendor)
    {
        if (!$vendor->isVendor()) {
            abort(404);
        }

        $followers = $vendor->followers()->paginate(20);

        return view('vendor.followers', compact('vendor', 'followers'));
    }

    /**
     * Show user's following list
     */
    public function following()
    {
        $following = auth()->user()->following()->paginate(20);

        return view('following', compact('following'));
    }
}
