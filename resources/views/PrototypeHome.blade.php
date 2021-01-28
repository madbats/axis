@extends('layout.template')
@section('title','Home')

@section('navbar')
    @parent
@endsection

@section('content')

<div class="justify-content-center p-5">
    
    <form method="GET" action="search">
    <h1>Search articles</h1>
        @csrf
        <div class="form-group d-flex">
            <select class="form-control" name="SearchBy" style="width: 8%;">
                <option value="All">All</option>
                <option value="Title">Title</option>
                <option value="Author">Author</option>
                <option value="Origin">Origin</option>
            </select>
            <input type="text" class="form-control @error('q') is-invalid @enderror " name="q" placeholder="search for an article">
            <div class="invalid-feedback">
                This field is required
            </div>
        </div>
        <input type="hidden" name="page" value="1">
        <button class="btn btn-primary" type="submit">
            Search
        </button>
    </form>

</div>

@endsection

