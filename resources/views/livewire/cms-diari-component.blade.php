<div class="px-2">
    <div x-data="{ open: @entangle('deleter') }">
        @include('partials.deleterModal')
    </div>
    <livewire:toastr />

    <div class="flex justify-between mb-6">
        <h1 class="sm:text-4xl text-xl text-newgray-900 dark:text-newgray-300 font-semibold ">Green Diary</h1>

        <div class="flex items-center gap-4">
            @if ($untranslated)
                {{-- fills the English text of every diary that has none yet, see CmsDiariComponent::translateAll --}}
                <button type="button" wire:loading.attr="disabled" wire:target="translateAll"
                    wire:click="translateAll" onclick="confirm('Translate {{ $untranslated }} {{ Str::plural('diary', $untranslated) }} with DeepL? Only English text that is empty or still the Indonesian text is replaced.') || event.stopImmediatePropagation()"
                    class="inline-flex items-center flex-shrink-0 whitespace-nowrap px-4 sm:py-2 py-1 text-sm rounded border border-gray-300 dark:border-opacity-20 text-newgray-900 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-newgray-700">
                    <svg wire:loading wire:target="translateAll" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="translateAll">Translate missing English ({{ $untranslated }})</span>
                    <span wire:loading wire:target="translateAll">Translating...</span>
                </button>
            @endif
            <a href="{{ url('/cms/adddiary') }}" class="inline-flex sm:px-12 px-8 sm:py-2 py-1 rounded dark:hover:bg-newgray-900 dark:hover:border-gray-200 dark:hover:text-gray-200 hover:bg-white hover:text-newgray-900 border hover:border-newgray-900 bg-newgray-900 dark:bg-gray-100 text-newgray-100 dark:text-newgray-900">
                Add
            </a>
        </div>
    </div>

    <div class="text-left lg:w-1/4 w-full">
        <label class="text-gray-600 dark:text-gray-300 mr-2 text-sm" >Search </label>
        <input placeholder="" type="text" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border  py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20"  wire:model.debounce.300ms="search">
    </div>
    <div class="flex flex-col py-5">
        <div class="-my-2  sm:-mx-6 lg:-mx-8 ">
            <div class="py-2 align-middle inline-block w-full sm:px-6 lg:px-8 ">
                <div class="shadow  border-b border-gray-200 dark:border-gray-800 sm:rounded-lg dark:bg-opacity-10  dark:text-white " >
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 rounded-lg  ">
                    <thead >
                        <tr >
                            <th wire:click='sortingField("titleEN")' class="px-6 py-4 bg-gray-50 dark:bg-opacity-10  dark:text-white text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer sm:w-4/12 w-11/12">
                               <div class="flex space-x-1">
                                   <a>Title</a>
                                   <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-4 py-3 bg-gray-50 dark:bg-opacity-10  dark:text-white text-left text-xs font-medium text-gray-500 uppercase tracking-wider  sm:w-2/12 w-0">
                                <a class="hidden sm:block">Images</a>
                            </th>
                            <th wire:click='sortingField("isActive")' class=" cursor-pointer px-4 py-3 bg-gray-50 dark:bg-opacity-10  dark:text-white text-center font-medium text-gray-500 uppercase tracking-wider  sm:w-2/12 w-0">
                                <div class=" space-x-1 hidden sm:flex justify-center">
                                    <a class="hidden sm:block text-xs">Status</a>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                     </svg>
                                 </div>
                            </th>
                            <th class=" text-right bg-gray-50 dark:bg-opacity-10  dark:text-white text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">

                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-opacity-20 dark:text-white divide-y divide-gray-200 dark:divide-gray-900">
                        @forelse ($diary as $item)
                        <tr>
                            <td class="px-6 py-4 break-words text-sm font-bold text-newgray-700 dark:text-gray-300">
                                <a href="{{ url('/cms/diary/'.$item->id) }}">{{ $item->titleEN ?: $item->titleID }}</a>
                                @if ($item->missingEnglish)
                                    <span class="block text-xs italic text-gray-400">No English yet</span>
                                @endif
                            </td>


                            <td class=" py-4 break-words text-sm font-bold text-newgray-700 dark:text-gray-300  ">
                                <div class="px-4 items-center flex ">
                                    @if (in_array(pathinfo(asset('storage/files/photos/'.$item->img), PATHINFO_EXTENSION),['mp4', 'avi', '3gp', 'mov', 'm4a']))
                                        <video class="spect-w-16 aspect-h-9  sm:block hidden bg-cover bg-center" controls>
                                            <source src="{{asset('storage/files/photos/'.$item->img)}}" type="video/mp4" >
                                        </video>
                                    @else
                                    <img src="{{asset('storage/files/photos/'.$item->img)}}" alt="" class="spect-w-16 aspect-h-9  sm:block hidden bg-cover bg-center">
                                    @endif
                                </div>

                            </td>
                            <td class=" py-4 break-words text-sm text-center font-bold text-newgray-700 dark:text-gray-300">

                                @if( $item->isActive == 1)
                                    <a class="break-all sm:inline-flex hidden justify-between  bg-gray-200 dark:bg-newgray-700 text-green-800 rounded mt-1 py-2 px-2 focus:outline-none items-center">Published</a>
                                @else
                                    <a class="break-all sm:inline-flex hidden justify-between  bg-gray-200 dark:bg-newgray-700 text-red-800 rounded mt-1 py-2 px-2 focus:outline-none items-center">Unpublish</a>
                                @endif
                            </td>
                            <td colspan="2" class=" break-words text-sm text-gray-500 dark:text-gray-300 px-6">
                                <div class="relative flex justify-end" x-data="{ open: false }">

                                    <button class=" focus:outline-none" @click="open = true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>

                                    <ul
                                        class="absolute mt-6  right-0 bg-white rounded-lg shadow-lg block w-24 z-10"
                                        x-show.transition="open"
                                        @click.away="open = false"
                                        x-cloak style="display: none !important">
                                        <a data-turbolinks="false" href="{{ url('/cms/diary/'.$item->id) }}"><li class="block hover:bg-gray-200 cursor-pointer py-1 mt-2 px-4 dark:text-gray-500" @click.away="open = false">Edit</li></a>
                                        <li class="block hover:bg-gray-200 cursor-pointer  py-1 mb-2 px-4 dark:text-gray-500"  wire:click="delete({{ $item->id }})" @click.away="open = false">Delete</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="whitespace-nowrap text-sm text-gray-500 px-6 py-3">
                                No data found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if ($diary)
    {{ $diary->links('livewire.pagination') }}
    @endif
</div>
