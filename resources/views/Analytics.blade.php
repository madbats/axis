@extends('layout.template')
@section('title','Analytics')

@section('navbar')
    @parent
@endsection

@section('content')

<div class="justify-content-center p-5">
    <div class="row">
        <div class="col-3">
            
            <form method="POST" action="/analytics">
                @csrf
                <fieldset  class="form-group">
                    <legend>Chart Type:</legend>
                    <div class="radio">
                        <label><input type="radio"name="type" value="PieChart" @if (!strcmp($request->input('type'),"PieChart"))
                            checked
                        @endif >Pie Chart</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="type" value="AreaChart" @if (!strcmp($request->input('type'),"AreaChart"))
                            checked
                        @endif >Area Chart</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="type" value="ColumnChart" @if (!strcmp($request->input('type'),"ColumnChart"))
                            checked
                        @endif >Column Chart</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="type" value="LineChart" @if (!strcmp($request->input('type'),"LineChart"))
                            checked
                        @endif >Line Chart</label>
                    </div>

                </fieldset>

                <fieldset  class="form-group">
                    <legend>Ordinate :</legend>
                    <div class="radio">
                        <label><input type="radio"name="Axis1" value="Authors"  @if (!strcmp($request->input('Axis1'),"Authors"))
                            checked
                        @endif >Number of Authors</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="Axis1" value="MaleProportion" @if (!strcmp($request->input('Axis1'),"MaleProportion"))
                            checked
                        @endif >Proportion of Male Authors</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="Axis1" value="FemaleProportion" @if (!strcmp($request->input('Axis1'),"FemaleProportion"))
                            checked
                        @endif >Proportion of Female Authors</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="Axis1" value="Articles" @if (!strcmp($request->input('Axis1'),"Articles"))
                            checked
                        @endif >Number of Articles</label>
                    </div>
                </fieldset>

                <fieldset  class="form-group">
                    <legend>Abscissa :</legend>
                    <div class="radio">
                        <label><input type="radio"name="Axis2" value="Year" @if (!strcmp($request->input('Axis2'),"Year"))
                            checked
                        @endif >Year</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="Axis2" value="Category" @if (!strcmp($request->input('Axis2'),"Category"))
                            checked
                        @endif >Category</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio"name="Axis2" value="Editor" @if (!strcmp($request->input('Axis2'),"Editor"))
                            checked
                        @endif >Editor</label>
                    </div>
                </fieldset>

                <div class="form-group">
                    <button class="btn btn-primary" type="submit">
                        Draw
                    </button>
                </div>

            </form>
        </div>
        <div class="col-7">
            <div id="chart_div" style="width: 1100px; height: 700px;"></div>
        </div>
    </div>
</div>

@endsection
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart()
    {
        var data = new google.visualization.arrayToDataTable([
            @php
                foreach ($data as $set)
                {
                    echo "['".$set[0]."',".$set[1]."],";
                }
            @endphp
        ]);

        var options = {
            @php
                foreach ($option as $set)
                {
                    echo $set[0].":".$set[1].",";
                }
            @endphp
        };

        var chart = new google.visualization.{{$graphType}}(document.getElementById('chart_div'));
        chart.draw(data,options);
    }
</script>
