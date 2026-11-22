@props(['logAttributes'])

<div>
    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">
        Attributes
    </p>

    <div class="overflow-x-auto rounded-lg border border-gray-800">

        <table class="w-full text-left text-sm">

            <thead class="bg-gray-900 text-xs text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Key</th>
                    <th class="px-4 py-2 font-medium">Value</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">

                @foreach ($logAttributes as $key => $value)

                    <tr>
                        <td class="whitespace-nowrap px-4 py-2 font-mono text-xs text-violet-300">
                            {{ $key }}
                        </td>

                        <td class="break-all px-4 py-2 text-gray-300">

                            @if (is_array($value))

                                <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>

                            @elseif (is_bool($value))

                                {{ $value ? 'true' : 'false' }}

                            @elseif ($value === null)

                                <span class="text-gray-500">null</span>

                            @else

                                {{ $value }}

                            @endif

                        </td>
                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>
</div>
