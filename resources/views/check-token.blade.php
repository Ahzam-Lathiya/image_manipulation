        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create a new token') }}
        </h2>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('token.create') }}">
                    @csrf

                    <!-- Email Address -->
                        <div>
                            <label for="email" :value="__('Your token name')"/>

                            <input id="tokenName" class="block mt-1 w-full" type="text" name="name"
                                     :value="old('name')" required autofocus/>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                                {{ __('Generate') }}
<!--
                            <x-button class="ml-3">
                            </x-button>
-->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
