@extends('shop::layouts.account')

@section('page_title')
    @lang('shop::app.customers.account.rma.view.id') #{{ $rma->id }}
@endsection

@visual_content

@include('shop::templates.account.rma-view')

@end_visual_content
