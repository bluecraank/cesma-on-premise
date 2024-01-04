<canvas id="ports-online"></canvas>
<script>
    new Chart(document.getElementById('ports-online'), {
        type: 'pie',
        data: {
            datasets: [{
                label: 'Ports',
                data: @json($portsOnline[1]),
            }],
            labels: @json($portsOnline[0]),
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                    text: 'Chart.js Pie Chart'
                }
            }
        },
    });
</script>
