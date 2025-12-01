<x-app-layout>

    <div class="max-w-4xl mx-auto mt-12 mb-24 tracking-tight">

        <div class="border border-white/10 bg-black/40 backdrop-blur-sm p-10 rounded-none shadow-xl">

            <h1 class="text-xl text-white font-semibold mb-10">
                :: user_profile_settings
            </h1>

            <div class="space-y-12">

                {{-- ============================
                     UPDATE PROFILE INFORMATION
                ============================= --}}
                <section>
                    <h2 class="text-white flex items-center gap-2 mb-2">
                        <span class="text-yellow-400">[*]</span>
                        update_profile_information
                    </h2>

                    <p class="text-xs text-zinc-500 mb-4">
                        edit your account display identity.
                    </p>

                    <div class="p-1">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </section>

                <div class="border-t border-zinc-800"></div>

                {{-- ============================
                     UPDATE PASSWORD
                ============================= --}}
                <section>
                    <h2 class="text-white flex items-center gap-2 mb-2">
                        <span class="text-sky-400">[#]</span>
                        update_password_key
                    </h2>

                    <p class="text-xs text-zinc-500 mb-4">
                        change your account password.
                    </p>

                    <div class="p-1">
                        <livewire:profile.update-password-form />
                    </div>
                </section>

                <div class="border-t border-zinc-800"></div>

                {{-- ============================
                     DELETE ACCOUNT
                ============================= --}}
                <section>
                    <h2 class="text-red-300 flex items-center gap-2 mb-2">
                        <span class="text-red-500">[!]</span>
                        delete_user_account
                    </h2>

                    <p class="text-xs text-red-300/70 mb-4">
                        this action cannot be undone.
                    </p>

                    <div class="border border-red-900/30 bg-red-900/5 p-6 rounded-none">
                        <livewire:profile.delete-user-form />
                    </div>
                </section>

            </div>

        </div>

    </div>

</x-app-layout>
