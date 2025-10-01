<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Class FollowController
 *
 * Manages following and unfollowing actions between users and vendors.
 *
 * @package App\Http\Controllers
 */
class FollowController extends Controller
{
    /**
     * FollowController constructor.
     *
     * Ensures that the user is authenticated before they can perform any follow-related actions.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Follow a vendor.
     *
     * Allows the authenticated user to follow a specified vendor.
     *
     * @param User $vendor The vendor to be followed.
     * @return RedirectResponse Redirects back with a success or error message.
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
     * Unfollow a vendor.
     *
     * Allows the authenticated user to unfollow a specified vendor.
     *
     * @param User $vendor The vendor to be unfollowed.
     * @return RedirectResponse Redirects back with a success message.
     */
    public function unfollow(User $vendor): RedirectResponse
    {
        auth()->user()->unfollow($vendor);

        return back()->with('success', "You have unfollowed {$vendor->name}.");
    }

    /**
     * Show a vendor's followers.
     *
     * Displays a paginated list of users who are following the specified vendor.
     *
     * @param User $vendor The vendor whose followers are to be displayed.
     * @return View Returns the view with the vendor's followers.
     */
    public function followers(User $vendor): View
    {
        if (!$vendor->isVendor()) {
            abort(404);
        }

        $followers = $vendor->followers()->paginate(20);

        return view('vendor.followers', compact('vendor', 'followers'));
    }

    /**
     * Show the authenticated user's following list.
     *
     * Displays a paginated list of vendors that the authenticated user is following.
     *
     * @return View Returns the view with the list of followed vendors.
     */
    public function following(): View
    {
        $following = auth()->user()->following()->paginate(20);

        return view('following', compact('following'));
    }
}
