<div class="py-2.5 px-1 space-y-1">
    <div class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-tight">
        {{ $getRecord()->formatted_created_at }}
    </div>
    <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed font-normal">
        {{ $getRecord()->description }}
    </div>
    @if($getRecord()->reason)
        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium">
            Alasan: {{ $getRecord()->reason }}
        </div>
    @endif
</div>

