<?php

use Webkul\RMA\Models\RMACustomField;

use function Pest\Laravel\get;

// ============================================================================
// Custom Field Rules
// ============================================================================

it('should give a required custom field the required validator, not a boolean', function () {
    $field = RMACustomField::create([
        'code' => fake()->unique()->lexify('field_????'),
        'label' => 'Delivered',
        'type' => 'text',
        'status' => true,
        'is_required' => true,
        'position' => 1,
    ]);

    $this->loginAsCustomer();

    $html = get(route('shop.customers.account.rma.create'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('name="customAttributes['.$field->id.']"')
        ->toContain('rules="required"')
        ->not->toContain('rules="1"');
});
