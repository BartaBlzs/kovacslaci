@extends('layouts.app')

@section('title', 'Városok')

@section('content')
<h1>Városok</h1>

<p>
    <a href="{{ route('cities.create') }}" class="btn">Új Város</a>
</p>

<!-- 1. Megye kiválasztása -->
<div class="filter-section">
    <h3>1. Válassz megyét:</h3>
    <form method="GET" action="{{ route('cities.index') }}">
        <select name="county_id" onchange="this.form.submit()">
            <option value="">-- Válassz megyét --</option>
            @foreach($counties as $county)
                <option value="{{ $county->id }}" {{ $selectedCounty == $county->id ? 'selected' : '' }}>
                    {{ $county->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- 2. Kezdőbetű kiválasztása -->
@if($selectedCounty && $letters->isNotEmpty())
<div class="filter-section">
    <h3>2. Válassz kezdőbetűt:</h3>
    <div>
        @foreach($letters as $letter)
            <a href="{{ route('cities.index', ['county_id' => $selectedCounty, 'letter' => $letter]) }}" 
               class="letter-btn {{ $selectedLetter == $letter ? 'active' : '' }}">
                {{ $letter }}
            </a>
        @endforeach
    </div>
</div>
@endif

<!-- 3. Városok listája -->
@if($cities->isNotEmpty())
<h2>Városok listája</h2>
<p>
    <a href="{{ route('cities.export.csv', request()->query()) }}" class="btn">CSV Export</a>
    <a href="{{ route('cities.export.pdf', request()->query()) }}" class="btn">PDF Export</a>
</p>

<table>
    <thead>
        <tr>
            <th>Város</th>
            <th>Megye</th>
            <th>Irányítószámok</th>
            <th>Műveletek</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cities as $city)
        <tr>
            <td>{{ $city->name }}</td>
            <td>{{ $city->county->name }}</td>
            <td>
                @foreach($city->postalCodes as $pc)
                    {{ $pc->code }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
            <td>
                <a href="{{ route('cities.show', $city) }}">Megtekintés</a> |
                <a href="{{ route('cities.edit', $city) }}">Szerkesztés</a> |
                <form action="{{ route('cities.destroy', $city) }}" method="POST" style="display: inline;" onsubmit="return confirm('Biztosan törölni szeretnéd?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 0; border: none; background: none; color: #0066cc; cursor: pointer; text-decoration: underline;">Törlés</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@elseif($selectedLetter)
<p><strong>Nincs "{{ $selectedLetter }}" betűvel kezdődő város ebben a megyében.</strong></p>
@endif
@endsection