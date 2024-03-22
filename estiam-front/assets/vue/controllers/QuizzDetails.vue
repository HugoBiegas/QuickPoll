<template>
  <div class="quizz-container">
    <h1 class="quizz-title">{{ quizzTitle }}</h1>

    <div v-for="(question, qIndex) in quizzQuestions" :key="question.id" class="question-container">
      <h2>{{ qIndex + 1 }}. {{ question.text }}
          <!-- Indicateur de réponse correcte ou incorrecte -->
          <span v-if="submitted">
            <span v-if="selectedOptions[qIndex] === question.correct_option" class="correct-mark">&#10003;</span> <!-- "V" vert -->
            <span v-else class="incorrect-mark">&#10060;</span> <!-- Croix rouge -->
          </span>
      </h2>
      <div class="options-container">
                <div v-for="option in filteredOptions(question.options)" :key="option.letter"
                class="option"
                :class="{
                  'selected': !submitted && selectedOptions[qIndex] === option.letter,
                  'correct': submitted && question.correct_option === option.letter,
                  'incorrect': submitted && selectedOptions[qIndex] === option.letter && question.correct_option !== option.letter,
                }"
                @click="!submitted && selectOption(qIndex, option.letter)">
            {{ option.letter.toUpperCase() }}: {{ option.text }}
          </div>
      </div>
    </div>
    <input type="hidden" name="csrf_token" :value="csrfToken">

    <button @click="submitAnswers" :disabled="submitted">{{ submitted ? 'Voir les résultats' : 'Soumettre les réponses' }}</button>

    <div  v-if="!submitted" class="progress-container">
      <div class="progress-bar" :style="{ width: progressWidth }"></div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      quizzId: '',
      quizzTitle: '',
      quizzQuestions: [],
      selectedOptions: [],
      submitted: false,
      correctAnswersCount: 0,
      csrfToken: '',
      action: ''
    };
  },
  async created() {
    const uniqueId = 'showQuizz'+ Math.random().toString(36).substr(2, 9);

    try {
      const response = await axios.get('http://192.168.90.3/api/csrf-token/'+ uniqueId);
      this.csrfToken = response.data.csrfToken;
      this.action = uniqueId;

    } catch (error) {
      console.error('Erreur lors de la récupération du jeton CSRF :', error);
    }
  },

  methods: {
        updateProgressBar() {
        const numSelectedAnswers = this.selectedOptions.filter(option => option !== null).length;
        const numTotalQuestions = this.quizzQuestions.length;
        const percentSelected = numSelectedAnswers / numTotalQuestions;

        // Calculer la couleur intermédiaire entre le rouge et le vert en fonction du pourcentage
        const color1 = { r: 255, g: 0, b: 0 }; // couleur de départ (rouge)
        const color2 = { r: 0, g: 255, b: 0 }; // couleur d'arrivée (vert)
        const interpolatedColor = {
          r: Math.round(color1.r + (color2.r - color1.r) * percentSelected),
          g: Math.round(color1.g + (color2.g - color1.g) * percentSelected),
          b: Math.round(color1.b + (color2.b - color1.b) * percentSelected),
        };
        const colorString = `rgb(${interpolatedColor.r}, ${interpolatedColor.g}, ${interpolatedColor.b})`;

        // Définir la couleur de la barre de progression
        const progressBar = document.querySelector('.progress-bar');
        progressBar.style.backgroundColor = colorString;
      },
      filteredOptions(options) {
        return Object.entries(options)
          .filter(([key, value]) => value.trim() !== "")
          .map(([key, value]) => ({ letter: key, text: value }));
      },

      async fetchQuizzData() {
        const pathSegments = window.location.pathname.split('/');
        const quizzId = pathSegments[pathSegments.length - 1];

        try {
          const response = await axios.get(`http://192.168.90.3/api/quizz/special/${quizzId}`);
          if (response.data && response.data.title && response.data.questions) {
            this.quizzTitle = response.data.title;
            this.quizzQuestions = response.data.questions;
            this.selectedOptions = new Array(response.data.questions.length).fill(null);
          } else {
            // Redirection vers la page d'accueil si aucune donnée valide n'est récupérée
            window.location.href = '/homePage';
          }
        } catch (error) {
          if (error.response && error.response.status === 401) {
            window.location.href = '/login';
          }
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Impossible de récupérer les données du quizz.',
          }).then(() => {
            window.location.href = '/homePage'; // Redirection vers la page d'accueil après l'alerte
          });
        }
      },

      async submitAnswers() {
      // Vérifie si toutes les questions ont une réponse sélectionnée
      const allQuestionsAnswered = this.selectedOptions.every(option => option !== null);

      if (!allQuestionsAnswered) {
        // Si une ou plusieurs questions n'ont pas de réponse, affiche une alerte
        Swal.fire({
          icon: 'warning',
          title: 'Attention',
          text: 'Veuillez répondre à toutes les questions avant de soumettre vos réponses.',
        });
        return; // Arrête l'exécution de la fonction ici
      }

      try {
        // Création d'un tableau d'objets contenant l'ID de la question et l'option sélectionnée
        const formattedAnswers = this.quizzQuestions.map((question, index) => {
          return { questionId: question.id, selectedOption: this.selectedOptions[index] };
        });

        const { value: showResults } = await Swal.fire({
          title: 'Souhaitez-vous voir les résultats ?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Oui',
          cancelButtonText: 'Non',
        });

        // Enregistrement des réponses
        await axios.post(`http://192.168.90.3/api/quizz/${this.quizzId}/submit`, {
          answers: formattedAnswers,
          csrf_token: this.csrfToken,
          action: this.action
        });

        if (showResults) {
          this.submitted = true;

          // Calcul du nombre de réponses correctes
          const correctCount = formattedAnswers.filter(answer =>
            this.quizzQuestions.find(question => question.id === answer.questionId).correct_option === answer.selectedOption
          ).length;

          // Mise à jour du nombre de réponses correctes
          this.correctAnswersCount = correctCount;

          // Affichage des résultats
          Swal.fire({
            icon: 'success',
            title: 'Succès',
            html: `Réponses soumises avec succès !<br>Bonnes réponses : ${correctCount}<br>Mauvaises réponses : ${formattedAnswers.length - correctCount}`,
          });
        } else {
          // Redirection vers la page d'accueil si l'utilisateur ne souhaite pas voir les résultats
          window.location.href = '/homePage';
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Une erreur est survenue lors de la soumission des réponses.',
        });
      }
    },

    selectOption(questionIndex, optionLetter) {
      this.selectedOptions.splice(questionIndex, 1, optionLetter);
      this.updateProgressBar(); // Mettre à jour la barre de progression
    }
  },
  computed: {
    progressWidth() {
      const progress = (this.selectedOptions.filter(option => option !== null).length / this.quizzQuestions.length) * 100;
      return `${progress}%`;
    }
  },
  created() {
    this.quizzId = window.location.pathname.split('/').pop();
    this.fetchQuizzData();
  }
};
</script>

<style scoped>
.quizz-container {
  max-width: 800px;
  margin: 0 auto;
  font-family: Arial, sans-serif;
}

.quizz-title {
  text-align: center;
  margin-top: 50px;
  margin-bottom: 50px;
  font-size: 36px;
  color: #333;
}

.question-container {
  margin-bottom: 50px;
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 10px;
  background-color: #f9f9f9;
}

.options-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.option {
  border: 1px solid #ccc;
  padding: 10px;
  margin: 5px;
  cursor: pointer;
  text-align: left;
  border-radius: 5px;
  background-color: #fff;
}

.option.selected {
  background-color: #ccc;
}

.option.correct {
  background-color: lightgreen;
}

.option.incorrect {
  background-color: lightcoral;
}

.correct-mark {
  color: green;
  margin-left: 10px;
}

.incorrect-mark {
  color: red;
  margin-left: 10px;
}

.results-container {
  margin-bottom: 50px;
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 10px;
  background-color: #f9f9f9;
}

.progress-container {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 20px;
  background-color: #f2f2f2;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 50px;
}


.progress-bar {
  height: 100%;
  background-color: #4caf50;
  border-radius: 10px;
  transition: width 0.25s ease-in-out;
}

button {
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  cursor: pointer;
  font-size: 16px;
  margin-bottom: 100px;
  transition: background-color 0.25s ease-in-out;
  display: block; /* Afficher le bouton comme un élément de bloc */
  margin-left: auto; /* Définir la marge gauche à auto */
  margin-right: auto; /* Définir la marge droite à auto */
}

button:hover {
  background-color: #3e8e41;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
