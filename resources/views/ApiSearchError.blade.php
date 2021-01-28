@extends('layout.template')

@section('title','Api Error')

@section('content')
    <h2>Error</h2>
    <p>The {{ $api }} search failed</p>
@endsection