<canvas id="clients-to-vlans"></canvas>
<script>
    new Chart(document.getElementById('clients-to-vlans'), {
        type: 'pie',
        data: {
            datasets: [{
                label: 'Clients',
                data: @json($clientsToVlans[1]),
            }],
            labels: @json($clientsToVlans[0]),
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
