@extends('layout.template')
@section('title','Chart')

 @section('navbar')
    @parent 
@endsection

@section('content')

<div class="justify-content-center p-5">
    <div class="row">
        <div class="col-3">
            <!-- <form method="POST" action="/createChart"> -->
                
                <fieldset  class="form-group">
                    <legend>Report :</legend>
                    <div class="radio">
                        <label><input type="radio" name="reporttype" value="Authors" checked>Number of Authors by Conference</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="reporttype" value="Articles" >Number of Articles by Conference</label>
                    </div>
                </fieldset>
                <div>
                    <button class="btn btn-primary" id="draw-chart" >Draw</button>
                </div>
            <!-- </form> -->
        </div>
        <div class="col-7">
            <chart_div />
        </div>
    </div>
</div>
<script>
$("document").ready(function() {
    setTimeout(function() {
        $("#draw-chart").trigger('click');
    }, 10);
});
</script>

<script type="module">
import {
	Runtime,
	Library,
	Inspector
} from "./js/runtime.js";

const runtime = new Runtime();

document.querySelector('#draw-chart').addEventListener('click', function(){
	// get selected button
	var value = $('input[name="reporttype"]:checked').val();
	console.log(value);
	// remove old chart
	document.querySelector('.col-7').innerHTML = '<chart_div />';
	var url = '';
	if (value === 'Authors'){
		url = 'getAuthorsbyConference';
	}else{
		// draw chart for articles
		url = 'getArticlesByConference';
	}

	// draw chart based on checked button
	const main = runtime.module(define, Inspector.into(document.body));
	
	function define(runtime, observer) {

			const main = runtime.module();

			//Get data from StatisticsController to local file
			const fileAttachments = new Map([
				["conference.json", new URL(url,
					import.meta.url)]
			]);

			main.builtin("FileAttachment", runtime.fileAttachments(name => fileAttachments.get(name)));

			main.variable(observer("chart")).define("chart", ["partition", "data", "d3", "width", "height", "color", "format"],
				function (partition, data, d3, width, height, color, format) {
					const root = partition(data);
					let focus = root;

					const svg = d3.select("chart_div").append("svg")
						//.create("svg")
						.attr("viewBox", [0, 0, width, height])
						.style("font", "10px sans-serif");

					const cell = svg
						.selectAll("g")
						.data(root.descendants())
						.join("g")
						.attr("transform", d => `translate(${d.y0},${d.x0})`);

					const rect = cell.append("rect")
						.attr("width", d => d.y1 - d.y0 - 1)
						.attr("height", d => rectHeight(d))
						.attr("fill-opacity", 0.6)
						.attr("fill", d => {
							if (!d.depth) return "#ccc";
							while (d.depth > 1) d = d.parent;
							return color(d.data.name);
						})
						.style("cursor", "pointer")
						.on("click", clicked);

					const text = cell.append("text")
						.style("user-select", "none")
						.attr("pointer-events", "none")
						.attr("x", 4)
						.attr("y", 13)
						.attr("fill-opacity", d => +labelVisible(d));

					text.append("tspan")
						.text(d => d.data.name);

					const tspan = text.append("tspan")
						.attr("fill-opacity", d => labelVisible(d) * 0.7)
						.text(d => ` ${format(d.value)}`);

					cell.append("title")
						.text(d => `${d.ancestors().map(d => d.data.name).reverse().join("/")}\n${format(d.value)}`);

					function clicked(p) {
						focus = focus === p ? p = p.parent : p;

						root.each(d => d.target = {
							x0: (d.x0 - p.x0) / (p.x1 - p.x0) * height,
							x1: (d.x1 - p.x0) / (p.x1 - p.x0) * height,
							y0: d.y0 - p.y0,
							y1: d.y1 - p.y0
						});

						const t = cell.transition().duration(750)
							.attr("transform", d => `translate(${d.target.y0},${d.target.x0})`);

						rect.transition(t).attr("height", d => rectHeight(d.target));
						text.transition(t).attr("fill-opacity", d => +labelVisible(d.target));
						tspan.transition(t).attr("fill-opacity", d => labelVisible(d.target) * 0.7);
					}

					function rectHeight(d) {
						return d.x1 - d.x0 - Math.min(1, (d.x1 - d.x0) / 2);
					}

					function labelVisible(d) {
						return d.y1 <= width && d.y0 >= 0 && d.x1 - d.x0 > 16;
					}

					return svg.node();
				});

			main.variable(observer("data")).define("data", ["FileAttachment"], function (FileAttachment) {
				return (
					FileAttachment("conference.json").json()
				)
			});

			main.variable(observer("partition")).define("partition", ["d3", "height", "width"], function (d3, height, width) {
				return (
					data => {
						const root = d3.hierarchy(data)
							.sum(d => d.value)
							.sort((a, b) => b.height - a.height || b.value - a.value);
						return d3.partition()
							.size([height, (root.height + 1) * width / 3])
							(root);
					}
				)
			});
			main.variable(observer("color")).define("color", ["d3", "data"], function (d3, data) {
				return (
					d3.scaleOrdinal(d3.quantize(d3.interpolateRainbow, data.children.length + 1))
				)
			});
			main.variable(observer("format")).define("format", ["d3"], function (d3) {
				return (
					d3.format(",d")
				)
			});
			main.variable(observer("width")).define("width", function () {
				return (
					975
				)
			});
			main.variable(observer("height")).define("height", function () {
				return (
					1200
				)
			});
			main.variable(observer("d3")).define("d3", ["require"], function (require) {
				return (
					require("d3@5")
				)
			});
			return main;
			}

});

</script>
@endsection