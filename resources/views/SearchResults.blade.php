@extends('layout.template')

@section('title',request('q'))

@section('content')

    <h2>Results for '{{request('q')}}'</h2>
    @if(count($data) == 0)
        <h3>No results found</h3>
    @else

        @foreach ($data as $article)
        
            
            <hr>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"><a href="{{ route('article',$article->id) }}" >{{ $article->title }} </a></h4>
                    <p>{{ $article->origine()->name }}</p>
                    <p class="card-text">

                    {{$article->editors}} 

                    @php 
                    $last = last(last($article->categories))
                    @endphp
                    @foreach($article->categories as $cat)
                        @if($last->name == $cat->name) {{$cat->name}}
                        @else {{$cat->name}} - 
                        @endif
                    @endforeach

                    <br>
                    <br>
                        @if(isset($article->authors[10])) $lastDisp = $article->authors[10] @endif
                        @php $last = last(last($article->authors)) @endphp
                    @foreach ($article->authors as $author)
                        @if(($author->first_name == $last->first_name)&&($author->last_name == $last->last_name)) {{ $author->first_name}} {{$author->last_name}}
                        @elseif(isset($lastDisp)&&($author->first_name == $lastDisp->first_name)&&($author->last_name == $lastDisp->last_name)) {{ $author->first_name}} {{$author->last_name}}...
                        @else {{ $author->first_name}} {{$author->last_name}},
                        @endif 
                    @endforeach
                 

                    </p>
                    <a href="{{ route('article',$article->id) }}" class="btn btn-primary">More</a>
                    <div class="pt-2">completion score : 
                        @if($article->score < 40) <div class="btn btn-danger"> {{$article->score}} </div> 
                        @elseif($article->score < 75) <div class="btn btn-warning"> {{$article->score}} </div> 
                        @else <div class="btn btn-success"> {{$article->score}} </div> 
                        @endif
                    </div>
                    
                </div>
            </div>
        @endforeach
        
        <div class="d-flex justify-content-center pt-3">
            <nav>
                <ul class="pagination">
                    @if (request('page')==$total[0])
                    <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                        <span class="page-link" aria-hidden="true">‹</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{URL::to('/')}}/search?q={{request('q')}}&amp;page={{request('page')-1}}&amp;SearchBy={{request('SearchBy')}}&amp;_token={{request('_token')}}" rel="previous" aria-label="« Previous">‹</a>
                    </li>
                    @endif
                    @foreach ($total as $page)
                        @if (request('page')==$page)
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{$page}}</span>
                        </li>
                        @else
                        <li class="page-item">
                        <a class="page-link" href="{{URL::to('/')}}/search?q={{request('q')}}&amp;page={{$page}}&amp;SearchBy={{request('SearchBy')}}&amp;_token={{request('_token')}}">{{$page}}</a>
                        </li>
                        @endif
                    @endforeach
                    @if (request('page')==end($total))
                    <li class="page-item">
                        <span class="page-link" aria-hidden="true">›</span>
                    </li>
                    @else
                    <li class="page-item">
                    <a class="page-link" href="{{URL::to('/')}}/search?q={{request('q')}}&amp;page={{request('page')+1}}&amp;SearchBy={{request('SearchBy')}}&amp;_token={{request('_token')}}" rel="next" aria-label="Next »">›</a>
                    </li>
                    @endif
                    <input type="hidden" name="test" value="{{request('ResearchBy')}}">
                </ul>
            </nav>
        </div>
    @endif


@endsection


