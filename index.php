<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>ACTAS CUIM - RESUMEN GRAFICO</title>
    <!-- Favicon-->
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="d3/dc.css">

</head>
<body class="theme-red">
    <div class="container">
        <div class="col-lg-4">
            <div class="panel">
                <div id="periodos">
                   <h1>Meses <button class="btn btn-primary" id="btnQuitarFiltros">[Quitar Filtros]</button> </h1>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Totales por Origen del Período: <strong><span id="vPeriodo">Completo</span></strong></h3>
                </div>
                <div class="panel-body">
                    <div class="col-lg-8">
                        <h4>Directas e Indirectas: </h4>    
                    </div>
                    <div class="col-lg-4">
                        <h4><span id="ChartQTY"></span></h4>
                    </div>
                    <div class="col-lg-8">
                        <h4>Directas: </h4>    
                    </div>
                    <div class="col-lg-4">
                        <h4><span id="ChartQTY1"></span></h4>
                    </div>
                    <div class="col-lg-8">
                        <h4>Indirectas: </h4>    
                    </div>
                    <div class="col-lg-4">
                        <h4><span id="ChartQTY2"></span></h4>
                    </div>
                </div>
                <div class="panel-footer">
                        <h4>Familiares: 
                        <span id="ChartQTY3"></span></h4>
                    
                </div>
            </div>
        </div>
             
    </div>

<script src="bootstrap/plugins/jquery/jquery.min.js"></script>


<script src="http://d3js.org/d3.v3.js"></script>
<script type="text/javascript" src="d3/crossfilter.js"></script>
<script type="text/javascript" src="d3/dc.js"></script>
    
<!-- bootstrap Core Js -->
<script src="bootstrap/plugins/bootstrap/js/bootstrap.js"></script>

<script type="text/javascript">
localeFormatter = d3.locale({
                "decimal": ",",
                "thousands": ".",
                "grouping": [3],
                "currency": ["$ ", ""],
                "dateTime": "%a, %e %b %Y, %X",
                "date": "%d.%m.%Y",
                "time": "%H:%M:%S",
                "periods": ["", ""],
               "days": ["domingo", "lunes","martes","miércoles","jueves","viernes","sábado"],
               "shortDays": ["dom", "lun", "mar", "mie", "jue", "vie", "sab"],
               "months": ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"],
               "shortMonths": ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"]
            });

//var ChartQTY =  dc.numberDisplay('#ChartQTY');
var periodos = dc.rowChart("#periodos")
var periodosDimension, periodosGroup;
var numberFormat = d3.format(',.0f');
var totalIndirectos;
var qtyTotal;
var originales


$(document).ready(function(){
    vurl="datos/datos.json"
    //vurl="https://www.bahia.gob.ar/panel01/detalles/ayudasm/datos/dmapa1.php"
    var datos = new Array()
    d3.json(vurl,function(error, arreglo) {
        
        arreglo.mensual.forEach(function(e) {
            e.cantidad=parseInt(e.I)+parseInt(e.D)+parseInt(e.DI)
            e.periodo=parseInt(e.ANO)*100+parseInt(e.MES)
            datos.push(e)
        })
        
        ndx = crossfilter(datos);

        periodosDimension=ndx.dimension(function(d) {return d.periodo;})
        periodosGroup = periodosDimension.group().reduceSum(function(d) {
                return +d.cantidad;
            });

        qtyTotal=ndx.groupAll().reduce(
          function (p,d) {
                p.qty +=parseInt(parseInt(d.I));
                return p;
              },
              function (p,d) {
                  p.qty -=parseInt(parseInt(d.I));

                return p;
              },
         function () { return {qty:0}; }
        );
        totalIndirectos  = function(d) {return d.qty;};

        /*ChartQTY
          .formatNumber(localeFormatter.numberFormat(",.0f"))
          .valueAccessor(totalIndirectos)
          .group(qtyTotal);*/
        originales=arreglo.periodo[0];

        $('#ChartQTY').html(numberFormat(originales.DI))
        $('#ChartQTY1').html(numberFormat(originales.D))
        $('#ChartQTY2').html(numberFormat(originales.I))
        $('#ChartQTY3').html(numberFormat(originales.F))


        var vtope = (periodosGroup.top(Infinity).length+1) * 40
        periodos
            .width(400)
            .group(periodosGroup)
            .elasticX(true)
            .height(vtope)
            .dimension(periodosDimension)
            .label(function (p) {
                return p.key+ ' ( ' +numberFormat(p.value)+'  )';
            })
             .title(function (p) {
                return numberFormat(p.value);
             })
            .controlsUseVisibility(true);

        dc.renderAll();

        periodos.renderlet(function(chart) {
            
            dc.events.trigger(function() {
                //conceptoVisArray = chart.filters()
                if (periodos.dimension().top(Infinity).length==1){
                    $('#vPeriodo').html(periodos.dimension().top(Infinity)[0].MES+" - "+periodos.dimension().top(Infinity)[0].ANO)
                    $('#ChartQTY').html(numberFormat(periodos.dimension().top(Infinity)[0].DI))
                    $('#ChartQTY1').html(numberFormat(periodos.dimension().top(Infinity)[0].D))
                    $('#ChartQTY2').html(numberFormat(periodos.dimension().top(Infinity)[0].I))   
                    $('#ChartQTY3').html(numberFormat(periodos.dimension().top(Infinity)[0].FAMILIARES))   

                }
            });
                if (periodos.filters().length>0){
                    dc.filterAll()
                    //.renderAll()
                    //$('#ChartQTY').html(numberFormat(originales.DI))
                    //$('#ChartQTY1').html(numberFormat(originales.D))
                    //$('#ChartQTY2').html(numberFormat(originales.I))
                    //$('#ChartQTY3').html(numberFormat(originales.F))
                }
            
        
        })
    

            
        })
        
    $("#btnQuitarFiltros").click(function(){
        
        $('#ChartQTY').html(numberFormat(originales.DI))
        $('#ChartQTY1').html(numberFormat(originales.D))
        $('#ChartQTY2').html(numberFormat(originales.I))
        $('#ChartQTY3').html(numberFormat(originales.F))
        dc.filterAll()
        dc.renderAll()
        
    })


    })


</script>
    
</body>
</html>
