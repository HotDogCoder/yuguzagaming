<?php

namespace App\Observers;

use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingDeleted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ListingObserver
{
    /**
     * Listen to the Listing deleting event.
     *
     * @param Listing $listing
     * @return void
     */
    public function deleting(Listing $listing): void
    {
        // Check status of listing
        if ($listing->status >= 1) {
            abort('404');
        }

        // Delete all trade games
        \DB::table('game_trade')->where('listing_id', $listing->id)->delete();

        // Notifications to all open offer user and delete all offers
        foreach ($listing->offers as $offer) {
            if ($offer->status === 0 && ! $offer->declined) {
                $offer_user = User::find($offer->user_id);
                $offer_user->notify(new ListingDeleted($offer));
                $offer->declined = 1;
                $offer->decline_note = 'listings.general.deleted';
                $offer->closed_at = new Carbon;
                $offer->save();
            }
        }

        // Remove last listings cache
        Cache::forget('last_24_listings');
    }

    /**
     * Listen to the Listing created event.
     *
     * @param Listing $listing
     * @return void
     */
    public function created(Listing $listing): void
    {
        // Remove last listings cache
        Cache::forget('last_24_listings');
    }

    /**
     * Listen to the Listing deleting event.
     *
     * @param Listing $listing
     * @return void
     */
    public function updated(Listing $listing): void
    {
        // Remove last listings cache
        Cache::forget('last_24_listings');
    }
}
