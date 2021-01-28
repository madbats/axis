@extends('layout.template')

@section('title','Array')

@section('content')
    <div class="col">
        <h2>The API has returned the following results</h2>
        <br>
        <pre>
        @php
            print_r($data);
        @endphp
        </pre>
    </div>
@endsection