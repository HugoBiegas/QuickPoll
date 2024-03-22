<template>
  <div>
    <h2>Créer un Quizz</h2>
    <input v-model="title" placeholder="Titre du quizz" required class="quizz-title-input" />
    <div v-for="(question, index) in questions" :key="index" class="question-container div-style">
      <input v-model="question.text" placeholder="Question" required class="question-input" />
      <div v-for="optionKey in Object.keys(question.options)" :key="optionKey" class="option-container div-style">
        <input v-model="question.options[optionKey]" :placeholder="`Option ${optionKey.toUpperCase()}`" required class="option-input" />
        <div v-if="question.options[optionKey] !== null && question.options[optionKey] !== ''">
          <input type="radio" :id="`option_${index}_${optionKey}`" :name="'correct_option_' + index" :value="optionKey" v-model="question.correct_option" class="hidden-checkbox" />
          <label :for="`option_${index}_${optionKey}`" class="checkbox-label"></label>
        </div>
      </div>
      <button v-if="index > 0" @click="removeQuestion(index)" class="remove-question-btn">Supprimer la question</button>
    </div>
    
    <!-- Conteneur pour les boutons centraux -->
    <div class="buttons-container">
      <button @click="addQuestion" class="add-question-btn">Ajouter une question</button>
      <button @click="validateAndSubmitQuizz" class="submit-quizz-btn">Soumettre le quizz</button>
      <input type="hidden" name="_token" :value="csrfToken">

    </div>
  </div>
</template>




<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      title: '',
      questions: [
        { text: '', options: { a: '', b: '', c: '', d: '' }, correct_option: null }
      ],
      csrfToken: '',
      action: ''
    };
  },
  async created() {
    const uniqueId = 'creatQuizz'+ Math.random().toString(36).substr(2, 9);

    try {
      const response = await axios.get('http://192.168.90.3/api/csrf-token/'+ uniqueId);
      this.csrfToken = response.data.csrfToken;
      this.action = uniqueId;

    } catch (error) {
      console.error('Erreur lors de la récupération du jeton CSRF :', error);
    }
  },

  methods: {
    addQuestion() {
      this.questions.push({
        text: '', 
        options: { a: '', b: '', c: '', d: '' }, 
        correct_option: null
      });
    },
    removeQuestion(index) {
      if (this.questions.length > 1) {
        this.questions.splice(index, 1);
      }
    },
      validateAndSubmitQuizz() {
    let missingFields = [];
    if (this.title.trim() === '') {
      missingFields.push(`titre du quizz`);
    }

    this.questions.forEach((q, index) => {
      if (q.text.trim() === '') {
        missingFields.push(`texte de la question ${index + 1}`);
      }

      // Vérifier qu'au moins une option n'est pas vide
      const optionsFilled = Object.values(q.options).filter(option => option.trim() !== '');
      if (optionsFilled.length === 0) {
        missingFields.push(`au moins une option pour la question ${index + 1}`);
      }

      // Vérifier que l'option correcte n'est ni null, ni une chaîne vide, et qu'elle correspond à une des options remplies
      if (!q.correct_option || q.options[q.correct_option].trim() === '') {
        missingFields.push(`option correcte valide pour la question ${index + 1}`);
      }
    });

    if (missingFields.length > 0) {
      Swal.fire({
        icon: 'error',
        title: 'Champs manquants',
        text: `Veuillez remplir les champs suivants: ${missingFields.join(', ')}.`
      });
    } else {
      this.submitQuizz();
    }
  },
    async submitQuizz() {
      const userId = this.getCookieValue('userId');

      const quizz = {
        csrf_token: this.csrfToken,
        action: this.action,
        title: this.title,
        question: this.questions.map(q => ({
          title: q.text,
          options: q.options,
          correct_option: q.correct_option
        })),
        user_id: userId
      };
      try {
        const response = await axios.post('http://192.168.90.3/api/quizz', quizz);
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Quizz soumis avec succès!'
        });
        this.title = '';
        this.questions = [{ text: '', options: { a: '', b: '', c: '', d: '' }, correct_option: null }];
      } catch (error) {
        if (error.response && error.response.status === 401) {
          window.location.href = '/login';
        }
        
        console.error('Erreur lors de la soumission du quizz:', error);
        Swal.fire({
          icon: 'error',
          title: 'Erreur lors de la soumission du quizz',
          text: error.response.data.message || 'Une erreur est survenue. Veuillez réessayer.'
        });

      }
    },
    getCookieValue(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    },
  }
};
</script>

<style scoped>
.div-style {
  font-family: 'Arial', sans-serif;
  max-width: 800px;
  margin: 20px auto;
  padding: 20px;
  background-color: #FFFFFF;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
  border-radius: 8px;
}
.remove-question-btn {
  background-color: #FF6347;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 10px 20px;
  font-size: 16px;
  margin-top: 10px;
  display: block; /* Changement pour permettre le centrage */
  margin: 10px auto; /* Centrer le bouton dans le conteneur */
}
h2 {
  color: #4A90E2;
  text-align: center;
  margin-bottom: 30px;
}

.quizz-title-input {
  display: block;
  width: 100%;
  margin: 0 auto 20px auto;
  padding: 10px;
  border: 2px solid #CCCCCC;
  border-radius: 20px;
  box-sizing: border-box;
  font-size: 18px;
}

.quizz-title-input:focus {
  border-color: #4A90E2;
  outline: none;
}

.question-input, .option-input {
  width: calc(100% - 22px);
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #CCCCCC;
  border-radius: 5px;
  box-sizing: border-box;
}

.question-input:focus, .option-input:focus {
  border-color: #4A90E2;
  outline: none;
}

.question-container {
  border: 1px solid #e2e2e2;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 5px;
  background-color: #f9f9f9;
}

.option-container {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.hidden-checkbox {
  display: none;
}

.checkbox-label {
  display: inline-block;
  cursor: pointer;
}

.checkbox-label:before {
  content: '';
  display: inline-block;
  width: 20px;
  height: 20px;
  position: relative;
  top: 0;
  margin-right: 10px;
  border: 2px solid #CCCCCC;
  border-radius: 4px;
}

input[type="radio"]:checked + .checkbox-label:before {
  content: '✓';
  font-size: 18px;
  text-align: center;
  line-height: 18px;
  border-color: #4A90E2;
}

.remove-question-btn, .add-question-btn, .submit-quizz-btn {
  background-color: #4A90E2;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 10px 20px;
  font-size: 16px;
  margin-top: 10px;
}

.remove-question-btn:hover, .add-question-btn:hover, .submit-quizz-btn:hover {
  background-color: #357ABD;
}

.question-container + .question-container {
  border-top: 2px solid #4A90E2;
}
.buttons-container {
  text-align: center;
  margin-top: 20px;
}

.add-question-btn {
  background-color: #4CAF50; /* Vert */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 10px 20px;
  font-size: 16px;
  display: inline-block; /* Pour permettre le centrage */
  margin-right: 10px; /* Espace entre les boutons */
}

.submit-quizz-btn {
  background-color: #2196F3; /* Bleu */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 10px 20px;
  font-size: 16px;
  display: inline-block; /* Pour permettre le centrage */
}

.add-question-btn:hover {
  background-color: #3E8E41; /* Vert foncé */
}

.submit-quizz-btn:hover {
  background-color: #0B7DDA; /* Bleu foncé */
}

</style>