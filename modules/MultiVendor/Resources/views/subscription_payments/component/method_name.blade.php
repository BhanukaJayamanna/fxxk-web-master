@if ($subscription->item_details)
<a href="{{ asset(asset_path($subscription->item_details->image_src)) }}" target="_blank">{{ @$subscription->transaction->payment_method }}</a>
@else
    {{ @$subscription->transaction->payment_method }}
@endif
