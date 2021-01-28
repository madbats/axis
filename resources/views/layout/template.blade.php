<!DOCTYPE html>
<html lang="en">
    <head>
        <title>AXIS - @yield('title')</title>
        <link href="/css/app.css" rel="stylesheet">
        <link href="{{ asset('css/templateStyle.css') }}" rel="stylesheet">
        <link href="/css/inspector.css" rel="stylesheet">        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://d3js.org/d3.v4.js"></script>
        <script type = "text/javascript" src = "https://d3js.org/d3.v4.min.js"></script>           
    </head>
    <header>
        <h1><a href="{{route('home')}}" style="text-decoration: none; color:white">A.X.I.S. </a>- Article Extraction and Statistical Analysis</h1>
    </header>

    <body>
        @section('navbar')
            <nav class="navbar navbar-expand-sm d-flex justify-content-between">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('analytics') }}">Simple Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('statistics') }}">Conference Analytics</a>
                    </li>
                </ul>
                <form class="d-flex justify-content-between" method="GET" action="/search">
                    @csrf
                    <select class="form-control" name="SearchBy" style="width: 45%;">
                        <option value="All">All</option>
                        <option value="Title">Title</option>
                        <option value="Author">Author</option>
                        <option value="Origin">Origin</option>
                    </select>
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Search">
                    </div>
                    <div class="input-group-btn">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                    <input type="hidden" name="page" value="1">
                </form>
            </nav>        
        @show

        <div class="container-flex">
            <div class="p-5 ">
                @yield('content')
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>