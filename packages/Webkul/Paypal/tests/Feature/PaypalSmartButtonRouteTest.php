<?php

use Symfony\Component\Routing\Exception\RouteNotFoundException;

// ============================================================================
// Smart Button Route Names
// ============================================================================

it('should keep kebab-case smart button names for themed checkout views', function () {
    expect(route('paypal.smart-button.create-order'))
        ->toBe(route('paypal.smart_button.create_order'))
        ->and(route('paypal.smart-button.capture-order'))
        ->toBe(route('paypal.smart_button.capture_order'));
});

it('should not type error when resolving a missing named route with a scalar parameter', function () {
    route('paypal.smart-button.unknown-route-name', 1);
})->throws(RouteNotFoundException::class);

it('should keep kebab-case customer print invoice route name for themed order views', function () {
    expect(route('shop.customers.account.orders.print-invoice', 42))
        ->toBe(route('shop.customers.account.orders.print_invoice', 42));
});
