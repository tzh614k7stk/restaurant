<div class="bg-white border-b border-zinc-200" x-data="navbar()">
    <nav class="container mx-auto">

        <!-- menu bar with urls (or mobile drop down button) -->
        <div class="w-full h-16 flex items-center justify-between relative tracking-wide" :class="is_open ? 'border-b border-zinc-200' : ''" style="visibility: hidden;" x-ref="menu_bar">

            <!-- urls -->
            <div class="flex overflow-hidden relative justify-center" x-ref="menu_urls_row">
                <div class="flex items-center gap-4">
                    <template x-for="link in links" :key="link.id">
                        <a :href="link.href" :class="link.color" class="text-nowrap text-md font-medium transition-transform hover:scale-105 px-3 py-2" x-text="link.text"></a>
                    </template>
                </div>
            </div>

            <!-- mobile drop down button -->
            <div class="flex overflow-hidden relative" x-ref="menu_column_button">
                <a @click="toggle_menu" class="text-zinc-900 text-nowrap text-sm font-medium transition-transform hover:scale-105 px-3 py-2">Menu</a>
            </div>

        </div>

        <!-- mobile drop down urls -->
        <div class="w-full space-y-1" x-cloak x-show="is_open" @click.away="is_open = false">
            <template x-for="link in links" :key="link.id">
                <a :href="link.href" :class="link.color" class="break-all text-sm font-medium transition-transform hover:scale-105 px-3 py-2 block" x-text="link.text"></a>
            </template>
        </div>

    </nav>

    <!-- data -->
    <script>
        function navbar() {
            return {
                init_done: false,
                is_open: false,
                is_overflowing: false,
                links: [
                    { id: 1, href: '/', text: 'Reservations', color: 'text-zinc-900', },
                    { id: 2, href: '/about', text: 'Information', color: 'text-zinc-900', },
                    @auth
                        @if(DB::table('employees')->where('user_id', Auth::id())->exists())
                            { id: 3, href: '/employees', text: 'Employees', color: 'text-zinc-900', },
                        @endif
                    @endauth
                    @guest
                        { id: 3, href: '/login', text: 'Account', color: 'text-zinc-900', },
                    @endguest
                ],
                toggle_menu() {
                    this.is_open = !this.is_open;
                },
                check_overflow() {
                    const menu_bar = this.$refs.menu_bar;
                    const menu_urls_row = this.$refs.menu_urls_row;
                    const menu_urls_container = menu_urls_row.firstElementChild;
                    const menu_column_button = this.$refs.menu_column_button;

                    //reset to default
                    menu_urls_row.style.width = 'auto';
                    menu_column_button.style.width = 'auto';
                    menu_urls_row.style.visibility = 'visible';
                    menu_column_button.style.visibility = 'visible';

                    //force reflow
                    void menu_bar.offsetWidth;

                    //check overflow
                    this.is_overflowing = menu_urls_container.getBoundingClientRect().width > menu_bar.clientWidth;

                    if (!this.is_overflowing)
                    {
                        this.is_open = false;
                        menu_urls_row.style.visibility = 'visible';
                        menu_column_button.style.visibility = 'hidden';
                        menu_column_button.style.width = '0px';
                        menu_urls_row.style.width = menu_bar.clientWidth + 'px';
                    }
                    else
                    {
                        menu_urls_row.style.visibility = 'hidden';
                        menu_column_button.style.visibility = 'visible';
                        menu_urls_row.style.width = '0px';
                        menu_column_button.style.width = menu_bar.clientWidth + 'px';
                    }

                    if (!this.init_done)
                    {
                        menu_bar.style.visibility = 'visible';
                        this.init_done = true;
                    }
                },
                init() {
                    this.$nextTick(() => {
                        this.check_overflow();
                        window.addEventListener('resize', () => {
                            setTimeout(this.check_overflow.bind(this), 50); //make sure dom is updated
                        });
                    });
                },
            }
        }
    </script>
</div>
