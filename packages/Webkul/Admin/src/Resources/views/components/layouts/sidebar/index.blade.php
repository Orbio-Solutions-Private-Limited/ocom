<div class="admin-sidebar fixed top-14 bottom-0 z-1000 flex w-67.5 flex-col pt-4 shadow-[0px_8px_10px_0px_rgba(0,0,0,0.2)] transition-all duration-300 group-[.sidebar-collapsed]/container:w-17.5 max-lg:hidden">
    <div class="journal-scroll min-h-0 flex-1 overflow-y-auto group-[.sidebar-collapsed]/container:overflow-visible">
        <nav class="grid w-full gap-2">
            <!-- Navigation Menu -->
            @foreach (menu()->getItems('admin') as $menuItem)
                <div
                    class="px-4 group/item {{ $menuItem->isActive() ? 'active' : 'inactive' }}"
                    onmouseenter="adjustSubMenuPosition(event)"
                >
                    <a
                        href="{{ $menuItem->getUrl() }}"
                        class="sidebar-link flex gap-2.5 p-1.5 items-center cursor-pointer rounded-lg {{ $menuItem->isActive() ? 'sidebar-active' : '' }} peer"
                    >
                        <span class="{{ $menuItem->getIcon() }} text-2xl {{ $menuItem->isActive() ? 'text-white' : 'text-sidebar-text'}}"></span>

                        <p class="font-normal whitespace-nowrap group-[.sidebar-collapsed]/container:hidden {{ $menuItem->isActive() ? 'text-white' : 'text-sidebar-text'}}">
                            {{ $menuItem->getName() }}
                        </p>
                    </a>

                    @if ($menuItem->haveChildren())
                        <div class="{{ $menuItem->isActive() ? 'grid!' : '' }} hidden min-w-45 ltr:pl-8 rtl:pr-8 pb-2 rounded-b-lg z-100 overflow-hidden group-[.sidebar-collapsed]/container:hidden! group-[.sidebar-collapsed]/container:fixed group-[.sidebar-collapsed]/container:ltr:left-17.5! group-[.sidebar-collapsed]/container:rtl:right-17.5! group-[.sidebar-collapsed]/container:p-0 group-[.sidebar-collapsed]/container:bg-white dark:group-[.sidebar-collapsed]/container:bg-gray-900 group-[.sidebar-collapsed]/container:border group-[.sidebar-collapsed]/container:ltr:rounded-r-lg group-[.sidebar-collapsed]/container:rtl:rounded-l-lg group-[.sidebar-collapsed]/container:border-gray-300 group-[.sidebar-collapsed]/container:dark:border-gray-800 group-[.sidebar-collapsed]/container:rounded-none group-[.sidebar-collapsed]/container:ltr:shadow-[34px_10px_14px_rgba(0,0,0,0.01),19px_6px_12px_rgba(0,0,0,0.03),9px_3px_9px_rgba(0,0,0,0.04),2px_1px_5px_rgba(0,0,0,0.05),0px_0px_0px_rgba(0,0,0,0.05)] group-[.sidebar-collapsed]/container:rtl:shadow-[-34px_10px_14px_rgba(0,0,0,0.01),-19px_6px_12px_rgba(0,0,0,0.03),-9px_3px_9px_rgba(0,0,0,0.04),-2px_1px_5px_rgba(0,0,0,0.05),-0px_0px_0px_rgba(0,0,0,0.05)] group-[.sidebar-collapsed]/container:max-h-[calc(100vh-3.5rem)] group-[.sidebar-collapsed]/container:overflow-y-auto group-[.sidebar-collapsed]/container:group-hover/item:grid! group-[.inactive]/item:hidden group-[.inactive]/item:fixed group-[.inactive]/item:ltr:left-67.5 group-[.inactive]/item:rtl:right-67.5 group-[.inactive]/item:p-0 group-[.inactive]/item:bg-white dark:group-[.inactive]/item:bg-gray-900 group-[.inactive]/item:border group-[.inactive]/item:ltr:rounded-r-lg group-[.inactive]/item:rtl:rounded-l-lg group-[.inactive]/item:border-gray-300 group-[.inactive]/item:dark:border-gray-800 group-[.inactive]/item:rounded-none group-[.inactive]/item:ltr:shadow-[34px_10px_14px_rgba(0,0,0,0.01),19px_6px_12px_rgba(0,0,0,0.03),9px_3px_9px_rgba(0,0,0,0.04),2px_1px_5px_rgba(0,0,0,0.05),0px_0px_0px_rgba(0,0,0,0.05)] group-[.inactive]/item:rtl:shadow-[-34px_10px_14px_rgba(0,0,0,0.01),-19px_6px_12px_rgba(0,0,0,0.03),-9px_3px_9px_rgba(0,0,0,0.04),-2px_1px_5px_rgba(0,0,0,0.05),-0px_0px_0px_rgba(0,0,0,0.05)] group-[.inactive]/item:max-h-[calc(100vh-3.5rem)] group-[.inactive]/item:overflow-y-auto group-[.inactive]/item:group-hover/item:grid! journal-scroll">
                            @foreach ($menuItem->getChildren() as $subMenuItem)
                                <a
                                    href="{{ $subMenuItem->getUrl() }}"
                                    class="sidebar-sublink px-5 py-2.5 {{ $subMenuItem->isCurrent() ? 'sidebar-active' : '' }}"
                                >
                                    {{ $subMenuItem->getName() }}
                                </a>

                                @if ($subMenuItem->haveChildren())
                                    <div class="grid ltr:pl-5 rtl:pr-5 pb-1">
                                        @foreach ($subMenuItem->getChildren() as $subSubMenuItem)
                                            <a
                                                href="{{ $subSubMenuItem->getUrl() }}"
                                                class="sidebar-sublink px-5 py-2.5 {{ $subSubMenuItem->isCurrent() ? 'sidebar-active' : '' }}"
                                            >
                                                {{ $subSubMenuItem->getName() }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    <v-sidebar-collapse></v-sidebar-collapse>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-sidebar-collapse-template"
    >
        <div
            class="shrink-0 w-full cursor-pointer border-t border-gray-200 bg-white px-4 transition-all duration-300 hover:bg-sidebar-hover"
            @click="toggle"
        >
            <div class="flex items-center gap-2.5 p-1.5">
                <span
                    class="icon-collapse text-2xl text-sidebar-text transition-all"
                    :class="[isCollapsed ? 'ltr:rotate-180 rtl:rotate-0' : 'ltr:rotate-0 rtl:rotate-180']"
                ></span>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-sidebar-collapse', {
            template: '#v-sidebar-collapse-template',

            data() {
                return {
                    isCollapsed: {{ request()->cookie('sidebar_collapsed') ?? 0 }},
                }
            },

            methods: {
                toggle() {
                    this.isCollapsed = parseInt(this.isCollapsedCookie()) ? 0 : 1;

                    var expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'sidebar_collapsed=' + this.isCollapsed + '; path=/; expires=' + expiryDate.toGMTString();

                    this.$root.$refs.appLayout.classList.toggle('sidebar-collapsed', this.isCollapsed);
                    this.$root.$refs.appLayout.classList.toggle('sidebar-not-collapsed', ! this.isCollapsed);
                },

                isCollapsedCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'sidebar_collapsed') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>

    <script>
        const headerHeight = 56;

        const adjustSubMenuPosition = (event) => {
            let menuContainer = event.currentTarget;

            let subMenuContainer = menuContainer.lastElementChild;

            if (subMenuContainer) {
                const menuTopOffset = menuContainer.getBoundingClientRect().top;

                const subMenuHeight = subMenuContainer.offsetHeight;

                const availableHeight = window.innerHeight - menuTopOffset;

                let subMenuTopOffset = menuTopOffset;

                if (subMenuHeight > availableHeight) {
                    subMenuTopOffset = menuTopOffset - (subMenuHeight - availableHeight);
                }

                subMenuContainer.style.top = `${Math.max(headerHeight, subMenuTopOffset)}px`;
            }
        };
    </script>
@endpushOnce
