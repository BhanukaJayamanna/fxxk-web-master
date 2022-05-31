@extends('backEnd.master')
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor mb-25">
        @if (isset($subscription))
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('seller.subscription_info') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="white-box single-summery">
                        <div class="d-block">
                            <h3>{{ __('seller.subscription_title') }}</h3>
                             <h1 class="gradient-color2 total_orders">{{ $subscription->pricing->name }}</h1>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="white-box single-summery">
                        <div class="d-block">
                            <h3>{{ __('common.price') }}</h3>
                             <h1 class="gradient-color2 total_orders">{{ auth()->user()->SellerAccount->subscription_type == "monthly" ? single_price($subscription->pricing->monthly_cost) : single_price($subscription->pricing->yearly_cost) }}</h1>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="white-box single-summery">
                        <div class="d-block">
                            <h3>{{ __('seller.next_payment_before') }}</h3>
                            
                             <h1 class="gradient-color2 total_orders">
                                @if($subscription->is_paid == 1 && $subscription->last_payment_date != null)
                                    {{ auth()->user()->SellerAccount->subscription_type == "monthly" ? date(app('general_setting')->dateFormat->format, strtotime(Carbon\Carbon::createFromFormat('Y-m-d', $subscription->last_payment_date)->addDay(30))) : date(app('general_setting')->dateFormat->format, strtotime(Carbon\Carbon::createFromFormat('Y-m-d', $subscription->last_payment_date)->addDay(365))) }}
                                
                                @elseif (isset($subscription) && auth()->user()->SellerAccount->seller_commission_id == 3 &&
                                    $subscription->is_paid == 0 && auth()->user()->role->type != "superadmin" && auth()->user()->role->type != "admin" && auth()->user()->role->type != "staff")
                                    {{ __('common.pay_first_for_subcription') }}
                                @endif
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('seller.subscription_payments') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="table-responsive">
                                <table class="table Crm_table_active2">
                                    <thead>
                                        <tr>
                                            <th>{{ __('common.date') }}</th>
                                            <th>{{ __('common.name') }}</th>
                                            <th>{{ __('common.txn') }}</th>
                                            <th>{{ __('common.payment_type') }}</th>
                                            <th>{{ __('common.total_amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($subscription))
                                            @foreach ($subscription_payment as $key => $payment)
                                                <tr>
                                                    <td class="nowrap">{{ date(app('general_setting')->dateFormat->format, strtotime($subscription->last_payment_date)) }}</td>
                                                    <td>{{ $payment->title }}</td>
                                                    <td>{{ $payment->subscription_payment->txn_id }}</td>
                                                    <td>
                                                        {{ auth()->user()->SellerAccount->subscription_type }}
                                                    </td>
                                                    <td>
                                                        {{ auth()->user()->SellerAccount->subscription_type == "monthly" ? single_price($subscription->pricing->monthly_cost) : single_price($subscription->pricing->yearly_cost) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
