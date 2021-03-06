@if(!config('settings.landing_page') || auth()->check())
<div style="position: relative">

  <div class="page-top-background with-carousel">
    <div class="background-overlay" id="parallax"></div>
  </div>

</div>
@endif

{{-- Start owl carousel --}}
<div class="owl-carousel theme-two">
{{-- Get games with max release in x days --}}
@php
$games = Cache::rememberForever('games_slider', function () {
    return \App\Models\Game::orderBy('release_date','desc')->with('metacritic','platform','listingsCount','cheapestListing')->groupBy('giantbomb_id')->where('release_date','<', date('Y-m-d', strtotime("+" . config('settings.frontpage_carousel_day') . " days")) )->limit(12)->get();
});
@endphp

@foreach($games as $game)

@php
// Get different platforms for the game
$different_platforms = Cache::remember('different_platforms_'.$game->giantbomb_id, '900', function () use ($game) {
    return \App\Models\Game::where('giantbomb_id', $game->giantbomb_id )->where('id', '!=', $game->id)->with('platform')->get();
});
@endphp
	{{-- Start Game Item --}}
  <div class="game-carousel hvr-grow-shadow2">
    <a href="{{ $game->url_slug}}" class="link">
      <div class="owl-lazy {{ $game->release_date->diffInDays( Carbon::now() , false) < 0 ? 'grayscale' : '' }}" data-src="{{$game->image_carousel}}" alt="{{$game->name}}"></div>

      <div class="overlay">
				{{-- Release in x days --}}
        @if($game->release_date->diffInDays( Carbon::now() , false) < 0)
          <div class="caption-release">
            <i class="fa fa-clock" aria-hidden="true"></i> {{ trans_choice('general.carousel.release_in',  $game->release_date->diffInDays( Carbon::now()) , ['days' => $game->release_date->diffInDays( Carbon::now())]) }}
          </div>
        @endif
        <div class="caption-metacritic">
          {{-- Start Metascore --}}
          @if(isset($game->metacritic) && $game->metacritic->score)
            <div class="metascore round {{$game->metacritic->score_class}}">
              <span class="score">{{$game->metacritic->score}}</span>
            </div>
          @endif
          {{-- End Metascore --}}
        </div>
        <div class="caption">
          <div class="caption-labels">
            {{-- Start Consoles --}}
              <x-platform-label :platform="$game->platform"></x-platform-label>
              @foreach($different_platforms as $console_details)
              {{-- Show only first 2 consoles --}}
              @if($loop->iteration < 2)
                <x-platform-label :platform="$console_details->platform" style="margin-right:6px;"></x-platform-label>
                {{-- Show remaining console count --}}
                @if($loop->iteration === 1 && $loop->remaining > 0)
                  <span class="label platform-label" style="background-color: #222121;">+{{ $loop->remaining }} <i class="fa fa-cube"></i></span>
                @endif
              @endif
            @endforeach
            {{-- End Consoles --}}
            {{-- Listings count --}}
            @if($game->listings_count > 0)
              <div class="listings-count"><i class="fa {{ $game->listings_count === 1 ? 'fa-tag' : 'fa-tags' }}"></i> {{$game->listings_count}}</div>
            @endif
          </div>
          <div class="post-title m-b-5">{{$game->name}}</div>
					{{-- Cheapest Listing --}}
          @if($game->cheapest_listing)
						<p>{!! trans('general.carousel.starting_from', ['price' => $game->cheapest_listing]) !!}</p>
          @else
            <p>{{ trans('general.carousel.no_listings') }}</p>
          @endif
        </div>
      </div>
    </a>
  </div>
	{{-- End Game Item --}}
@endforeach
</div>
{{-- End owl carousel --}}
