<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600&family=Fredoka+One&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="min-h-screen bg-background">
    <div class="min-h-screen grid grid-cols-1 md:grid-cols-10">

        <!-- LEFT SIDE (Branding) -->
        <div class="hidden md:flex md:col-span-3 flex-col items-center justify-center bg-primary text-white p-10">

            <img src="/images/Logo.png" alt="Bookworms Logo" class="w-40 mb-6">

            <h1 class="font-display text-pink-200 text-4xl mb-4">Bookworms</h1>
            <p class="font-script text-center text-lg max-w-md">
                Really cool slogan 
            </p>
        </div>

        <!-- RIGHT SIDE (Auth Form) -->
        <div class="flex items-center justify-center p-6 md:col-span-7">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
