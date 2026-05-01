<x-login-layout title="Login">
    <main class="w-full">
        <section class="container mx-auto px-4 py-32">
            <div class="max-w-[600px] mx-auto">
                <div class="bg-[#ffffff] rounded-xl shadow-md overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <!-- Header -->
                            <h2 class="text-2xl font-semibold text-gray-800">Forgot Password</h2>
                        </div>
                        @if(session('success'))
                            <div class="bg-green-100 border border-green-300 text-green-800 rounded-lg p-4 mb-4">
                                {{ session('success') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('password.forgot.submit') }}" class="space-y-6">
                            @csrf
                            <!-- Username Login -->
                            <div id="loginFields">
                                <!-- Username | DEFAULT-->
                                <div id="usernameField">
                                    <x-form-input name="username" id="username" type="text" label="Username"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"/>
                                    <x-form-error name="username"/>
                                </div>

                                <!-- Email | HIDDEN -->
                                <div id="emailField" class="hidden">
                                    <x-form-input name="email" id="email" type="email" label="Email"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"/>
                                    <x-form-error name="email"/>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div>
                                <!-- Invalid login error -->
                                <x-form-error name="invalid"/>

                                <button type="submit" class="w-full bg-primary text-white hover:bg-primary/90 font-bold py-3 px-6 rounded-lg transition flex items-center justify-center">
                                    Submit
                                </button>
                            </div>

                            <div class="text-sm text-gray-600">
                                Forgot your username? Contact your school administrator for help.
                                <div>
                                    <a href="login" class="text-primary text-sm mt-1 inline-block font-extrabold hover:text-primary/80 transition">Back to login</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Show/hide password on hover -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordContainers = document.querySelectorAll('.password-container');
            
            passwordContainers.forEach(container => {
                const inputField = container.querySelector('input');
                const toggleIcon = container.querySelector('.password-toggle');
                
                // Hover to show pass
                toggleIcon.addEventListener('mouseenter', function() {
                    inputField.type = 'text';
                });
                
                // when mouse leaves, change it to password = hide
                toggleIcon.addEventListener('mouseleave', function() {
                    inputField.type = 'password';
                });
            });
        });
    </script>

</x-login-layout>