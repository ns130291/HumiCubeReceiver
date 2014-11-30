<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>HumiCube</title>
        <script src="js/jquery-2.1.1.min.js"></script>
        <script src="js/highcharts.js"></script>
        <script src="js/highcharts-more.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {

                $.get('ajax.php', {}, function(data) {
                    var result = JSON.parse(data);
                    var seriesHumidity = [];
                    var seriesTemperature = [];
                    for (var device in result) {
                        var humidity = [];
                        var humidityRange = [];
                        var temperature = [];
                        var temperatureRange = [];
                        for (var element in result[device].data) {
                            humidity.push([result[device].data[element].timestamp * 1000, result[device].data[element].humidity]);
                            humidityRange.push([result[device].data[element].timestamp * 1000, result[device].data[element].humidity_min, result[device].data[element].humidity_max]);
                            temperature.push([result[device].data[element].timestamp * 1000, result[device].data[element].temperature]);
                            temperatureRange.push([result[device].data[element].timestamp * 1000, result[device].data[element].temperature_min, result[device].data[element].temperature_max]);
                        }

                        seriesHumidity.push({
                            name: result[device].device,
                            data: humidity,
                            type: 'spline',
                            zIndex: 1,
                            /*marker: {
                             fillColor: 'white',
                             lineWidth: 2,
                             lineColor: Highcharts.getOptions().colors[0]
                             }*/
                        }, {
                            name: 'Range',
                            data: humidityRange,
                            type: 'areasplinerange',
                            lineWidth: 0,
                            linkedTo: ':previous',
                            color: Highcharts.getOptions().colors[device],
                            fillOpacity: 0.3,
                            zIndex: 0
                        });

                        seriesTemperature.push({
                            name: result[device].device,
                            data: temperature,
                            type: 'spline',
                            zIndex: 1,
                            /*marker: {
                             fillColor: 'white',
                             lineWidth: 2,
                             lineColor: Highcharts.getOptions().colors[0]
                             }*/
                        }, {
                            name: 'Range',
                            data: temperatureRange,
                            type: 'areasplinerange',
                            lineWidth: 0,
                            linkedTo: ':previous',
                            color: Highcharts.getOptions().colors[device],
                            fillOpacity: 0.3,
                            zIndex: 0
                        });
                    }
                    //alert(JSON.stringify(series));

                    $('#container1').highcharts({
                        title: {
                            text: 'Relative Luftfeuchtigkeit'
                        },
                        xAxis: {
                            type: 'datetime'
                        },
                        yAxis: {
                            min: 0.0,
                            max: 100.0,
                            tickInterval: 20.0,
                            title: {
                                align: 'high',
                                rotation: 0,
                                text: '%',
                                y: -10,
                                offset: 0
                            },
                            plotBands: [{
                                    from: 0.0,
                                    to: 60.0,
                                    color: 'rgba(0, 0, 0, 0)'
                                }, {
                                    from: 60.0,
                                    to: 100.0,
                                    color: 'rgba(255, 0, 0, 0.2)',
                                    label: {
                                        text: 'Schimmelgefahr',
                                        style: {
                                            color: '#606060'
                                        },
                                        verticalAlign: 'bottom',
                                        y: -10
                                    }
                                }]
                        },
                        tooltip: {
                            crosshairs: true,
                            shared: true,
                            valueSuffix: '%'
                        },
                        legend: {
                        },
                        series: seriesHumidity
                    });
                    
                    $('#container2').highcharts({
                        title: {
                            text: 'Temperatur'
                        },
                        xAxis: {
                            type: 'datetime'
                        },
                        yAxis: {
                            min: 0.0,
                            tickInterval: 10.0,
                            title: {
                                align: 'high',
                                rotation: 0,
                                text: '°C',
                                y: -10,
                                offset: 0
                            }
                        },
                        tooltip: {
                            crosshairs: true,
                            shared: true,
                            valueSuffix: '°C'
                        },
                        legend: {
                        },
                        series: seriesTemperature
                    });
                });
            });
        </script>
    </head>
    <body>
        <div id="container1" style="min-width: 310px; height: 500px; margin: 0 auto"></div>
        <div id="container2" style="min-width: 310px; height: 500px; margin: 0 auto"></div>
    </body>
</html>
