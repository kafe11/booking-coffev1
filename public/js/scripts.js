   const ctx = document.getElementById("myChart");
        new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei"],
                datasets: [
                    {
                        label: "Pelanggan",
                        data: [5, 9, 7, 12, 10],
                        borderWidth: 2
                    }
                ]
            }
        });