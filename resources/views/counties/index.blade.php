@extends('layouts.app')

@section('title', 'Megyék')

@section('content')
<h1>Megyék</h1>

<p>
    <a href="{{ route('counties.create') }}" class="btn">Új Megye</a>
    <a href="{{ route('counties.export.csv') }}" class="btn">CSV Export</a>
    <a href="{{ route('counties.export.pdf') }}" class="btn">PDF Export</a>
</p>

<table>
    <thead>
        <tr>
            <th>Megye név</th>
            <th>Városok száma</th>
            <th>Műveletek</th>
        </tr>
    </thead>
    <tbody>
        @forelse($counties as $county)
        <tr>
            <td>{{ $county->name }}</td>
            <td>{{ $county->cities->count() }}</td>
            <td>
                <a href="{{ route('counties.show', $county) }}">Megtekintés</a> |
                <a href="{{ route('counties.edit', $county) }}">Szerkesztés</a> |
                <form action="{{ route('counties.destroy', $county) }}" method="POST" style="display: inline;" onsubmit="return confirm('Biztosan törölni szeretnéd?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 0; border: none; background: none; color: #0066cc; cursor: pointer; text-decoration: underline;">Törlés</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="text-align: center;">Még nincs megye hozzáadva.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection