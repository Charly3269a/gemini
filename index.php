<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'auth/check_auth.php';
require_once 'config/database.php';

// Obtener datos para los gráficos
function getFinancialData($pdo, $period = 'week') {
    if ($period === 'week') {
        // Para la semana, obtenemos desde el lunes hasta el domingo
        $sql = "WITH RECURSIVE dates AS (
            SELECT DATE(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)) AS date
            UNION ALL
            SELECT DATE_ADD(date, INTERVAL 1 DAY)
            FROM dates
            WHERE DATE_ADD(date, INTERVAL 1 DAY) <= CURDATE()
        ),
        daily_totals AS (
            SELECT 
                DATE(schedule_date) as date,
                SUM(value) as total_value,
                SUM(expenses) as total_expenses,
                SUM(value - expenses) as profit
            FROM tasks 
            WHERE schedule_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
            GROUP BY DATE(schedule_date)
        )
        SELECT 
            dates.date,
            COALESCE(daily_totals.total_value, 0) as total_value,
            COALESCE(daily_totals.total_expenses, 0) as total_expenses,
            COALESCE(daily_totals.profit, 0) as profit
        FROM dates
        LEFT JOIN daily_totals ON dates.date = daily_totals.date
        ORDER BY dates.date";
    } else {
        // Para el mes, solo necesitamos los totales
        $sql = "SELECT 
            DATE(schedule_date) as date,
            SUM(value) as total_value,
            SUM(expenses) as total_expenses,
            SUM(value - expenses) as profit
        FROM tasks 
        WHERE schedule_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
        GROUP BY DATE(schedule_date)
        ORDER BY date ASC";
    }
    
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

$weeklyData = getFinancialData($pdo, 'week');
$monthlyData = getFinancialData($pdo, 'month');

// Calcular totales
$weeklyTotals = [
    'income' => array_sum(array_column($weeklyData, 'total_value')),
    'expenses' => array_sum(array_column($weeklyData, 'total_expenses')),
    'profit' => array_sum(array_column($weeklyData, 'profit'))
];

$monthlyTotals = [
    'income' => array_sum(array_column($monthlyData, 'total_value')),
    'expenses' => array_sum(array_column($monthlyData, 'total_expenses')),
    'profit' => array_sum(array_column($monthlyData, 'profit'))
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    <?php include 'auth/adduser_modal.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Panel de Control</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Bienvenido, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Usuario'; ?></span>
                <a href="/bolt/auth/logout.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                    </svg>
                    Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Resumen Financiero -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Resumen Semanal -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Resumen Semanal</h2>
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600">Ingresos</p>
                        <p class="text-2xl font-bold text-green-700">$<?php echo number_format($weeklyTotals['income'], 2); ?></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-red-600">Gastos</p>
                        <p class="text-2xl font-bold text-red-700">$<?php echo number_format($weeklyTotals['expenses'], 2); ?></p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600">Ganancia</p>
                        <p class="text-2xl font-bold text-blue-700">$<?php echo number_format($weeklyTotals['profit'], 2); ?></p>
                    </div>
                </div>
                <canvas id="weeklyChart" class="w-full"></canvas>
            </div>

            <!-- Resumen Mensual -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Resumen Mensual</h2>
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600">Ingresos</p>
                        <p class="text-2xl font-bold text-green-700">$<?php echo number_format($monthlyTotals['income'], 2); ?></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-red-600">Gastos</p>
                        <p class="text-2xl font-bold text-red-700">$<?php echo number_format($monthlyTotals['expenses'], 2); ?></p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600">Ganancia</p>
                        <p class="text-2xl font-bold text-blue-700">$<?php echo number_format($monthlyTotals['profit'], 2); ?></p>
                    </div>
                </div>
                <canvas id="monthlyChart" class="w-full"></canvas>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Clientes -->
            <a href="/bolt/pages/clients/list.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">CLIENTES</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-gray-600">Gestionar información de clientes</p>
                <div class="mt-4 flex justify-end">
                    <span class="text-blue-600 hover:text-blue-800">Ver más →</span>
                </div>
            </a>

            <!-- Tareas -->
            <a href="/bolt/pages/tasks/list.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">TAREAS</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-gray-600">Ver y gestionar tareas activas</p>
                <div class="mt-4 flex justify-end">
                    <span class="text-green-600 hover:text-green-800">Ver más →</span>
                </div>
            </a>

            <!-- Historial -->
            <a href="/bolt/pages/tasks/archived.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">HISTORIAL</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <p class="text-gray-600">Consultar tareas completadas</p>
                <div class="mt-4 flex justify-end">
                    <span class="text-yellow-600 hover:text-yellow-800">Ver más →</span>
                </div>
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="/bolt/assets/js/main.js"></script>
    <script>
        // Preparar datos para los gráficos
        const rawWeeklyData = <?php echo json_encode($weeklyData); ?>;
        const rawMonthlyData = <?php echo json_encode($monthlyData); ?>;

        // Convertir datos a acumulativos
        function convertToCumulative(data) {
            let cumulativeIncome = 0;
            let cumulativeExpenses = 0;
            let cumulativeProfit = 0;
            
            return data.map(item => {
                cumulativeIncome += parseFloat(item.total_value);
                cumulativeExpenses += parseFloat(item.total_expenses);
                cumulativeProfit += parseFloat(item.profit);
                
                return {
                    date: item.date,
                    total_value: cumulativeIncome,
                    total_expenses: cumulativeExpenses,
                    profit: cumulativeProfit
                };
            });
        }

        const weeklyData = convertToCumulative(rawWeeklyData);
        const monthlyData = convertToCumulative(rawMonthlyData);

        // Función para crear gráficos
        function createChart(elementId, data, title, showDates = true) {
            const ctx = document.getElementById(elementId).getContext('2d');
            const dates = data.map(item => {
                const date = new Date(item.date);
                return showDates ? date.toLocaleDateString('es-ES', { weekday: 'short' }) : '';
            });
            
            // Encontrar el valor máximo para ajustar la escala
            const maxIncome = Math.max(...data.map(item => item.total_value));
            const maxExpenses = Math.max(...data.map(item => item.total_expenses));
            const maxProfit = Math.max(...data.map(item => item.profit));
            const maxValue = Math.max(maxIncome, maxExpenses, maxProfit);
            
            // Añadir un 10% de margen a la escala máxima
            const yAxisMax = Math.ceil(maxValue * 1.1);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Ingresos Acumulados',
                            data: data.map(item => item.total_value),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            fill: true,
                            tension: 0.1,
                            borderWidth: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Gastos Acumulados',
                            data: data.map(item => item.total_expenses),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.1,
                            borderWidth: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Ganancia Acumulada',
                            data: data.map(item => item.profit),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.1,
                            borderWidth: 3,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: title,
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('es-AR', {
                                            style: 'currency',
                                            currency: 'ARS'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Ingresos y Gastos',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: yAxisMax,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Ganancia',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: yAxisMax,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }

        // Crear los gráficos cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            createChart('weeklyChart', weeklyData, 'Análisis Semanal Acumulado', true);
            createChart('monthlyChart', monthlyData, 'Análisis Mensual Acumulado', false);
        });
    </script>
</body>
</html>