<template>
    <div>
      <canvas ref="chartContainer" class="bar-chart"></canvas>
      <p>La bonne réponse est :</p>
      <p v-if="chartData.correctOption" style="color: green;">{{ chartData.correctOption }}</p>
      <hr>

    </div>
  </template>
  
  <script>
  import { Chart, registerables } from 'chart.js';
  Chart.register(...registerables);
  
  export default {
    props: {
      chartData: {
        type: Object,
        required: true,
      },
      options: {
        type: Object,
        default: () => {},
      },
    },
    mounted() {
      console.log('Mounted! Ref:', this.$refs.chartContainer); // Vérifier que la référence est définie
      if (this.$refs.chartContainer) {
        this.createChart();
      } else {
        console.error('Référence non définie:', this.$refs.chartContainer);
      }
    },
    methods: {
      createChart() {
        const ctx = this.$refs.chartContainer.getContext('2d');
        if (!ctx) {
          console.error('Contexte non trouvé:', ctx);
          return;
        }
        new Chart(ctx, {
          type: 'bar',
          data: this.chartData,
          options: this.options,
        });
      },
    },
  }
  </script>
  
  <style>
  .bar-chart {
    /* Ajustez selon vos besoins */
    width: 100%;
    height: 400px;
  }
  </style>
  