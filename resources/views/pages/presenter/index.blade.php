<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-5 pb-3">
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Presenter') }}
            </h2>
            <div class="flex flex-wrap justify-center items-center gap-3 px-2 text-gray-600">
                <div class="flex bg-gray-200 px-4 py-2 text-sm rounded-xl items-center gap-2">
                    <i class="fa-solid fa-database"></i>
                    <h2 id="count_filter">{{ $total }}</h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            @if (session('message'))
                <div id="alert" class="mx-2 flex items-center p-4 mb-4 bg-emerald-400 text-white rounded-xl"
                    role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <div class="ml-3 text-sm font-reguler">
                        {{ session('message') }}
                    </div>
                </div>
            @endif
            <div class="px-2">
                <a href="{{ route('presenters.create') }}"
                    class="bg-lp3i-100 hover:bg-lp3i-200 px-4 py-2 text-sm rounded-xl text-white"><i
                        class="fa-solid fa-circle-plus"></i> Tambah Data</a>
            </div>
            <div class="bg-gray-50 overflow-hidden border rounded-3xl">
                <div class="p-8 bg-white border-b border-gray-200">
                    <div class="relative overflow-x-auto sm:rounded-xl">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3 bg-gray-50">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Nama lengkap
                                    </th>
                                    <th scope="col" class="px-6 py-3 bg-gray-50">
                                        No. Telpon (Whatsapp)
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($presenters as $key => $presenter)
                                    <tr class="border-b border-gray-200">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50">
                                            {{ $presenters->perPage() * ($presenters->currentPage() - 1) + $key + 1 }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $presenter->name }}
                                        </td>
                                        <td class="px-6 py-4 bg-gray-50">
                                            {{ $presenter->phone }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <form action="{{ route('presenters.status', $presenter->id) }}"
                                                method="GET" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    class="{{ $presenter->status ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-red-500 hover:bg-red-600' }} px-3 py-2 rounded-lg text-white transition-all ease-in-out">toggle</button>
                                            </form>
                                            <a href="{{ route('presenters.show', $presenter->id) }}"
                                                class="bg-blue-500 hover:bg-blue-600 px-3 py-2 rounded-lg text-white transition-all ease-in-out">detail</a>
                                            <a href="{{ route('presenters.edit', $presenter->id) }}"
                                                class="bg-amber-500 hover:bg-amber-600 px-3 py-2 rounded-lg text-white transition-all ease-in-out">edit</a>
                                            <form action="{{ route('presenters.destroy', $presenter->id) }}"
                                                method="post" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-lg text-white transition-all ease-in-out">delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="p-5">
                            {{ $presenters->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
