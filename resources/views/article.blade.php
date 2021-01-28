@extends('layout.template')

@section('title',"$data->title")

@section('content')

    <h2>{{$data->title}}</h2>
    <br/>
    <h4>@if (strcmp($data->pdf,"")!=0)
            <a href="{{$data->pdf}}" class='btn btn-primary'>Download Article</a></h4>
        @else
            <button class='btn btn-primary' disabled="true">No Download Article</a></h4>
        @endif
    <hr>
    <h4>
        @foreach ($data->origine()->getAttributes() as $key => $value)
            @if(!empty($value)) {{ $key }}: {{ $value }} @endif
        @endforeach
    </h4>
    <p>
    @php
    $last = last(last($data->categories));
    @endphp
    @foreach($data->categories as $cat)
        @if($cat == $last) {{$cat->name}}
        @else {{$cat->name}} - @endif
    @endforeach
    </p>
    <p>
    {{$data->editors}}
    </p>
    <p>Year of publication : {{$data->published}}</p>
    <p>DOI : @if (strcmp($data->doi,"")!=0)
        <a href="https://doi.org/{{ $data->doi}}">{{ $data->doi }}</a>
        @else
            No DOI Found
        @endif </p>
    <div> Completion score : 
    @if($data->score < 40) <div class="btn btn-danger" "> {{$data->score}} </div> 
    @elseif($data->score < 75) <div class="btn btn-warning"> {{$data->score}} </div> 
    @else <div class="btn btn-success"> {{$data->score}} </div>
    @endif
    </div>
    <p>
    <br/>
    <br/>
    <h3>Abstract</h3>
    <p>
        @if (strcmp( $data->abstract ,"")!=0)
        {{$data->abstract}}
        @else
        No Abstract Found
        @endif
    </p>
    <br/>
    <h3>Authors</h3>
    <ul>
    @foreach ($data->authors as $author)
        <li>{{ $author->first_name}} {{ $author->last_name }} -- {{ $author->gender }}</li>
        <ul>
            
        </ul>
    @endforeach
    </ul>
    
    <br/>

    <h3>Citations</h3>
    <ul>
    @foreach($data->references as $citation)
        <li><a href="{{ route('article',$citation->id) }}">{{$citation->title}} - {{$citation->published}} - {{$citation->origine()->name}}</a></li>
    @endforeach
    </ul>

    <h3>References</h3>
    <ul>        
    @foreach($data->citations as $reference)
        <li><a href="{{ route('article',$reference->id) }}">{{$reference->title}} - {{$reference->published}} - {{$reference->origine()->name}}</a></li>
    @endforeach
    </ul>
@endsection


