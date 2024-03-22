<template>
  <div>
    <h2>Modifier un Quizz</h2>
    <input v-model="quizz.title" placeholder="Titre du quizz" required class="quizz-title-input" />
    <div v-for="(question, index) in quizz.questions" :key="index" class="question-container div-style">
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
    
    <div class="buttons-container">
      <button @click="addQuestion" class="add-question-btn">Ajouter une question</button>
      <button @click="validateAndSubmitQuizz" class="submit-quizz-btn">Mettre à jour le quizz</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      quizz: {
        id: '',
        title: '',
        questions: []
      }
    };
  },
  async created() {
    const pathSegments = window.location.pathname.split('/');
    const quizzId = pathSegments[pathSegments.length - 1];

    try {
      const response = await axios.get(`http://192.168.90.3/api/quizz/special/${quizzId}`);
      this.quizz = { ...response.data, questions: response.data.questions.map(q => ({ ...q, title: q.title })) };
    } catch (error) {
      if (error.response && error.response.status === 401) {
          window.location.href = '/login';
      }
      console.error('Erreur lors de la récupération du quizz:', error);
      Swal.fire('Erreur', 'Impossible de charger les données du quizz pour modification.', 'error');
    }
  },
  methods: {
    addQuestion() {
      this.quizz.questions.push({
        text: '', 
        options: { a: '', b: '', c: '', d: '' }, 
        correct_option: null
      });
    },
    removeQuestion(index) {
      this.quizz.questions.splice(index, 1);
    },
    validateAndSubmitQuizz() {
  let missingFields = [];
  if (!this.quizz.title || this.quizz.title.trim() === '') {
    missingFields.push('titre du quizz');
  }

  this.quizz.questions.forEach((question, index) => {
    if (!question.text || question.text.trim() === '') {
      missingFields.push(`texte de la question ${index + 1}`);
    }

    const optionsFilled = Object.values(question.options).filter(option => option.trim() !== '');
    if (optionsFilled.length === 0) {
      missingFields.push(`au moins une option pour la question ${index + 1}`);
    }

    if (!question.correct_option || question.options[question.correct_option].trim() === '') {
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
    this.updateQuizz();
  }
},
    async updateQuizz() {
      try {
        await axios.put(`http://192.168.90.3/api/quizz/${this.quizz.id}`, {
          ...this.quizz,
          questions: this.quizz.questions.map(q => ({ 
            title: q.text, 
            options: q.options, 
            correct_option: q.correct_option 
          }))
        });
        console.log(this.quizz);
        Swal.fire('Succès', 'Le quizz a été mis à jour avec succès.', 'success');
      } catch (error) {
        console.error('Erreur lors de la mise à jour du quizz:', error);
        Swal.fire('Erreur', 'La mise à jour du quizz a échoué.', 'error');
      }
    }
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
  margin-left: 10px;
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

