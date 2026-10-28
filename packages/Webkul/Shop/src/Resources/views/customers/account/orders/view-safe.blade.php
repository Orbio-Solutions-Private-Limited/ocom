<x-shop::layouts.account>
    <x-slot:title>
        @lang('shop::app.customers.account.orders.view.page-title', ['order_id' => $order->increment_id])
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="flex items-center justify-between">
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.orders.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium ltr:ml-2.5 rtl:mr-2.5 max-md:text-xl max-sm:text-base md:ltr:ml-0 md:rtl:mr-0">
                @lang('shop::app.customers.account.orders.view.page-title', ['order_id' => $order->increment_id])
            </h2>
        </div>

        <div class="mt-8 overflow-x-auto rounded-xl border">
            <table class="w-full text-left">
                <thead class="border-b border-zinc-200 bg-zinc-100 text-sm text-black">
                    <tr class="[&>*]:px-6 [&>*]:py-4 [&>*]:font-medium">
                        <th scope="col">
                            @lang('shop::app.customers.account.orders.view.information.sku')
                        </th>

                        <th scope="col">
                            @lang('shop::app.customers.account.orders.view.information.product-name')
                        </th>

                        <th scope="col">
                            @lang('shop::app.customers.account.orders.view.information.price')
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b bg-white font-medium [&>*]:px-6 [&>*]:py-4">
                            <td>{{ $item->sku }}</td>

                            <td>{{ $item->name }}</td>

                            <td>{{ core()->formatPrice((float) $item->price, $order->order_currency_code) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-shop::layouts.account>
