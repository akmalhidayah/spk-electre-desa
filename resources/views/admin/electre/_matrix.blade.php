@if (! empty($matrix))
    @php
        $columns = collect($matrix)->first() ? array_keys(collect($matrix)->first()) : [];
    @endphp
    <div class="table-wrap matrix-detail-wrap">
        <table class="data-table matrix-detail-table">
            <thead>
                <tr>
                    <th>Alternatif</th>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($matrix as $rowLabel => $row)
                    <tr>
                        <td><strong>{{ $rowLabel }}</strong></td>
                        @foreach ($columns as $column)
                            <td>
                                @if (is_numeric($row[$column] ?? null))
                                    {{ number_format((float) $row[$column], is_float($row[$column] + 0) && (float) $row[$column] != (int) $row[$column] ? 6 : 0, ',', '.') }}
                                @else
                                    {{ $row[$column] ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="muted">-</p>
@endif
