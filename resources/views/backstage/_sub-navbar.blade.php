<div class="bg-light p-xs-y-4 border-b">
    <div class="container">
        <div class="flex-spaced flex-y-center">
            <h1 class="text-lg">
                <strong class="wt-medium">{{ $concert->title }}</strong>
                <span class="m-xs-x-2 text-dark-muted">/</span>
                <span class="wt-normal text-dark-soft text-base">
                    {{ $concert->formatted_date }}
                </span>
            </h1>
            <div class="text-base">
                <a href="{{ route('backstage.published-concert-orders.index', $concert) }}" class="{{ $page == 'orders' ? 'wt-bold inline-block m-xs-r-4' : 'inline-block m-xs-r-4' }}">
                    Orders
                </a>
                <a href="{{ route('backstage.concert-messages.create', $concert) }}" class="{{ $page == 'message' ? 'wt-bold inline-block' : 'inline-block' }}">
                    Message Attendees
                </a>
            </div>
        </div>
    </div>
</div>
