<canvas id="ports-to-vlans"></canvas>
<script>
    setTimeout(() => {
        new Chart(document.getElementById('ports-to-vlans'), {
        type: 'pie',
        data: {
            datasets: [{
                label: 'Ports',
                data: @json($portsToVlans[1]),
            }],
            labels: @json($portsToVlans[0]),
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
    }, 500);

</script>
