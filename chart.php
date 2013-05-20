<script src="js/jquery-1.8.1.js"></script>
<script src="js/jquery.flot.js"></script>


<script>
    $(function(){
        $.ProfileChart = function(container) {
            var self = this;

            this.mainContainer = container;
            this.mainPlot = null;
            this.data = {};

            /*charts*/
            this.load = function(data) {
                this.data = data;
            }

            this.plot = function() {
                var options = {
                    legend: {
                        // show:false
                    },
                    grid: {
                        autoHighlight: true
                    },
                    xaxis: {
                        mode: 'time'
                    }
                };
                //console.log(options, self.type, self.data, _.values(self.data));

                self.mainContainer.empty();
                self.mainPlot = $.plot(
                    self.mainContainer,
                    self.data,
                    options
                );
            }
        }



    });
</script>
<?php
function makeChart($title, $name, $data) {
    echo '<h1>'.$title.'</h1><br/>
    <div id="ch'.$name.'" style="width: 90%; height: 400px"></div>
    <script>
    $(function(){
        $.chart = new $.ProfileChart($("#ch'.$name.'"));
        $.chart.load('. json_encode($data) .');
        $.chart.plot();
    });
    </script><br/>';
}?>