<div class="bg-background box border-none shadow-sm">
    <div class="border-b p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-secondary text-2xl">
                @lang('shop::app.rma.customer-rma-index.heading')
            </h1>

            <x-shop::ui.button
                color="primary"
                variant="soft"
                icon="lucide-plus"
                size="sm"
                href="{{ route('shop.customers.account.rma.create') }}"
            >
                @lang('shop::app.rma.customer.create.heading')
            </x-shop::ui.button>
        </div>
    </div>

    <x-shop::datagrid :src="route('shop.customers.account.rma.index')" />
</div>
