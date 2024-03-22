<template>
  <div class="register-container">
    <h2 style="text-align: center;">Inscription</h2>
    <form @submit.prevent="register" class="register-form">
      <input type="hidden" name="csrf_token" :value="csrfToken">

      <div class="form-group">
        <label for="email">Email :</label>
        <input id="email" v-model="email" type="email" required>
      </div>
      <div class="form-group">
        <label for="username">Nom d'utilisateur :</label>
        <input id="username" type="username" v-model="username" required>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe :</label>
        <input id="password" v-model="password" type="password" required>
      </div>
      <div class="button-container">
        <button type="submit" class="btn btn-primary">S'inscrire</button>
        <a href="/login" class="btn btn-secondary">Déjà un compte ?</a>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      email: '',
      username: '',
      password: '',
      errors: [],
      csrfToken: '',
      action:''  
    };
  },
  mounted() {
    // Récupérez le jeton CSRF lors du chargement du composant
    this.fetchCsrfToken();
  },

  methods: {
    async fetchCsrfToken() {
        // Effectuez une requête pour récupérer le jeton CSRF du serveur
         const uniqueId = 'register' + Math.random().toString(36).substr(2, 9);

        try {
          const response = await axios.get('http://192.168.90.3/api/csrf-token/' + uniqueId);
          this.csrfToken = response.data.csrfToken; 
          this.action = uniqueId; 

        } catch (error) {
          console.error('Erreur lors de la récupération du jeton CSRF :', error);
        }
      },
    async register() {
      try {
        const response = await axios.post('http://192.168.90.3/api/register', {
          email: this.email,
          username: this.username,
          password: this.password,
          csrf_token: this.csrfToken,
          action: this.action,
        }, {
          headers: {
            'Content-Type': 'application/json',
          }
        });
        
        // Utiliser SweetAlert2 pour une alerte de succès
        Swal.fire({
          title: 'Inscription réussie!',
          text: 'Vous êtes maintenant inscrit.',
          icon: 'success',
          confirmButtonText: 'OK'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = '/login'; // Redirigez vers la page de connexion après la confirmation
          }
        });

        this.errors = []; // Réinitialiser les erreurs
      } catch (error) {
        console.error(error);
        let errorMessage = 'Une erreur inattendue est survenue.';
        if (error.response && error.response.data && error.response.data.errors) {
          // Concaténer tous les messages d'erreur en une seule chaîne
          errorMessage = Object.values(error.response.data.errors).join('\n');
          this.errors = Object.values(error.response.data.errors);
        } else if (error.response && error.response.data && error.response.data.message) {
          // Utiliser le message d'erreur général si disponible
          errorMessage = error.response.data.message;
          this.errors = [error.response.data.message];
        }
        
        // Utiliser SweetAlert2 pour afficher l'erreur
        Swal.fire({
          title: 'Erreur!',
          text: errorMessage,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    }
  }
}
</script>

<style scoped>
.register-container {
  max-width: 400px;
  margin: auto;
}

.register-form {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.btn {
  cursor: pointer;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  text-align: center;
  transition: background-color 0.3s;
}

.btn-primary {
  background-color: #007bff;
  color: #fff;
}

.btn-secondary {
  background-color: #6c757d;
  color: #fff;
}

.button-container {
  display: flex;
  justify-content: space-between;
}

.btn-primary:hover,
.btn-secondary:hover {
  opacity: 0.8;
}
</style>
