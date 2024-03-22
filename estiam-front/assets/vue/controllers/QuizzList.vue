<template>
  <div>
    <h2>Mes Quizz  ({{ quizzes.length }})</h2>
    <div v-for="(quizz, index) in paginatedQuizzes" :key="quizz.id" class="quizz-item">
        <div class="quizz-content">
          <a :href="`/showQuizz/${quizz.id}`" class="show-quizz-btn">
            <button class="show-quizz-btn">←</button>
          </a>
          <a :href="`/modifQuizz/${quizz.id}`" class="quizz-link">
            <span class="quizz-title">{{ quizz.title }}</span>
          </a>
          <div>
            <button @click="shareQuizz(quizz.id)" class="share-btn">Share</button>
            <button @click.stop="deleteQuizz(quizz.id)" class="delete-btn">🗑</button>
          </div>
        </div>
    </div>
    <!-- Boutons centraux pour la navigation -->
    <div v-if="quizzes.length >= 6" class="navigation-buttons">
      <button @click="prevPage">← </button>
      <button @click="nextPage">→</button>
    </div>

  </div>
</template>


<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      quizzes: [],
      currentPage: 1,
      itemsPerPage: 5,
    };
  },
  computed: {
    paginatedQuizzes() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = this.currentPage * this.itemsPerPage;
      return this.quizzes.slice(start, end);
    },
  },
  methods: {
    getCookieValue(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    },
    async deleteQuizz(quizzId) {
      try {
        await axios.delete(`http://192.168.90.3/api/quizz/${quizzId}`);
        // Supprime le quizz de la liste affichée sans recharger la page
        this.quizzes = this.quizzes.filter(quizz => quizz.id !== quizzId);
        Swal.fire('Supprimé!', 'Le quiz a été supprimé.', 'success');
      } catch (error) {
        Swal.fire('Erreur', 'Un problème est survenu lors de la suppression du quiz.', 'error');
      }
    },
    async shareQuizz(quizzId) {
      try {
        const quizzLink = `${window.location.origin}/showQuizz/${quizzId}`;
        const isConfirmed = await Swal.fire({
          title: 'Partager le quiz',
          html: `
            <p>Voici le lien pour partager le quiz :</p>
            <input type="text" value="${quizzLink}" id="quizzLinkInput" readonly style="width: 100%; padding: 10px; margin-top: 10px;">
            `,
          showCancelButton: true,
          confirmButtonText: 'Copier le lien',
          cancelButtonText: 'Fermer',
          showLoaderOnConfirm: true,
          preConfirm: () => {
            const input = document.getElementById('quizzLinkInput');
            input.select();
            document.execCommand('copy');
          },
        });

        if (isConfirmed.isConfirmed) {
          Swal.fire('Partagé!', 'Le lien vers le quiz a été copié.', 'success');
        }
      } catch (error) {
        Swal.fire('Erreur', 'Un problème est survenu lors du partage du lien.', 'error');
      }
    },

    prevPage() {
      if (this.currentPage > 1) this.currentPage--;
    },
    nextPage() {
      if (this.currentPage * this.itemsPerPage < this.quizzes.length) this.currentPage++;
    },
    async fetchQuizzes() {
      const userId = this.getCookieValue('userId');
      if(!userId)
        window.location.href = '/login';
      try {
        const response = await axios.get(`http://192.168.90.3/api/user/${userId}/quizzes`);
        this.quizzes = response.data;
      } catch (error) {
        console.error(error);
        if (error.response && error.response.status === 401) {
          window.location.href = '/login';
        }
      }
    },
  },
  async created() {
    await this.fetchQuizzes();
  },
};
</script>

<style>
/* Ajoutez du style pour le bouton de partage */
.share-btn {
  margin-right: 10px; 
  border: none;
  background-color: #007bff; 
  color: white;
  cursor: pointer;
  border-radius: 5px;
  padding: 5px 10px;
}

.share-btn:hover {
  background-color: #0056b3;
}

/* Style restant inchangé */
.quizz-item {
  border: 1px solid #ccc;
  padding: 10px;
  margin: 10px 0;
  position: relative;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.quizz-link {
  text-decoration: none;
  color: black;
  display: block;
}

.quizz-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.quizz-title {
  flex-grow: 1;
}

.quizz-link:hover {
  text-decoration: none;
}

.delete-btn {
  border: none;
  background-color: #ff4d4d;
  color: white;
  cursor: pointer;
  border-radius: 50%;
  padding: 5px 8px;
}

.navigation-buttons {
  text-align: center;
  margin-top: 20px;
}

.navigation-buttons button {
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 10px 15px;
  margin: 0 10px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 5px;
}

.navigation-buttons button:hover {
  background-color: #367c39;
}

</style>
