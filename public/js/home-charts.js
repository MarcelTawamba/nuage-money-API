function initializeCharts(transData, transStatData) {
    let data = transData;
    
    am5.ready(function() {
        for (let i = 0; i < data.length; i++) {
            data[i]['date'] = new Date(data[i]['date']).getTime();
        }

        let root = am5.Root.new("chartdiv");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        let chart = root.container.children.push(
            am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "panX",
                wheelY: "zoomX",
                pinchZoomX:true
            })
        );

        let cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "none"
        }));
        cursor.lineY.set("visible", false);

        let xAxis = chart.xAxes.push(
            am5xy.DateAxis.new(root, {
                baseInterval: { timeUnit: "day", count: 1 },
                renderer: am5xy.AxisRendererX.new(root, {}),
                tooltip: am5.Tooltip.new(root, {}),
                tooltipDateFormat: "yyyy-MM-dd"
            })
        );

        let yAxis = chart.yAxes.push(
            am5xy.ValueAxis.new(root, {
                maxDeviation:1,
                renderer: am5xy.AxisRendererY.new(root, {pan:"zoom"})
            })
        );

        createSeries("XAF",root,chart,xAxis,yAxis)
        createSeries("NGN",root,chart,xAxis,yAxis)

        let scrollbar = chart.set("scrollbarX", am5xy.XYChartScrollbar.new(root, {
            orientation: "horizontal",
            height: 60
        }));

        let sbDateAxis = scrollbar.chart.xAxes.push(
            am5xy.DateAxis.new(root, {
                baseInterval: {
                    timeUnit: "day",
                    count: 1
                },
                renderer: am5xy.AxisRendererX.new(root, {})
            })
        );

        let sbValueAxis = scrollbar.chart.yAxes.push(
            am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            })
        );

        let sbSeries = scrollbar.chart.series.push(
            am5xy.LineSeries.new(root, {
                valueYField: "amount",
                valueXField: "date",
                xAxis: sbDateAxis,
                yAxis: sbValueAxis
            })
        );

        sbSeries.fills.template.setAll({
            fillOpacity: 0.2,
            visible: true
        });

        sbSeries.data.setAll(data);
        let legend = chart.rightAxesContainer.children.push(am5.Legend.new(root, {
            width: 70,
            paddingLeft: 15,
            height: am5.percent(100)
        }));

        legend.itemContainers.template.events.on("pointerover", function(e) {
            let itemContainer = e.target;
            let series = itemContainer.dataItem.dataContext;

            chart.series.each(function(chartSeries) {
                if (chartSeries != series) {
                    chartSeries.strokes.template.setAll({
                        strokeOpacity: 0.15,
                        stroke: am5.color(0x000000)
                    });
                } else {
                    chartSeries.strokes.template.setAll({
                        strokeWidth: 3
                    });
                }
            })
        })

        legend.itemContainers.template.events.on("pointerout", function(e) {
            let itemContainer = e.target;
            let series = itemContainer.dataItem.dataContext;

            chart.series.each(function(chartSeries) {
                chartSeries.strokes.template.setAll({
                    strokeOpacity: 1,
                    strokeWidth: 1,
                    stroke: chartSeries.get("fill")
                });
            });
        })

        legend.itemContainers.template.set("width", am5.p100);
        legend.valueLabels.template.setAll({
            width: am5.p100,
            textAlign: "right"
        });

        legend.data.setAll(chart.series.values);

        chart.appear(1000, 100);

        function createSeries(name,root,chart, xAxis,yAxis) {
            let series= chart.series.push(
                am5xy.LineSeries.new(root, {
                    name: name,
                    xAxis: xAxis,
                    yAxis: yAxis,
                    stacked: true,
                    valueYField: "amount",
                    valueXField: "date",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "[bold]{name} {valueY}"
                    })
                })
            );
            series.fills.template.setAll({
                fillOpacity: 0.2,
                visible: true
            });

            series.strokes.template.setAll({
                strokeWidth: 2
            });

            series.data.setAll(data.filter((elt)=> elt["currency"] === name));
            series.appear(1000);
        }
    });

    let data2 = transStatData;
    am5.ready(function() {
        let root = am5.Root.new("failed_stat");

        let data3 = [];
        for(let i=0;i<data2.length;i++){
            if(data2[i]["status"] === "SUCCESSFUL"){
                data3.push( {
                    status: data2[i]["status"],
                    total : data2[i]["total"],
                    columnSettings: {
                        fill: am5.color(0xA6D997),
                        stroke: am5.color(0xbabf95)
                    }
                })
            }else if(data2[i]["status"] === "PENDING"){
                data3.push({
                    status: data2[i]["status"],
                    total : data2[i]["total"],
                    columnSettings: {
                        fill: am5.color(0x6EAFFB),
                        stroke: am5.color(0xbabf95)
                    }
                })
            }else if(data2[i]["status"] === "FAILED"){
                data3.push({
                    status: data2[i]["status"],
                    total : data2[i]["total"],
                    columnSettings: {
                        fill: am5.color(0xFF1919),
                        stroke: am5.color(0xbabf95)
                    }
                })
            }else if(data2[i]["status"] === "CREATED"){
                data3.push( {
                    status: data2[i]["status"],
                    total : data2[i]["total"],
                    columnSettings: {
                        fill: am5.color(0xEEDBDB),
                        stroke: am5.color(0xbabf95)
                    }
                })
            }
        }

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        let chart = root.container.children.push(am5percent.PieChart.new(root, {
            layout: root.verticalLayout,
            innerRadius: am5.percent(50)
        }));

        let series = chart.series.push(am5percent.PieSeries.new(root, {
            valueField: "total",
            categoryField: "status",
        }));

        series.labels.template.set("visible", false);
        series.ticks.template.set("visible", false);
        series.slices.template.setAll({
            templateField: "columnSettings"
        });
        series.data.setAll(data3);

        let legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.percent(50),
            x: am5.percent(50),
            marginTop: 15,
            marginBottom: 15,
        }));

        legend.data.setAll(series.dataItems);

        series.appear(1000, 100);
    });
}
