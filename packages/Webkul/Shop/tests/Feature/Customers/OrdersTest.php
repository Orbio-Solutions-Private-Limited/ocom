<?php

use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

// ============================================================================
// Index
// ============================================================================

it('should return the customer orders page', function () {
    $this->loginAsCustomer();

    get(route('shop.customers.account.orders.index'))
        ->assertOk()
        ->assertSeeText(trans('shop::app.customers.account.orders.title'));
});

it('should list only the orders of the signed-in customer', function () {
    $customer = Customer::factory()->create();

    $order = $this->createOrder(customer: $customer);

    $this->createOrder();

    $this->loginAsCustomer($customer);

    $records = getJson(route('shop.customers.account.orders.index'), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->json('records');

    expect(collect($records)->pluck('id')->all())->toBe([$order->id]);
});

// ============================================================================
// View
// ============================================================================

it('should show an order to the customer who placed it', function () {
    $customer = Customer::factory()->create();

    $product = $this->createSimpleProduct();

    $order = $this->createOrder(customer: $customer, items: [[
        'product' => $product,
        'additional' => [
            'product_id' => $product->id,
            'quantity' => 1,
            'attributes' => [],
        ],
    ]]);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText($order->increment_id)
        ->assertSeeText($order->items->first()->name);
});

it('should not show the order of another customer', function () {
    $order = $this->createOrder();

    $this->loginAsCustomer();

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertNotFound();
});

it('should show an order after its catalog product has been removed', function () {
    $customer = Customer::factory()->create();

    $product = $this->createSimpleProduct();

    $order = $this->createOrder(customer: $customer, items: [['product' => $product]]);

    $order->items->first()->update(['product_id' => null]);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText($order->increment_id);
});

it('should show an order whose stored option labels are locale arrays or loose strings', function () {
    $customer = Customer::factory()->create();

    $product = $this->createSimpleProduct();

    $order = $this->createOrder(customer: $customer, items: [[
        'product' => $product,
        'additional' => [
            'product_id' => $product->id,
            'quantity' => 1,
            'attributes' => [
                'Color' => 'Red',
                [
                    'attribute_name' => ['en' => 'Size'],
                    'option_label' => ['en' => 'M'],
                ],
                [
                    'attribute_type' => 'file',
                    'attribute_name' => 'Artwork',
                    'option_label' => ['path' => 'orders/artwork.pdf'],
                ],
            ],
        ],
    ]]);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText($order->increment_id)
        ->assertSeeText('Size')
        ->assertSeeText('M');
});

it('should show an order when the catalog product type is no longer registered', function () {
    $customer = Customer::factory()->create();

    $product = $this->createSimpleProduct();

    $order = $this->createOrder(customer: $customer, items: [['product' => $product]]);

    $product->update(['type' => 'not-a-real-type']);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText($order->increment_id);
});

it('should show a configurable order when the variant child item is missing', function () {
    $customer = Customer::factory()->create();

    $product = $this->createConfigurableProduct();

    $order = $this->createOrder(customer: $customer, items: [[
        'product' => $product,
        'type' => 'configurable',
        'sku' => $product->sku,
        'name' => $product->name,
    ]]);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText($order->increment_id)
        ->assertSeeText($product->sku);
});

// ============================================================================
// Cancel
// ============================================================================

it('should cancel a pending order and mark its items canceled', function () {
    $customer = Customer::factory()->create();

    $order = $this->createOrder(customer: $customer, items: [['product' => $this->createSimpleProduct(), 'qty_ordered' => 2]]);

    $this->loginAsCustomer($customer);

    post(route('shop.customers.account.orders.cancel', $order->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => Order::STATUS_CANCELED,
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'qty_canceled' => 2,
    ]);
});

it('should refuse to cancel an order that has already been invoiced', function () {
    $customer = Customer::factory()->create();

    $order = $this->createOrder(customer: $customer, items: [['product' => $this->createSimpleProduct()]]);

    $this->invoiceOrder($order);

    $this->loginAsCustomer($customer);

    post(route('shop.customers.account.orders.cancel', $order->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => Order::STATUS_PROCESSING,
    ]);
});

it('should not cancel the order of another customer', function () {
    $order = $this->createOrder();

    $this->loginAsCustomer();

    post(route('shop.customers.account.orders.cancel', $order->id))
        ->assertNotFound();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => Order::STATUS_PENDING,
    ]);
});

// ============================================================================
// Reorder
// ============================================================================

it('should put the items of a previous order back into the cart', function () {
    $customer = Customer::factory()->create();

    $product = $this->createSimpleProduct();

    $order = $this->createOrder(['status' => Order::STATUS_COMPLETED], [['product' => $product, 'qty_ordered' => 2]], $customer);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.reorder', $order->id))
        ->assertRedirect(route('shop.checkout.cart.index'));

    $this->assertCartHasProduct($product->id, 2);
});

it('should not reorder the order of another customer', function () {
    $order = $this->createOrder(['status' => Order::STATUS_COMPLETED], [['product' => $this->createSimpleProduct()]]);

    $this->loginAsCustomer();

    get(route('shop.customers.account.orders.reorder', $order->id))
        ->assertNotFound();

    $this->assertCartIsEmpty();
});

// ============================================================================
// Invoice
// ============================================================================

it('should expose the kebab-case print invoice route name for themed order views', function () {
    $invoiceId = 42;

    expect(route('shop.customers.account.orders.print-invoice', $invoiceId))
        ->toBe(route('shop.customers.account.orders.print_invoice', $invoiceId));
});

it('should let the customer download the invoice of their order', function () {
    $customer = Customer::factory()->create();

    $order = $this->createOrder(customer: $customer);

    $invoice = $this->invoiceOrder($order);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.print_invoice', $invoice->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('should let the customer download the invoice using the kebab-case route name', function () {
    $customer = Customer::factory()->create();

    $order = $this->createOrder(customer: $customer);

    $invoice = $this->invoiceOrder($order);

    $this->loginAsCustomer($customer);

    get(route('shop.customers.account.orders.print-invoice', $invoice->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('should not serve the invoice of another customer', function () {
    $invoice = $this->invoiceOrder($this->createOrder());

    $this->loginAsCustomer();

    get(route('shop.customers.account.orders.print_invoice', $invoice->id))
        ->assertNotFound();
});
