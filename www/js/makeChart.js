var amchartsLoaded = false;

AmCharts.useUTC = true;

AmCharts.ready(function () {
    if(marketDataLoaded == true){
        makeChart();
    }else{
        amchartsLoaded = true;
    }
});

function makeChart(){
    generateChartData();
    createStockChart();
}

var chart;
var chartData = [];
var newPanel;
var stockPanel;

function generateChartData() {

    var hashes = [];
    var days = [];
    var sections = [];
    var volumes = [];
    
    for(var i=0;i<markethistory.length;i++){
        
        var day = markethistory[i];
        var date = day["date"];
        var hash = date.getUTCDate()+""+date.getUTCMonth()+""+date.getUTCFullYear();
        var price = markethistory[i]["price"];
        var total = markethistory[i]["total"];
        
        if(hashes.indexOf(hash) == -1){
            hashes.push(hash);
            days.push(date);
            var section = [];
            section.push(price);
            sections.push(section);
            var totals = [];
            totals.push(total);
            volumes.push(totals);
        }else{
            sections[sections.length-1].push(price);
            volumes[volumes.length-1].push(total);
        }
        
    }
    
    for(var i=0;i<sections.length;i++){
        var open = sections[i][0]+"";
        var high = math.max(sections[i])+"";
        var low = math.min(sections[i])+"";
        var close = sections[i][sections[i].length-1]+"";
        var volume = math.round(math.sum(volumes[i]),8)+"";
        
        var months = ["Jan","Feb","Mar","Apr","May","June","July","Aug","Sept","Oct","Nov","Dec"];
        var datestring = months[days[i].getUTCMonth()]+" "+days[i].getUTCDate()+", "+days[i].getUTCFullYear();
        
        var end = "Close";
        
        var current = new Date(days[i].getTime()).setUTCHours(0,0,0,0);
        var today = new Date().setUTCHours(0,0,0,0);
        
        if(i == sections.length-1 && current == today){
            end = "Last";
        }
        
        chartData[i] = ({
                date: days[i],
                datestring: datestring,
                end: end,
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
    }, {
        fromField: "date",
        toField: "date"
    }, {
        fromField: "end",
        toField: "end"
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
    graph.balloonText = "<p>[[datestring]]</p><div style='text-align:left;'>Open:<b>[[open]] sat</b><br>High:<b>[[high]] sat</b><br>Low:<b>[[low]] sat</b><br>[[end]]:<b>[[close]] sat</b><br>Volume:<b>[[volume]] BTC</b></div>";
    graph.useDataSetColors = false;
    stockPanel.addStockGraph(graph); 
    chart.panels = [stockPanel];
    graph.proCandlesticks = "true";
    

    // OTHER SETTINGS ////////////////////////////////////
    var sbsettings = new AmCharts.ChartScrollbarSettings();
    sbsettings.graph = graph;
    sbsettings.graphType = "line";
    sbsettings.usePeriod = "WW";
    sbsettings.position = "bottom";
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

function group (p) {
    chart.categoryAxesSettings.groupToPeriods = [p];
    chart.validateData();
}