<template>
  <div>
    <h2>Statistiques des Quizz ({{ quizzes.length }})</h2>
    <div v-for="(quiz, index) in quizzes" :key="quiz.id" class="quiz-statistics">
      <h3 @click="toggleQuiz(index)" class="quiz-title">
        {{ quiz.title }}
        <span class="toggle-arrow">{{ isQuizOpen(index) ? '▲' : '▼' }}</span>
      </h3>
      <div v-if="isQuizOpen(index)">
        <!-- Affiche un diagramme en bâton pour chaque question du quiz -->
        <bar-chart v-for="(question, qIndex) in quiz.questions" :key="qIndex" :chart-data="prepareChartData(question)"></bar-chart>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BarChart from './BarChart.vue'; // Assurez-vous que ce composant est correctement configuré

export default {
  components: {
    BarChart, // Enregistrement du composant BarChart
  },
  data() {
    return {
      quizzes: [], // Données des quizzes initialisées à vide
      openQuizzes: [], // Tableau pour suivre quels quizz sont ouverts ou fermés
    };
  },
  created() {
    this.loadQuizzes(); // Chargement des données des quizzes dès la création du composant
  },
  methods: {
    async loadQuizzes() {
      const userId = this.getCookieValue('userId');
      if (!userId) window.location.href = '/login';

      try {
        const response = await axios.get(`http://192.168.90.3/api/user/${userId}/quizz/stats`);
        this.quizzes = response.data;
        this.openQuizzes = new Array(this.quizzes.length).fill(false); // Initialise tous les quizz enroulés
      } catch (error) {
        if (error.response && error.response.status === 401) {
          window.location.href = '/login';
        }
        console.error('Erreur lors du chargement des quizzes:', error);
      }
    },
    getCookieValue(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    },
    prepareChartData(question) {
      const responseOptions = ['A', 'B', 'C', 'D']; // Tableau ordonné des réponses possibles
      const labels = responseOptions.map(option => question.options[option]);
      const data = responseOptions.map(option => question.responses[option] || 0);
      const correctOptionUpperCase = question.correctOption.toUpperCase(); // Convertit correctOption en majuscules

      const correctOption = question.options[correctOptionUpperCase]; // Récupère la bonne réponse

      return {
        labels: labels, // Utilise le texte des réponses comme labels
        datasets: [{
          label: question.text,
          data: data,
          backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#E7E9ED'], // Couleurs pour chaque barre
        }],
        correctOption: correctOption // Ajoute la bonne réponse au dataset
      };
    },
    toggleQuiz(index) {
      // Inverse l'état du quiz (ouvert/fermé)
      this.openQuizzes[index] = !this.openQuizzes[index];
    },
    isQuizOpen(index) {
      // Vérifie si le quiz est ouvert ou fermé
      return this.openQuizzes[index];
    }
  }
};
</script>

<style>
.quiz-statistics {
  margin-bottom: 20px;
}

.quiz-title {
  cursor: pointer;
}

.toggle-arrow {
  margin-left: 5px;
}

.bar-chart {
  /* Ajustez selon vos besoins */
  width: 100%;
  height: 400px;
}
</style>
