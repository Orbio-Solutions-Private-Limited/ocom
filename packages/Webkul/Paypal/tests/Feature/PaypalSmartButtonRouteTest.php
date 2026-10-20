<?php

// ============================================================================
// Smart Button Route Names
// ============================================================================

it('should keep kebab-case smart button names for themed checkout views', function () {
    expect(route('paypal.smart-button.create-order'))
        ->toBe(route('paypal.smart_button.create_order'))
        ->and(route('paypal.smart-button.capture-order'))
        ->toBe(route('paypal.smart_button.capture_order'));
});
