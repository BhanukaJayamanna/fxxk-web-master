@if ($seller->slug)

<a target="_blank" href="{{route('frontend.seller',$seller->slug)}}">{{@$seller->user->first_name}} {{@$seller->user->last_name}}</a>
@else
<a target="_blank" href="{{route('frontend.seller',base64_encode($seller->id))}}">{{@$seller->user->first_name}} {{@$seller->user->last_name}}</a>
@endif

