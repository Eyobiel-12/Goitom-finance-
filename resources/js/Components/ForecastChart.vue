<template>
  <div class="relative">
    <canvas ref="chartCanvas" class="w-full h-64"></canvas>
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/80">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  forecastData: Object,
  loading: Boolean,
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
  if (!props.forecastData || !props.forecastData.forecast || !chartCanvas.value) return;

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  
  // Prepare data with safe fallbacks
  const forecast = props.forecastData.forecast || {};
  const months = Object.keys(forecast.net_profit || {});
  const incomeData = Object.values(forecast.income || {});
  const expenseData = Object.values(forecast.expenses || {});
  const profitData = Object.values(forecast.net_profit || {});

  if (months.length === 0) return;

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months.map(month => {
        const [year, monthNum] = month.split('-');
        const date = new Date(year, monthNum - 1);
        return date.toLocaleDateString('nl-NL', { month: 'short', year: '2-digit' });
      }),
      datasets: [
        {
          label: 'Inkomsten',
          data: incomeData,
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          tension: 0.4,
          fill: false,
        },
        {
          label: 'Uitgaven',
          data: expenseData,
          borderColor: 'rgb(239, 68, 68)',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4,
          fill: false,
        },
        {
          label: 'Netto Winst',
          data: profitData,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true,
          pointRadius: 6,
          pointHoverRadius: 8,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20,
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function(context) {
              const value = context.parsed.y;
              return context.dataset.label + ': €' + value.toLocaleString('nl-NL', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              });
            }
          }
        }
      },
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: 'Maand'
          }
        },
        y: {
          display: true,
          title: {
            display: true,
            text: 'Bedrag (€)'
          },
          ticks: {
            callback: function(value) {
              return '€' + value.toLocaleString('nl-NL');
            }
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    }
  });
};

watch(() => props.forecastData, () => {
  nextTick(() => {
    createChart();
  });
}, { deep: true });

onMounted(() => {
  if (props.forecastData) {
    createChart();
  }
});
</script>
