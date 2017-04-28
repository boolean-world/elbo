google.charts.load("current", {
	packages:[
		"corechart"
	]
});
google.charts.setOnLoadCallback(function() {
	function createClicksChart(data, elem) {
		var arr = [];

		for (var i in data) {
			arr.unshift([i, data[i]]);
		}

		arr.unshift(["Country", "Clicks"]);

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.ColumnChart(elem);
		chart.draw(table, {
			legend: {
				position: "none"
			},
			width: "100%",
			backgroundColor: "#FAFAFA"
		});
	}

	function createCountryChart(data, elem) {
		var arr = [["Country", "Clicks"]];
		var len = data.length;

		for (var i = 0; i < len; i++) {
			if (data[i].country !== null) {
				arr.push([data[i].country, Number(data[i].count)]);
			}
		}

		console.log(arr);

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.GeoChart(elem);
		chart.draw(table, {
			colorAxis: {
				minValue: 0,
				colors: ['#E1BEE7', '#6A1B9A']
			},
			backgroundColor: "#FAFAFA"
		});
	}

	function createCountryChart(data, elem) {
		var arr = [["Country", "Clicks"]];
		var len = data.length;

		for (var i = 0; i < len; i++) {
			if (data[i].country !== null) {
				arr.push([data[i].country, Number(data[i].count)]);
			}
		}

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.GeoChart(elem);
		chart.draw(table, {
			colorAxis: {
				minValue: 0,
				colors: ['#E1BEE7', '#6A1B9A']
			},
			backgroundColor: "#FAFAFA"
		});
	}

	function createRefererChart(data, elem) {
		var arr = [["Referer", "Clicks"]];
		var len = data.length;

		for (var i = 0; i < len; i++) {
			arr.push([data[i].referer || "Direct", Number(data[i].count)]);
		}

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.PieChart(elem);
		chart.draw(table, {
			pieHole: 0.5,
			backgroundColor: "#FAFAFA"
		});
	}

	function createBrowserChart(data, elem) {
		var arr = [["Browser", "Clicks"]];
		var len = data.length;

		for (var i = 0; i < len; i++) {
			arr.push([data[i].browser || "Unknown", Number(data[i].count)]);
		}

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.PieChart(elem);
		chart.draw(table, {
			pieHole: 0.5,
			backgroundColor: "#FAFAFA"
		});
	}


	function createPlatformChart(data, elem) {
		var arr = [["Platform", "Clicks"]];
		var len = data.length;

		for (var i = 0; i < len; i++) {
			arr.push([data[i].platform || "Unknown", Number(data[i].count)]);
		}

		var table = google.visualization.arrayToDataTable(arr);

		var chart = new google.visualization.PieChart(elem);
		chart.draw(table, {
			pieHole: 0.5,
			backgroundColor: "#FAFAFA"
		});
	}

	function createCharts(data, elem) {
		var clicks_chart = $("<div/>", {
			"class": "chart clicks-chart"
		});

		var country_chart = $("<div/>", {
			"class": "chart country-chart"
		});

		var referer_chart = $("<div/>", {
			"class": "chart referer-chart"
		});

		var browser_chart = $("<div/>", {
			"class": "chart browser-chart"
		});

		var platform_chart = $("<div/>", {
			"class": "chart platform-chart"
		});

		elem.append($("<h3/>").text("Clicks over time"))
		    .append($("<hr/>"))
		    .append(clicks_chart);

		var first_row = $("<div/>", {
			"class": "row"
		});

		var second_row = $("<div/>", {
			"class": "row"
		});

		first_row.append($("<div/>", {
			"class": "col-sm-6"
		}).append($("<h3/>").text("Country statistics"))
		  .append($("<hr/>"))
		  .append(country_chart));

		first_row.append($("<div/>", {
			"class": "col-sm-6"
		}).append($("<h3/>").text("Referer statistics"))
		  .append($("<hr/>"))
		  .append(referer_chart));

		second_row.append($("<div/>", {
			"class": "col-sm-6"
		}).append($("<h3/>").text("Browser statistics"))
		  .append($("<hr/>"))
		  .append(browser_chart));

		second_row.append($("<div/>", {
			"class": "col-sm-6"
		}).append($("<h3/>").text("Platform statistics"))
		  .append($("<hr/>"))
		  .append(platform_chart));

		elem.append(first_row).append(second_row);

		createClicksChart(data.click_stats, clicks_chart[0]);
		createCountryChart(data.country_stats, country_chart[0]);
		createRefererChart(data.referer_stats, referer_chart[0]);
		createBrowserChart(data.browser_stats, browser_chart[0]);
		createPlatformChart(data.platform_stats, platform_chart[0]);
	}

	var shorturl = /[a-z0-9_-]+$/i.exec(window.location.pathname);

	var week_stats = $("#week-stats");
	var month_stats = $("#month-stats");
	var year_stats = $("#year-stats");

	week_stats.html('<i class="fa fa-spin fa-fw fa-3x fa-refresh"></i><br>Fetching data...')
	          .addClass('centered-text lowered-element');

	$.get("/~analytics/data/" + shorturl + "/week").done(function(data) {
		week_stats.empty().removeClass("centered-text lowered-element");
		createCharts(data, week_stats);
	});

	var month_pill = $("#month-pill");
	var year_pill = $("#year-pill");

	month_pill.click(function() {
		if (!month_pill.attr("data-loaded")) {
			month_stats.html('<i class="fa fa-spin fa-fw fa-3x fa-refresh"></i><br>Fetching data...')
			           .addClass("centered-text lowered-element");

			$.get("/~analytics/data/" + shorturl + "/month").done(function(data) {
				// ugly hack to prevent loading google charts on
				// unrendered elements, which causes it to go bust.

				window.setTimeout(function() {
					month_stats.empty().removeClass("centered-text lowered-element");
					createCharts(data, month_stats);
					month_pill.attr("data-loaded", true);
				}, 300);
			});
		}
	});

	year_pill.click(function() {
		if (!year_pill.attr("data-loaded")) {
			year_stats.html('<i class="fa fa-spin fa-fw fa-3x fa-refresh"></i><br>Fetching data...')
			          .addClass("centered-text lowered-element");

			$.get("/~analytics/data/" + shorturl + "/year").done(function(data) {
				// ugly hack to prevent loading google charts on
				// unrendered elements, which causes it to go bust.

				window.setTimeout(function() {
					year_stats.empty().removeClass("centered-text lowered-element");
					createCharts(data, year_stats);
					year_pill.attr("data-loaded", true);
				}, 300);
			});
		}
	});
});
