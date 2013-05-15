<script src="js/jquery-1.8.1.js"></script>
<script src="js/jquery.flot.js"></script>

<div id="chartsWraper">
    <div id="mainContainer" style="width: 1200px; height: 500px"></div>
</div>

<script>
    $(function(){
        function ProfileChart() {
            var self = this;

            this.mainContainer = $('#mainContainer');
            this.mainPlot = null;
            this.data = {};

            /*charts*/
            this.load = function(data) {
                this.data = data;
            }

            this.plot = function() {
                var options = {
                    legend: {
                        show:false
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
                    [self.data],
                    options
                );
            }
        }


        $.chart = new ProfileChart();
        $.chart.load(<?php echo json_encode($chartDataSet); ?>);
        $.chart.plot();
    });
</script>