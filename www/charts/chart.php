<html>
<head>
<style>
#chartdiv {
	width:100%;
	height:450px;
}
</style>
<link rel="stylesheet" type="text/css" href="/charts/amcharts/style.css">
</head>
<body>

<div style="padding-bottom:5px;" class="amChartsDataSetSelector">
  Interval: 
<input type="button" class="amChartsButton" id="hour" value="1 hour">
  <input type="button" class="amChartsButton" id="hour12" value="12 hours">
  <input type="button" class="amChartsButton" id="day" value="1 day">
  <input type="button" class="amChartsButton" id="day5" value="5 days">
  <input type="button" class="amChartsButton" id="week" value="week">
  <input type="button" class="amChartsButton" id="month" value="month">
</div>
<div id="chartdiv"></div>

<script type="text/javascript" src="/charts/amcharts/amcharts.js"></script>
<script type="text/javascript" src="/charts/amcharts/serial.js"></script>
<script type="text/javascript" src="/charts/amcharts/themes/light.js"></script>
<script type="text/javascript" src="/charts/amcharts/amstock.js"></script>

<script>
AmCharts.ready(function () {
    generateChartData();
    createStockChart();
});

var chart;
var chartData = [];
var newPanel;
var stockPanel;

function generateChartData() {
    var firstDate = new Date();
    firstDate.setHours(0, 0, 0, 0);
    firstDate.setDate(firstDate.getDate() - 2000);
    
    for (var i = 0; i < 2000; i++) {
        var newDate = new Date(firstDate);
        
        newDate.setDate(newDate.getDate() + i);
        
        var open = Math.round(Math.random() * (30) + 100);
        var close = open + Math.round(Math.random() * (15) - Math.random() * 10);
        
        var low;
        if (open < close) {
            low = open - Math.round(Math.random() * 5);
        } else {
            low = close - Math.round(Math.random() * 5);
        }
        
        var high;
        if (open < close) {
            high = close + Math.round(Math.random() * 5);
        } else {
            high = open + Math.round(Math.random() * 5);
        }
        
        var volume = Math.round(Math.random() * (1000 + i)) + 100 + i;
        
        
        chartData[i] = ({
            date: newDate,
            open: open,
            close: close,
            high: high,
            low: low,
            volume: volume
        });
    }
}

function createStockChart() {
    chart = new AmCharts.AmStockChart();
    chart.pathToImages = "/charts/amcharts/images/";
    
    chart.balloon.horizontalPadding = 13;
    
    // DATASET //////////////////////////////////////////
    var dataSet = new AmCharts.DataSet();
    dataSet.fieldMappings = [{
        fromField: "open",
        toField: "open"
    }, {
        fromField: "close",
        toField: "close"
    }, {
        fromField: "high",
        toField: "high"
    }, {
        fromField: "low",
        toField: "low"
    }, {
        fromField: "volume",
        toField: "volume"
    }, {
        fromField: "value",
        toField: "value"
    }];
    dataSet.color = "#7f8da9";
    dataSet.dataProvider = chartData;
    dataSet.categoryField = "date";
    
    chart.dataSets = [dataSet];
    
    // PANELS ///////////////////////////////////////////                                                  
    stockPanel = new AmCharts.StockPanel();
    stockPanel.title = "Value";
    
    // graph of first stock panel
    var graph = new AmCharts.StockGraph();
    graph.type = "candlestick";
    graph.openField = "open";
    graph.closeField = "close";
    graph.highField = "high";
    graph.lowField = "low";
    graph.valueField = "close";
    graph.lineColor = "#7f8da9";
    graph.fillColors = "#7f8da9";
    graph.negativeLineColor = "#db4c3c";
    graph.negativeFillColors = "#db4c3c";
    graph.fillAlphas = 1;
    graph.balloonText = "open:<b>[[open]]</b><br>close:<b>[[close]]</b><br>low:<b>[[low]]</b><br>high:<b>[[high]]</b>";
    graph.useDataSetColors = false;
    stockPanel.addStockGraph(graph); 
    chart.panels = [stockPanel];
    graph.proCandlesticks = "true";
    

    // OTHER SETTINGS ////////////////////////////////////
    var sbsettings = new AmCharts.ChartScrollbarSettings();
    sbsettings.graph = graph;
    sbsettings.graphType = "line";
    sbsettings.usePeriod = "WW";
    chart.chartScrollbarSettings = sbsettings;
    
    // Enable pan events
    var panelsSettings = new AmCharts.PanelsSettings();
    panelsSettings.panEventsEnabled = true;
    chart.panelsSettings = panelsSettings;
    
    // CURSOR
    var cursorSettings = new AmCharts.ChartCursorSettings();
    cursorSettings.valueBalloonsEnabled = true;
  	cursorSettings.valueLineBalloonEnabled = false;
  	cursorSettings.valueLineEnabled = false;
  	cursorSettings.pan = true;
    chart.chartCursorSettings = cursorSettings;
    
    // PERIOD SELECTOR ///////////////////////////////////
    var periodSelector = new AmCharts.PeriodSelector();
    periodSelector.position = "bottom";
    periodSelector.periods = [{
        period: "DD",
        count: 10,
        label: "10 days"
    }, {
        period: "MM",
        selected: true,
        count: 1,
        label: "1 month"
    }, {
        period: "YYYY",
        count: 1,
        label: "1 year"
    }, {
        period: "YTD",
        label: "YTD"
    }, {
        period: "MAX",
        label: "MAX"
    }];
    chart.periodSelector = periodSelector;
    
    
    chart.write('chartdiv');
}
</script>

</body>