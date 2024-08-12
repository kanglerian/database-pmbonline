<div class="my-8 flex-col justify-center items-center px-5">
    <div class="w-full flex items-center justify-center">
        <a href="{{ route('welcome') }}" class="flex items-center gap-5">
            <img src="{{ asset('img/esaunggul-logo.png') }}" alt="Universitas Esa Unggul" class="h-16">
        </a>
    </div>
    <div class="w-full flex items-center justify-center">
        <div class="w-full lg:w-7/12 mt-6 p-8 bg-white border border-gray-100 rounded-3xl">
            {{ $slot }}
        </div>
    </div>
</div>
